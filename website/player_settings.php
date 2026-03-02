<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['player']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$player = $demo['player'];
$roleLabel='Player'; $roleSub='Player Console'; $sidebarInclude='player_sidebar.php';
$pageTitle='Player Settings'; $activeNav='settings';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-page-grid">
  <section class="sp-card">
    <div class="sp-card__hd"><div><div class="sp-card__title">Manage Profile</div><div class="sp-card__sub">Photo, username, name, team, jersey and account info</div></div></div>
    <div class="sp-card__bd">
      <div class="sp-profile-grid">
        <div class="sp-profile-left">
          <div class="sp-avatar-xl">A</div>
          <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-camera"></i> Upload Photo</button>
          <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-image-portrait"></i> Change Avatar</button>
        </div>
        <div class="sp-profile-right">
          <div class="sp-form-grid">
            <div class="sp-col-6"><label class="sp-form-label">Username</label><input class="sp-input" style="width:100%;" value="player_aleksa" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Role</label><input class="sp-input" style="width:100%;" value="Player" readonly /></div>
            <div class="sp-col-6"><label class="sp-form-label">First name</label><input class="sp-input" style="width:100%;" value="Player" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Last name</label><input class="sp-input" style="width:100%;" value="Aleksa" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Team</label><input class="sp-input" style="width:100%;" value="<?php echo htmlspecialchars($player['profile']['team']); ?>" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Position</label><input class="sp-input" style="width:100%;" value="<?php echo htmlspecialchars($player['profile']['position']); ?>" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Jersey #</label><input class="sp-input" style="width:100%;" value="<?php echo (int)$player['profile']['number']; ?>" /></div>
            <div class="sp-col-6"><label class="sp-form-label">Email</label><input class="sp-input" style="width:100%;" value="player@sportsplay.test" /></div>
            <div class="sp-col-12"><label class="sp-form-label">About</label><textarea class="sp-input" style="width:100%; height:110px; padding:12px;">Central midfielder working on first touch, scanning and passing range.</textarea></div>
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
    <div class="sp-card__hd"><div><div class="sp-card__title">Saved Card & Preferences</div><div class="sp-card__sub">Billing card + notification settings</div></div></div>
    <div class="sp-card__bd">
      <div class="sp-credit-card-ui">
        <div class="chip"></div><div class="number">•••• •••• •••• 4242</div><div class="row"><span>Family Card</span><span>08/28</span></div>
      </div>
      <div class="sp-sep"></div>
      <div class="sp-checklist">
        <label><input type="checkbox" checked> Notify me about coach messages</label>
        <label><input type="checkbox" checked> Notify me about announcements</label>
        <label><input type="checkbox"> Weekly performance summary</label>
      </div>
      <div class="sp-sep"></div>
      <a class="sp-btn sp-btn--ghost" href="player_payments.php"><i class="fa-solid fa-credit-card"></i> Open Payments Page</a>
    </div>
  </section>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>