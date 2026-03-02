<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['coach']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$coach = $demo['coach'];
$roleLabel='Coach'; $roleSub='Coach Console'; $sidebarInclude='coach_sidebar.php';
$pageTitle='Coach Announcements'; $activeNav='announcements';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-page-grid">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Create Announcement</div><div class="sp-card__sub">Broadcast to players and/or parents (UI only)</div></div>
      <span class="sp-pill sp-pill--warning"><i class="fa-solid fa-bullhorn"></i> Static form</span>
    </div>
    <div class="sp-card__bd">
      <div class="sp-form-grid">
        <div class="sp-col-6">
          <label class="sp-form-label">Audience</label>
          <select class="sp-select" style="width:100%;">
            <option>Players + Parents</option>
            <option>Players only</option>
            <option>Parents only</option>
            <option>Selected contacts (future)</option>
          </select>
        </div>
        <div class="sp-col-6">
          <label class="sp-form-label">Priority</label>
          <select class="sp-select" style="width:100%;">
            <option>Normal</option><option>Important</option><option>Urgent</option>
          </select>
        </div>
        <div class="sp-col-12">
          <label class="sp-form-label">Title</label>
          <input class="sp-input" style="width:100%;" type="text" value="Saturday Match Logistics" />
        </div>
        <div class="sp-col-12">
          <label class="sp-form-label">Message</label>
          <textarea class="sp-input" style="width:100%; height:140px; padding:12px;" placeholder="Write announcement...">Please arrive 15 minutes early. Bring both kits (blue/white), shin guards and water bottle.</textarea>
        </div>
        <div class="sp-col-12">
          <div class="sp-actions">
            <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-eye"></i> Preview</button>
            <button class="sp-btn sp-btn--primary" type="button"><i class="fa-solid fa-paper-plane"></i> Send Announcement (Demo)</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Announcement Tips</div><div class="sp-card__sub">Suggested content</div></div>
      <span class="sp-pill" style="background:rgba(255,255,255,.14);color:#fff;border-color:rgba(255,255,255,.2);"><i class="fa-solid fa-lightbulb"></i> Best practice</span>
    </div>
    <div class="sp-card__bd">
      <ul class="sp-bullets">
        <li>Include date, arrival time, and exact field/location.</li>
        <li>Mention required gear (kits, shin guards, water).</li>
        <li>Use private messages for individual issues.</li>
        <li>Keep emergency changes at the top of the message.</li>
      </ul>
    </div>
  </section>
</section>

<section class="sp-card">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">Sent Announcements</div><div class="sp-card__sub">Recent team broadcasts (demo)</div></div>
    <a class="sp-btn sp-btn--ghost" href="coach_messages.php"><i class="fa-solid fa-comments"></i> Open Private Messages</a>
  </div>
  <div class="sp-card__bd">
    <div class="sp-stack">
      <?php foreach ($coach['announcements'] as $a): ?>
        <article class="sp-announcement-card">
          <div class="sp-announcement-card__hd">
            <div>
              <h3><?php echo htmlspecialchars($a['title']); ?></h3>
              <div class="sp-announcement-card__sub"><?php echo htmlspecialchars($a['time']); ?> · Audience: <?php echo htmlspecialchars($a['audience']); ?></div>
            </div>
            <span class="sp-pill sp-pill--success"><i class="fa-solid fa-check"></i> Sent</span>
          </div>
          <p><?php echo htmlspecialchars($a['body']); ?></p>
          <div class="sp-actions">
            <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-copy"></i> Duplicate</button>
            <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-pen"></i> Edit (future)</button>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>