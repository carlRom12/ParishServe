<?php
/**
 * step-bar.php
 * ---------------------------------------------------------------------
 * The 3-node wizard step bar shared by every multi-step request form
 * (wedding-request*.php, baptism-request*.php, and whatever comes
 * next). Extracted here because copy-pasting the same ~15 lines into
 * 5 files was already starting to drift -- this reference image added
 * a "done" state (checkmark instead of a number, for any step before
 * the current one) that wedding's step 2/3 never had, and fixing that
 * in one shared file beats fixing it in five almost-identical ones.
 *
 * USAGE: before requiring this file, the calling page sets:
 *   $steps        -> array of ['title' => ..., 'sub' => ...]
 *   $currentStep  -> 0-based index of the step this page IS
 *
 * Renders three visual states per step:
 *   done      (index < $currentStep)  -- cream circle, checkmark icon
 *   current   (index === $currentStep) -- maroon-filled circle, number
 *   upcoming  (index > $currentStep)   -- cream circle, muted number
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/icons.php';
?>
<div class="ps-card wr-stepbar">
    <?php foreach ($steps as $i => $step): ?>
        <?php
        $stateClass = '';
        if ($i < $currentStep) {
            $stateClass = ' is-done';
        } elseif ($i === $currentStep) {
            $stateClass = ' is-current';
        }
        ?>
        <div class="wr-step<?php echo $stateClass; ?>">
            <span class="wr-step-num">
                <?php if ($i < $currentStep): ?>
                    <?php ps_icon('check'); ?>
                <?php else: ?>
                    <?php echo (int) $i + 1; ?>
                <?php endif; ?>
            </span>
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
