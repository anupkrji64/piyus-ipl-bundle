<?php
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$s   = $config['site'];
$h   = $config['hero'];
$p   = $config['pricing'];
$btn = $config['buy_button'];
$cta = $config['cta'];
$pol = $config['policies'];
function stars($n){ return str_repeat('★',$n).str_repeat('☆',5-$n); }
function esc($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function nl2modal($text){
  $text = esc($text);
  $text = preg_replace('/^### (.+)$/m','<h3>$1</h3>',$text);
  $text = preg_replace('/^## (.+)$/m','<h2 style="font-size:18px;font-weight:800;margin-bottom:4px">$1</h2>',$text);
  $text = preg_replace('/^\*\*(.+)\*\*$/m','<span class="updated">$1</span>',$text);
  $text = preg_replace('/^- (.+)$/m','<li>$1</li>',$text);
  $text = preg_replace('/(<li>.*<\/li>)/s','<ul>$1</ul>',$text);
  $text = preg_replace('/\n{2,}/','</p><p>',$text);
  return '<p>'.$text.'</p>';
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="<?= esc($s['meta_description']) ?>"/>
  <title><?= esc($s['title']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
    :root{
      --orange:#f97316;--orange2:#ea580c;--yellow:#fbbf24;
      --blue:#1e3a8a;--blue2:#1d4ed8;--dark:#0f172a;
      --gray1:#f8fafc;--gray2:#f1f5f9;--gray3:#e2e8f0;--gray4:#94a3b8;
      --text:#1e293b;--text2:#475569;
    }
    body{font-family:'Inter',sans-serif;background:#fff;color:var(--text);overflow-x:hidden}
    /* TOP BAR */
    .topbar{background:var(--blue);color:#fff;text-align:center;font-size:13px;font-weight:600;padding:10px 16px;letter-spacing:.3px}
    .topbar span{color:var(--yellow)}
    /* NAV */
    nav{background:#fff;border-bottom:1px solid var(--gray3);padding:14px 40px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 1px 8px rgba(0,0,0,.06)}
    .logo{font-size:20px;font-weight:900;color:var(--blue);display:flex;align-items:center;gap:8px}
    .logo .dot{color:var(--orange)}
    .nav-btn{background:linear-gradient(135deg,var(--orange),var(--orange2));color:#fff;border:none;padding:10px 26px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;text-decoration:none;transition:opacity .2s,transform .2s}
    .nav-btn:hover{opacity:.9;transform:translateY(-1px)}
    /* HERO */
    .hero{background:linear-gradient(135deg,#eff6ff 0%,#fff7ed 50%,#fef3c7 100%);padding:72px 40px 64px;display:flex;align-items:center;justify-content:center;gap:64px;flex-wrap:wrap}
    .hero-text{max-width:540px}
    .hero-badge{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1.5px solid #fed7aa;color:var(--orange2);font-size:12px;font-weight:700;padding:5px 14px;border-radius:30px;margin-bottom:22px;text-transform:uppercase;letter-spacing:1px}
    .hero h1{font-size:clamp(30px,4.5vw,52px);font-weight:900;line-height:1.12;color:var(--dark);margin-bottom:18px}
    .hero h1 .hl{background:linear-gradient(90deg,var(--orange),var(--yellow));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
    .hero-desc{font-size:16px;color:var(--text2);line-height:1.7;margin-bottom:30px}
    .price-row{display:flex;align-items:center;gap:14px;margin-bottom:28px;flex-wrap:wrap}
    .price-new{font-size:48px;font-weight:900;color:var(--orange);line-height:1}
    .price-new sub{font-size:22px;vertical-align:super}
    .price-old{font-size:22px;color:var(--gray4);text-decoration:line-through}
    .price-off{background:#dcfce7;color:#16a34a;font-size:13px;font-weight:700;padding:4px 12px;border-radius:20px;border:1px solid #bbf7d0}
    .btn-buy{display:inline-flex;align-items:center;gap:10px;background:linear-gradient(135deg,var(--orange),var(--orange2));color:#fff;font-size:18px;font-weight:800;padding:16px 42px;border-radius:12px;text-decoration:none;border:none;cursor:pointer;box-shadow:0 8px 30px rgba(249,115,22,.35);transition:transform .2s,box-shadow .2s}
    .btn-buy:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(249,115,22,.5)}
    .trust-row{display:flex;gap:20px;flex-wrap:wrap;margin-top:18px}
    .trust-item{display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text2);font-weight:500}
    .hero-img-wrap{position:relative;flex-shrink:0}
    .hero-img-wrap img{width:340px;max-width:90vw;border-radius:20px;box-shadow:0 30px 80px rgba(0,0,0,.18);display:block;animation:float 4s ease-in-out infinite}
    @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    .float-badge{position:absolute;background:#fff;border-radius:12px;padding:8px 14px;font-size:13px;font-weight:700;box-shadow:0 6px 24px rgba(0,0,0,.12);display:flex;align-items:center;gap:6px;white-space:nowrap}
    .fb1{top:-14px;right:-16px;color:var(--orange2)}
    .fb2{bottom:18px;left:-20px;color:var(--blue2)}
    .fb3{bottom:-14px;right:10px;color:#16a34a}
    /* STATS */
    .stats{background:var(--blue);padding:44px 40px}
    .stats-inner{max-width:900px;margin:0 auto;display:flex;justify-content:space-around;flex-wrap:wrap;gap:24px}
    .stat-num{font-size:38px;font-weight:900;color:var(--yellow);line-height:1}
    .stat-label{font-size:13px;color:#93c5fd;margin-top:6px;font-weight:500;text-align:center}
    /* SECTIONS */
    .section{padding:72px 40px}
    .section-inner{max-width:1000px;margin:0 auto}
    .section-tag{display:inline-block;background:#fff7ed;color:var(--orange2);font-size:12px;font-weight:700;padding:4px 14px;border-radius:20px;border:1px solid #fed7aa;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px}
    .section-title{font-size:clamp(24px,3vw,36px);font-weight:900;color:var(--dark);margin-bottom:10px}
    .section-sub{font-size:16px;color:var(--text2);margin-bottom:44px;max-width:560px}
    /* FEATURES */
    .features-bg{background:var(--gray1)}
    .features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}
    .feat-card{background:#fff;border:1px solid var(--gray3);border-radius:16px;padding:28px 24px;transition:transform .2s,box-shadow .2s,border-color .2s}
    .feat-card:hover{transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.08);border-color:#fed7aa}
    .feat-icon{width:48px;height:48px;border-radius:12px;background:#fff7ed;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
    .feat-card h3{font-size:16px;font-weight:700;margin-bottom:8px;color:var(--dark)}
    .feat-card p{font-size:14px;color:var(--text2);line-height:1.6}
    /* STEPS */
    .steps{display:flex;gap:0;flex-wrap:wrap;margin-top:10px}
    .step{flex:1;min-width:200px;display:flex;flex-direction:column;align-items:center;text-align:center;padding:20px 16px;position:relative}
    .step:not(:last-child)::after{content:'→';position:absolute;right:-10px;top:36px;font-size:22px;color:var(--gray4)}
    .step-num{width:56px;height:56px;background:linear-gradient(135deg,var(--orange),var(--yellow));color:#fff;font-size:22px;font-weight:900;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:14px;box-shadow:0 6px 20px rgba(249,115,22,.3)}
    .step h3{font-size:15px;font-weight:700;margin-bottom:6px;color:var(--dark)}
    .step p{font-size:13px;color:var(--text2);line-height:1.5}
    /* BEST FOR */
    .bestfor-bg{background:var(--gray2)}
    .best-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px}
    .best-card{background:#fff;border:1.5px solid var(--gray3);border-radius:14px;padding:22px 18px;display:flex;align-items:center;gap:14px;transition:border-color .2s,box-shadow .2s}
    .best-card:hover{border-color:#fed7aa;box-shadow:0 6px 24px rgba(0,0,0,.07)}
    .best-icon{width:44px;height:44px;background:linear-gradient(135deg,var(--orange),var(--yellow));border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
    .best-card span{font-size:14px;font-weight:700;color:var(--dark)}
    /* TESTIMONIALS */
    .testimonials-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}
    .testi-card{background:#fff;border:1px solid var(--gray3);border-radius:16px;padding:26px 22px}
    .stars{color:var(--yellow);font-size:16px;margin-bottom:10px}
    .testi-text{font-size:14px;color:var(--text2);line-height:1.6;margin-bottom:16px}
    .testi-author{display:flex;align-items:center;gap:10px}
    .av{width:36px;height:36px;background:linear-gradient(135deg,var(--orange),var(--yellow));border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:14px}
    .testi-author .info strong{font-size:13px;display:block;color:var(--dark)}
    .testi-author .info span{font-size:12px;color:var(--gray4)}
    /* CTA */
    .cta-bottom{background:linear-gradient(135deg,var(--blue),var(--blue2));padding:80px 40px;text-align:center}
    .cta-bottom h2{font-size:clamp(26px,4vw,44px);font-weight:900;color:#fff;margin-bottom:14px}
    .cta-bottom p{font-size:16px;color:#93c5fd;margin-bottom:36px}
    .cta-price-row{display:flex;align-items:center;justify-content:center;gap:14px;margin-bottom:30px;flex-wrap:wrap}
    .cta-price-new{font-size:52px;font-weight:900;color:var(--yellow);line-height:1}
    .cta-price-old{font-size:22px;color:rgba(255,255,255,.4);text-decoration:line-through}
    .cta-off{background:#dcfce7;color:#16a34a;font-size:14px;font-weight:700;padding:5px 14px;border-radius:20px}
    .btn-buy-white{display:inline-flex;align-items:center;gap:10px;background:#fff;color:var(--orange2);font-size:18px;font-weight:800;padding:16px 48px;border-radius:12px;text-decoration:none;box-shadow:0 8px 30px rgba(0,0,0,.25);transition:transform .2s,box-shadow .2s}
    .btn-buy-white:hover{transform:translateY(-2px);box-shadow:0 14px 40px rgba(0,0,0,.3)}
    .cta-note{margin-top:16px;font-size:13px;color:#93c5fd}
    /* FOOTER */
    footer{background:var(--dark);padding:36px 40px;text-align:center}
    .footer-logo{font-size:18px;font-weight:900;color:#fff;margin-bottom:14px}
    .footer-logo span{color:var(--orange)}
    .footer-links{display:flex;justify-content:center;gap:24px;flex-wrap:wrap;margin-bottom:14px}
    .footer-links a{font-size:13px;color:#64748b;text-decoration:none;cursor:pointer}
    .footer-links a:hover{color:var(--orange)}
    footer p{font-size:12px;color:#334155}
    /* MODALS */
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:999;align-items:center;justify-content:center;padding:20px}
    .modal-overlay.active{display:flex}
    .modal-box{background:#fff;border-radius:18px;max-width:700px;width:100%;max-height:85vh;overflow-y:auto;box-shadow:0 24px 80px rgba(0,0,0,.25);animation:modalIn .25s ease}
    @keyframes modalIn{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
    .modal-header{display:flex;align-items:center;justify-content:space-between;padding:22px 28px 18px;border-bottom:1px solid var(--gray3);position:sticky;top:0;background:#fff;border-radius:18px 18px 0 0}
    .modal-header h2{font-size:20px;font-weight:800;color:var(--dark)}
    .modal-close{width:36px;height:36px;border-radius:50%;border:none;background:var(--gray2);cursor:pointer;font-size:18px;color:var(--text2);display:flex;align-items:center;justify-content:center;transition:background .15s}
    .modal-close:hover{background:var(--gray3)}
    .modal-body{padding:24px 28px 32px;font-size:14px;color:var(--text2);line-height:1.75}
    .modal-body h3{font-size:15px;font-weight:700;color:var(--dark);margin:22px 0 8px}
    .modal-body h3:first-child{margin-top:0}
    .modal-body p{margin-bottom:10px}
    .modal-body ul{padding-left:18px;margin-bottom:10px}
    .modal-body ul li{margin-bottom:5px}
    .modal-body .updated{display:inline-block;background:#eff6ff;color:var(--blue2);font-size:12px;font-weight:600;padding:3px 12px;border-radius:20px;margin-bottom:18px}
    @media(max-width:600px){.hero{padding:40px 20px 30px;gap:32px}.step::after{display:none}.nav{padding:12px 16px}}
  </style>
</head>
<body>

<div class="topbar"><?= esc($s['topbar_text']) ?></div>

<nav>
  <div class="logo">🏏 IPL<span class="dot">Reels</span>Bundle</div>
  <a href="<?= esc($btn['url']) ?>" class="nav-btn"><?= esc($btn['nav_text']) ?></a>
</nav>

<section class="hero">
  <div class="hero-text">
    <div class="hero-badge"><?= esc($h['badge']) ?></div>
    <h1>
      <?= esc($h['heading_line1']) ?><br/>
      <span class="hl"><?= esc($h['heading_highlight']) ?></span><br/>
      <?= esc($h['heading_line2']) ?>
    </h1>
    <p class="hero-desc"><?= esc($h['description']) ?></p>
    <div class="price-row">
      <div class="price-new"><sub><?= esc($p['currency_symbol']) ?></sub><?= esc($p['current_price']) ?></div>
      <div class="price-old"><?= esc($p['currency_symbol']) ?><?= esc($p['original_price']) ?></div>
      <div class="price-off"><?= esc($p['discount_label']) ?></div>
    </div>
    <a href="<?= esc($btn['url']) ?>" class="btn-buy"><?= esc($btn['text']) ?></a>
    <div class="trust-row">
      <div class="trust-item"><span>✅</span> No Copyright Issues</div>
      <div class="trust-item"><span>⚡</span> Instant Download</div>
      <div class="trust-item"><span>♾️</span> Lifetime Access</div>
      <div class="trust-item"><span>🎨</span> Fully Editable</div>
    </div>
  </div>
  <div class="hero-img-wrap">
    <img src="<?= esc($h['product_image']) ?>" alt="IPL Reels Bundle"/>
    <div class="float-badge fb1">🔥 1000+ Reels</div>
    <div class="float-badge fb2">📈 Go Viral Fast</div>
    <div class="float-badge fb3">✅ No Copyright</div>
  </div>
</section>

<div class="stats">
  <div class="stats-inner">
    <?php foreach($config['stats'] as $st): ?>
    <div style="text-align:center">
      <div class="stat-num"><?= esc($st['num']) ?></div>
      <div class="stat-label"><?= esc($st['label']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="section features-bg">
  <div class="section-inner">
    <div class="section-tag">📦 What's Included</div>
    <h2 class="section-title">Everything You Get</h2>
    <p class="section-sub">One bundle, everything you need to dominate cricket content on every platform.</p>
    <div class="features-grid">
      <?php foreach($config['features'] as $f): ?>
      <div class="feat-card">
        <div class="feat-icon"><?= esc($f['icon']) ?></div>
        <h3><?= esc($f['title']) ?></h3>
        <p><?= esc($f['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="section">
  <div class="section-inner">
    <div class="section-tag">🔄 Process</div>
    <h2 class="section-title">How It Works</h2>
    <p class="section-sub">Start growing your page in simple steps.</p>
    <div class="steps">
      <?php foreach([['Purchase','Complete the secure one-time payment.'],['Download','Instantly access & download 1000+ IPL reels.'],['Brand & Upload','Add your logo and upload to platforms.'],['Go Viral','Watch your views and followers grow.']] as $i=>$st): ?>
      <div class="step">
        <div class="step-num"><?= $i+1 ?></div>
        <h3><?= $st[0] ?></h3>
        <p><?= $st[1] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="section bestfor-bg">
  <div class="section-inner">
    <div class="section-tag">🎯 Ideal For</div>
    <h2 class="section-title">Who Is This For?</h2>
    <p class="section-sub">Perfect for any creator in the cricket & sports content space.</p>
    <div class="best-grid">
      <?php foreach($config['best_for'] as $b): ?>
      <div class="best-card">
        <div class="best-icon"><?= esc($b['icon']) ?></div>
        <span><?= esc($b['label']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="section">
  <div class="section-inner">
    <div class="section-tag">⭐ Reviews</div>
    <h2 class="section-title">What Creators Are Saying</h2>
    <p class="section-sub">Join thousands of content creators already growing with this bundle.</p>
    <div class="testimonials-grid">
      <?php foreach($config['testimonials'] as $t): ?>
      <div class="testi-card">
        <div class="stars"><?= stars($t['rating']) ?></div>
        <p class="testi-text">"<?= esc($t['text']) ?>"</p>
        <div class="testi-author">
          <div class="av"><?= esc(strtoupper(substr($t['name'],0,1))) ?></div>
          <div class="info">
            <strong><?= esc($t['name']) ?></strong>
            <span><?= esc($t['location']) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<section class="cta-bottom" id="buy">
  <h2><?= esc($cta['heading']) ?></h2>
  <p><?= esc($cta['subtext']) ?></p>
  <div class="cta-price-row">
    <div class="cta-price-old"><?= esc($p['currency_symbol'].$p['original_price']) ?></div>
    <div class="cta-price-new"><?= esc($p['currency_symbol'].$p['current_price']) ?></div>
    <div class="cta-off"><?= esc($p['discount_label']) ?></div>
  </div>
  <a href="<?= esc($btn['url']) ?>" class="btn-buy-white"><?= esc($btn['text']) ?></a>
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

<!-- MODALS -->
<?php foreach([
  ['privacyModal',   '🔒 Privacy Policy',      $pol['privacy_policy']],
  ['termsModal',     '📄 Terms & Conditions',   $pol['terms_conditions']],
  ['refundModal',    '↩️ Refund Policy',         $pol['refund_policy']],
  ['disclaimerModal','⚠️ Disclaimer',            $pol['disclaimer']],
] as [$mid,$mtitle,$mcontent]): ?>
<div class="modal-overlay" id="<?= $mid ?>">
  <div class="modal-box">
    <div class="modal-header">
      <h2><?= esc($mtitle) ?></h2>
      <button class="modal-close" onclick="closeModal('<?= $mid ?>')">✕</button>
    </div>
    <div class="modal-body"><?= nl2modal($mcontent) ?></div>
  </div>
</div>
<?php endforeach; ?>

<script>
function openModal(id){document.getElementById(id).classList.add('active');document.body.style.overflow='hidden'}
function closeModal(id){document.getElementById(id).classList.remove('active');document.body.style.overflow=''}
document.querySelectorAll('.modal-overlay').forEach(el=>{el.addEventListener('click',function(e){if(e.target===this)closeModal(this.id)})});
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.modal-overlay.active').forEach(el=>closeModal(el.id))});
</script>
</body>
</html>
