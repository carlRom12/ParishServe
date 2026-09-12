<?php
/**
 * admin-announcements.php
 * ---------------------------------------------------------------------
 * CRUD-look management for the announcements table (currently read-
 * only for parishioners on announcements.php). FRONTEND ONLY this
 * pass -- $announcements is hardcoded from database/schema.sql's seed
 * rows. Add/Edit/Delete are design previews only (see initAdminModals()
 * in main.js): Save closes the modal and shows a toast without
 * persisting anything, Delete removes the row from view only. Reuses
 * the same .ps-dropzone upload component donations.php already has.
 * ---------------------------------------------------------------------
 */

$announcements = [
    ['id' => 1, 'title' => 'Feast Day Celebration this Sunday!', 'body' => 'Join us this coming Sunday for the Feast of the Assumption of Mary. There will be a solemn Mass at 9:00 AM followed by a community gathering.', 'image' => 'assets/images/announcements/feast-day.svg', 'featured' => true,  'date' => '2026-08-16'],
    ['id' => 2, 'title' => 'Adoration Every Friday',              'body' => 'Eucharistic Adoration is held every Friday after the 6:00 PM Mass.',                                                                            'image' => 'assets/images/announcements/adoration.svg',  'featured' => false, 'date' => '2026-05-10'],
    ['id' => 3, 'title' => 'Mass Intentions Now Open',            'body' => 'You can now submit your Mass intention requests for June.',                                                                                    'image' => 'assets/images/announcements/mass-intentions.svg', 'featured' => false, 'date' => '2026-05-09'],
    ['id' => 4, 'title' => 'Church Cleaning Drive',                'body' => "Let's keep our church clean and beautiful. See you there!",                                                                                     'image' => 'assets/images/announcements/cleaning-drive.svg', 'featured' => false, 'date' => '2026-05-08'],
];

$pageTitle = 'Announcements';
$pageCss   = 'admin.css';
$userFirstName = 'Parish';
$userRole = 'Admin';
$activeNav = 'announcements';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/admin-sidebar.php';
?>
<main class="ps-main">

    <section class="ps-plain-header" style="position:relative;">
        <div>
            <div class="ps-heading-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
            <h1>Announcements</h1>
            <p>Publish and manage parish announcements.</p>
        </div>
        <?php require __DIR__ . '/includes/topbar.php'; ?>
    </section>

    <div class="ps-card">
        <div class="admin-toolbar">
            <span class="ps-search"><?php ps_icon('search'); ?><input type="text" placeholder="Search title" data-admin-search></span>
            <div class="admin-toolbar-spacer"></div>
            <button type="button" class="ps-btn ps-btn-primary" data-modal-trigger="announcementModal"
                data-title="" data-body="" data-posteddate="<?php echo date('Y-m-d'); ?>" data-featured="">
                <?php ps_icon('megaphone'); ?> Add Announcement
            </button>
        </div>

        <div class="admin-table" style="--admin-cols: 60px 1fr 90px 130px 160px;">
            <div class="admin-table-head">
                <span></span><span>Title</span><span>Featured</span><span>Posted</span><span></span>
            </div>
            <?php foreach ($announcements as $a): ?>
                <div class="admin-row" data-admin-row data-search="<?php echo htmlspecialchars(strtolower($a['title'])); ?>">
                    <span><img class="admin-thumb" src="<?php echo htmlspecialchars($a['image']); ?>" alt=""></span>
                    <span class="admin-cell-name">
                        <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                        <small><?php echo htmlspecialchars(strlen($a['body']) > 90 ? substr($a['body'], 0, 90) . '…' : $a['body']); ?></small>
                    </span>
                    <span><?php echo $a['featured'] ? '<span class="ps-status is-approved">Featured</span>' : ''; ?></span>
                    <span><?php echo htmlspecialchars(date('M j, Y', strtotime($a['date']))); ?></span>
                    <span class="admin-cell-actions">
                        <button type="button" class="ps-btn ps-btn-outline" data-modal-trigger="announcementModal"
                            data-title="<?php echo htmlspecialchars($a['title']); ?>"
                            data-body="<?php echo htmlspecialchars($a['body']); ?>"
                            data-posteddate="<?php echo htmlspecialchars($a['date']); ?>"
                            data-featured="<?php echo $a['featured'] ? '1' : ''; ?>">
                            Edit
                        </button>
                        <button type="button" class="ps-btn ps-btn-outline" data-mock-delete>Delete</button>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-empty" data-admin-empty hidden><?php ps_icon('megaphone'); ?><p>No announcements match this search.</p></div>
    </div>

</main>

<div class="ps-modal-overlay" id="announcementModal" data-modal hidden>
    <div class="ps-modal-card">
        <button type="button" class="ps-modal-close" data-modal-close><?php ps_icon('close'); ?></button>
        <h2 class="ps-modal-title">Announcement</h2>
        <form data-mock-form="Announcement saved. (Design preview only -- not connected to a database.)">
            <div class="ps-modal-field">
                <label for="annTitle">Title</label>
                <input type="text" id="annTitle" name="title" data-modal-field="title" required>
            </div>
            <div class="ps-modal-field">
                <label for="annBody">Body</label>
                <textarea id="annBody" name="body" rows="4" data-modal-field="body" required></textarea>
            </div>
            <div class="ps-modal-field">
                <label for="annPostedDate">Posted date</label>
                <input type="date" id="annPostedDate" name="posted_date" data-modal-field="posteddate">
            </div>
            <div class="ps-modal-field">
                <label for="annImage">Image</label>
                <span class="ps-dropzone" data-dropzone>
                    <input type="file" id="annImage" name="image" accept=".png,.jpg,.jpeg,.svg" data-max-size-mb="5" data-dropzone-input>
                    <span class="ps-dropzone-icon is-ringed"><?php ps_icon('upload'); ?></span>
                    <span class="ps-dropzone-text">Click to upload or drag and drop</span>
                    <span class="ps-dropzone-or">PNG, JPG, JPEG, SVG (Max. 5MB)</span>
                </span>
            </div>
            <div class="ps-modal-field ps-modal-checkbox">
                <label class="ps-toggle">
                    <input type="checkbox" id="annFeatured" name="is_featured" value="1" data-modal-field="featured">
                    <span class="ps-toggle-track"><span class="ps-toggle-thumb"></span></span>
                </label>
                <label for="annFeatured">Show as featured</label>
            </div>
            <div class="ps-modal-actions">
                <button type="button" class="ps-btn ps-btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="ps-btn ps-btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="ps-toast" data-toast hidden><?php ps_icon('check-circle'); ?> <span data-toast-text></span></div>

<?php require __DIR__ . '/includes/footer.php'; ?>
