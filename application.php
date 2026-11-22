<?php
declare(strict_types=1);
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

$versions = [
    [
        'version' => '3.0.0',
        'date' => '2026-11-22',
        'size' => '22.7 MB',
        'sha256' => '868dc3280f85de11fd883e9ea2db1c9250d3bd4d10d6537f015c42f0cb3481ae',
        'file' => 'https://github.com/LChatDev/LChat/releases/download/android-v3.0.0/LChat-3.0.0-universal.apk',
        'latest' => true,
        'changes' => ['Native Calls v3: audio, signaling and lifecycle rebuilt.','Fixed reproduced Android 12 crash: required network-state permission.','Tested two-way audio through TURN, background, screen off and repeat calls.'],
    ],
    [
        'version' => '2.2.3',
        'date' => '2026-11-21',
        'size' => '21.9 MB',
        'sha256' => 'dda7dc696a25790b18150595e49aabecbca95a8688e09355128210dbc47c988a',
        'file' => 'https://github.com/LChatDev/LChat/releases/download/android-v2.2.3/LChat-2.2.3-universal.apk',
        'latest' => false,
        'changes' => ['Pixel palette and chat wallpaper.','Updated launcher icon and sharper native radii.'],
    ],
    [
        'version' => '2.2.2',
        'date' => '2026-11-20',
        'size' => '22.7 MB',
        'sha256' => '13db5337dde2e54ce4f1d39883feb3fafb0324a9d417090bd1098b61bdf7ff81',
        'file' => 'https://github.com/LChatDev/LChat/releases/download/android-v2.2.2/LChat-2.2.2-universal.apk',
        'latest' => false,
        'changes' => ['WebRTC 150.7871.08 and standard audio-track negotiation.','Additional native call diagnostics. Device verification required.'],
    ],
    [
        'version' => '2.2.1',
        'date' => '2026-11-20',
        'size' => '22.7 MB',
        'sha256' => '1467890018f2fe83009a36410b95fd01d10d80af26411806ddf5265e188c105f',
        'file' => 'https://github.com/LChatDev/LChat/releases/download/clients-2026.11.20/LChat-2.2.1-universal.apk',
        'latest' => false,
        'changes' => ['Agreement acceptance and email confirmation guidance.','Password reset link on the login screen.'],
    ],
    [
        'version' => '2.2.0',
        'date' => '30 October 2026',
        'size' => '22,7 MB',
        'sha256' => 'aafbe3a5ab22bc02b7fbd0be15a37c679c2b9e549aff2ad58cae3b354e5bb6fb',
        'file' => 'https://github.com/LChatDev/LChat/releases/download/android-v2.2.0/LChat-2.2.0-universal.apk',
        'latest' => false,
        'changes' => [
            'Native interface of chats, groups and channels in the style LChat.',
            'Separate collapsible call screen and participant list.',
            'Replies, reactions, editing messages and managing communities.',
            'Update of temporary TURN-data and improved connection recovery.',
        ],
    ],
    [
        'version' => '2.1.0',
        'date' => '30 October 2026',
        'size' => '22,7 MB',
        'sha256' => '40a40acb686705a8be0de96b26fa0872cc68a2a94f28ef4ac491d4e0ac4af5c4',
        'file' => 'https://github.com/LChatDev/LChat/releases/download/android-v2.1.0/LChat-2.1.0-universal.apk',
        'changes' => ['Text messages up to 10 MiB.', 'Opening very long messages page by page.', 'Diagnostics of the audio process of calls.'],
    ],
    [
        'version' => '2.0.9',
        'date' => '30 October 2026',
        'size' => '22,7 MB',
        'sha256' => 'a137828c7a920392d0e29de39adfdfa0fe0137effdccb375ffa1901ef68e0259',
        'file' => 'https://github.com/LChatDev/LChat/releases/download/android-v2.0.9/LChat-2.0.9-universal.apk',
        'changes' => ['Changed processing of audio effects on devices Samsung.', 'Diagnostics of native failures has been expanded WebRTC.'],
    ],
    [
        'version' => '2.0.8',
        'date' => '30 October 2026',
        'size' => '22,7 MB',
        'sha256' => 'e6e7dd123cd069a95a60dc58dc68b7aa88034e7be730474f18f50ccf4b28040a',
        'file' => 'https://github.com/LChatDev/LChat/releases/download/android-v2.0.8/LChat-2.0.8-universal.apk',
        'changes' => ['Added transcript of audio process crash.', 'Improved call diagnostic report.'],
    ],
    [
        'version' => '2.0.7',
        'date' => '30 October 2026',
        'size' => '22,7 MB',
        'sha256' => 'bfdf0d8cef94fe4082a3d5e31421d2ef1f6b789a56a8d70e6a0c556cba6cb529',
        'file' => 'https://github.com/LChatDev/LChat/releases/download/android-v2.0.7/LChat-2.0.7-universal.apk',
        'changes' => ['Stabilization of a separate audio process.', 'Fix clearing state after call ends.'],
    ],
    [
        'version' => '2.0.6',
        'date' => '30 October 2026',
        'size' => '22,7 MB',
        'sha256' => '5344c9c5c1c2c65c580e4b6bd3f3cefdd3bee4a0c372877eb036d15122657615',
        'file' => 'https://github.com/LChatDev/LChat/releases/download/android-v2.0.6/LChat-2.0.6-universal.apk',
        'changes' => ['Improved startup of background audio service.', 'Added restoration of call state after restarting the application.'],
    ],
    [
        'version' => '2.0.5',
        'date' => '30 October 2026',
        'size' => '22,7 MB',
        'sha256' => 'a479780d64839680bd42f7a891ef293ad0fc6e673b849fa10aea677bfa6d9b85',
        'file' => 'https://github.com/LChatDev/LChat/releases/download/android-v2.0.5/LChat-2.0.5-universal.apk',
        'changes' => ['Early version of native Android-client.', 'Chats, channels, voice messages and internet calls.'],
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
        <div class="requirements"><span>Android 8.0+</span><span>ARM64 · ARMv7 · x86 · x86_64</span></div>
      </div>
    </section>

<section class="install"><h2>LChat for Windows</h2><p>Portable client for Windows 10/11 x64. Close to tray; use Exit to end calls. No publisher signature.</p><a class="download" href="https://github.com/LChatDev/LChat/releases/download/clients-2026.11.20/LChat-1.0.0-Windows-x64.exe">Download EXE</a></section>
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
      <ol><li>Download the latest version APK.</li><li>Open the file on your phone.</li><li>If Android will ask for permission, allow installation from this source.</li><li>Install the application over the old version - your account and settings will be saved.</li></ol>
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
