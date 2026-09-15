<?php
/**
 * admin-announcements.php
 * ---------------------------------------------------------------------
 * Staff post and manage parish announcements (the announcements table,
 * database/migrations/003-announcements.sql). "Add Announcement" and
 * "Edit" open the modal; Save POSTs to admin-save-announcement.php and
 * the page reloads from the database. Delete asks first, then POSTs
 * action=delete (initAdminDelete() in main.js). A posted date in the
 * future shows as Scheduled -- the public pages skip it until that day.
 * ---------------------------------------------------------------------
 */
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/announcements.php';

$announcements = ps_fetch_announcements($conn, false);
$today = date('Y-m-d');

$pageTitle = 'Announcements';
$pageCss   = 'admin.css';
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
            <span class="ps-search"><?php ps_icon('search'); ?><input type="text" placeholder="Search title or category" data-admin-search></span>
            <div class="admin-toolbar-spacer"></div>
            <button type="button" class="ps-btn ps-btn-primary" data-modal-trigger="announcementModal"
                data-modaltitle="Add Announcement" data-id="" data-title="" data-body=""
                data-category="<?php echo htmlspecialchars(PS_ANNOUNCEMENT_CATEGORIES[0]); ?>"
                data-posteddate="<?php echo $today; ?>" data-featured="" data-imagenote="">
                <?php ps_icon('megaphone'); ?> Add Announcement
            </button>
        </div>

        <div class="admin-table" style="--admin-cols: 60px 1fr 130px 90px 120px 160px;">
            <div class="admin-table-head">
                <span></span><span>Title</span><span>Category</span><span>Featured</span><span>Posted</span><span></span>
            </div>
            <?php foreach ($announcements as $a): ?>
                <?php
                $imageUrl = ps_announcement_image_url($a);
                $excerpt = mb_strlen($a['body'], 'UTF-8') > 90 ? mb_substr($a['body'], 0, 90, 'UTF-8') . '…' : $a['body'];
                $isScheduled = $a['posted_date'] > $today;
                ?>
                <div class="admin-row" data-admin-row data-search="<?php echo htmlspecialchars(mb_strtolower($a['title'] . ' ' . $a['category'], 'UTF-8')); ?>">
                    <span>
                        <?php if ($imageUrl): ?>
                            <img class="admin-thumb" src="<?php echo htmlspecialchars($imageUrl); ?>" alt="">
                        <?php else: ?>
                            <span class="admin-proof-none">—</span>
                        <?php endif; ?>
                    </span>
                    <span class="admin-cell-name">
                        <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                        <small><?php echo htmlspecialchars($excerpt); ?></small>
                    </span>
                    <span><?php echo htmlspecialchars($a['category']); ?></span>
                    <span><?php echo $a['is_featured'] ? '<span class="ps-status is-approved">Featured</span>' : ''; ?></span>
                    <span class="admin-cell-name">
                        <strong><?php echo htmlspecialchars(date('M j, Y', strtotime($a['posted_date']))); ?></strong>
                        <?php if ($isScheduled): ?><small>Scheduled</small><?php endif; ?>
                    </span>
                    <span class="admin-cell-actions">
                        <button type="button" class="ps-btn ps-btn-outline" data-modal-trigger="announcementModal"
                            data-modaltitle="Edit Announcement"
                            data-id="<?php echo (int) $a['id']; ?>"
                            data-title="<?php echo htmlspecialchars($a['title']); ?>"
                            data-body="<?php echo htmlspecialchars($a['body']); ?>"
                            data-category="<?php echo htmlspecialchars($a['category']); ?>"
                            data-posteddate="<?php echo htmlspecialchars($a['posted_date']); ?>"
                            data-featured="<?php echo $a['is_featured'] ? '1' : ''; ?>"
                            data-imagenote="<?php echo $imageUrl ? 'The current image stays unless you choose a new one.' : ''; ?>">
                            Edit
                        </button>
                        <button type="button" class="ps-btn ps-btn-outline" data-admin-delete="admin-save-announcement.php"
                            data-id="<?php echo (int) $a['id']; ?>" data-name="<?php echo htmlspecialchars($a['title']); ?>">
                            <span data-submit-label>Delete</span>
                        </button>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-empty" data-admin-empty<?php echo $announcements ? ' hidden' : ''; ?>><?php ps_icon('megaphone'); ?><p><?php echo $announcements ? 'No announcements match this search.' : 'No announcements have been posted yet. Use "Add Announcement" to post the first one.'; ?></p></div>
    </div>

</main>

<div class="ps-modal-overlay" id="announcementModal" data-modal hidden>
    <div class="ps-modal-card" role="dialog" aria-modal="true" aria-labelledby="announcementModalTitle">
        <button type="button" class="ps-modal-close" data-modal-close aria-label="Close"><?php ps_icon('close'); ?></button>
        <h2 class="ps-modal-title" id="announcementModalTitle" data-modal-field="modaltitle">Announcement</h2>
        <form data-admin-form="admin-save-announcement.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" data-modal-field="id">
            <div class="ps-modal-field">
                <label for="annTitle">Title</label>
                <input type="text" id="annTitle" name="title" maxlength="<?php echo PS_ANNOUNCEMENT_TITLE_MAX; ?>" data-modal-field="title" required>
            </div>
            <div class="ps-modal-field">
                <label for="annBody">Announcement text</label>
                <textarea id="annBody" name="body" rows="5" maxlength="<?php echo PS_ANNOUNCEMENT_BODY_MAX; ?>" data-modal-field="body" required></textarea>
            </div>
            <div class="ps-modal-field">
                <label for="annCategory">Category</label>
                <span class="ps-select">
                    <select id="annCategory" name="category" data-modal-field="category" required>
                        <?php foreach (PS_ANNOUNCEMENT_CATEGORIES as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>
            </div>
            <div class="ps-modal-field">
                <label for="annPostedDate">Posted date</label>
                <input type="date" id="annPostedDate" name="posted_date" data-modal-field="posteddate" required>
                <small class="admin-doc-hint">A future date keeps it hidden from parishioners until that day.</small>
            </div>
            <div class="ps-modal-field">
                <label for="annImage">Image <span class="wr-optional">(optional)</span></label>
                <span class="ps-dropzone" data-dropzone>
                    <input type="file" id="annImage" name="image" accept=".png,.jpg,.jpeg" data-max-size-mb="<?php echo PS_ANNOUNCEMENT_IMAGE_MAX_MB; ?>" data-dropzone-input>
                    <span class="ps-dropzone-icon is-ringed"><?php ps_icon('upload'); ?></span>
                    <span class="ps-dropzone-text">Click to upload or drag and drop</span>
                    <span class="ps-dropzone-or">PNG, JPG, JPEG (Max. <?php echo PS_ANNOUNCEMENT_IMAGE_MAX_MB; ?>MB)</span>
                    <span class="ps-dropzone-filename" data-dropzone-filename>No file chosen</span>
                </span>
                <small class="wr-file-error" data-file-error hidden></small>
                <small class="admin-doc-hint" data-modal-field="imagenote"></small>
            </div>
            <div class="ps-modal-field ps-modal-checkbox">
                <label class="ps-toggle">
                    <input type="checkbox" id="annFeatured" name="is_featured" value="1" data-modal-field="featured">
                    <span class="ps-toggle-track"><span class="ps-toggle-thumb"></span></span>
                </label>
                <label for="annFeatured">Show as featured</label>
            </div>
            <div class="admin-modal-error" data-modal-error role="alert" hidden></div>
            <div class="ps-modal-actions">
                <button type="button" class="ps-btn ps-btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="ps-btn ps-btn-primary"><span data-submit-label>Save</span></button>
            </div>
        </form>
    </div>
</div>

<div class="ps-toast" data-toast role="status" aria-live="polite" hidden><?php ps_icon('check-circle'); ?> <span data-toast-text></span></div>

<?php require __DIR__ . '/includes/footer.php'; ?>
