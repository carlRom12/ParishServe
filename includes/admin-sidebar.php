<?php
/**
 * admin-sidebar.php
 * ---------------------------------------------------------------------
 * Left navigation rail for the admin portal. Mirrors includes/sidebar
 * .php structurally (same $psNavGroups -> .ps-nav-link markup, same
 * active-link convention via $activeNav set by the calling page before
 * requiring header.php) but with the admin-specific nav items.
 *
 * FRONTEND ONLY, same as the rest of the site this session: no auth
 * guard here, no session -- these pages are reachable directly, same
 * as dashboard.php/announcements.php/etc. Wiring real admin auth is a
 * separate backend task, not part of this pass.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/icons.php';

if (!isset($activeNav)) {
    $activeNav = '';
}

$psNavGroups = [
    [
        'label' => null,
        'items' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'href' => 'admin-dashboard.php'],
        ],
    ],
    [
        'label' => 'Manage',
        'items' => [
            ['key' => 'requests',      'label' => 'Requests',      'icon' => 'document', 'href' => 'admin-requests.php'],
            ['key' => 'donations',     'label' => 'Donations',     'icon' => 'heart',     'href' => 'admin-donations.php'],
            ['key' => 'announcements', 'label' => 'Announcements', 'icon' => 'megaphone', 'href' => 'admin-announcements.php'],
        ],
    ],
];
?>
<aside class="ps-sidebar">

    <div class="ps-logo">
        <div class="ps-logo-crest"><?php ps_icon('crest'); ?></div>
        <div class="ps-logo-eyebrow">Our Lady<br>of the Gate</div>
        <div class="ps-logo-name">ParishServe</div>
        <div class="ps-logo-sub">Admin Portal</div>
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
        <!-- no real session this pass -- same placeholder convention
             as includes/sidebar.php's own logout link. -->
        <a href="index.php" class="ps-logout-btn">
            <?php ps_icon('logout'); ?>
            <span>Log out</span>
        </a>
    </div>

</aside>
