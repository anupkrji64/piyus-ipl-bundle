<?php
require_once __DIR__ . '/auth.php';
if (!auth_check()) { header('Location: /admin'); exit; }
$editSlug   = preg_replace('/[^a-z0-9\-]/','',strtolower(trim($_GET['page']??'')));
$pagesDir   = __DIR__ . '/../../pages/';
$configPath = $editSlug ? ($pagesDir.$editSlug.'.json') : __DIR__.'/../../config.json';
$config     = file_exists($configPath) ? json_decode(file_get_contents($configPath),true) : [];
$pageFiles  = is_dir($pagesDir) ? glob($pagesDir.'*.json') : [];
$pages = [];
foreach ($pageFiles as $pf) {
    $slug=$basename=basename($pf,'.json');
    $pc=json_decode(file_get_contents($pf),true);
    $pages[]=['slug'=>$slug,'title'=>$pc['site']['title']??$slug,'price'=>($pc['pricing']['currency_symbol']??'₹').($pc['pricing']['current_price']??'')];
}
function e($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
$rzp=$config['razorpay']??[]; $btn=$config['buy_button']??[];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bl:#1e3a8a;--bl2:#1d4ed8;--or:#f97316;--or2:#ea580c;--dk:#0f172a;--sw:256px;--g1:#f8fafc;--g2:#f1f5f9;--g3:#e2e8f0;--tx:#1e293b;--tx2:#64748b}
body{font-family:'Inter',sans-serif;background:var(--g1);color:var(--tx);display:flex;min-height:100vh}
.sidebar{width:var(--sw);background:var(--dk);display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;z-index:50;overflow-y:auto}
.sl{padding:20px 18px 14px;font-size:17px;font-weight:900;color:#fff;border-bottom:1px solid #1e293b}.sl span{color:var(--or)}
.su{padding:10px 18px;font-size:11px;color:#64748b;border-bottom:1px solid #1e293b}.su strong{color:#94a3b8;display:block;font-size:12px}
.ss{padding:12px 18px 4px;font-size:10px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:1.5px}
.ni{display:flex;align-items:center;gap:9px;padding:9px 18px;font-size:13px;font-weight:500;color:#94a3b8;cursor:pointer;transition:background .15s,color .15s;border:none;background:none;width:100%;text-align:left}
.ni:hover,.ni.active{background:rgba(255,255,255,.06);color:#fff}.ni.active{border-left:3px solid var(--or);font-weight:600}
.sf{margin-top:auto;padding:14px 18px;border-top:1px solid #1e293b}
.lo{display:flex;align-items:center;gap:7px;color:#ef4444;font-size:13px;font-weight:600;text-decoration:none}
.main{margin-left:var(--sw);flex:1;padding:26px 22px}
.ph{margin-bottom:20px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.ph h1{font-size:19px;font-weight:800;color:var(--dk)}
.vl{display:inline-flex;align-items:center;gap:5px;background:var(--g2);border:1px solid var(--g3);color:var(--tx);padding:7px 13px;border-radius:7px;font-size:12px;font-weight:600;text-decoration:none}
.eb{background:linear-gradient(135deg,var(--bl),var(--bl2));color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:16px}
.st{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:20px}
.sc{background:#fff;border:1px solid var(--g3);border-radius:10px;padding:14px 16px}
.sc .n{font-size:22px;font-weight:900;color:var(--or)}.sc .l{font-size:11px;color:var(--tx2);margin-top:3px}
.panel{background:#fff;border:1px solid var(--g3);border-radius:12px;margin-bottom:18px;overflow:hidden;display:none}
.panel.active{display:block}
.ph2{padding:14px 18px;border-bottom:1px solid var(--g3);display:flex;align-items:center;gap:8px}
.ph2 h2{font-size:14px;font-weight:700;color:var(--dk)}
.pb{padding:18px}
.fr{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}.fr.full{grid-template-columns:1fr}
.fg label{display:block;font-size:11px;font-weight:600;color:var(--tx2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.fg input,.fg textarea,.fg select{width:100%;padding:8px 11px;border:1.5px solid var(--g3);border-radius:7px;font-size:13px;color:var(--tx);outline:none;font-family:'Inter',sans-serif;background:#fff;transition:border-color .2s}
.fg input:focus,.fg textarea:focus{border-color:var(--bl2);box-shadow:0 0 0 3px rgba(29,78,216,.08)}
.fg textarea{resize:vertical;min-height:70px}
.sbtn{background:linear-gradient(135deg,var(--or),var(--or2));color:#fff;border:none;padding:9px 22px;border-radius:7px;font-size:13px;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif}
.sbtn:hover{opacity:.9}.sbtn.bl{background:linear-gradient(135deg,var(--bl),var(--bl2))}.sbtn.rd{background:#ef4444}
.tw{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.tg{position:relative;width:42px;height:22px}.tg input{opacity:0;width:0;height:0}
.tsl{position:absolute;cursor:pointer;inset:0;background:#cbd5e1;border-radius:11px;transition:.3s}
.tsl:before{content:'';position:absolute;width:16px;height:16px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s}
.tg input:checked+.tsl{background:var(--or)}.tg input:checked+.tsl:before{transform:translateX(20px)}
.ri{background:var(--g1);border:1px solid var(--g3);border-radius:8px;padding:12px;margin-bottom:9px;position:relative}
.rm{position:absolute;top:7px;right:7px;background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:5px;padding:2px 7px;font-size:11px;cursor:pointer;font-family:'Inter',sans-serif;font-weight:600}
.ab{background:var(--g2);border:1.5px dashed var(--g3);color:var(--tx2);padding:7px 14px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;width:100%;margin-top:2px}
.ab:hover{border-color:var(--bl2);color:var(--bl2)}
.pg{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:12px;margin-bottom:16px}
.pc{background:#fff;border:1.5px solid var(--g3);border-radius:10px;padding:16px;transition:border-color .2s}
.pc:hover{border-color:#fed7aa}.pc-t{font-size:13px;font-weight:700;color:var(--dk);margin-bottom:3px}
.pc-s{font-size:11px;color:var(--tx2);margin-bottom:8px}.pc-p{font-size:13px;font-weight:700;color:var(--or);margin-bottom:10px}
.pc-a{display:flex;gap:7px;flex-wrap:wrap}
.pe{background:var(--g2);border:1px solid var(--g3);color:var(--tx);padding:4px 10px;border-radius:5px;font-size:11px;font-weight:600;text-decoration:none}
.pv{background:#eff6ff;border:1px solid #bfdbfe;color:var(--bl2);padding:4px 10px;border-radius:5px;font-size:11px;font-weight:600;text-decoration:none}
.pd{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:4px 10px;border-radius:5px;font-size:11px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif}
.npf{background:var(--g1);border:1.5px dashed var(--g3);border-radius:10px;padding:16px;margin-bottom:18px}
.npf h3{font-size:13px;font-weight:700;margin-bottom:12px;color:var(--dk)}
.toast{position:fixed;bottom:22px;right:22px;background:var(--dk);color:#fff;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;z-index:9999;transform:translateY(20px);opacity:0;transition:all .3s;pointer-events:none;box-shadow:0 6px 24px rgba(0,0,0,.2)}
.toast.show{transform:translateY(0);opacity:1}.toast.err{background:#dc2626}
@media(max-width:768px){.sidebar{width:100%;height:auto;position:relative}.main{margin-left:0}.fr{grid-template-columns:1fr}}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="sl">🏏 IPL<span>Reels</span>Bundle</div>
  <div class="su">Logged in as<br/><strong><?= e(auth_user()) ?></strong></div>
  <?php if($editSlug): ?><div style="background:rgba(249,115,22,.12);padding:7px 18px;font-size:11px;color:#fed7aa;border-bottom:1px solid #1e293b">✏️ Editing: <strong><?= e($editSlug) ?></strong><br/><a href="/admin/dashboard" style="color:#93c5fd">← Back to main</a></div><?php endif; ?>
  <div class="ss">Content</div>
  <button class="ni active" onclick="sp('site',this)">🌐 Site Settings</button>
  <button class="ni" onclick="sp('hero',this)">🎯 Hero</button>
  <button class="ni" onclick="sp('pricing',this)">💰 Pricing</button>
  <button class="ni" onclick="sp('razorpay',this)">💳 Razorpay</button>
  <button class="ni" onclick="sp('button',this)">🛒 Buy Button</button>
  <button class="ni" onclick="sp('cta',this)">📢 CTA</button>
  <div class="ss">Components</div>
  <button class="ni" onclick="sp('stats',this)">📊 Stats</button>
  <button class="ni" onclick="sp('features',this)">📦 Features</button>
  <button class="ni" onclick="sp('bestfor',this)">🎯 Best For</button>
  <button class="ni" onclick="sp('testimonials',this)">⭐ Testimonials</button>
  <div class="ss">Legal & Pages</div>
  <button class="ni" onclick="sp('policies',this)">📄 Policies</button>
  <button class="ni" onclick="sp('pages',this)">📋 Manage Pages</button>
  <div class="ss">Security</div>
  <button class="ni" onclick="sp('creds',this)">🔑 Change Password</button>
  <div class="sf"><a href="/admin/logout" class="lo">🚪 Logout</a></div>
</aside>

<main class="main">
  <div class="ph">
    <div><h1><?= $editSlug ? '<span class="eb">Editing: '.e($editSlug).'</span> ' : '' ?>Admin Dashboard</h1></div>
    <a href="<?= $editSlug ? '/'.e($editSlug) : '/' ?>" target="_blank" class="vl">↗ View <?= $editSlug ? e($editSlug) : 'Site' ?></a>
    <?php if($editSlug): ?><a href="/admin/dashboard" class="vl">← Main Config</a><?php endif; ?>
  </div>
  <div class="st">
    <div class="sc"><div class="n"><?= e(($config['pricing']['currency_symbol']??'₹').($config['pricing']['current_price']??'')) ?></div><div class="l">Price</div></div>
    <div class="sc"><div class="n"><?= count($config['features']??[]) ?></div><div class="l">Features</div></div>
    <div class="sc"><div class="n"><?= count($config['testimonials']??[]) ?></div><div class="l">Reviews</div></div>
    <div class="sc"><div class="n"><?= count($pages)+1 ?></div><div class="l">Total Pages</div></div>
  </div>
  <input type="hidden" id="ps" value="<?= e($editSlug) ?>"/>

  <!-- SITE -->
  <div class="panel active" id="panel-site"><div class="ph2"><span>🌐</span><h2>Site Settings</h2></div><div class="pb">
    <form onsubmit="sv(event,'site')">
      <div class="fr full"><div class="fg"><label>Page Title</label><input name="title" value="<?= e($config['site']['title']??'') ?>"/></div></div>
      <div class="fr full"><div class="fg"><label>Meta Description</label><textarea name="meta_description"><?= e($config['site']['meta_description']??'') ?></textarea></div></div>
      <div class="fr full"><div class="fg"><label>Top Banner</label><input name="topbar_text" value="<?= e($config['site']['topbar_text']??'') ?>"/></div></div>
      <button type="submit" class="sbtn">💾 Save</button>
    </form>
  </div></div>

  <!-- HERO -->
  <div class="panel" id="panel-hero"><div class="ph2"><span>🎯</span><h2>Hero Section</h2></div><div class="pb">
    <form onsubmit="sv(event,'hero')">
      <div class="fr full"><div class="fg"><label>Badge</label><input name="badge" value="<?= e($config['hero']['badge']??'') ?>"/></div></div>
      <div class="fr"><div class="fg"><label>Heading Line 1</label><input name="heading_line1" value="<?= e($config['hero']['heading_line1']??'') ?>"/></div><div class="fg"><label>Heading Highlight (orange)</label><input name="heading_highlight" value="<?= e($config['hero']['heading_highlight']??'') ?>"/></div></div>
      <div class="fr full"><div class="fg"><label>Heading Line 2</label><input name="heading_line2" value="<?= e($config['hero']['heading_line2']??'') ?>"/></div></div>
      <div class="fr full"><div class="fg"><label>Description</label><textarea name="description"><?= e($config['hero']['description']??'') ?></textarea></div></div>
      <button type="submit" class="sbtn">💾 Save</button>
    </form>
  </div></div>

  <!-- PRICING -->
  <div class="panel" id="panel-pricing"><div class="ph2"><span>💰</span><h2>Pricing</h2></div><div class="pb">
    <form onsubmit="sv(event,'pricing')">
      <div class="fr"><div class="fg"><label>Currency</label><input name="currency_symbol" value="<?= e($config['pricing']['currency_symbol']??'₹') ?>"/></div><div class="fg"><label>Discount Label</label><input name="discount_label" value="<?= e($config['pricing']['discount_label']??'') ?>"/></div></div>
      <div class="fr"><div class="fg"><label>Sale Price</label><input name="current_price" value="<?= e($config['pricing']['current_price']??'') ?>"/></div><div class="fg"><label>Original Price</label><input name="original_price" value="<?= e($config['pricing']['original_price']??'') ?>"/></div></div>
      <button type="submit" class="sbtn">💾 Save</button>
    </form>
  </div></div>

  <!-- RAZORPAY -->
  <div class="panel" id="panel-razorpay"><div class="ph2"><span>💳</span><h2>Razorpay Payment Gateway</h2></div><div class="pb">
    <form onsubmit="sv(event,'razorpay')">
      <div class="tw"><label class="tg"><input type="checkbox" name="use_razorpay" <?= !empty($btn['use_razorpay'])?'checked':'' ?>><span class="tsl"></span></label><span style="font-size:13px;font-weight:600">Enable Razorpay Checkout</span></div>
      <div class="fr full"><div class="fg"><label>Razorpay Key ID</label><input name="key_id" value="<?= e($rzp['key_id']??'') ?>" placeholder="rzp_live_..."/></div></div>
      <div class="fr"><div class="fg"><label>Business Name</label><input name="business_name" value="<?= e($rzp['business_name']??'') ?>"/></div><div class="fg"><label>Payment Description</label><input name="rzp_description" value="<?= e($rzp['description']??'') ?>"/></div></div>
      <div class="fr"><div class="fg"><label>Theme Color</label><input type="color" name="theme_color" value="<?= e($rzp['theme_color']??'#f97316') ?>" style="height:36px;padding:2px 6px"/></div></div>
      <button type="submit" class="sbtn">💾 Save Razorpay</button>
    </form>
  </div></div>

  <!-- BUY BUTTON -->
  <div class="panel" id="panel-button"><div class="ph2"><span>🛒</span><h2>Buy Button</h2></div><div class="pb">
    <form onsubmit="sv(event,'button')">
      <div class="fr full"><div class="fg"><label>Fallback URL (if Razorpay OFF)</label><input name="url" value="<?= e($btn['url']??'') ?>" placeholder="https://..."/></div></div>
      <div class="fr"><div class="fg"><label>Button Text</label><input name="text" value="<?= e($btn['text']??'') ?>"/></div><div class="fg"><label>Nav Button Text</label><input name="nav_text" value="<?= e($btn['nav_text']??'') ?>"/></div></div>
      <button type="submit" class="sbtn">💾 Save</button>
    </form>
  </div></div>

  <!-- CTA -->
  <div class="panel" id="panel-cta"><div class="ph2"><span>📢</span><h2>CTA Section</h2></div><div class="pb">
    <form onsubmit="sv(event,'cta')">
      <div class="fr full"><div class="fg"><label>Heading</label><input name="heading" value="<?= e($config['cta']['heading']??'') ?>"/></div></div>
      <div class="fr full"><div class="fg"><label>Subtext</label><input name="subtext" value="<?= e($config['cta']['subtext']??'') ?>"/></div></div>
      <button type="submit" class="sbtn">💾 Save</button>
    </form>
  </div></div>

  <!-- STATS -->
  <div class="panel" id="panel-stats"><div class="ph2"><span>📊</span><h2>Stats Bar</h2></div><div class="pb">
    <form onsubmit="sv(event,'stats')">
      <div id="sl"><?php foreach($config['stats']??[] as $st): ?><div class="ri"><button type="button" class="rm" onclick="this.parentElement.remove()">✕</button><div class="fr"><div class="fg"><label>Value</label><input name="num[]" value="<?= e($st['num']) ?>"/></div><div class="fg"><label>Label</label><input name="label[]" value="<?= e($st['label']) ?>"/></div></div></div><?php endforeach; ?></div>
      <button type="button" class="ab" onclick="aS()">+ Add Stat</button><br/><br/><button type="submit" class="sbtn">💾 Save</button>
    </form>
  </div></div>

  <!-- FEATURES -->
  <div class="panel" id="panel-features"><div class="ph2"><span>📦</span><h2>Features</h2></div><div class="pb">
    <form onsubmit="sv(event,'features')">
      <div id="fl"><?php foreach($config['features']??[] as $f): ?><div class="ri"><button type="button" class="rm" onclick="this.parentElement.remove()">✕</button><div class="fr"><div class="fg"><label>Icon</label><input name="ficon[]" value="<?= e($f['icon']) ?>"/></div><div class="fg"><label>Title</label><input name="ftitle[]" value="<?= e($f['title']) ?>"/></div></div><div class="fg" style="margin-top:9px"><label>Desc</label><textarea name="fdesc[]"><?= e($f['desc']) ?></textarea></div></div><?php endforeach; ?></div>
      <button type="button" class="ab" onclick="aF()">+ Add Feature</button><br/><br/><button type="submit" class="sbtn">💾 Save</button>
    </form>
  </div></div>

  <!-- BEST FOR -->
  <div class="panel" id="panel-bestfor"><div class="ph2"><span>🎯</span><h2>Best For</h2></div><div class="pb">
    <form onsubmit="sv(event,'best_for')">
      <div id="bl"><?php foreach($config['best_for']??[] as $b): ?><div class="ri"><button type="button" class="rm" onclick="this.parentElement.remove()">✕</button><div class="fr"><div class="fg"><label>Icon</label><input name="bicon[]" value="<?= e($b['icon']) ?>"/></div><div class="fg"><label>Label</label><input name="blabel[]" value="<?= e($b['label']) ?>"/></div></div></div><?php endforeach; ?></div>
      <button type="button" class="ab" onclick="aBf()">+ Add Item</button><br/><br/><button type="submit" class="sbtn">💾 Save</button>
    </form>
  </div></div>

  <!-- TESTIMONIALS -->
  <div class="panel" id="panel-testimonials"><div class="ph2"><span>⭐</span><h2>Testimonials</h2></div><div class="pb">
    <form onsubmit="sv(event,'testimonials')">
      <div id="tl"><?php foreach($config['testimonials']??[] as $t): ?><div class="ri"><button type="button" class="rm" onclick="this.parentElement.remove()">✕</button><div class="fr"><div class="fg"><label>Name</label><input name="tname[]" value="<?= e($t['name']) ?>"/></div><div class="fg"><label>Location</label><input name="tlocation[]" value="<?= e($t['location']) ?>"/></div></div><div class="fg" style="margin-top:9px"><label>Review</label><textarea name="ttext[]"><?= e($t['text']) ?></textarea></div><div class="fg" style="margin-top:9px"><label>Rating</label><select name="trating[]"><?php for($r=5;$r>=1;$r--): ?><option value="<?= $r ?>" <?= $t['rating']==$r?'selected':'' ?>><?= $r ?> Stars</option><?php endfor; ?></select></div></div><?php endforeach; ?></div>
      <button type="button" class="ab" onclick="aT()">+ Add Testimonial</button><br/><br/><button type="submit" class="sbtn">💾 Save</button>
    </form>
  </div></div>

  <!-- POLICIES -->
  <div class="panel" id="panel-policies"><div class="ph2"><span>📄</span><h2>Legal Policies</h2></div><div class="pb">
    <form onsubmit="sv(event,'policies')">
      <?php foreach([['privacy_policy','Privacy Policy'],['terms_conditions','Terms & Conditions'],['refund_policy','Refund Policy'],['disclaimer','Disclaimer']] as [$key,$label]): ?>
      <div class="fg" style="margin-bottom:13px"><label><?= $label ?></label><textarea name="<?= $key ?>" style="min-height:90px"><?= e($config['policies'][$key]??'') ?></textarea></div>
      <?php endforeach; ?>
      <button type="submit" class="sbtn">💾 Save</button>
    </form>
  </div></div>

  <!-- PAGES -->
  <div class="panel" id="panel-pages"><div class="ph2"><span>📋</span><h2>Manage Landing Pages</h2></div><div class="pb">
    <p style="font-size:13px;color:#64748b;margin-bottom:14px">Create pages for different products. Each gets its own URL: <code style="background:#f1f5f9;padding:1px 6px;border-radius:4px">/anime-bundle</code>, <code style="background:#f1f5f9;padding:1px 6px;border-radius:4px">/football-reels</code>, etc.</p>
    <div class="npf">
      <h3>➕ Create New Page</h3>
      <div class="fr">
        <div class="fg"><label>Page Title</label><input id="nt" placeholder="Anime Reels Bundle"/></div>
        <div class="fg"><label>URL Slug</label><input id="ns" placeholder="anime-bundle"/></div>
      </div>
      <button type="button" class="sbtn" onclick="cP()">🚀 Create Page</button>
    </div>
    <p style="font-size:12px;font-weight:700;color:#475569;margin-bottom:10px">MAIN PAGE</p>
    <div class="pg" style="margin-bottom:16px">
      <div class="pc" style="border-color:#fed7aa">
        <div class="pc-t"><?= e($config['site']['title']??'Main Page') ?></div>
        <div class="pc-s">URL: /</div>
        <div class="pc-p"><?= e(($config['pricing']['currency_symbol']??'₹').($config['pricing']['current_price']??'')) ?></div>
        <div class="pc-a"><a href="/admin/dashboard" class="pe">✏️ Edit</a><a href="/" target="_blank" class="pv">↗ View</a></div>
      </div>
    </div>
    <?php if(!empty($pages)): ?>
    <p style="font-size:12px;font-weight:700;color:#475569;margin-bottom:10px">EXTRA PAGES</p>
    <div class="pg" id="extra-pages">
      <?php foreach($pages as $pg): ?>
      <div class="pc" id="card-<?= e($pg['slug']) ?>">
        <div class="pc-t"><?= e($pg['title']) ?></div>
        <div class="pc-s">URL: /<?= e($pg['slug']) ?></div>
        <div class="pc-p"><?= e($pg['price']) ?></div>
        <div class="pc-a">
          <a href="/admin/dashboard?page=<?= e($pg['slug']) ?>" class="pe">✏️ Edit</a>
          <a href="/<?= e($pg['slug']) ?>" target="_blank" class="pv">↗ View</a>
          <button class="pd" onclick="dP('<?= e($pg['slug']) ?>')">🗑️</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p id="no-pages" style="font-size:13px;color:#94a3b8;padding:16px;text-align:center;background:#f8fafc;border-radius:8px">No extra pages yet. Create your first one above!</p>
    <?php endif; ?>
  </div></div>

  <!-- CREDENTIALS -->
  <div class="panel" id="panel-creds"><div class="ph2"><span>🔑</span><h2>Change Credentials</h2></div><div class="pb">
    <form onsubmit="sv(event,'admin_credentials')">
      <div class="fr full"><div class="fg"><label>Current Password</label><input type="password" name="current_password" required/></div></div>
      <div class="fr"><div class="fg"><label>New Username</label><input type="text" name="new_username" placeholder="Leave blank to keep"/></div><div class="fg"><label>New Password</label><input type="password" name="new_password" placeholder="Leave blank to keep"/></div></div>
      <button type="submit" class="sbtn">🔐 Update</button>
    </form>
  </div></div>
</main>

<div class="toast" id="toast"></div>
<script>
var PS=document.getElementById('ps').value;
function sp(id,btn){
  document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.ni').forEach(b=>b.classList.remove('active'));
  document.getElementById('panel-'+id).classList.add('active');
  btn.classList.add('active');
}
async function sv(e,sec){
  e.preventDefault();
  var fd=new FormData(e.target);
  fd.append('section',sec); fd.append('page_slug',PS);
  try{ var r=await fetch('/admin/save',{method:'POST',body:fd}); var d=await r.json(); toast(d.msg,d.ok?'':'err'); }
  catch(err){ toast('Network error','err'); }
}
function toast(msg,type=''){
  var t=document.getElementById('toast');
  t.textContent=msg; t.className='toast'+(type?' '+type:''); t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),3000);
}
async function cP(){
  var title=document.getElementById('nt').value.trim();
  var slug=document.getElementById('ns').value.trim().toLowerCase().replace(/[^a-z0-9\-]/g,'');
  if(!title||!slug){toast('Enter title and slug','err');return;}
  var fd=new FormData(); fd.append('section','create_page'); fd.append('new_title',title); fd.append('new_slug',slug);
  var r=await fetch('/admin/save',{method:'POST',body:fd}); var d=await r.json();
  toast(d.msg,d.ok?'':'err');
  if(d.ok) setTimeout(()=>location.reload(),800);
}
async function dP(slug){
  if(!confirm('Delete page "'+slug+'"?')) return;
  var fd=new FormData(); fd.append('section','delete_page'); fd.append('del_slug',slug);
  var r=await fetch('/admin/save',{method:'POST',body:fd}); var d=await r.json();
  toast(d.msg,d.ok?'':'err');
  if(d.ok){var c=document.getElementById('card-'+slug);if(c)c.remove();}
}
function aS(){var d=document.createElement('div');d.className='ri';d.innerHTML='<button type="button" class="rm" onclick="this.parentElement.remove()">✕</button><div class="fr"><div class="fg"><label>Value</label><input name="num[]" placeholder="5000+"/></div><div class="fg"><label>Label</label><input name="label[]" placeholder="Happy Creators"/></div></div>';document.getElementById('sl').appendChild(d);}
function aF(){var d=document.createElement('div');d.className='ri';d.innerHTML='<button type="button" class="rm" onclick="this.parentElement.remove()">✕</button><div class="fr"><div class="fg"><label>Icon</label><input name="ficon[]" placeholder="🎬"/></div><div class="fg"><label>Title</label><input name="ftitle[]" placeholder="Title"/></div></div><div class="fg" style="margin-top:9px"><label>Desc</label><textarea name="fdesc[]" placeholder="..."></textarea></div>';document.getElementById('fl').appendChild(d);}
function aBf(){var d=document.createElement('div');d.className='ri';d.innerHTML='<button type="button" class="rm" onclick="this.parentElement.remove()">✕</button><div class="fr"><div class="fg"><label>Icon</label><input name="bicon[]" placeholder="🎯"/></div><div class="fg"><label>Label</label><input name="blabel[]" placeholder="..."/></div></div>';document.getElementById('bl').appendChild(d);}
function aT(){var d=document.createElement('div');d.className='ri';d.innerHTML='<button type="button" class="rm" onclick="this.parentElement.remove()">✕</button><div class="fr"><div class="fg"><label>Name</label><input name="tname[]" placeholder="Name"/></div><div class="fg"><label>Location</label><input name="tlocation[]" placeholder="Mumbai"/></div></div><div class="fg" style="margin-top:9px"><label>Review</label><textarea name="ttext[]" placeholder="..."></textarea></div><div class="fg" style="margin-top:9px"><label>Rating</label><select name="trating[]"><option value="5">5 ★</option><option value="4">4 ★</option><option value="3">3 ★</option><option value="2">2 ★</option><option value="1">1 ★</option></select></div>';document.getElementById('tl').appendChild(d);}
document.getElementById('nt').addEventListener('input',function(){document.getElementById('ns').value=this.value.toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');});
</script>
</body>
</html>
