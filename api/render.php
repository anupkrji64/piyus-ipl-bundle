<?php
/**
 * Shared page renderer — used by index.php and page.php
 * Call: render_page($config, $base_path)
 */
function render_page(array $config, string $base_path = ''): void {
  $s   = $config['site']     ?? [];
  $h   = $config['hero']     ?? [];
  $p   = $config['pricing']  ?? [];
  $btn = $config['buy_button'] ?? [];
  $cta = $config['cta']      ?? [];
  $pol = $config['policies'] ?? [];
  $rzp = $config['razorpay'] ?? [];

  $cur       = $p['currency_symbol'] ?? '₹';
  $price_now = $p['current_price']   ?? '29';
  $price_old = $p['original_price']  ?? '999';
  $disc      = $p['discount_label']  ?? '';

  $use_rzp   = !empty($rzp['key_id']) && ($btn['use_razorpay'] ?? false);
  $buy_url   = $btn['url'] ?? '#';
  $amount_paise = (int)$price_now * 100;

  function e2($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
  function stars2($n){ return str_repeat('★', max(1,min(5,(int)$n))).str_repeat('☆', 5-max(1,min(5,(int)$n))); }
  function nl2md($text){
    $text = e2($text);
    $text = preg_replace('/^### (.+)$/m','<h3>$1</h3>',$text);
    $text = preg_replace('/^## (.+)$/m','<h2 class="mh2">$1</h2>',$text);
    $text = preg_replace('/\*\*(.+?)\*\*/','<span class="updated">$1</span>',$text);
    $text = preg_replace('/^- (.+)$/m','<li>$1</li>',$text);
    $text = preg_replace('/(<li>[\s\S]+?<\/li>)+/','<ul>$0</ul>',$text);
    $text = preg_replace('/\n{2,}/','</p><p class="mp">',$text);
    return '<p class="mp">'.$text.'</p>';
  }

  ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="description" content="<?= e2($s['meta_description'] ?? '') ?>"/>
<title><?= e2($s['title'] ?? 'Bundle') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --or:#f97316;--or2:#ea580c;--ye:#fbbf24;
  --bl:#1e3a8a;--bl2:#1d4ed8;--dk:#0f172a;
  --g1:#f8fafc;--g2:#f1f5f9;--g3:#e2e8f0;--g4:#94a3b8;
  --tx:#1e293b;--tx2:#475569;
}
body{font-family:'Inter',sans-serif;background:#fff;color:var(--tx);overflow-x:hidden}

/* TOP BAR */
.topbar{background:var(--bl);color:#fff;text-align:center;font-size:12px;font-weight:600;padding:9px 12px;letter-spacing:.3px;line-height:1.4}
.topbar span{color:var(--ye)}

/* NAV */
nav{background:#fff;border-bottom:1px solid var(--g3);padding:12px 16px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 1px 8px rgba(0,0,0,.06);gap:8px}
.logo{font-size:17px;font-weight:900;color:var(--bl);display:flex;align-items:center;gap:5px;white-space:nowrap}
.logo .dot{color:var(--or)}
.nav-btn{background:linear-gradient(135deg,var(--or),var(--or2));color:#fff;border:none;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;white-space:nowrap;transition:opacity .2s}
.nav-btn:hover{opacity:.9}

/* HERO */
.hero{background:linear-gradient(135deg,#eff6ff 0%,#fff7ed 50%,#fef3c7 100%);padding:40px 20px 36px;display:flex;align-items:center;justify-content:center;gap:40px;flex-wrap:wrap}
.hero-text{max-width:520px;width:100%}
.hero-badge{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1.5px solid #fed7aa;color:var(--or2);font-size:11px;font-weight:700;padding:5px 12px;border-radius:30px;margin-bottom:16px;text-transform:uppercase;letter-spacing:1px}
.hero h1{font-size:clamp(26px,6vw,52px);font-weight:900;line-height:1.12;color:var(--dk);margin-bottom:14px}
.hero h1 .hl{background:linear-gradient(90deg,var(--or),var(--ye));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero-desc{font-size:15px;color:var(--tx2);line-height:1.65;margin-bottom:22px}
.price-row{display:flex;align-items:center;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.price-new{font-size:44px;font-weight:900;color:var(--or);line-height:1}
.price-new sub{font-size:20px;vertical-align:super}
.price-old{font-size:20px;color:var(--g4);text-decoration:line-through}
.price-off{background:#dcfce7;color:#16a34a;font-size:12px;font-weight:700;padding:4px 10px;border-radius:20px;border:1px solid #bbf7d0}
.btn-buy{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(135deg,var(--or),var(--or2));color:#fff;font-size:17px;font-weight:800;padding:15px 36px;border-radius:12px;text-decoration:none;border:none;cursor:pointer;box-shadow:0 8px 28px rgba(249,115,22,.35);transition:transform .2s,box-shadow .2s;width:100%;max-width:380px;font-family:inherit}
.btn-buy:hover{transform:translateY(-2px);box-shadow:0 12px 36px rgba(249,115,22,.5)}
.trust-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:16px}
.trust-item{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--tx2);font-weight:500}

/* HERO IMAGE */
.hero-img-wrap{position:relative;flex-shrink:0;width:300px;max-width:100%}
.hero-img-wrap img{width:100%;border-radius:16px;box-shadow:0 24px 60px rgba(0,0,0,.15);display:block;animation:float 4s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
.float-badges{display:flex;gap:8px;justify-content:center;margin-top:12px;flex-wrap:wrap}
.float-badge{background:#fff;border-radius:20px;padding:6px 14px;font-size:12px;font-weight:700;box-shadow:0 4px 16px rgba(0,0,0,.1);display:inline-flex;align-items:center;gap:5px}
.fb-or{color:var(--or2)}
.fb-bl{color:var(--bl2)}
.fb-gr{color:#16a34a}

/* STATS */
.stats{background:var(--bl);padding:32px 20px}
.stats-inner{max-width:900px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:16px}
.stat-item{text-align:center}
.stat-num{font-size:30px;font-weight:900;color:var(--ye);line-height:1}
.stat-label{font-size:12px;color:#93c5fd;margin-top:5px;font-weight:500}

/* SECTIONS */
.section{padding:56px 20px}
.section-inner{max-width:960px;margin:0 auto}
.section-tag{display:inline-block;background:#fff7ed;color:var(--or2);font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;border:1px solid #fed7aa;text-transform:uppercase;letter-spacing:1px;margin-bottom:10px}
.section-title{font-size:clamp(22px,4vw,34px);font-weight:900;color:var(--dk);margin-bottom:8px}
.section-sub{font-size:15px;color:var(--tx2);margin-bottom:36px;max-width:540px;line-height:1.6}

/* FEATURES */
.features-bg{background:var(--g1)}
.features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
.feat-card{background:#fff;border:1px solid var(--g3);border-radius:14px;padding:24px 20px;transition:transform .2s,box-shadow .2s,border-color .2s}
.feat-card:hover{transform:translateY(-3px);box-shadow:0 10px 36px rgba(0,0,0,.07);border-color:#fed7aa}
.feat-icon{width:44px;height:44px;border-radius:10px;background:#fff7ed;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:14px}
.feat-card h3{font-size:15px;font-weight:700;margin-bottom:6px;color:var(--dk)}
.feat-card p{font-size:13px;color:var(--tx2);line-height:1.6}

/* STEPS */
.steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;margin-top:10px}
.step{display:flex;flex-direction:column;align-items:center;text-align:center;padding:20px 12px}
.step-num{width:52px;height:52px;background:linear-gradient(135deg,var(--or),var(--ye));color:#fff;font-size:20px;font-weight:900;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:12px;box-shadow:0 6px 18px rgba(249,115,22,.3)}
.step h3{font-size:14px;font-weight:700;margin-bottom:5px;color:var(--dk)}
.step p{font-size:12px;color:var(--tx2);line-height:1.5}

/* BEST FOR */
.bestfor-bg{background:var(--g2)}
.best-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px}
.best-card{background:#fff;border:1.5px solid var(--g3);border-radius:12px;padding:18px 16px;display:flex;align-items:center;gap:12px;transition:border-color .2s,box-shadow .2s}
.best-card:hover{border-color:#fed7aa;box-shadow:0 5px 20px rgba(0,0,0,.06)}
.best-icon{width:40px;height:40px;background:linear-gradient(135deg,var(--or),var(--ye));border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.best-card span{font-size:13px;font-weight:700;color:var(--dk)}

/* TESTIMONIALS */
.testi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
.testi-card{background:#fff;border:1px solid var(--g3);border-radius:14px;padding:22px 18px}
.stars{color:var(--ye);font-size:15px;margin-bottom:8px}
.testi-text{font-size:13px;color:var(--tx2);line-height:1.6;margin-bottom:14px}
.testi-author{display:flex;align-items:center;gap:10px}
.av{width:34px;height:34px;background:linear-gradient(135deg,var(--or),var(--ye));border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:13px;flex-shrink:0}
.testi-author .info strong{font-size:12px;display:block;color:var(--dk)}
.testi-author .info span{font-size:11px;color:var(--g4)}

/* CTA */
.cta-bottom{background:linear-gradient(135deg,var(--bl),var(--bl2));padding:64px 20px;text-align:center}
.cta-bottom h2{font-size:clamp(22px,5vw,42px);font-weight:900;color:#fff;margin-bottom:10px}
.cta-bottom p.sub{font-size:15px;color:#93c5fd;margin-bottom:28px}
.cta-price-row{display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:26px;flex-wrap:wrap}
.cta-price-new{font-size:48px;font-weight:900;color:var(--ye);line-height:1}
.cta-price-old{font-size:20px;color:rgba(255,255,255,.35);text-decoration:line-through}
.cta-off{background:#dcfce7;color:#16a34a;font-size:13px;font-weight:700;padding:5px 12px;border-radius:20px}
.btn-buy-white{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:var(--or2);font-size:17px;font-weight:800;padding:15px 40px;border-radius:12px;text-decoration:none;box-shadow:0 8px 28px rgba(0,0,0,.22);transition:transform .2s;border:none;cursor:pointer;font-family:inherit;width:100%;max-width:360px}
.btn-buy-white:hover{transform:translateY(-2px)}
.cta-note{margin-top:14px;font-size:12px;color:#93c5fd}

/* FOOTER */
footer{background:var(--dk);padding:30px 20px;text-align:center}
.footer-logo{font-size:17px;font-weight:900;color:#fff;margin-bottom:12px}
.footer-logo span{color:var(--or)}
.footer-links{display:flex;justify-content:center;gap:16px;flex-wrap:wrap;margin-bottom:12px}
.footer-links a{font-size:12px;color:#64748b;text-decoration:none;cursor:pointer}
.footer-links a:hover{color:var(--or)}
footer p{font-size:11px;color:#334155}

/* MODALS */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:999;align-items:flex-end;justify-content:center;padding:0}
.modal-overlay.active{display:flex}
.modal-box{background:#fff;border-radius:20px 20px 0 0;width:100%;max-width:600px;max-height:88vh;overflow-y:auto;box-shadow:0 -8px 40px rgba(0,0,0,.2);animation:slideUp .3s ease}
@keyframes slideUp{from{transform:translateY(100%)}to{transform:translateY(0)}}
.modal-header{display:flex;align-items:center;justify-content:space-between;padding:18px 20px 14px;border-bottom:1px solid var(--g3);position:sticky;top:0;background:#fff;border-radius:20px 20px 0 0}
.modal-header h2{font-size:17px;font-weight:800;color:var(--dk)}
.modal-close{width:32px;height:32px;border-radius:50%;border:none;background:var(--g2);cursor:pointer;font-size:16px;color:var(--tx2);display:flex;align-items:center;justify-content:center}
.modal-close:hover{background:var(--g3)}
.modal-body{padding:20px 20px 32px;font-size:14px;color:var(--tx2);line-height:1.75}
.modal-body h3{font-size:14px;font-weight:700;color:var(--dk);margin:18px 0 6px}
.modal-body .mh2{font-size:16px;font-weight:800;margin-bottom:6px}
.modal-body h3:first-child{margin-top:0}
.modal-body .mp{margin-bottom:8px}
.modal-body ul{padding-left:16px;margin-bottom:8px}
.modal-body ul li{margin-bottom:4px}
.modal-body .updated{display:inline-block;background:#eff6ff;color:var(--bl2);font-size:11px;font-weight:600;padding:2px 10px;border-radius:16px;margin-bottom:14px}

/* DESKTOP OVERRIDES */
@media(min-width:768px){
  .hero{padding:64px 40px 56px;gap:56px}
  .hero-img-wrap{width:320px}
  .float-badges{flex-direction:column;position:absolute;right:-20px;top:50%;transform:translateY(-50%);gap:8px;margin-top:0;justify-content:flex-start}
  .float-badge{white-space:nowrap}
  .modal-overlay{align-items:center;padding:20px}
  .modal-box{border-radius:18px;max-height:85vh}
  @keyframes slideUp{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
  .stats-inner{grid-template-columns:repeat(5,1fr)}
  .section{padding:64px 40px}
  nav{padding:14px 40px}
}
@media(max-width:480px){
  .price-new{font-size:38px}
  .btn-buy{font-size:15px;padding:14px 24px}
  .trust-row{grid-template-columns:1fr 1fr}
}
</style>
</head>
<body>

<div class="topbar"><?= e2($s['topbar_text'] ?? '') ?></div>

<nav>
  <div class="logo">🏏 IPL<span class="dot">Reels</span>Bundle</div>
  <?php if($use_rzp): ?>
  <button class="nav-btn" onclick="openRzp()">Buy – <?= e2($cur.$price_now) ?></button>
  <?php else: ?>
  <a href="<?= e2($buy_url ?: '#') ?>" class="nav-btn"><?= e2($btn['nav_text'] ?? 'Buy Now') ?></a>
  <?php endif; ?>
</nav>

<section class="hero">
  <div class="hero-text">
    <div class="hero-badge"><?= e2($h['badge'] ?? '') ?></div>
    <h1>
      <?= e2($h['heading_line1'] ?? '') ?><br/>
      <span class="hl"><?= e2($h['heading_highlight'] ?? '') ?></span><br/>
      <?= e2($h['heading_line2'] ?? '') ?>
    </h1>
    <p class="hero-desc"><?= e2($h['description'] ?? '') ?></p>
    <div class="price-row">
      <div class="price-new"><sub><?= e2($cur) ?></sub><?= e2($price_now) ?></div>
      <div class="price-old"><?= e2($cur.$price_old) ?></div>
      <?php if($disc): ?><div class="price-off"><?= e2($disc) ?></div><?php endif; ?>
    </div>
    <?php if($use_rzp): ?>
    <button class="btn-buy" onclick="openRzp()"><?= e2($btn['text'] ?? '🛒 Buy Now') ?></button>
    <?php else: ?>
    <a href="<?= e2($buy_url ?: '#') ?>" class="btn-buy"><?= e2($btn['text'] ?? '🛒 Buy Now') ?></a>
    <?php endif; ?>
    <div class="trust-row">
      <div class="trust-item">✅ No Copyright Issues</div>
      <div class="trust-item">⚡ Instant Download</div>
      <div class="trust-item">♾️ Lifetime Access</div>
      <div class="trust-item">🎨 Fully Editable</div>
    </div>
  </div>
  <div class="hero-img-wrap">
    <?php $img = $h['product_image'] ?? ''; if($img): ?>
    <img src="<?= e2($img) ?>" alt="Bundle"/>
    <?php else: ?>
    <div style="width:280px;height:280px;background:linear-gradient(135deg,#fed7aa,#fef3c7);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:64px">📦</div>
    <?php endif; ?>
    <div class="float-badges">
      <span class="float-badge fb-or">🔥 1000+ Reels</span>
      <span class="float-badge fb-bl">📈 Go Viral Fast</span>
      <span class="float-badge fb-gr">✅ No Copyright</span>
    </div>
  </div>
</section>

<?php if(!empty($config['stats'])): ?>
<div class="stats">
  <div class="stats-inner">
    <?php foreach($config['stats'] as $st): ?>
    <div class="stat-item">
      <div class="stat-num"><?= e2($st['num']) ?></div>
      <div class="stat-label"><?= e2($st['label']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if(!empty($config['features'])): ?>
<div class="section features-bg">
  <div class="section-inner">
    <div class="section-tag">📦 What's Included</div>
    <h2 class="section-title">Everything You Get</h2>
    <p class="section-sub">One bundle, everything you need to dominate content on every platform.</p>
    <div class="features-grid">
      <?php foreach($config['features'] as $f): ?>
      <div class="feat-card">
        <div class="feat-icon"><?= e2($f['icon']) ?></div>
        <h3><?= e2($f['title']) ?></h3>
        <p><?= e2($f['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="section">
  <div class="section-inner">
    <div class="section-tag">🔄 Process</div>
    <h2 class="section-title">How It Works</h2>
    <p class="section-sub">Start growing your page in 4 simple steps.</p>
    <div class="steps">
      <?php foreach([['Purchase','Complete the one-time payment.'],['Download','Get instant access to all files.'],['Brand & Upload','Add your logo and upload.'],['Go Viral','Watch your views explode.']] as $i=>$st): ?>
      <div class="step">
        <div class="step-num"><?= $i+1 ?></div>
        <h3><?= $st[0] ?></h3><p><?= $st[1] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php if(!empty($config['best_for'])): ?>
<div class="section bestfor-bg">
  <div class="section-inner">
    <div class="section-tag">🎯 Ideal For</div>
    <h2 class="section-title">Who Is This For?</h2>
    <p class="section-sub">Perfect for any creator in this content space.</p>
    <div class="best-grid">
      <?php foreach($config['best_for'] as $b): ?>
      <div class="best-card">
        <div class="best-icon"><?= e2($b['icon']) ?></div>
        <span><?= e2($b['label']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if(!empty($config['testimonials'])): ?>
<div class="section">
  <div class="section-inner">
    <div class="section-tag">⭐ Reviews</div>
    <h2 class="section-title">What Creators Say</h2>
    <p class="section-sub">Join thousands already growing with this bundle.</p>
    <div class="testi-grid">
      <?php foreach($config['testimonials'] as $t): ?>
      <div class="testi-card">
        <div class="stars"><?= stars2($t['rating']) ?></div>
        <p class="testi-text">"<?= e2($t['text']) ?>"</p>
        <div class="testi-author">
          <div class="av"><?= e2(mb_strtoupper(mb_substr($t['name'],0,1))) ?></div>
          <div class="info"><strong><?= e2($t['name']) ?></strong><span><?= e2($t['location']) ?></span></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<section class="cta-bottom" id="buy">
  <h2><?= e2($cta['heading'] ?? '') ?></h2>
  <p class="sub"><?= e2($cta['subtext'] ?? '') ?></p>
  <div class="cta-price-row">
    <div class="cta-price-old"><?= e2($cur.$price_old) ?></div>
    <div class="cta-price-new"><?= e2($cur.$price_now) ?></div>
    <?php if($disc): ?><div class="cta-off"><?= e2($disc) ?></div><?php endif; ?>
  </div>
  <?php if($use_rzp): ?>
  <button class="btn-buy-white" onclick="openRzp()"><?= e2($btn['text'] ?? '🛒 Buy Now') ?></button>
  <?php else: ?>
  <a href="<?= e2($buy_url ?: '#') ?>" class="btn-buy-white"><?= e2($btn['text'] ?? '🛒 Buy Now') ?></a>
  <?php endif; ?>
  <p class="cta-note">⚡ One-Time Payment &nbsp;•&nbsp; Instant Download &nbsp;•&nbsp; Lifetime Access</p>
</section>

<footer>
  <div class="footer-logo">🏏 IPL<span>Reels</span>Bundle</div>
  <div class="footer-links">
    <a onclick="openModal('refundModal')">Refund Policy</a>
    <a onclick="openModal('privacyModal')">Privacy Policy</a>
    <a onclick="openModal('termsModal')">Terms &amp; Conditions</a>
    <a onclick="openModal('disclaimerModal')">Disclaimer</a>
  </div>
  <p>&copy; <?= date('Y') ?> IPLReelsBundle. All rights reserved.</p>
</footer>

<?php foreach([
  ['privacyModal','🔒 Privacy Policy',$pol['privacy_policy'] ?? ''],
  ['termsModal','📄 Terms & Conditions',$pol['terms_conditions'] ?? ''],
  ['refundModal','↩️ Refund Policy',$pol['refund_policy'] ?? ''],
  ['disclaimerModal','⚠️ Disclaimer',$pol['disclaimer'] ?? ''],
] as [$mid,$mt,$mc]): ?>
<div class="modal-overlay" id="<?= $mid ?>">
  <div class="modal-box">
    <div class="modal-header">
      <h2><?= e2($mt) ?></h2>
      <button class="modal-close" onclick="closeModal('<?= $mid ?>')">✕</button>
    </div>
    <div class="modal-body"><?= nl2md($mc) ?></div>
  </div>
</div>
<?php endforeach; ?>

<?php if($use_rzp): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function openRzp(){
  var opts={
    key:"<?= e2($rzp['key_id']) ?>",
    amount:<?= $amount_paise ?>,
    currency:"INR",
    name:"<?= e2($rzp['business_name'] ?? '') ?>",
    description:"<?= e2($rzp['description'] ?? '') ?>",
    image:"<?= e2($img ?? '') ?>",
    theme:{color:"<?= e2($rzp['theme_color'] ?? '#f97316') ?>"},
    handler:function(response){
      document.body.innerHTML='<div style="min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:Inter,sans-serif;text-align:center;padding:40px"><div style="font-size:72px;margin-bottom:20px">🎉</div><h1 style="font-size:28px;font-weight:900;color:#0f172a;margin-bottom:12px">Payment Successful!</h1><p style="color:#64748b;font-size:16px;margin-bottom:8px">Order ID: '+response.razorpay_payment_id+'</p><p style="color:#64748b;font-size:15px">Check your email for the download link.</p></div>';
    }
  };
  var rzp=new Razorpay(opts);
  rzp.open();
}
</script>
<?php endif; ?>
<script>
function openModal(id){document.getElementById(id).classList.add('active');document.body.style.overflow='hidden'}
function closeModal(id){document.getElementById(id).classList.remove('active');document.body.style.overflow=''}
document.querySelectorAll('.modal-overlay').forEach(el=>{el.addEventListener('click',function(e){if(e.target===this)closeModal(this.id)})});
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.modal-overlay.active').forEach(el=>closeModal(el.id))});
</script>
</body>
</html>
<?php } // end render_page
