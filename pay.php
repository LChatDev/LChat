<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
$packages = [
    ['tickets' => 10, 'rubles' => 5],
    ['tickets' => 50, 'rubles' => 25],
    ['tickets' => 100, 'rubles' => 50],
    ['tickets' => 500, 'rubles' => 250],
    ['tickets' => 1000, 'rubles' => 500],
];
$premiumPrice = 199;
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#10101d">
  <meta name="description" content="Tickets and Premium c LChat: prices and features.">
  <title>Tickets and Premium · LChat</title>
  <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/pay.css?v=20261107-2">
  <script src="assets/pay.js?v=20261107-2" defer></script>
<script src="assets/lchatai-secondary-theme.js?v=20261111-1"></script><link rel="stylesheet" href="assets/lchatai-secondary.css?v=20261111-1"></head>
<body class="lchat-secondary lchat-pay">
  <div class="background-glow glow-one" aria-hidden="true"></div>
  <div class="background-glow glow-two" aria-hidden="true"></div>
  <header class="topbar">
    <a class="brand" href="index.php" aria-label="Return to LChat"><span class="brand-icon">L</span><span>LChat</span></a>
    <nav aria-label="Page sections"><a href="#tickets">Tickets</a><a href="#premium">Premium</a></nav>
    <a class="back" href="index.php">In messenger <span aria-hidden="true">↗</span></a>
  </header>
  <main class="shell">
    <section class="hero">
      <div class="hero-copy">
        <span class="eyebrow"><span class="eyebrow-dot"></span> MORE OPPORTUNITIES IN LCHAT</span>
        <h1>Small signs.<br><span>Big emotions.</span></h1>
        <p>Choose gifts for tickets and discover future opportunities LChat Premium.</p>
        <div class="hero-actions"><a class="primary-link" href="#tickets">View tickets <span aria-hidden="true">↗</span></a><a class="ghost-link" href="#premium">Learn about Premium <span aria-hidden="true">↓</span></a></div>
      </div>
      <div class="hero-art" aria-hidden="true"><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div><div class="hero-star">✦</div><span class="spark spark-one">✧</span><span class="spark spark-two">✦</span><span class="spark spark-three">✧</span><span class="floating-chip chip-one">✦ <strong>Tickets</strong></span><span class="floating-chip chip-two">✧ <strong>Premium</strong></span></div>
    </section>

    <div class="availability" id="payment-status" role="status"><span class="availability-icon" aria-hidden="true">i</span><div><strong>Payment system is not connected</strong><p>Buy tickets and issue Premium not yet possible. Tickets are now issued by the administration.</p></div></div>

    <section id="tickets" class="section tickets-section">
      <div class="section-heading"><div><span class="eyebrow">GIVING IS NICE</span><h2>Tickets <span>✦</span></h2><p>Internal currency LChat for purchasing gifts.</p></div><a class="text-link" href="rewards.php">Open gifts <span aria-hidden="true">↗</span></a></div>
      <form id="ticket-calculator" class="ticket-calculator">
        <div class="custom-copy"><span class="custom-icon" aria-hidden="true">✦</span><div><h3>Your quantity</h3><p>Select how many tickets you want to buy.</p></div></div>
        <div class="custom-actions"><div class="quantity-control"><button type="button" id="ticket-minus" aria-label="Reduce quantity">−</button><label><span class="sr-only">Number of tickets</span><input type="number" id="ticket-quantity" min="1" max="100000" step="1" value="10" inputmode="numeric" required></label><button type="button" id="ticket-plus" aria-label="Increase quantity">+</button></div><div class="custom-price"><span>To be paid</span><output id="ticket-total" aria-live="polite" for="ticket-quantity">5 ₽</output></div><button type="submit" class="custom-buy">Buy <span aria-hidden="true">↗</span></button></div>
      </form>
      <div class="ticket-grid">
        <?php foreach ($packages as $package): ?>
        <article class="ticket-card">
          <div class="ticket-symbol" aria-hidden="true">✦</div><span class="card-label">TICKETS LCHAT</span>
          <h3><?=number_format($package['tickets'], 0, '.', ' ')?> <small>tickets</small></h3>
          <p class="ticket-note">For gifts in LChat</p>
          <button type="button" class="ticket-buy" data-payment-unavailable aria-label="Buy <?= $package['tickets'] ?> tickets per <?= $package['rubles'] ?> rubles"><?=number_format($package['rubles'], 0, '.', ' ')?> ₽ <span aria-hidden="true">↗</span></button>
        </article>
        <?php endforeach; ?>
      </div>
      <p class="rate-note">10 tickets = 5 ₽. The price of other sets is calculated at the same rate.</p>
    </section>

    <section id="premium" class="section premium-section">
      <div class="premium-intro"><div><span class="eyebrow">A LITTLE MORE OF YOUR STYLE</span><h2>LChat <span>Premium</span></h2><p>Ideas for the subscription we are preparing. Features are still under development and may change before launch..</p></div><div class="premium-emblem" aria-hidden="true">✦</div></div>
      <div class="premium-grid">
        <article class="benefit"><span class="benefit-icon violet" aria-hidden="true">✦</span><h3>Icon Premium</h3><p>Special star next to your name and profile.</p><span class="planned">Planned</span></article>
        <article class="benefit"><span class="benefit-icon rose" aria-hidden="true">◈</span><h3>Profile design</h3><p>Additional colors and beautiful accents for the profile.</p><span class="planned">Planned</span></article>
        <article class="benefit"><span class="benefit-icon blue" aria-hidden="true">◌</span><h3>Chat topics</h3><p>A selection of exclusive backgrounds for your conversations.</p><span class="planned">Planned</span></article>
        <article class="benefit"><span class="benefit-icon gold" aria-hidden="true">✧</span><h3>Status with emoji</h3><p>Animated emoji next to the name of your choice.</p><span class="planned">Planned</span></article>
      </div>
      <div class="subscription"><div><span class="card-label">SUBSCRIPTION</span><p><strong><?=$premiumPrice?> ₽</strong> <span>/ month</span></p><small>Registration will become available after payment is connected.</small></div><div class="subscription-actions"><label>Deadline <select id="premium-duration" data-monthly-price="<?=$premiumPrice?>" aria-label="Subscription period"><option value="1">1 month</option><option value="3">3 of the month</option><option value="6">6 months</option><option value="12">12 months</option></select></label><div class="premium-total">Total: <output id="premium-total" aria-live="polite" for="premium-duration"><?=$premiumPrice?> ₽</output></div><button type="button" class="premium-buy" data-payment-unavailable>Issue Premium <span aria-hidden="true">✦</span></button></div></div>
    </section>
    <section class="faq"><h2>What you need to know</h2><div class="faq-grid"><article><h3>How to spend tickets?</h3><p>Select a gift from the catalog and send it to yourself or another user.</p></article><article><h3>You can pay now?</h3><p>Not yet. After connecting the payment system, the payment option will appear here.</p></article><article><h3>When it starts working Premium?</h3><p>Subscription and its features are under development. We will inform you about the launch separately..</p></article></div></section>
  </main>
  <footer class="footer"><a class="footer-brand" href="index.php">LChat <span>✦</span></a><div><a href="privacy.php">Confidentiality</a><a href="terms.php">Agreement</a><a href="rules.php#refund">Rules and refunds</a><a href="mailto:admin@example.com">Support</a></div></footer>
  <dialog id="payment-dialog" aria-labelledby="payment-title" aria-describedby="payment-description"><div class="dialog-inner"><button type="button" class="dialog-close" data-close-dialog aria-label="Close">×</button><div class="dialog-icon" aria-hidden="true">✦</div><h2 id="payment-title">Payment system<br>not connected!</h2><p id="payment-description">Not yet available for purchase. Check back later when we’ve completed payment..</p><button type="button" class="dialog-ok" data-close-dialog>I see</button></div></dialog>
  <noscript><style>[data-payment-unavailable],.ticket-calculator,.subscription-actions{display:none}</style><p class="noscript">Payment is not yet available: the payment system is not connected.</p></noscript>
</body>
</html>
