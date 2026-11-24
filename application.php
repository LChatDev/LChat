<?php
declare(strict_types=1);
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

$versions = [
    [
        'version' => '2.1 (210)',
        'date' => '2026-11-24',
        'size' => '484.7 KB',
        'sha256' => '0f995e3e153f9034c59e74bc115108c152714590bd02b4f9850521dd6a8d18d1',
        'file' => 'https://data.lbs.su/lchat-apps/LChat.apk',
        'latest' => true,
        'changes' => ['APK supplied by the owner; published without rebuilding.','Different signing key: cannot update old signed builds.'],
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <meta name="theme-color" content="#212121">
  <meta name="description" content="Application versions LChat for Android">
  <title>Appendix LChat</title>
  <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/application.css?v=20261101-apps1">
<script src="assets/lchatai-secondary-theme.js?v=20261111-1"></script><link rel="stylesheet" href="assets/lchatai-secondary.css?v=20261111-1"></head>
<body class="lchat-secondary lchat-apps">
  <header class="topbar">
    <a class="back" href="index.php" aria-label="Return to LChat">←</a>
    <a class="brand" href="index.php"><span class="brand-mark">L</span><span>LChat<small>Appendix for Android</small></span></a>
  </header>
  <main>
    <section class="hero">
      <div class="app-icon" aria-hidden="true"><span>L</span></div>
      <div>
        <span class="eyebrow">OFFICIAL ASSEMBLY LCHAT</span>
        <h1>Appendix LChat</h1>
        <p>Native chats, groups, channels, voice messages and internet calls. Select version and download APK directly from this site.</p>
        <div class="requirements"><span>Android 7.0+</span><span>APK · 2.1 · code 210</span></div>
      </div>
    </section>

<section class="versions" aria-labelledby="versions-title">
      <div class="section-heading"><div><span class="eyebrow">VERSION HISTORY</span><h2 id="versions-title">Available assemblies</h2></div><span><?= count($versions) ?> versions</span></div>
      <?php foreach ($versions as $index => $item): ?>
        <article class="version-card<?= !empty($item['latest']) ? ' latest' : '' ?>">
          <div class="version-main">
            <div class="version-number"><span><?= htmlspecialchars($item['version']) ?></span><?php if (!empty($item['latest'])): ?><b>Last</b><?php else: ?><b class="archive">Archive</b><?php endif; ?></div>
            <p class="meta"><?= htmlspecialchars($item['date']) ?> · <?= htmlspecialchars($item['size']) ?></p>
            <ul><?php foreach ($item['changes'] as $change): ?><li><?= htmlspecialchars($change) ?></li><?php endforeach; ?></ul>
          </div>
          <div class="version-actions">
            <a class="download" href="<?= htmlspecialchars($item['file']) ?>" download>Download APK <span>↓</span></a>
            <button class="checksum" type="button" data-hash="<?= htmlspecialchars($item['sha256']) ?>" aria-label="Copy SHA-256 version <?= htmlspecialchars($item['version']) ?>">SHA-256</button>
          </div>
        </article>
      <?php endforeach; ?>
    </section>

    <section class="install">
      <h2>How to install</h2>
      <ol><li>Download the latest version APK.</li><li>Open the file on your phone.</li><li>If Android will ask for permission, allow installation from this source.</li><li>This APK uses a different signing key. Preserve needed local data before uninstalling the old app; Android cannot update it with this file.</li></ol>
      <p>Old versions are left for diagnostic purposes. For normal use, select the build marked ”Latest”».</p>
    </section>
  </main>
  <div id="toast" role="status" aria-live="polite" hidden>SHA-256 copied</div>
  <script>
  document.querySelectorAll('.checksum').forEach(button => button.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(button.dataset.hash);
      const toast = document.getElementById('toast'); toast.hidden = false;
      clearTimeout(window.lchatToastTimer); window.lchatToastTimer = setTimeout(() => toast.hidden = true, 1800);
    } catch { window.prompt('SHA-256', button.dataset.hash); }
  }));
  </script>
</body>
</html>
