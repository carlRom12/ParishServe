<?php
/**
 * wedding-request.php
 * ---------------------------------------------------------------------
 * Step 1 of 3 ("The Couple") of the wedding request flow, linked from
 * wedding.php's "Start Wedding Request" button and quick-link cards.
 * Per the group's instruction, THIS session only builds the screen
 * shown in the reference image -- Step 1, laid out the same way but
 * with every field left BLANK (placeholders only, no pre-filled
 * values) rather than pre-filled sample data like the mockup showed.
 *
 * WHAT HAPPENS WHEN YOU CLICK "Save and Continue":
 * wedding-request-step2.php exists now, so this form genuinely
 * submits there -- native HTML5 validation (required/pattern/type)
 * still runs first via the browser's own default behavior. Step 2's
 * OWN "Save and Continue" is the one that shows an inline "not built
 * yet" notice instead of navigating, since Step 3 (Review & Send)
 * isn't built. NOTE: there's still no session/database, so nothing
 * typed on this page is preserved once you move to Step 2 -- see that
 * file's header comment.
 *
 * FIELD MISMATCH WITH database/schema.sql (flagging for the group):
 * wedding_requests currently has single bride_name/groom_name VARCHAR
 * columns, but this form collects first/middle/last/suffix separately
 * per person, plus an email address the table doesn't have a column
 * for at all. Whoever wires this up to MySQL will need to either (a)
 * concatenate the four name parts into one string per person before
 * INSERT, or (b) migrate the table to separate name-part + email
 * columns. Not fixing schema.sql this session since backend work is
 * out of scope -- just don't want this to be a surprise later.
 *
 * PLACEHOLDER TEXT: each name/date/mobile/email field shows the same
 * example values the mockup had pre-filled (e.g. "Juan" as the First
 * Name placeholder) as grey placeholder text instead of a real value
 * -- purely a visual hint of the expected format, submits as empty if
 * left untouched. The Suffix dropdowns default to nothing selected.
 *
 * DROPPED THE "Who is getting married?" RADIO from the mockup -- both
 * people are obviously getting married, so a Groom/Bride "which one
 * are you" toggle didn't actually mean anything useful here. The form
 * just asks for both names directly now.
 * ---------------------------------------------------------------------
 */

$steps = [
    ['title' => 'The Couple',    'sub' => 'Tell us about you'],
    ['title' => 'Requirements',  'sub' => 'Submit documents'],
    ['title' => 'Review & Send', 'sub' => 'Review and submit'],
];
$currentStep = 0; // index into $steps -- this page is always Step 1 for now

$pageTitle = 'Wedding Request';
$pageCss   = 'wedding-request.css';
$activeNav = 'wedding';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PLAIN HEADER ========================== -->
    <section class="ps-plain-header">
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <h1>Wedding Request</h1>
        <nav class="ps-breadcrumb">
            <a href="wedding.php">Wedding</a>
            <?php ps_icon('chevron-right'); ?>
            <span>Start Request</span>
        </nav>
    </section>

    <!-- ============================ STEP BAR ============================== -->
    <div class="ps-card wr-stepbar">
        <?php foreach ($steps as $i => $step): ?>
            <div class="wr-step<?php echo $i === $currentStep ? ' is-current' : ''; ?>">
                <span class="wr-step-num"><?php echo (int) $i + 1; ?></span>
                <span class="wr-step-text">
                    <strong><?php echo htmlspecialchars($step['title']); ?></strong>
                    <small><?php echo htmlspecialchars($step['sub']); ?></small>
                </span>
            </div>
            <?php if ($i < count($steps) - 1): ?>
                <?php ps_icon('chevron-right', 'wr-step-sep'); ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- ============================ STEP 1 FORM =========================== -->
    <!-- Step 2 is a real page now, so this form genuinely submits (native
         HTML5 validation via `required`/`pattern`/etc. still runs before
         the browser navigates) -- no more JS intercept here. That intercept
         moved to Step 2's own form instead, since Step 3 doesn't exist yet. -->
    <form class="ps-card wr-form" action="wedding-request-step2.php" method="post">

        <h2>Step 1 of 3: The Couple</h2>
        <p class="wr-form-sub">Please provide the basic information about you and your fiancé(e).</p>

        <div class="wr-field-group">
            <span class="ps-field-label">Groom's Name</span>
            <div class="ps-form-row-4">
                <div class="ps-field">
                    <label for="groomFirstName">First Name</label>
                    <input type="text" id="groomFirstName" name="groomFirstName" placeholder="Juan" required>
                </div>
                <div class="ps-field">
                    <label for="groomMiddleName">Middle Name</label>
                    <input type="text" id="groomMiddleName" name="groomMiddleName" placeholder="Dela Cruz">
                </div>
                <div class="ps-field">
                    <label for="groomLastName">Last Name</label>
                    <input type="text" id="groomLastName" name="groomLastName" placeholder="Santos" required>
                </div>
                <div class="ps-field">
                    <label for="groomSuffix">Suffix (Optional)</label>
                    <span class="ps-select is-block">
                        <select id="groomSuffix" name="groomSuffix">
                            <option value="" selected>(None)</option>
                            <option value="Jr.">Jr.</option>
                            <option value="Sr.">Sr.</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                        </select>
                        <?php ps_icon('chevron-down'); ?>
                    </span>
                </div>
            </div>
            <small class="ps-form-hint">Example: Juan Dela Cruz Jr.</small>
        </div>

        <div class="wr-field-group">
            <span class="ps-field-label">Bride's Name</span>
            <div class="ps-form-row-4">
                <div class="ps-field">
                    <label for="brideFirstName">First Name</label>
                    <input type="text" id="brideFirstName" name="brideFirstName" placeholder="Maria" required>
                </div>
                <div class="ps-field">
                    <label for="brideMiddleName">Middle Name</label>
                    <input type="text" id="brideMiddleName" name="brideMiddleName" placeholder="Clara">
                </div>
                <div class="ps-field">
                    <label for="brideLastName">Last Name</label>
                    <input type="text" id="brideLastName" name="brideLastName" placeholder="Santos" required>
                </div>
                <div class="ps-field">
                    <label for="brideSuffix">Suffix (Optional)</label>
                    <span class="ps-select is-block">
                        <select id="brideSuffix" name="brideSuffix">
                            <option value="" selected>(None)</option>
                            <option value="Jr.">Jr.</option>
                            <option value="Sr.">Sr.</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                        </select>
                        <?php ps_icon('chevron-down'); ?>
                    </span>
                </div>
            </div>
            <small class="ps-form-hint">Example: Maria Clara Santos</small>
        </div>

        <div class="ps-form-row-2">
            <div class="ps-field">
                <label for="weddingDate">Preferred wedding date</label>
                <span class="ps-input-icon">
                    <input type="date" id="weddingDate" name="weddingDate" required>
                    <?php ps_icon('calendar'); ?>
                </span>
                <small class="ps-form-hint">The date you would like to celebrate your wedding</small>
            </div>
            <div class="ps-field">
                <label for="mobileNumber">Mobile number</label>
                <input type="tel" id="mobileNumber" name="mobileNumber" placeholder="09XXXXXXXXX"
                       pattern="^09[0-9]{9}$" maxlength="11" inputmode="numeric"
                       title="Format: 09XXXXXXXXX (11 digits)" required>
                <small class="ps-form-hint">Format: 09XXXXXXXXX (11 digits)</small>
            </div>
        </div>

        <div class="ps-field">
            <label for="emailAddress">Email address</label>
            <input type="email" id="emailAddress" name="emailAddress" placeholder="juandelacruz@email.com" required>
            <small class="ps-form-hint">This is where we'll send updates and confirmations.</small>
        </div>

        <div class="wr-actions">
            <a href="wedding.php" class="ps-btn wr-cancel">Cancel</a>
            <button type="submit" class="ps-btn ps-btn-primary wr-submit">Save and Continue <?php ps_icon('arrow-right'); ?></button>
        </div>

    </form>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
