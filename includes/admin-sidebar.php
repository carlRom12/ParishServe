<?php
/**
 * admin-sidebar.php
 * ---------------------------------------------------------------------
 * Left navigation rail for the admin portal. Mirrors the parishioner
 * sidebar structurally (same $psNavGroups -> .ps-nav-link markup, same
 * active-link convention via $activeNav set by the calling page before
 * requiring header.php) but with the admin-specific nav items: one link
 * per request type (PS_REQUEST_TYPES, each its own page) alongside
 * Donations and Announcements, plus the Calendar and Reports.
 *
 * Every page that includes this has already passed
 * includes/auth-guard.php. The "Accounts" link only shows for a Super
 * Admin -- admin-accounts.php enforces that itself too, hiding the link
 * is just tidiness.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/request-types.php';

if (!isset($activeNav)) {
    $activeNav = '';
}

$psSacramentItems = [];
$psServiceItems = [];
foreach (PS_REQUEST_TYPES as $psTypeKey => $psTypeInfo) {
    $item = ['key' => $psTypeKey, 'label' => $psTypeInfo['plural'], 'icon' => $psTypeInfo['icon'], 'href' => $psTypeInfo['page']];
    if (in_array($psTypeKey, ['wedding', 'baptism', 'confirmation', 'funeral'], true)) {
        $psSacramentItems[] = $item;
    } else {
        $psServiceItems[] = $item;
    }
}
$psServiceItems[] = ['key' => 'donations', 'label' => 'Donations', 'icon' => 'heart', 'href' => 'admin-donations.php'];

$psNavGroups = [
    [
        'label' => 'Main',
        'items' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home',     'href' => 'admin-dashboard.php'],
            ['key' => 'announcements', 'label' => 'Announcements', 'icon' => 'megaphone', 'href' => 'admin-announcements.php'],
            ['key' => 'calendar',  'label' => 'Calendar',  'icon' => 'calendar', 'href' => 'admin-calendar.php'],
            ['key' => 'reports',   'label' => 'Reports',   'icon' => 'chart',    'href' => 'admin-reports.php'],
        ],
    ],
    [
        'label' => 'Sacraments',
        'items' => $psSacramentItems,
    ],
    [
        'label' => 'Parish Services',
        'items' => $psServiceItems,
    ],
];

if (function_exists('ps_is_super_admin') && ps_is_super_admin()) {
    $psNavGroups[] = [
        'label' => 'Account',
        'items' => [
            ['key' => 'accounts', 'label' => 'Accounts', 'icon' => 'people', 'href' => 'admin-accounts.php'],
        ],
    ];
}
?>
<aside class="ps-sidebar">

    <div class="ps-logo">
        <div class="ps-logo-crest"><?php ps_icon('crest'); ?></div>
        <div class="ps-logo-eyebrow">Our Lady<br>of the Gate</div>
        <div class="ps-logo-name">ParishServe</div>
        <div class="ps-logo-sub">Admin Portal</div>
    </div>

    <nav class="ps-nav" aria-label="Admin navigation">
        <?php foreach ($psNavGroups as $group): ?>
            <?php if ($group['label']): ?>
                <span class="ps-nav-section"><?php echo htmlspecialchars($group['label']); ?></span>
            <?php endif; ?>
            <ul class="ps-nav-list">
                <?php foreach ($group['items'] as $item): ?>
                    <li>
                        <a class="ps-nav-link<?php echo $activeNav === $item['key'] ? ' active' : ''; ?>"
                           href="<?php echo htmlspecialchars($item['href']); ?>"<?php echo $activeNav === $item['key'] ? ' aria-current="page"' : ''; ?>>
                            <?php ps_icon($item['icon']); ?>
                            <span><?php echo htmlspecialchars($item['label']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </nav>

    <div class="ps-logout-wrap">
        <a href="logout.php" class="ps-logout-btn">
            <?php ps_icon('logout'); ?>
            <span>Log out</span>
        </a>
    </div>

</aside>
