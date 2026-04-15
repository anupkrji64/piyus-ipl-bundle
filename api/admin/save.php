<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in']!==true){
  echo json_encode(['ok'=>false,'msg'=>'Unauthorized']); exit;
}
if($_SERVER['REQUEST_METHOD']!=='POST'){
  echo json_encode(['ok'=>false,'msg'=>'Method not allowed']); exit;
}
$configPath = __DIR__.'/../../config.json';
$config = json_decode(file_get_contents($configPath), true);
$section = $_POST['section'] ?? '';

switch($section){
  case 'site':
    $config['site']['title']           = trim($_POST['title'] ?? '');
    $config['site']['meta_description']= trim($_POST['meta_description'] ?? '');
    $config['site']['topbar_text']     = trim($_POST['topbar_text'] ?? '');
    break;
  case 'hero':
    $config['hero']['badge']             = trim($_POST['badge'] ?? '');
    $config['hero']['heading_line1']     = trim($_POST['heading_line1'] ?? '');
    $config['hero']['heading_highlight'] = trim($_POST['heading_highlight'] ?? '');
    $config['hero']['heading_line2']     = trim($_POST['heading_line2'] ?? '');
    $config['hero']['description']       = trim($_POST['description'] ?? '');
    break;
  case 'pricing':
    $config['pricing']['current_price']  = trim($_POST['current_price'] ?? '');
    $config['pricing']['original_price'] = trim($_POST['original_price'] ?? '');
    $config['pricing']['discount_label'] = trim($_POST['discount_label'] ?? '');
    $config['pricing']['currency_symbol']= trim($_POST['currency_symbol'] ?? '');
    break;
  case 'button':
    $config['buy_button']['text']     = trim($_POST['text'] ?? '');
    $config['buy_button']['url']      = trim($_POST['url'] ?? '');
    $config['buy_button']['nav_text'] = trim($_POST['nav_text'] ?? '');
    break;
  case 'cta':
    $config['cta']['heading'] = trim($_POST['heading'] ?? '');
    $config['cta']['subtext'] = trim($_POST['subtext'] ?? '');
    break;
  case 'stats':
    $nums   = $_POST['num']   ?? [];
    $labels = $_POST['label'] ?? [];
    $config['stats'] = [];
    foreach($nums as $i=>$n){
      if(trim($n)!==''){
        $config['stats'][] = ['num'=>trim($n),'label'=>trim($labels[$i] ?? '')];
      }
    }
    break;
  case 'features':
    $icons  = $_POST['ficon']  ?? [];
    $titles = $_POST['ftitle'] ?? [];
    $descs  = $_POST['fdesc']  ?? [];
    $config['features'] = [];
    foreach($titles as $i=>$t){
      if(trim($t)!==''){
        $config['features'][] = ['icon'=>trim($icons[$i] ?? ''),'title'=>trim($t),'desc'=>trim($descs[$i] ?? '')];
      }
    }
    break;
  case 'best_for':
    $bicons  = $_POST['bicon']  ?? [];
    $blabels = $_POST['blabel'] ?? [];
    $config['best_for'] = [];
    foreach($blabels as $i=>$bl){
      if(trim($bl)!==''){
        $config['best_for'][] = ['icon'=>trim($bicons[$i] ?? ''),'label'=>trim($bl)];
      }
    }
    break;
  case 'testimonials':
    $tnames   = $_POST['tname']     ?? [];
    $tlocs    = $_POST['tlocation'] ?? [];
    $ttexts   = $_POST['ttext']     ?? [];
    $tratings = $_POST['trating']   ?? [];
    $config['testimonials'] = [];
    foreach($tnames as $i=>$tn){
      if(trim($tn)!==''){
        $config['testimonials'][] = [
          'name'     => trim($tn),
          'location' => trim($tlocs[$i] ?? ''),
          'text'     => trim($ttexts[$i] ?? ''),
          'rating'   => (int)($tratings[$i] ?? 5),
        ];
      }
    }
    break;
  case 'policies':
    $config['policies']['privacy_policy']  = trim($_POST['privacy_policy'] ?? '');
    $config['policies']['terms_conditions']= trim($_POST['terms_conditions'] ?? '');
    $config['policies']['refund_policy']   = trim($_POST['refund_policy'] ?? '');
    $config['policies']['disclaimer']      = trim($_POST['disclaimer'] ?? '');
    break;
  case 'admin_credentials':
    $newUser = trim($_POST['new_username'] ?? '');
    $newPass = trim($_POST['new_password'] ?? '');
    $curPass = trim($_POST['current_password'] ?? '');
    if($curPass !== $config['admin']['password']){
      echo json_encode(['ok'=>false,'msg'=>'Current password is incorrect.']); exit;
    }
    if($newUser) $config['admin']['username'] = $newUser;
    if($newPass) $config['admin']['password'] = $newPass;
    break;
  default:
    echo json_encode(['ok'=>false,'msg'=>'Unknown section']); exit;
}

$result = file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
if($result===false){
  echo json_encode(['ok'=>false,'msg'=>'Failed to write config.json — check file permissions.']);
} else {
  echo json_encode(['ok'=>true,'msg'=>'Saved successfully!']);
}
