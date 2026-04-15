<?php
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json');
if (!auth_check()) {
    echo json_encode(['ok'=>false,'msg'=>'Unauthorized']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false,'msg'=>'Method not allowed']); exit;
}

$section  = $_POST['section'] ?? '';
$pageSlug = preg_replace('/[^a-z0-9\-]/','',strtolower(trim($_POST['page_slug'] ?? '')));
$pagesDir = __DIR__ . '/../../pages/';
$configPath = $pageSlug ? ($pagesDir.$pageSlug.'.json') : __DIR__.'/../../config.json';

// PAGE CRUD
if ($section === 'create_page') {
    $newSlug  = preg_replace('/[^a-z0-9\-]/','',strtolower(trim($_POST['new_slug']??'')));
    $newTitle = trim($_POST['new_title']??'New Bundle');
    if (!$newSlug) { echo json_encode(['ok'=>false,'msg'=>'Invalid slug']); exit; }
    if (!is_dir($pagesDir)) mkdir($pagesDir,0755,true);
    $target = $pagesDir.$newSlug.'.json';
    if (file_exists($target)) { echo json_encode(['ok'=>false,'msg'=>'Page already exists']); exit; }
    $base = json_decode(file_get_contents(__DIR__.'/../../config.json'),true);
    $base['site']['title'] = $newTitle;
    $base['hero']['heading_highlight'] = $newTitle;
    $base['buy_button']['url'] = '';
    $base['buy_button']['use_razorpay'] = false;
    file_put_contents($target,json_encode($base,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true,'msg'=>'Page created!','slug'=>$newSlug]); exit;
}

if ($section === 'delete_page') {
    $delSlug = preg_replace('/[^a-z0-9\-]/','',strtolower(trim($_POST['del_slug']??'')));
    $target  = $pagesDir.$delSlug.'.json';
    if ($delSlug && file_exists($target)) { unlink($target); echo json_encode(['ok'=>true,'msg'=>'Deleted']); }
    else echo json_encode(['ok'=>false,'msg'=>'Not found']);
    exit;
}

if (!file_exists($configPath)) { echo json_encode(['ok'=>false,'msg'=>'Config not found: '.$configPath]); exit; }
$config = json_decode(file_get_contents($configPath),true);

switch ($section) {
    case 'site':
        $config['site']['title']            = trim($_POST['title']??'');
        $config['site']['meta_description'] = trim($_POST['meta_description']??'');
        $config['site']['topbar_text']      = trim($_POST['topbar_text']??'');
        break;
    case 'hero':
        $config['hero']['badge']             = trim($_POST['badge']??'');
        $config['hero']['heading_line1']     = trim($_POST['heading_line1']??'');
        $config['hero']['heading_highlight'] = trim($_POST['heading_highlight']??'');
        $config['hero']['heading_line2']     = trim($_POST['heading_line2']??'');
        $config['hero']['description']       = trim($_POST['description']??'');
        break;
    case 'pricing':
        $config['pricing']['current_price']   = trim($_POST['current_price']??'');
        $config['pricing']['original_price']  = trim($_POST['original_price']??'');
        $config['pricing']['discount_label']  = trim($_POST['discount_label']??'');
        $config['pricing']['currency_symbol'] = trim($_POST['currency_symbol']??'');
        break;
    case 'razorpay':
        if (!isset($config['razorpay'])) $config['razorpay'] = [];
        $config['razorpay']['key_id']         = trim($_POST['key_id']??'');
        $config['razorpay']['business_name']  = trim($_POST['business_name']??'');
        $config['razorpay']['description']    = trim($_POST['rzp_description']??'');
        $config['razorpay']['theme_color']    = trim($_POST['theme_color']??'#f97316');
        $config['buy_button']['use_razorpay'] = !empty($_POST['use_razorpay']);
        break;
    case 'button':
        $config['buy_button']['text']     = trim($_POST['text']??'');
        $config['buy_button']['url']      = trim($_POST['url']??'');
        $config['buy_button']['nav_text'] = trim($_POST['nav_text']??'');
        break;
    case 'cta':
        $config['cta']['heading'] = trim($_POST['heading']??'');
        $config['cta']['subtext'] = trim($_POST['subtext']??'');
        break;
    case 'stats':
        $nums=$_POST['num']??[]; $labels=$_POST['label']??[];
        $config['stats']=[];
        foreach ($nums as $i=>$n) if(trim($n)!=='') $config['stats'][]=['num'=>trim($n),'label'=>trim($labels[$i]??'')];
        break;
    case 'features':
        $icons=$_POST['ficon']??[]; $titles=$_POST['ftitle']??[]; $descs=$_POST['fdesc']??[];
        $config['features']=[];
        foreach ($titles as $i=>$t) if(trim($t)!=='') $config['features'][]=['icon'=>trim($icons[$i]??''),'title'=>trim($t),'desc'=>trim($descs[$i]??'')];
        break;
    case 'best_for':
        $bicons=$_POST['bicon']??[]; $blabels=$_POST['blabel']??[];
        $config['best_for']=[];
        foreach ($blabels as $i=>$bl) if(trim($bl)!=='') $config['best_for'][]=['icon'=>trim($bicons[$i]??''),'label'=>trim($bl)];
        break;
    case 'testimonials':
        $tnames=$_POST['tname']??[]; $tlocs=$_POST['tlocation']??[];
        $ttexts=$_POST['ttext']??[]; $tratings=$_POST['trating']??[];
        $config['testimonials']=[];
        foreach ($tnames as $i=>$tn) {
            if(trim($tn)!=='') $config['testimonials'][]=[
                'name'=>trim($tn),'location'=>trim($tlocs[$i]??''),
                'text'=>trim($ttexts[$i]??''),'rating'=>(int)($tratings[$i]??5)
            ];
        }
        break;
    case 'policies':
        $config['policies']['privacy_policy']   = trim($_POST['privacy_policy']??'');
        $config['policies']['terms_conditions']  = trim($_POST['terms_conditions']??'');
        $config['policies']['refund_policy']     = trim($_POST['refund_policy']??'');
        $config['policies']['disclaimer']        = trim($_POST['disclaimer']??'');
        break;
    case 'admin_credentials':
        $curPw = trim($_POST['current_password']??'');
        if ($curPw !== ($config['admin']['password']??'')) { echo json_encode(['ok'=>false,'msg'=>'Wrong current password']); exit; }
        if ($u=trim($_POST['new_username']??'')) $config['admin']['username']=$u;
        if ($pw=trim($_POST['new_password']??'')) $config['admin']['password']=$pw;
        break;
    default:
        echo json_encode(['ok'=>false,'msg'=>'Unknown section']); exit;
}

$r = file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode($r!==false ? ['ok'=>true,'msg'=>'Saved!'] : ['ok'=>false,'msg'=>'Write failed — check file permissions']);
