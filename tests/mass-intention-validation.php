<?php
// Run: php tests/mass-intention-validation.php. Does not submit requests.
require_once __DIR__ . '/../includes/request-forms.php';
set_error_handler(function ($level, $message) { throw new RuntimeException($message); });
$base = [
    'intentionType' => 'For the Souls of', 'intentionSubject' => '',
    'soulName1' => 'Juan Dela Cruz', 'soulName2' => '', 'occasion' => '',
    'intentionDetails' => 'For eternal rest.', 'requesterName' => 'UI Test',
    'mobileNumber' => '09123456789', 'emailAddress' => 'test@example.test',
    'preferredDate' => (new DateTimeImmutable('+2 months'))->format('Y-m-d'),
    'preferredTime' => '6:00 AM', 'schedulingNotes' => '', 'confirmRespectful' => 'on',
];
function check_case($post, $expectedAmount, $expectedSubject = null) {
    [$v, $errors] = ps_validate_request_fields('massintention', [], $post);
    $built = $errors ? null : ps_build_massintention($v);
    $failed = $errors || ($built && $built['errors']);
    if ($expectedAmount === null) {
        if (!$failed) throw new RuntimeException('Invalid request was accepted.');
    } else {
        if ($failed) throw new RuntimeException(json_encode($errors ?: $built['errors']));
        if ($built['details']['Offering amount (PHP)'] !== $expectedAmount) throw new RuntimeException('Wrong offering.');
        if ($expectedSubject !== null && $built['columns']['intention_for'] !== $expectedSubject) throw new RuntimeException('Wrong subject.');
    }
}
check_case($base, '100.00', 'Juan Dela Cruz');
check_case(array_replace($base, ['soulName2'=>'Maria Santos', 'offeringTotal'=>'1']), '200.00', 'Juan Dela Cruz; Maria Santos');
check_case(array_replace($base, ['soulName1'=>'']), null);
check_case(array_replace($base, ['soulName2'=>'juan dela cruz']), null);
check_case(array_replace($base, ['soulName3'=>'Third Person']), null);
check_case(array_replace($base, ['soulName2'=>['Second', 'Third']]), null);
check_case(array_replace($base, ['soulName1'=>'Juan; Maria; Pedro']), null);
check_case(array_replace($base, ['soulName1'=>str_repeat('A', 71)]), null);
check_case(array_replace($base, ['intentionType'=>'All Souls','soulName2'=>'Maria Santos']), '100.00', 'All the faithful departed');
foreach (['Thanksgiving Mass', 'Special Intention', 'Petition Mass'] as $type) {
    check_case(array_replace($base, ['intentionType'=>$type,'intentionSubject'=>'Passing the board examination']), '100.00');
    check_case(array_replace($base, ['intentionType'=>$type]), null);
}
check_case(array_replace($base, ['intentionType'=>'Unknown']), null);
echo "Mass intention validation and offering tests passed.\n";
