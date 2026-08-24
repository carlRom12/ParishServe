<?php
/**
 * sidebar.php
 * ---------------------------------------------------------------------
 * The left navigation rail, included right after header.php opens
 * .ps-shell. Structure/order/icons here are copied straight from the
 * reference dashboard image: logo block, then Dashboard/Announcements/
 * Parish Calendar with no section label, then a SACRAMENTS group, then
 * a PARISH SERVICES group, then an OTHER group, then Log out pinned to
 * the bottom.
 *
 * ACTIVE LINK HIGHLIGHTING:
 * The calling page sets $activeNav = 'dashboard' (or whichever key)
 * BEFORE requiring header.php. We compare that against each item's
 * 'key' below to decide which link gets the gold ".active" pill.
 *
 * LINKS TO PAGES THAT DON'T EXIST YET:
 * We're building this system one page at a time (dashboard first).
 * Every href below already points to where that page WILL live
 * (wedding.php, baptism.php, etc.) even though most of those files
 * don't exist yet -- clicking them will 404 until we build them in a
 * later session. That's expected and fine; the nav itself is what we
 * need locked down now since every future page reuses this file.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/icons.php';

if (!isset($activeNav)) {
    $activeNav = '';
}

// Grouped exactly like the reference image: ungrouped top-level items
// first, then three labeled sections.
$psNavGroups = [
    [
        'label' => null,
        'items' => [
            ['key' => 'dashboard',     'label' => 'Dashboard',      'icon' => 'home',      'href' => 'dashboard.php'],
            ['key' => 'announcements', 'label' => 'Announcements',  'icon' => 'megaphone', 'href' => 'announcements.php'],
            ['key' => 'calendar',      'label' => 'Parish Calendar','icon' => 'calendar',  'href' => 'calendar.php'],
        ],
    ],
    [
        'label' => 'Sacraments',
        'items' => [
            ['key' => 'wedding',      'label' => 'Wedding',      'icon' => 'ring',    'href' => 'wedding.php'],
            ['key' => 'baptism',      'label' => 'Baptism',      'icon' => 'droplet', 'href' => 'baptism.php'],
            ['key' => 'confirmation', 'label' => 'Confirmation', 'icon' => 'flame',   'href' => 'confirmation.php'],
            ['key' => 'funeral',      'label' => 'Funeral',      'icon' => 'cross',   'href' => 'funeral.php'],
        ],
    ],
    [
        'label' => 'Parish Services',
        'items' => [
            ['key' => 'counseling', 'label' => 'Counseling',           'icon' => 'people',   'href' => 'counseling.php'],
            ['key' => 'massintention', 'label' => 'Mass Intention',    'icon' => 'chalice',  'href' => 'mass-intention.php'],
            ['key' => 'facility',   'label' => 'Facility Reservation', 'icon' => 'building', 'href' => 'facility-reservation.php'],
            ['key' => 'donations',  'label' => 'Donate',               'icon' => 'heart',    'href' => 'donations.php'],
        ],
    ],
    [
        'label' => 'Other',
        'items' => [
            ['key' => 'profile',  'label' => 'My Profile', 'icon' => 'user', 'href' => 'profile.php'],
            ['key' => 'settings', 'label' => 'Settings',   'icon' => 'gear', 'href' => 'settings.php'],
        ],
    ],
];
?>
<aside class="ps-sidebar">

    <div class="ps-logo">
        <div class="ps-logo-crest"><?php ps_icon('crest'); ?></div>
        <div class="ps-logo-eyebrow">Our Lady<br>of the Gate</div>
        <div class="ps-logo-name">ParishServe</div>
        <div class="ps-logo-sub">Parish Community Portal</div>
    </div>

    <nav class="ps-nav">
        <?php foreach ($psNavGroups as $group): ?>
            <?php if ($group['label']): ?>
                <span class="ps-nav-section"><?php echo htmlspecialchars($group['label']); ?></span>
            <?php endif; ?>
            <ul class="ps-nav-list">
                <?php foreach ($group['items'] as $item): ?>
                    <li>
                        <a class="ps-nav-link<?php echo $activeNav === $item['key'] ? ' active' : ''; ?>"
                           href="<?php echo htmlspecialchars($item['href']); ?>">
                            <?php ps_icon($item['icon']); ?>
                            <span><?php echo htmlspecialchars($item['label']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </nav>

    <div class="ps-sidebar-art"><?php ps_icon('church'); ?></div>

    <div class="ps-logout-wrap">
        <!-- there's no real session to destroy yet (no backend this
             session), so "logging out" just returns to the public
             landing page rather than pointing at a logout.php that
             doesn't exist. Once auth is real, this becomes a POST to
             a logout endpoint that clears the session and THEN
             redirects here. -->
        <a href="index.php" class="ps-logout-btn">
            <?php ps_icon('logout'); ?>
            <span>Log out</span>
        </a>
    </div>

</aside>
