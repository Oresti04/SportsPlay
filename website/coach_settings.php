<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['coach']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$coach = $demo['coach'];
$roleLabel='Coach'; $roleSub='Coach Console'; $sidebarInclude='coach_sidebar.php';
$pageTitle='Coach Settings'; $activeNav='settings';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-page-grid">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Manage Profile</div><div class="sp-card__sub">Photo, identity, team details and account preferences</div></div>
      <span class="sp-pill"><i class="fa-solid fa-user-gear"></i> Profile</span>
    </div>
    <div class="sp-card__bd">
      <div class="sp-profile-grid">
        <div class="sp-profile-left">
          <div class="sp-avatar-xl">C</div>
          <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-camera"></i> Upload Photo</button>
          <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-trash"></i> Remove</button>
          <div class="sp-muted" style="font-size:12px;">JPG/PNG · UI only for now</div>
        </div>
        <div class="sp-profile-right">
          <div class="sp-form-grid">
            <div class="sp-col-6"><label class="sp-form-label">Username</label><input class="sp-input" style="width:100%;" value="coach_jovan" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Role</label><input class="sp-input" style="width:100%;" value="Coach" readonly /></div>
            <div class="sp-col-6"><label class="sp-form-label">First name</label><input class="sp-input" style="width:100%;" value="Coach" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Last name</label><input class="sp-input" style="width:100%;" value="Jovan" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Email</label><input class="sp-input" style="width:100%;" value="coach@sportsplay.test" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Phone</label><input class="sp-input" style="width:100%;" value="(585) 555-2007" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Team</label><input class="sp-input" style="width:100%;" value="<?php echo htmlspecialchars($coach['team']['name']); ?>" /></div>
            <div class="sp-col-6"><label class="sp-form-label">League</label><input class="sp-input" style="width:100%;" value="Division A" /></div>
            <div class="sp-col-12"><label class="sp-form-label">Bio</label><textarea class="sp-input" style="width:100%; height:120px; padding:12px;">U14 coach focused on development, communication, and match confidence.</textarea></div>
          </div>
          <div class="sp-form-actions">
            <button class="sp-btn sp-btn--ghost" type="button">Cancel</button>
            <button class="sp-btn sp-btn--primary" type="button"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd"><div><div class="sp-card__title">Preferences & Card</div><div class="sp-card__sub">Optional payment / reimbursement card + notifications</div></div></div>
    <div class="sp-card__bd">
      <div class="sp-soft-panel sp-soft-panel--dark">
        <div class="sp-statline"><span>Saved Card (optional)</span><strong>VISA •••• 4242</strong></div>
        <div class="sp-statline"><span>Expiry</span><strong>08/28</strong></div>
        <div class="sp-statline"><span>Billing ZIP</span><strong>14623</strong></div>
      </div>
      <div class="sp-sep"></div>
      <div class="sp-checklist">
        <label><input type="checkbox" checked> Email me when parent sends a private message</label>
        <label><input type="checkbox" checked> Push alert for urgent announcements</label>
        <label><input type="checkbox"> Weekly roster summary report</label>
      </div>
    </div>
  </section>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>