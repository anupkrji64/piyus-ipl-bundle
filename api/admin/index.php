<?php
require_once __DIR__ . '/auth.php';
if (auth_check()) { header('Location: /admin/dashboard'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = json_decode(file_get_contents(__DIR__ . '/../../config.json'), true);
    $u  = trim($_POST['username'] ?? '');
    $pw = trim($_POST['password'] ?? '');
    if ($u === ($config['admin']['username'] ?? '') && $pw === ($config['admin']['password'] ?? '')) {
        auth_login($u);
        header('Location: /admin/dashboard'); exit;
    }
    $error = 'Invalid username or password.';
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Admin Login</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet"/>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#1e3a8a,#1d4ed8);min-height:100vh;display:flex;align-items:center;justify-content:center}
.card{background:#fff;border-radius:20px;padding:40px 36px;width:100%;max-width:400px;box-shadow:0 24px 80px rgba(0,0,0,.25)}
.logo{text-align:center;font-size:20px;font-weight:900;color:#1e3a8a;margin-bottom:6px}.logo span{color:#f97316}
.sub{text-align:center;font-size:13px;color:#64748b;margin-bottom:28px}
.fg{margin-bottom:16px}
label{display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px}
input{width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;color:#1e293b;outline:none;font-family:'Inter',sans-serif}
input:focus{border-color:#1d4ed8;box-shadow:0 0 0 3px rgba(29,78,216,.1)}
.btn{width:100%;background:linear-gradient(135deg,#f97316,#ea580c);color:#fff;border:none;padding:12px;border-radius:9px;font-size:15px;font-weight:700;cursor:pointer;margin-top:6px;font-family:'Inter',sans-serif}
.btn:hover{opacity:.92}
.err{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:9px 13px;border-radius:7px;font-size:13px;margin-bottom:16px}
.back{text-align:center;margin-top:16px}<br/>.back a{font-size:13px;color:#64748b;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <div class="logo">🏏 IPL<span>Reels</span>Bundle</div>
  <div class="sub">Admin Panel — Sign In</div>
  <?php if($error): ?><div class="err">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <div class="fg"><label>Username</label><input type="text" name="username" required autocomplete="username"/></div>
    <div class="fg"><label>Password</label><input type="password" name="password" required autocomplete="current-password"/></div>
    <button type="submit" class="btn">🔐 Sign In</button>
  </form>
  <div class="back"><a href="/">← Back to Site</a></div>
</div>
</body>
</html>
