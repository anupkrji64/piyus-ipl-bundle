<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in']!==true){
  header('Location: /admin'); exit;
}
$config = json_decode(file_get_contents(__DIR__.'/../../config.json'), true);
function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Admin Dashboard – IPLReelsBundle</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --blue:#1e3a8a;--blue2:#1d4ed8;--orange:#f97316;--orange2:#ea580c;
  --dark:#0f172a;--sidebar-w:260px;
  --gray1:#f8fafc;--gray2:#f1f5f9;--gray3:#e2e8f0;--text:#1e293b;--text2:#64748b;
}
body{font-family:'Inter',sans-serif;background:var(--gray1);color:var(--text);display:flex;min-height:100vh}

/* SIDEBAR */
.sidebar{width:var(--sidebar-w);background:var(--dark);color:#fff;display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;z-index:50;overflow-y:auto}
.sidebar-logo{padding:24px 20px 18px;font-size:18px;font-weight:900;border-bottom:1px solid #1e293b}
.sidebar-logo span{color:var(--orange)}
.sidebar-user{padding:14px 20px;font-size:12px;color:#64748b;border-bottom:1px solid #1e293b}
.sidebar-user strong{color:#94a3b8;display:block;font-size:13px}
.nav-section{padding:16px 20px 6px;font-size:10px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:1.5px}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 20px;font-size:14px;font-weight:500;color:#94a3b8;cursor:pointer;transition:background .15s,color .15s;border:none;background:none;width:100%;text-align:left}
.nav-item:hover,.nav-item.active{background:rgba(255,255,255,.06);color:#fff}
.nav-item.active{border-left:3px solid var(--orange);color:#fff;font-weight:600}
.sidebar-footer{margin-top:auto;padding:16px 20px;border-top:1px solid #1e293b}
.logout-btn{display:flex;align-items:center;gap:8px;color:#ef4444;font-size:14px;font-weight:600;text-decoration:none;padding:8px 0}
.logout-btn:hover{opacity:.8}

/* MAIN */
.main{margin-left:var(--sidebar-w);flex:1;padding:32px;min-height:100vh}
.page-header{margin-bottom:28px}
.page-header h1{font-size:22px;font-weight:800;color:var(--dark)}
.page-header p{font-size:14px;color:var(--text2);margin-top:4px}

/* PANELS */
.panel{background:#fff;border:1px solid var(--gray3);border-radius:16px;margin-bottom:24px;overflow:hidden;display:none}
.panel.active{display:block}
.panel-header{padding:18px 24px;border-bottom:1px solid var(--gray3);display:flex;align-items:center;gap:10px}
.panel-header h2{font-size:16px;font-weight:700;color:var(--dark)}
.panel-body{padding:24px}

/* FORM */
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.form-row.full{grid-template-columns:1fr}
.form-group{margin-bottom:0}
.form-group label{display:block;font-size:12px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px}
.form-group input,.form-group textarea,.form-group select{width:100%;padding:10px 14px;border:1.5px solid var(--gray3);border-radius:8px;font-size:14px;color:var(--text);outline:none;transition:border-color .2s;font-family:'Inter',sans-serif;background:#fff}
.form-group input:focus,.form-group textarea:focus{border-color:var(--blue2);box-shadow:0 0 0 3px rgba(29,78,216,.08)}
.form-group textarea{resize:vertical;min-height:80px}
.save-btn{background:linear-gradient(135deg,var(--orange),var(--orange2));color:#fff;border:none;padding:10px 28px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;transition:opacity .2s,transform .2s;font-family:'Inter',sans-serif}
.save-btn:hover{opacity:.9;transform:translateY(-1px)}

/* REPEATER */
.repeater-item{background:var(--gray1);border:1px solid var(--gray3);border-radius:10px;padding:16px;margin-bottom:12px;position:relative}
.remove-btn{position:absolute;top:10px;right:10px;background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer;font-family:'Inter',sans-serif;font-weight:600}
.add-btn{background:var(--gray2);border:1.5px dashed var(--gray3);color:var(--text2);padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:border-color .2s,color .2s;width:100%;margin-top:4px}
.add-btn:hover{border-color:var(--blue2);color:var(--blue2)}

/* TOAST */
.toast{position:fixed;bottom:28px;right:28px;background:var(--dark);color:#fff;padding:12px 22px;border-radius:10px;font-size:14px;font-weight:600;z-index:9999;transform:translateY(20px);opacity:0;transition:all .3s;pointer-events:none;box-shadow:0 8px 30px rgba(0,0,0,.25)}
.toast.show{transform:translateY(0);opacity:1}
.toast.error{background:#dc2626}

/* STATS TOP */
.stats-top{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:28px}
.stat-card{background:#fff;border:1px solid var(--gray3);border-radius:12px;padding:18px 20px}
.stat-card .sc-num{font-size:26px;font-weight:900;color:var(--orange)}
.stat-card .sc-label{font-size:12px;color:var(--text2);margin-top:4px}

/* VIEW SITE BTN */
.view-site{display:inline-flex;align-items:center;gap:6px;background:var(--gray2);border:1px solid var(--gray3);color:var(--text);padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;margin-left:12px;transition:background .15s}
.view-site:hover{background:var(--gray3)}

@media(max-width:768px){
  .sidebar{width:100%;height:auto;position:relative}
  .main{margin-left:0}
  .form-row{grid-template-columns:1fr}
}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">🏏 IPL<span>Reels</span>Bundle</div>
  <div class="sidebar-user">
    Logged in as<br/>
    <strong><?= e($_SESSION['admin_user']) ?></strong>
  </div>
  <div class="nav-section">Content</div>
  <button class="nav-item active" onclick="showPanel('site',this)">🌐 Site Settings</button>
  <button class="nav-item" onclick="showPanel('hero',this)">🎯 Hero Section</button>
  <button class="nav-item" onclick="showPanel('pricing',this)">💰 Pricing</button>
  <button class="nav-item" onclick="showPanel('button',this)">🛒 Buy Button</button>
  <button class="nav-item" onclick="showPanel('cta',this)">📢 CTA Section</button>
  <div class="nav-section">Components</div>
  <button class="nav-item" onclick="showPanel('stats',this)">📊 Stats Bar</button>
  <button class="nav-item" onclick="showPanel('features',this)">📦 Features</button>
  <button class="nav-item" onclick="showPanel('best_for',this)">🎯 Best For</button>
  <button class="nav-item" onclick="showPanel('testimonials',this)">⭐ Testimonials</button>
  <div class="nav-section">Legal</div>
  <button class="nav-item" onclick="showPanel('policies',this)">📄 Policies</button>
  <div class="nav-section">Security</div>
  <button class="nav-item" onclick="showPanel('credentials',this)">🔑 Change Password</button>
  <div class="sidebar-footer">
    <a href="/admin/logout" class="logout-btn">🚪 Logout</a>
  </div>
</aside>

<main class="main">
  <div class="page-header">
    <h1>Admin Dashboard
      <a href="/" target="_blank" class="view-site">↗ View Site</a>
    </h1>
    <p>Control every aspect of your IPLReelsBundle landing page.</p>
  </div>

  <div class="stats-top">
    <div class="stat-card"><div class="sc-num"><?= e($config['pricing']['currency_symbol'].$config['pricing']['current_price']) ?></div><div class="sc-label">Current Price</div></div>
    <div class="stat-card"><div class="sc-num"><?= count($config['features']) ?></div><div class="sc-label">Features Listed</div></div>
    <div class="stat-card"><div class="sc-num"><?= count($config['testimonials']) ?></div><div class="sc-label">Testimonials</div></div>
    <div class="stat-card"><div class="sc-num"><?= count($config['stats']) ?></div><div class="sc-label">Stats Shown</div></div>
  </div>

  <!-- SITE SETTINGS -->
  <div class="panel active" id="panel-site">
    <div class="panel-header"><span>🌐</span><h2>Site Settings</h2></div>
    <div class="panel-body">
      <form onsubmit="save(event,'site')">
        <div class="form-row full"><div class="form-group"><label>Page Title</label><input name="title" value="<?= e($config['site']['title']) ?>"/></div></div>
        <div class="form-row full"><div class="form-group"><label>Meta Description</label><textarea name="meta_description"><?= e($config['site']['meta_description']) ?></textarea></div></div>
        <div class="form-row full"><div class="form-group"><label>Top Banner Text</label><input name="topbar_text" value="<?= e($config['site']['topbar_text']) ?>"/></div></div>
        <button type="submit" class="save-btn">💾 Save Changes</button>
      </form>
    </div>
  </div>

  <!-- HERO -->
  <div class="panel" id="panel-hero">
    <div class="panel-header"><span>🎯</span><h2>Hero Section</h2></div>
    <div class="panel-body">
      <form onsubmit="save(event,'hero')">
        <div class="form-row full"><div class="form-group"><label>Badge Text</label><input name="badge" value="<?= e($config['hero']['badge']) ?>"/></div></div>
        <div class="form-row"><div class="form-group"><label>Heading Line 1</label><input name="heading_line1" value="<?= e($config['hero']['heading_line1']) ?>"/></div>
        <div class="form-group"><label>Heading Highlight (orange)</label><input name="heading_highlight" value="<?= e($config['hero']['heading_highlight']) ?>"/></div></div>
        <div class="form-row full"><div class="form-group"><label>Heading Line 2</label><input name="heading_line2" value="<?= e($config['hero']['heading_line2']) ?>"/></div></div>
        <div class="form-row full"><div class="form-group"><label>Description</label><textarea name="description"><?= e($config['hero']['description']) ?></textarea></div></div>
        <button type="submit" class="save-btn">💾 Save Changes</button>
      </form>
    </div>
  </div>

  <!-- PRICING -->
  <div class="panel" id="panel-pricing">
    <div class="panel-header"><span>💰</span><h2>Pricing</h2></div>
    <div class="panel-body">
      <form onsubmit="save(event,'pricing')">
        <div class="form-row">
          <div class="form-group"><label>Currency Symbol</label><input name="currency_symbol" value="<?= e($config['pricing']['currency_symbol']) ?>"/></div>
          <div class="form-group"><label>Discount Label</label><input name="discount_label" value="<?= e($config['pricing']['discount_label']) ?>"/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Current Price (sale)</label><input name="current_price" value="<?= e($config['pricing']['current_price']) ?>"/></div>
          <div class="form-group"><label>Original Price (crossed out)</label><input name="original_price" value="<?= e($config['pricing']['original_price']) ?>"/></div>
        </div>
        <button type="submit" class="save-btn">💾 Save Changes</button>
      </form>
    </div>
  </div>

  <!-- BUY BUTTON -->
  <div class="panel" id="panel-button">
    <div class="panel-header"><span>🛒</span><h2>Buy Button</h2></div>
    <div class="panel-body">
      <form onsubmit="save(event,'button')">
        <div class="form-row full"><div class="form-group"><label>Button URL (Payment Link)</label><input name="url" type="url" value="<?= e($config['buy_button']['url']) ?>" placeholder="https://rzp.io/..."/></div></div>
        <div class="form-row">
          <div class="form-group"><label>Hero Button Text</label><input name="text" value="<?= e($config['buy_button']['text']) ?>"/></div>
          <div class="form-group"><label>Navbar Button Text</label><input name="nav_text" value="<?= e($config['buy_button']['nav_text']) ?>"/></div>
        </div>
        <button type="submit" class="save-btn">💾 Save Changes</button>
      </form>
    </div>
  </div>

  <!-- CTA -->
  <div class="panel" id="panel-cta">
    <div class="panel-header"><span>📢</span><h2>CTA Section</h2></div>
    <div class="panel-body">
      <form onsubmit="save(event,'cta')">
        <div class="form-row full"><div class="form-group"><label>Heading</label><input name="heading" value="<?= e($config['cta']['heading']) ?>"/></div></div>
        <div class="form-row full"><div class="form-group"><label>Subtext</label><input name="subtext" value="<?= e($config['cta']['subtext']) ?>"/></div></div>
        <button type="submit" class="save-btn">💾 Save Changes</button>
      </form>
    </div>
  </div>

  <!-- STATS -->
  <div class="panel" id="panel-stats">
    <div class="panel-header"><span>📊</span><h2>Stats Bar</h2></div>
    <div class="panel-body">
      <form onsubmit="save(event,'stats')">
        <div id="stats-list">
        <?php foreach($config['stats'] as $i=>$st): ?>
          <div class="repeater-item">
            <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="form-row">
              <div class="form-group"><label>Number / Value</label><input name="num[]" value="<?= e($st['num']) ?>"/></div>
              <div class="form-group"><label>Label</label><input name="label[]" value="<?= e($st['label']) ?>"/></div>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
        <button type="button" class="add-btn" onclick="addStat()">+ Add Stat</button><br/><br/>
        <button type="submit" class="save-btn">💾 Save Changes</button>
      </form>
    </div>
  </div>

  <!-- FEATURES -->
  <div class="panel" id="panel-features">
    <div class="panel-header"><span>📦</span><h2>Features Cards</h2></div>
    <div class="panel-body">
      <form onsubmit="save(event,'features')">
        <div id="features-list">
        <?php foreach($config['features'] as $f): ?>
          <div class="repeater-item">
            <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="form-row">
              <div class="form-group"><label>Icon (emoji)</label><input name="ficon[]" value="<?= e($f['icon']) ?>"/></div>
              <div class="form-group"><label>Title</label><input name="ftitle[]" value="<?= e($f['title']) ?>"/></div>
            </div>
            <div class="form-group" style="margin-top:10px"><label>Description</label><textarea name="fdesc[]"><?= e($f['desc']) ?></textarea></div>
          </div>
        <?php endforeach; ?>
        </div>
        <button type="button" class="add-btn" onclick="addFeature()">+ Add Feature</button><br/><br/>
        <button type="submit" class="save-btn">💾 Save Changes</button>
      </form>
    </div>
  </div>

  <!-- BEST FOR -->
  <div class="panel" id="panel-best_for">
    <div class="panel-header"><span>🎯</span><h2>Best For Section</h2></div>
    <div class="panel-body">
      <form onsubmit="save(event,'best_for')">
        <div id="bestfor-list">
        <?php foreach($config['best_for'] as $b): ?>
          <div class="repeater-item">
            <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="form-row">
              <div class="form-group"><label>Icon (emoji)</label><input name="bicon[]" value="<?= e($b['icon']) ?>"/></div>
              <div class="form-group"><label>Label</label><input name="blabel[]" value="<?= e($b['label']) ?>"/></div>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
        <button type="button" class="add-btn" onclick="addBestFor()">+ Add Item</button><br/><br/>
        <button type="submit" class="save-btn">💾 Save Changes</button>
      </form>
    </div>
  </div>

  <!-- TESTIMONIALS -->
  <div class="panel" id="panel-testimonials">
    <div class="panel-header"><span>⭐</span><h2>Testimonials</h2></div>
    <div class="panel-body">
      <form onsubmit="save(event,'testimonials')">
        <div id="testi-list">
        <?php foreach($config['testimonials'] as $t): ?>
          <div class="repeater-item">
            <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="form-row">
              <div class="form-group"><label>Name</label><input name="tname[]" value="<?= e($t['name']) ?>"/></div>
              <div class="form-group"><label>Location / Role</label><input name="tlocation[]" value="<?= e($t['location']) ?>"/></div>
            </div>
            <div class="form-group" style="margin-top:10px"><label>Review Text</label><textarea name="ttext[]"><?= e($t['text']) ?></textarea></div>
            <div class="form-group" style="margin-top:10px"><label>Star Rating (1-5)</label>
              <select name="trating[]">
                <?php for($r=5;$r>=1;$r--): ?>
                <option value="<?= $r ?>" <?= $t['rating']==$r?'selected':'' ?>><?= $r ?> Stars</option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
        <button type="button" class="add-btn" onclick="addTesti()">+ Add Testimonial</button><br/><br/>
        <button type="submit" class="save-btn">💾 Save Changes</button>
      </form>
    </div>
  </div>

  <!-- POLICIES -->
  <div class="panel" id="panel-policies">
    <div class="panel-header"><span>📄</span><h2>Legal Policies</h2></div>
    <div class="panel-body">
      <form onsubmit="save(event,'policies')">
        <div class="form-group" style="margin-bottom:16px"><label>Privacy Policy</label><textarea name="privacy_policy" style="min-height:120px"><?= e($config['policies']['privacy_policy']) ?></textarea></div>
        <div class="form-group" style="margin-bottom:16px"><label>Terms & Conditions</label><textarea name="terms_conditions" style="min-height:120px"><?= e($config['policies']['terms_conditions']) ?></textarea></div>
        <div class="form-group" style="margin-bottom:16px"><label>Refund Policy</label><textarea name="refund_policy" style="min-height:120px"><?= e($config['policies']['refund_policy']) ?></textarea></div>
        <div class="form-group" style="margin-bottom:16px"><label>Disclaimer</label><textarea name="disclaimer" style="min-height:120px"><?= e($config['policies']['disclaimer']) ?></textarea></div>
        <button type="submit" class="save-btn">💾 Save Changes</button>
      </form>
    </div>
  </div>

  <!-- CREDENTIALS -->
  <div class="panel" id="panel-credentials">
    <div class="panel-header"><span>🔑</span><h2>Change Admin Credentials</h2></div>
    <div class="panel-body">
      <form onsubmit="save(event,'admin_credentials')">
        <div class="form-row full"><div class="form-group"><label>Current Password (required)</label><input type="password" name="current_password" required/></div></div>
        <div class="form-row">
          <div class="form-group"><label>New Username (leave blank to keep)</label><input type="text" name="new_username" placeholder="<?= e($config['admin']['username']) ?>"/></div>
          <div class="form-group"><label>New Password (leave blank to keep)</label><input type="password" name="new_password" placeholder="••••••••"/></div>
        </div>
        <button type="submit" class="save-btn">🔐 Update Credentials</button>
      </form>
    </div>
  </div>

</main>

<div class="toast" id="toast"></div>

<script>
function showPanel(id,btn){
  document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(b=>b.classList.remove('active'));
  document.getElementById('panel-'+id).classList.add('active');
  btn.classList.add('active');
}

async function save(e,section){
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);
  fd.append('section',section);
  try{
    const r = await fetch('/admin/save',{method:'POST',body:fd});
    const d = await r.json();
    showToast(d.msg, d.ok ? '' : 'error');
  } catch(err){
    showToast('Network error','error');
  }
}

function showToast(msg, type=''){
  const t=document.getElementById('toast');
  t.textContent=msg;
  t.className='toast'+(type?' '+type:'');
  t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),3000);
}

function addStat(){
  const d=document.createElement('div');d.className='repeater-item';
  d.innerHTML=`<button type="button" class="remove-btn" onclick="this.parentElement.remove()">✕ Remove</button>
  <div class="form-row">
    <div class="form-group"><label>Number / Value</label><input name="num[]" placeholder="e.g. 5000+"/></div>
    <div class="form-group"><label>Label</label><input name="label[]" placeholder="e.g. Happy Creators"/></div>
  </div>`;
  document.getElementById('stats-list').appendChild(d);
}
function addFeature(){
  const d=document.createElement('div');d.className='repeater-item';
  d.innerHTML=`<button type="button" class="remove-btn" onclick="this.parentElement.remove()">✕ Remove</button>
  <div class="form-row">
    <div class="form-group"><label>Icon (emoji)</label><input name="ficon[]" placeholder="🎬"/></div>
    <div class="form-group"><label>Title</label><input name="ftitle[]" placeholder="Feature title"/></div>
  </div>
  <div class="form-group" style="margin-top:10px"><label>Description</label><textarea name="fdesc[]" placeholder="Feature description..."></textarea></div>`;
  document.getElementById('features-list').appendChild(d);
}
function addBestFor(){
  const d=document.createElement('div');d.className='repeater-item';
  d.innerHTML=`<button type="button" class="remove-btn" onclick="this.parentElement.remove()">✕ Remove</button>
  <div class="form-row">
    <div class="form-group"><label>Icon (emoji)</label><input name="bicon[]" placeholder="🎯"/></div>
    <div class="form-group"><label>Label</label><input name="blabel[]" placeholder="e.g. Cricket Fan Pages"/></div>
  </div>`;
  document.getElementById('bestfor-list').appendChild(d);
}
function addTesti(){
  const d=document.createElement('div');d.className='repeater-item';
  d.innerHTML=`<button type="button" class="remove-btn" onclick="this.parentElement.remove()">✕ Remove</button>
  <div class="form-row">
    <div class="form-group"><label>Name</label><input name="tname[]" placeholder="Customer name"/></div>
    <div class="form-group"><label>Location / Role</label><input name="tlocation[]" placeholder="e.g. Instagram Creator, Mumbai"/></div>
  </div>
  <div class="form-group" style="margin-top:10px"><label>Review Text</label><textarea name="ttext[]" placeholder="Review text..."></textarea></div>
  <div class="form-group" style="margin-top:10px"><label>Star Rating</label>
    <select name="trating[]"><option value="5">5 Stars</option><option value="4">4 Stars</option><option value="3">3 Stars</option><option value="2">2 Stars</option><option value="1">1 Star</option></select>
  </div>`;
  document.getElementById('testi-list').appendChild(d);
}
</script>
</body>
</html>
