<?php
/**
 * request-forms.php
 * ---------------------------------------------------------------------
 * Server side of the six public request forms. Each final-step page
 * (wedding-request-step3.php, baptism-request-step2.php,
 * confirmation-request-step4.php, funeral-request-step4.php,
 * mass-intention-request.php, donation-request.php) starts with
 *     ps_handle_request_form('<flow>');
 * and prints ps_request_form_fields() inside its <form>.
 *
 * GET issues a one-time submit token. POST:
 *   1. checks the token -- a double-click or a resubmitted page lands on
 *      the confirmation that already exists instead of a second row
 *   2. validates everything again, the same rules the pages enforce in
 *      the browser: earlier steps' answers come from the hidden ps_draft
 *      JSON (copied from sessionStorage by main.js / funeral-request.js),
 *      the submitting page's own fields from the POST itself
 *   3. refuses a requested time that overlaps a booked request
 *      (ps_request_schedule_errors(); conflict rules in request-types.php)
 *   4. inserts one row with a server-generated <PREFIX>-<YEAR>-<NNNN>
 *      reference number and moves the staged documents
 *      (includes/uploads.php) into uploads/<type>/<reference>/, recording
 *      them in request_documents (or donations.proof_of_payment)
 *   5. redirects (303) to request-confirmation.php?ref=..., or back to the
 *      page, which then lists the problems.
 * What a form collects beyond the table's own columns is kept in the
 * row's `details` JSON ({"Label": "value"}), shown to staff in the admin
 * Update window. No login is needed: requests are tracked by reference
 * number + contact number -- the request tables have no users.id link
 * (database/migrations/001-auth-and-requests.sql).
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/request-types.php';
require_once __DIR__ . '/uploads.php';

const PS_MOBILE_PATTERN = '/^09\d{9}$/';
const PS_NAME_SUFFIXES = ['Jr.', 'Sr.', 'II', 'III'];
const PS_GUARDIAN_RELATIONSHIPS = ['Father', 'Mother', 'Legal Guardian', 'Other'];
const PS_DONATION_FUNDS = [
    'general'    => 'General Parish Fund',
    'building'   => 'Building & Maintenance Fund',
    'outreach'   => 'Outreach & Charity Programs',
    'sacraments' => 'Sacramental Support',
];

/**
 * flow => form definition. The flow key is the request type key
 * (PS_REQUEST_TYPES), or 'donation'.
 *   steps        the wizard's pages; the last one is the page that submits
 *   upload_step  which step's page has the document inputs
 *   schedule_step  which step's page has the date/time a schedule conflict is reported on
 *   nav          sidebar link highlighted on the confirmation page
 *   fields       input name => rule. 'step' (default 0) indexes 'steps'.
 *                type: text (default) | email | mobile | date | time |
 *                checkbox | amount; dates can be 'when' => past|future.
 * Keep these in sync with the pages' own attributes and scripts.
 */
const PS_REQUEST_FORMS = [
    'wedding' => [
        'label' => 'Wedding Request', 'prefix' => 'WED', 'nav' => 'wedding.html',
        'draft_key' => 'parishserve-draft-wedding', 'upload_step' => 1,
        'steps' => ['wedding-request.html', 'wedding-request-step2.html', 'wedding-request-step3.php'],
        'fields' => [
            'groomFirstName'  => ['label' => "Groom's first name", 'required' => true, 'max' => 80],
            'groomMiddleName' => ['label' => "Groom's middle name", 'max' => 80],
            'groomLastName'   => ['label' => "Groom's last name", 'required' => true, 'max' => 80],
            'groomSuffix'     => ['label' => "Groom's suffix", 'options' => PS_NAME_SUFFIXES],
            'brideFirstName'  => ['label' => "Bride's first name", 'required' => true, 'max' => 80],
            'brideMiddleName' => ['label' => "Bride's middle name", 'max' => 80],
            'brideLastName'   => ['label' => "Bride's last name", 'required' => true, 'max' => 80],
            'brideSuffix'     => ['label' => "Bride's suffix", 'options' => PS_NAME_SUFFIXES],
            'weddingDate'     => ['label' => 'Preferred wedding date', 'type' => 'date', 'when' => 'future', 'required' => true],
            'mobileNumber'    => ['label' => 'Mobile number', 'type' => 'mobile', 'required' => true],
            'emailAddress'    => ['label' => 'Email address', 'type' => 'email', 'required' => true],
            'seminarDate'     => ['label' => 'Preferred seminar date', 'type' => 'date', 'when' => 'future', 'required' => true],
            'seminarTime'     => ['label' => 'Preferred seminar time', 'required' => true, 'options' => ['08:00 AM', '09:00 AM', '01:00 PM', '02:00 PM']],
            'seminarLocation' => ['label' => 'Preferred seminar location', 'required' => true, 'options' => ['Parish Hall', 'Main Church', 'Function Room']],
            'officeNotes'     => ['label' => 'Notes for the parish office', 'step' => 1, 'max' => 500],
            'confirmTruthful' => ['label' => 'Confirmation', 'step' => 2, 'type' => 'checkbox', 'required' => true],
        ],
    ],
    'baptism' => [
        'label' => 'Baptism Request', 'prefix' => 'BAP', 'nav' => 'baptism.html',
        'draft_key' => 'parishserve-draft-baptism', 'upload_step' => 1,
        'steps' => ['baptism-request.html', 'baptism-request-step2.php'],
        'fields' => [
            'baptismType'       => ['label' => 'Baptism type', 'required' => true, 'options' => ['regular', 'special']],
            'childFirstName'    => ['label' => "Child's first name", 'required' => true, 'max' => 80],
            'childMiddleName'   => ['label' => "Child's middle name", 'max' => 80],
            'childLastName'     => ['label' => "Child's last name", 'required' => true, 'max' => 80],
            'childSuffix'       => ['label' => "Child's suffix", 'options' => PS_NAME_SUFFIXES],
            'childDob'          => ['label' => "Child's date of birth", 'type' => 'date', 'when' => 'past', 'required' => true],
            'childPlaceOfBirth' => ['label' => 'Place of birth', 'required' => true, 'max' => 150],
            'childGender'       => ['label' => "Child's gender", 'required' => true, 'options' => ['Male', 'Female']],
            'relationship'      => ['label' => 'Relationship to the child', 'required' => true, 'options' => PS_GUARDIAN_RELATIONSHIPS],
            'requestorName'     => ['label' => "Requestor's full name", 'required' => true, 'max' => 150],
            'requestorContact'  => ['label' => 'Contact number', 'type' => 'mobile', 'required' => true],
            'requestorEmail'    => ['label' => 'Email', 'type' => 'email'],
            'baptismDate'       => ['label' => 'Preferred baptism date', 'type' => 'date', 'when' => 'future', 'required' => true],
            'officeNotes'       => ['label' => 'Additional note', 'step' => 1, 'max' => 500],
        ],
    ],
    'confirmation' => [
        'label' => 'Confirmation Application', 'prefix' => 'CNF', 'nav' => 'confirmation.html',
        'draft_key' => 'parishserve-draft-confirmation', 'upload_step' => 2,
        'steps' => ['confirmation-request.html', 'confirmation-request-step2.html', 'confirmation-request-step3.html', 'confirmation-request-step4.php'],
        'fields' => [
            'candidateFirstName'   => ['label' => 'First name', 'required' => true, 'max' => 80],
            'candidateLastName'    => ['label' => 'Last name', 'required' => true, 'max' => 80],
            'candidateMiddleName'  => ['label' => 'Middle name', 'max' => 80],
            'candidateSuffix'      => ['label' => 'Suffix', 'options' => PS_NAME_SUFFIXES],
            'candidateDob'         => ['label' => 'Date of birth', 'type' => 'date', 'when' => 'past', 'required' => true],
            'candidateAddress'     => ['label' => 'Complete address', 'required' => true, 'max' => 255],
            'candidateMobile'      => ['label' => 'Mobile number', 'type' => 'mobile', 'required' => true],
            'candidateEmail'       => ['label' => 'Email address', 'type' => 'email', 'required' => true],
            'parishName'           => ['label' => 'Parish', 'step' => 1, 'required' => true, 'max' => 150],
            'guardianName'         => ['label' => 'Name of parent/guardian', 'step' => 1, 'required' => true, 'max' => 150],
            'schoolName'           => ['label' => 'Name of school', 'step' => 1, 'max' => 150],
            'guardianRelationship' => ['label' => 'Relationship to applicant', 'step' => 1, 'required' => true, 'options' => PS_GUARDIAN_RELATIONSHIPS],
            'gradeLevel'           => ['label' => 'Grade / year level', 'step' => 1, 'options' => ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12', 'College', 'Not in School']],
            'emergencyContact'     => ['label' => 'Emergency contact number', 'step' => 1, 'type' => 'mobile', 'required' => true],
            'firstCommunion'       => ['label' => 'Received First Communion', 'step' => 1, 'required' => true, 'options' => ['Yes', 'No']],
            'sponsorName'          => ['label' => 'Name of sponsor', 'step' => 1, 'max' => 150],
            'sponsorContact'       => ['label' => 'Sponsor contact number', 'step' => 1, 'type' => 'mobile'],
            'parishNotes'          => ['label' => 'Notes / message to parish', 'step' => 1, 'max' => 500],
            'documentNotes'        => ['label' => 'Document note', 'step' => 2, 'max' => 500],
            'confirmAccurate'      => ['label' => 'Confirmation', 'step' => 3, 'type' => 'checkbox', 'required' => true],
        ],
    ],
    'funeral' => [
        'label' => 'Funeral Service Request', 'prefix' => 'FUN', 'nav' => 'funeral.html',
        'draft_key' => 'parishserve-draft-funeral', 'upload_step' => 2, 'schedule_step' => 1,
        'steps' => ['funeral-request.html', 'funeral-request-step2.html', 'funeral-request-step3.html', 'funeral-request-step4.php'],
        'fields' => [
            'familyFirstName'         => ['label' => 'Your first name', 'required' => true, 'max' => 80],
            'familyLastName'          => ['label' => 'Your last name', 'required' => true, 'max' => 80],
            'relationship'            => ['label' => 'Relationship to the deceased', 'required' => true, 'options' => ['Spouse', 'Son', 'Daughter', 'Parent', 'Sibling', 'Relative', 'Other']],
            'familyMobile'            => ['label' => 'Mobile number', 'type' => 'mobile', 'required' => true],
            'familyEmail'             => ['label' => 'Email address', 'type' => 'email', 'required' => true],
            'deceasedName'            => ['label' => 'Name of the deceased', 'required' => true, 'max' => 160],
            'dateOfDeath'             => ['label' => 'Date of death', 'type' => 'date', 'when' => 'past', 'required' => true],
            'burialArrangement'       => ['label' => 'Burial arrangement', 'required' => true, 'options' => ['Funeral Mass only', 'Funeral Mass + burial assistance', 'Church service only']],
            'serviceType'             => ['label' => 'Requested service', 'step' => 1, 'required' => true, 'options' => ['Funeral Mass', 'Funeral prayer service']],
            'preferredMassDate'       => ['label' => 'Preferred service date', 'step' => 1, 'type' => 'date', 'when' => 'future', 'required' => true],
            'preferredTime'           => ['label' => 'Preferred time', 'step' => 1, 'type' => 'time', 'required' => true],
            'wakeVenue'               => ['label' => 'Wake venue / chapel name', 'step' => 1, 'max' => 200],
            'wakeAddress'             => ['label' => 'Wake address', 'step' => 1, 'max' => 200],
            'burialLocation'          => ['label' => 'Burial place / cemetery', 'step' => 1, 'max' => 200],
            'differentBurialLocation' => ['label' => 'Burial in a different location', 'step' => 1, 'required' => true, 'options' => ['Yes', 'No']],
            'funeralCoordinator'      => ['label' => 'Funeral home / coordinator', 'step' => 1, 'max' => 200],
            'serviceNotes'            => ['label' => 'Additional notes', 'step' => 1, 'max' => 500],
            'documentNotes'           => ['label' => 'Document note', 'step' => 2, 'max' => 500],
            'confirmAccurate'         => ['label' => 'Confirmation', 'step' => 3, 'type' => 'checkbox', 'required' => true],
        ],
    ],
    'massintention' => [
        'label' => 'Mass Intention Request', 'prefix' => 'MI', 'nav' => 'mass-intention.html',
        'draft_key' => 'parishserve-draft-mass-intention', 'upload_step' => null,
        // One page: its three steps are panels of the same form (mass-intention-request.js).
        'steps' => ['mass-intention-request.php'],
        'fields' => [
            'intentionType'     => ['label' => 'Intention type', 'required' => true, 'options' => ['For the Deceased', 'For the Living', 'Thanksgiving', 'Milestones & Celebrations', 'Special Intention']],
            'intentionSubject'  => ['label' => 'Name of person / family / intention subject', 'required' => true, 'max' => 150],
            'occasion'          => ['label' => 'Occasion or purpose', 'max' => 150],
            'intentionDetails'  => ['label' => 'Intention details', 'required' => true, 'max' => 500],
            'requesterName'     => ['label' => "Requester's full name", 'required' => true, 'max' => 150],
            'mobileNumber'      => ['label' => 'Mobile number', 'type' => 'mobile', 'required' => true],
            'emailAddress'      => ['label' => 'Email address', 'type' => 'email', 'required' => true],
            'preferredDate'     => ['label' => 'Preferred Mass date', 'type' => 'date', 'when' => 'future', 'required' => true],
            'preferredTime'     => ['label' => 'Preferred Mass time', 'required' => true, 'options' => ['6:00 AM', '7:00 AM', '8:30 AM', '10:00 AM (Family Mass)', '12:00 PM (Noon Mass)', '5:00 PM (Anticipated Mass — Saturday only)', '6:00 PM']],
            'schedulingNotes'   => ['label' => 'Scheduling notes', 'max' => 500],
            'confirmRespectful' => ['label' => 'Confirmation', 'type' => 'checkbox', 'required' => true],
        ],
    ],
    'donation' => [
        'label' => 'Donation', 'prefix' => 'DON', 'nav' => 'donations.html',
        'draft_key' => 'parishserve-draft-donations', 'upload_step' => 0,
        'steps' => ['donation-request.php'],
        'fields' => [
            'donationPurpose' => ['label' => 'Donation purpose', 'options' => ['general', 'building', 'outreach', 'sacraments']],
            'donationAmount'  => ['label' => 'Donation amount', 'type' => 'amount', 'required' => true],
            'donorName'       => ['label' => 'Full name', 'max' => 150],
            'donorEmail'      => ['label' => 'Email', 'type' => 'email'],
            'donorContact'    => ['label' => 'Contact number', 'type' => 'mobile'],
            'donationNote'    => ['label' => 'Note / prayer intention', 'max' => 500],
            'isAnonymous'     => ['label' => 'Remain anonymous', 'type' => 'checkbox'],
        ],
    ],
];

// ---------------------------------------------------------------------
// Small text helpers
// ---------------------------------------------------------------------
function ps_text_length($text) {
    return mb_strlen($text, 'UTF-8');
}

/** Fits a value into a VARCHAR column (the full text is kept in details). */
function ps_clip($text, $length) {
    return mb_substr($text, 0, $length, 'UTF-8');
}

function ps_join_name(...$parts) {
    return implode(' ', array_filter($parts, fn($part) => $part !== ''));
}

function ps_long_date($ymd) {
    return $ymd === '' ? '' : date('F j, Y', strtotime($ymd));
}

/** "10:00 AM (Family Mass)" or "14:30" -> "10:00:00" / "14:30:00" for a TIME column. */
function ps_sql_time($text) {
    if (preg_match('/^(\d{1,2}):([0-5]\d)\s*(AM|PM)/i', $text, $m)) {
        return sprintf('%02d:%s:00', (int) $m[1] % 12 + (strtoupper($m[3]) === 'PM' ? 12 : 0), $m[2]);
    }
    if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)/', $text, $m)) {
        return "{$m[1]}:{$m[2]}:00";
    }
    return null;
}

function ps_form_error($step, $message) {
    return ['message' => $message, 'step' => $step];
}

// ---------------------------------------------------------------------
// Validation
// ---------------------------------------------------------------------

/**
 * Applies a flow's field rules. Fields on the submitting page are read
 * from the POST (an unticked box isn't sent at all), earlier steps' from
 * the draft. Returns [values (every field, '' when empty), errors].
 */
function ps_validate_request_fields($flow, array $draft, array $post) {
    $form = PS_REQUEST_FORMS[$flow];
    $pageStep = count($form['steps']) - 1;
    $today = new DateTimeImmutable('today');
    $values = [];
    $errors = [];

    foreach ($form['fields'] as $name => $field) {
        $step = $field['step'] ?? 0;
        $type = $field['type'] ?? 'text';
        $raw = $step === $pageStep ? ($post[$name] ?? '') : ($draft[$name] ?? '');
        $value = is_string($raw) ? trim(str_replace("\r\n", "\n", $raw)) : '';
        if ($type === 'checkbox') {
            $value = $value === '' ? '' : 'Yes';
        }
        $values[$name] = '';

        $problem = null;
        if ($value === '') {
            if (!empty($field['required'])) {
                $problem = $type === 'checkbox'
                    ? 'Please switch on "I confirm" before submitting.'
                    : "{$field['label']} is required.";
            }
        } elseif (isset($field['max']) && ps_text_length($value) > $field['max']) {
            $problem = "{$field['label']} must be {$field['max']} characters or fewer.";
        } elseif (isset($field['options']) && !in_array($value, $field['options'], true)) {
            $problem = "Please choose one of the listed options for {$field['label']}.";
        } elseif ($type === 'email' && (strlen($value) > 150 || !filter_var($value, FILTER_VALIDATE_EMAIL))) {
            $problem = "{$field['label']} must be a valid email address.";
        } elseif ($type === 'mobile' && !preg_match(PS_MOBILE_PATTERN, $value)) {
            $problem = "{$field['label']} must be 11 digits starting with 09 (09XXXXXXXXX).";
        } elseif ($type === 'time' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $value)) {
            $problem = "{$field['label']} must be a valid time.";
        } elseif ($type === 'amount' && (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $value) || (float) $value <= 0)) {
            $problem = "{$field['label']} must be more than zero, with up to 2 decimal places.";
        } elseif ($type === 'date') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $problem = "{$field['label']} must be a valid date.";
            } elseif (($field['when'] ?? '') === 'past' && $date > $today) {
                $problem = "{$field['label']} can't be in the future.";
            } elseif (($field['when'] ?? '') === 'future' && $date <= $today) {
                $problem = "{$field['label']} must be a future date.";
            }
        }

        if ($problem !== null) {
            $errors[] = ps_form_error($step, $problem);
        } else {
            $values[$name] = $value;
        }
    }
    return [$values, $errors];
}

// ---------------------------------------------------------------------
// Row builders: validated values -> table columns + details + any
// cross-field problems. Only called once every field rule has passed.
// ---------------------------------------------------------------------
function ps_build_wedding(array $v) {
    $groom = ps_join_name($v['groomFirstName'], $v['groomMiddleName'], $v['groomLastName'], $v['groomSuffix']);
    $bride = ps_join_name($v['brideFirstName'], $v['brideMiddleName'], $v['brideLastName'], $v['brideSuffix']);
    return [
        'columns' => [
            'contact_number' => $v['mobileNumber'],
            'contact_email'  => $v['emailAddress'],
            'bride_name'     => ps_clip($bride, 150),
            'groom_name'     => ps_clip($groom, 150),
            'preferred_date' => $v['weddingDate'],
        ],
        'details' => [
            'Groom'                       => $groom,
            'Bride'                       => $bride,
            'Mobile number'               => $v['mobileNumber'],
            'Email address'               => $v['emailAddress'],
            'Preferred seminar date'      => ps_long_date($v['seminarDate']),
            'Preferred seminar time'      => $v['seminarTime'],
            'Preferred seminar location'  => $v['seminarLocation'],
            'Notes for the parish office' => $v['officeNotes'],
        ],
        'errors' => [],
    ];
}

function ps_build_baptism(array $v) {
    $errors = [];
    if ($v['baptismType'] === 'regular' && date('w', strtotime($v['baptismDate'])) !== '6') {
        $errors[] = ps_form_error(0, 'Regular Baptism is only held on Saturdays. Please choose a Saturday, or request a Special Baptism.');
    }
    $child = ps_join_name($v['childFirstName'], $v['childMiddleName'], $v['childLastName'], $v['childSuffix']);
    return [
        'columns' => [
            'contact_number' => $v['requestorContact'],
            'contact_email'  => $v['requestorEmail'] === '' ? null : $v['requestorEmail'],
            'child_name'     => ps_clip($child, 150),
            'preferred_date' => $v['baptismDate'],
        ],
        'details' => [
            'Baptism type'          => $v['baptismType'] === 'special' ? 'Special Baptism' : 'Regular Baptism (Saturday)',
            "Child's date of birth" => ps_long_date($v['childDob']),
            'Place of birth'        => $v['childPlaceOfBirth'],
            'Gender'                => $v['childGender'],
            'Requested by'          => "{$v['requestorName']} ({$v['relationship']})",
            'Contact number'        => $v['requestorContact'],
            'Email address'         => $v['requestorEmail'],
            'Additional note'       => $v['officeNotes'],
        ],
        'errors' => $errors,
    ];
}

function ps_build_confirmation(array $v) {
    $name = ps_join_name($v['candidateFirstName'], $v['candidateMiddleName'], $v['candidateLastName'], $v['candidateSuffix']);
    return [
        'columns' => [
            'contact_number' => $v['candidateMobile'],
            'contact_email'  => $v['candidateEmail'],
            'applicant_name' => ps_clip($name, 150),
        ],
        'details' => [
            'Date of birth'              => ps_long_date($v['candidateDob']),
            'Complete address'           => $v['candidateAddress'],
            'Mobile number'              => $v['candidateMobile'],
            'Email address'              => $v['candidateEmail'],
            'Parish'                     => $v['parishName'],
            'Parent / guardian'          => "{$v['guardianName']} ({$v['guardianRelationship']})",
            'School'                     => $v['schoolName'],
            'Grade / year level'         => $v['gradeLevel'],
            'Emergency contact number'   => $v['emergencyContact'],
            'Received First Communion'   => $v['firstCommunion'],
            'Sponsor'                    => $v['sponsorName'],
            'Sponsor contact number'     => $v['sponsorContact'],
            'Notes / message to parish'  => $v['parishNotes'],
            'Document note'              => $v['documentNotes'],
        ],
        'errors' => [],
    ];
}

function ps_build_funeral(array $v) {
    $errors = [];
    if ($v['preferredMassDate'] < $v['dateOfDeath']) {
        $errors[] = ps_form_error(1, "The preferred service date can't be before the date of death.");
    }
    return [
        'columns' => [
            'contact_number' => $v['familyMobile'],
            'contact_email'  => $v['familyEmail'],
            'deceased_name'  => ps_clip($v['deceasedName'], 150),
            'service_date'   => $v['preferredMassDate'],
            'service_time'   => ps_sql_time($v['preferredTime']),
        ],
        'details' => [
            'Requested by'                   => "{$v['familyFirstName']} {$v['familyLastName']} ({$v['relationship']})",
            'Mobile number'                  => $v['familyMobile'],
            'Email address'                  => $v['familyEmail'],
            'Date of death'                  => ps_long_date($v['dateOfDeath']),
            'Burial arrangement'             => $v['burialArrangement'],
            'Requested service'              => $v['serviceType'],
            'Preferred time'                 => date('g:i A', strtotime($v['preferredTime'])),
            'Wake venue / chapel'            => $v['wakeVenue'],
            'Wake address'                   => $v['wakeAddress'],
            'Burial place / cemetery'        => $v['burialLocation'],
            'Burial in a different location' => $v['differentBurialLocation'],
            'Funeral home / coordinator'     => $v['funeralCoordinator'],
            'Additional notes'               => $v['serviceNotes'],
            'Document note'                  => $v['documentNotes'],
        ],
        'errors' => $errors,
    ];
}

function ps_build_massintention(array $v) {
    return [
        'columns' => [
            'contact_number' => $v['mobileNumber'],
            'contact_email'  => $v['emailAddress'],
            'requester_name' => $v['requesterName'],
            'intention_type' => $v['intentionType'],
            'intention_for'  => $v['intentionSubject'],
            'mass_date'      => $v['preferredDate'],
            'mass_time'      => ps_sql_time($v['preferredTime']),
        ],
        'details' => [
            'Occasion or purpose' => $v['occasion'],
            'Intention details'   => $v['intentionDetails'],
            'Preferred Mass time' => $v['preferredTime'],
            'Mobile number'       => $v['mobileNumber'],
            'Email address'       => $v['emailAddress'],
            'Scheduling notes'    => $v['schedulingNotes'],
        ],
        'errors' => [],
    ];
}

function ps_build_donation(array $v) {
    $errors = [];
    if ($v['donorName'] === '' && $v['isAnonymous'] === '') {
        $errors[] = ps_form_error(0, 'Full name is required unless you choose to remain anonymous.');
    }
    return [
        'columns' => [
            'contact_number' => $v['donorContact'],
            'contact_email'  => $v['donorEmail'] === '' ? null : $v['donorEmail'],
            'donor_name'     => $v['donorName'] === '' ? 'Anonymous' : $v['donorName'],
            'amount'         => $v['donationAmount'],
            'purpose'        => PS_DONATION_FUNDS[$v['donationPurpose'] === '' ? 'general' : $v['donationPurpose']],
        ],
        'details' => [
            'Email address'           => $v['donorEmail'],
            'Contact number'          => $v['donorContact'],
            'Note / prayer intention' => $v['donationNote'],
            'Remain anonymous'        => $v['isAnonymous'] === '' ? 'No' : 'Yes',
        ],
        'errors' => $errors,
    ];
}

// ---------------------------------------------------------------------
// Submission
// ---------------------------------------------------------------------
function ps_redirect($location) {
    header('Location: ' . $location, true, 303);
    exit;
}

/**
 * A requested date + time that overlaps a request the parish has already
 * booked, as a form error (no names -- the requester only learns the
 * time is taken). Forms that don't ask for a time can't clash yet; staff
 * set the time, and the check runs again, when they approve the request.
 */
function ps_request_schedule_errors($flow, array $columns) {
    global $conn;
    $type = PS_REQUEST_TYPES[$flow] ?? null;
    if (!$type || $type['resource'] === null || empty($columns[$type['date']])) {
        return [];
    }
    $date = $columns[$type['date']];
    $window = ps_booking_window($flow, $columns[$type['time']] ?? null, $type['end'] ? ($columns[$type['end']] ?? null) : null);
    if (!$window) {
        return [];
    }
    $conflicts = ps_schedule_conflicts($conn, $flow, $date, $window, $type['subtype'] ? ($columns[$type['subtype']] ?? null) : null);
    if (!$conflicts) {
        return [];
    }
    $taken = implode(', ', array_map(fn($booking) => ps_window_label($booking['window']), $conflicts));
    return [ps_form_error(
        PS_REQUEST_FORMS[$flow]['schedule_step'] ?? null,
        'The parish is already booked on ' . ps_long_date($date) . " from {$taken}. Please choose a different date or time."
    )];
}

function ps_form_back($flow, array $errors) {
    $form = PS_REQUEST_FORMS[$flow];
    $_SESSION['ps_form_errors'][$flow] = $errors;
    ps_redirect($form['steps'][count($form['steps']) - 1]);
}

/**
 * Inserts $columns into $table with the next <PREFIX>-<YEAR>-<NNNN>
 * reference number. Call inside a transaction: the SELECT ... FOR UPDATE
 * holds other submissions of the same type back, and a duplicate key
 * (should one slip through) is retried. Returns [reference, row id].
 */
function ps_insert_with_reference(mysqli $conn, $table, $prefix, array $columns) {
    $year = date('Y');
    $pattern = "{$prefix}-{$year}-%";
    $names = array_merge(['reference_no'], array_keys($columns));
    $insert = "INSERT INTO {$table} (" . implode(', ', $names) . ') VALUES (' . implode(', ', array_fill(0, count($names), '?')) . ')';

    for ($attempt = 1; ; $attempt++) {
        $stmt = $conn->prepare("SELECT reference_no FROM {$table} WHERE reference_no LIKE ?
                                 ORDER BY CAST(SUBSTRING_INDEX(reference_no, '-', -1) AS UNSIGNED) DESC LIMIT 1 FOR UPDATE");
        $stmt->bind_param('s', $pattern);
        $stmt->execute();
        $last = $stmt->get_result()->fetch_row()[0] ?? null;
        $stmt->close();

        $sequence = $last === null ? 1 : (int) substr($last, strrpos($last, '-') + 1) + 1;
        $reference = sprintf('%s-%s-%04d', $prefix, $year, $sequence);
        $params = array_merge([$reference], array_values($columns));
        try {
            $stmt = $conn->prepare($insert);
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            $stmt->execute();
            $id = $stmt->insert_id;
            $stmt->close();
            return [$reference, $id];
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() !== 1062 || $attempt >= 3) {
                throw $e;
            }
        }
    }
}

function ps_submit_request_form($flow) {
    global $conn;
    $form = PS_REQUEST_FORMS[$flow];

    $token = is_string($_POST['ps_token'] ?? null) ? $_POST['ps_token'] : '';
    if ($token !== '' && isset($_SESSION['ps_form_done'][$token])) {
        // Already submitted (double-click, refresh, Back + submit): show that request.
        ps_redirect('request-confirmation.php?ref=' . rawurlencode($_SESSION['ps_form_done'][$token]));
    }
    $tokenIndex = $token === '' ? false : array_search($token, $_SESSION['ps_form_tokens'][$flow] ?? [], true);
    if ($tokenIndex === false) {
        ps_form_back($flow, [ps_form_error(null, 'This page was open too long, so nothing was submitted. Please check your details and submit again.')]);
    }

    $draft = json_decode(is_string($_POST['ps_draft'] ?? null) ? $_POST['ps_draft'] : '', true);
    [$values, $errors] = ps_validate_request_fields($flow, is_array($draft) ? $draft : [], $_POST);

    // A file posted with the form itself (the upload script didn't run) is staged like any other.
    $failedUploads = [];
    foreach (ps_upload_rules($flow) as $rule) {
        $file = $_FILES[$rule['field']] ?? null;
        if (is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $result = ps_stage_upload($flow, $rule['field'], $file);
            if (!$result['ok']) {
                $failedUploads[$rule['field']] = true;
                $errors[] = ps_form_error($form['upload_step'], $result['error']);
            }
        }
    }
    $staged = ps_staged_uploads($flow);
    foreach (ps_upload_rules($flow) as $rule) {
        if ($rule['required'] && !isset($staged[$rule['field']]) && !isset($failedUploads[$rule['field']])) {
            $errors[] = ps_form_error($form['upload_step'], "Please upload the {$rule['label']}.");
        }
    }

    $built = $errors ? null : call_user_func('ps_build_' . $flow, $values);
    if ($built && $built['errors']) {
        $errors = $built['errors'];
    }
    if ($built && !$errors) {
        $errors = ps_request_schedule_errors($flow, $built['columns']);
    }
    if ($errors) {
        ps_form_back($flow, $errors);
    }

    $table = $flow === 'donation' ? PS_DONATION_TABLE : PS_REQUEST_TYPES[$flow]['table'];
    $details = array_filter($built['details'], fn($value) => $value !== '');
    $columns = $built['columns'] + ['details' => json_encode($details, JSON_UNESCAPED_UNICODE)];
    $moved = [];

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn->begin_transaction();
        [$reference, $id] = ps_insert_with_reference($conn, $table, $form['prefix'], $columns);

        foreach (ps_upload_rules($flow) as $rule) {
            $entry = $staged[$rule['field']] ?? null;
            if (!$entry) {
                continue;
            }
            $path = ps_promote_upload($entry, $flow, $reference, $rule['field']);
            if ($path === null) {
                throw new RuntimeException("Could not move the staged {$rule['field']} file.");
            }
            $moved[] = [$path, $entry['path']];

            if ($flow === 'donation') {
                $stmt = $conn->prepare('UPDATE donations SET proof_of_payment = ? WHERE id = ?');
                $stmt->bind_param('si', $path, $id);
            } else {
                $uploadedAt = date('Y-m-d H:i:s');
                $stmt = $conn->prepare('INSERT INTO request_documents (request_type, request_id, document_label, received, file_path, original_name, uploaded_at)
                                        VALUES (?, ?, ?, 1, ?, ?, ?)');
                $stmt->bind_param('sissss', $flow, $id, $rule['label'], $path, $entry['name'], $uploadedAt);
            }
            $stmt->execute();
            $stmt->close();
        }
        $conn->commit();
    } catch (Throwable $e) {
        try {
            $conn->rollback();
        } catch (Throwable $ignored) {
        }
        foreach ($moved as [$path, $stagedPath]) {
            $absolute = ps_upload_abs($path);
            if ($absolute) {
                rename($absolute, __DIR__ . '/../' . $stagedPath); // keep the file for the retry
            }
        }
        error_log("ps_submit_request_form({$flow}) failed: " . $e->getMessage());
        ps_form_back($flow, [ps_form_error(null, 'Something went wrong while saving your request, so nothing was submitted. Please try again.')]);
    }

    unset($_SESSION['ps_form_tokens'][$flow][$tokenIndex], $_SESSION['ps_staged'][$flow]);
    $_SESSION['ps_form_done'] = array_slice(($_SESSION['ps_form_done'] ?? []) + [$token => $reference], -20, null, true);
    $_SESSION['ps_confirmations'] = array_slice(($_SESSION['ps_confirmations'] ?? []) + [$reference => [
        'flow'         => $flow,
        'contact'      => $columns['contact_number'],
        'submitted_at' => date('F j, Y \a\t g:i A'),
        'documents'    => count($moved),
    ]], -10, null, true);
    ps_redirect('request-confirmation.php?ref=' . rawurlencode($reference));
}

/**
 * Entry point for a final-step page (before any output): processes a
 * POST (always redirects), otherwise prepares the submit token and any
 * errors from the last attempt for ps_request_form_fields().
 */
function ps_handle_request_form($flow) {
    global $psRequestForm;
    if (!isset(PS_REQUEST_FORMS[$flow])) {
        throw new InvalidArgumentException("Unknown request form: {$flow}");
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    header('Cache-Control: no-store'); // Back always re-requests the page, with a fresh token

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        ps_submit_request_form($flow);
    }

    $errors = $_SESSION['ps_form_errors'][$flow] ?? [];
    unset($_SESSION['ps_form_errors'][$flow]);
    $token = bin2hex(random_bytes(16));
    $_SESSION['ps_form_tokens'][$flow] = array_slice(array_merge($_SESSION['ps_form_tokens'][$flow] ?? [], [$token]), -5);
    $psRequestForm = ['flow' => $flow, 'token' => $token, 'errors' => $errors];
}

/** Prints the submit token and, after a failed attempt, the list of problems. Call right inside the <form>. */
function ps_request_form_fields() {
    global $psRequestForm;
    $form = PS_REQUEST_FORMS[$psRequestForm['flow']];
    $pageStep = count($form['steps']) - 1;

    echo '<input type="hidden" name="ps_token" value="' . htmlspecialchars($psRequestForm['token']) . '">';
    if (!$psRequestForm['errors']) {
        return;
    }
    echo '<div class="wr-form-errors" role="alert" tabindex="-1" data-form-errors>';
    ps_icon('info');
    echo '<div><strong>Your request hasn\'t been submitted yet. Please fix the following:</strong><ul>';
    foreach ($psRequestForm['errors'] as $error) {
        echo '<li>' . htmlspecialchars($error['message']);
        $step = $error['step'];
        if (is_int($step) && $step !== $pageStep && isset($form['steps'][$step])) {
            echo ' <a href="' . htmlspecialchars($form['steps'][$step]) . '">Edit step ' . ($step + 1) . '</a>';
        }
        echo '</li>';
    }
    echo '</ul></div></div>';
}
