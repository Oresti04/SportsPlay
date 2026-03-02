<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['parent']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$parent = $demo['parent'];
$roleLabel='Parent'; $roleSub='Parent Console'; $sidebarInclude='parent_sidebar.php';
$pageTitle='Parent Settings'; $activeNav='settings';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-page-grid">
  <section class="sp-card">
    <div class="sp-card__hd"><div><div class="sp-card__title">Manage Profile</div><div class="sp-card__sub">Photo, identity, children, contacts and notifications</div></div></div>
    <div class="sp-card__bd">
      <div class="sp-profile-grid">
        <div class="sp-profile-left">
          <div class="sp-avatar-xl">P</div>
          <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-camera"></i> Upload Photo</button>
          <div class="sp-muted" style="font-size:12px;">Static uploader UI</div>
        </div>
        <div class="sp-profile-right">
          <div class="sp-form-grid">
            <div class="sp-col-6"><label class="sp-form-label">Username</label><input class="sp-input" style="width:100%;" value="parent_maria" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Role</label><input class="sp-input" style="width:100%;" value="Parent" readonly /></div>
            <div class="sp-col-6"><label class="sp-form-label">First name</label><input class="sp-input" style="width:100%;" value="Parent" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Last name</label><input class="sp-input" style="width:100%;" value="Maria" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Email</label><input class="sp-input" style="width:100%;" value="parent@sportsplay.test" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Phone</label><input class="sp-input" style="width:100%;" value="(585) 555-2101" /></div>
            <div class="sp-col-12"><label class="sp-form-label">Linked Children</label><input class="sp-input" style="width:100%;" value="Luka Petrovic, Mia Petrovic" /></div>
            <div class="sp-col-12"><label class="sp-form-label">Address</label><input class="sp-input" style="width:100%;" value="Rochester, NY (demo)" /></div>
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
    <div class="sp-card__hd"><div><div class="sp-card__title">Payment Card & Preferences</div><div class="sp-card__sub">Saved card + communication settings</div></div></div>
    <div class="sp-card__bd">
      <div class="sp-credit-card-ui">
        <div class="chip"></div>
        <div class="number">•••• •••• •••• 4242</div>
        <div class="row"><span>Parent Maria</span><span>08/28</span></div>
      </div>
      <div class="sp-sep"></div>
      <div class="sp-checklist">
        <label><input type="checkbox" checked> Email me about schedule changes</label>
        <label><input type="checkbox" checked> Push notifications for coach announcements</label>
        <label><input type="checkbox"> Auto-pay season fees (future)</label>
      </div>
    </div>
  </section>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>