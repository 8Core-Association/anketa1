<?php
require __DIR__ . '/config.php';
require __DIR__ . '/ensure_vendor.php';

use Dompdf\Dompdf; use Dompdf\Options;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); echo 'Invalid id'; exit; }

$pdo = pdo_db();
$stmt = $pdo->prepare('SELECT * FROM submissions WHERE id = :id');
$stmt->execute([':id'=>$id]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); echo 'Not found'; exit; }

$css = 'body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;color:#111} .header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ccc;padding-bottom:6px;margin-bottom:10px}.h1{font-size:18px;font-weight:bold}.small{color:#555}.section{margin:12px 0}.table{width:100%;border-collapse:collapse}.table th,.table td{border:1px solid #ccc;padding:6px}.rule{font-size:11px;color:#555}';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
$hTitle = $row['lang']==='en' ? 'CUSTOMER SATISFACTION SURVEY' : 'ANKETA – ZADOVOLJSTVO KORISNIKA';
$hSub   = $row['lang']==='en' ? 'Anketa – Zadovoljstvo korisnika' : 'Customer Satisfaction Survey';
$ratingsTitle = $row['lang']==='en' ? 'How satisfied are you' : 'Koliko ste zadovoljni';
$openTitle    = $row['lang']==='en' ? 'Open questions' : 'Otvorena pitanja';
$footerTitle  = $row['lang']==='en' ? 'Signature & Date' : 'Potpis i datum';

$html = '<html><head><meta charset="utf-8"><style>'.$css.'</style></head><body>';
$html .= '<div class="header"><div><div class="h1">'.e($hTitle).'</div><div class="small">'.e($hSub).'</div></div><div class="small">Record: '.e($row['record_no']).'<br>Rev. '.e($row['revision']).'<br>Date: '.e($row['issue_date']).'</div></div>';
$html .= '<div class="section"><strong>Organization</strong><br>'.e($row['company']).' — '.e($row['address']).'<br>Phone: '.e($row['phone']).' | Fax: '.e($row['fax']).' | Web: '.e($row['web']).' | Email: '.e($row['email']).'</div>';
$html .= '<div class="section"><strong>Certified QMS</strong>: '.($row['qms']==='yes'?'YES':'NO'); if ($row['qms']==='yes' && $row['certificate']) $html .= ' — '.e($row['certificate']); $html .= '</div>';
$html .= '<div class="section"><strong>'.e($ratingsTitle).'</strong><br><table class="table"><tbody><tr><th>Product / service characteristics</th><td>'.(int)$row['r1'].'</td></tr><tr><th>Employees cooperativity</th><td>'.(int)$row['r2'].'</td></tr><tr><th>Term of delivery</th><td>'.(int)$row['r3'].'</td></tr><tr><th>Price and terms of payment</th><td>'.(int)$row['r4'].'</td></tr></tbody></table><div class="rule">4 – Completely satisfied / 3 – Mainly satisfied / 2 – Mainly unsatisfied / 1 – Completely unsatisfied</div></div>';
$html .= '<div class="section"><strong>'.e($openTitle).'</strong><div><em>Maintain cooperation and why?</em><br>'.nl2br(e($row['q1'])).'</div><div><em>Recommend us and why?</em><br>'.nl2br(e($row['q2'])).'</div><div><em>Remarks & recommendations</em><br>'.nl2br(e($row['q3'])).'</div></div>';
$html .= '<div class="section"><strong>'.e($footerTitle).'</strong><br>Filled by: '.e($row['filled_by']).' — Signature: '.e($row['signature']).' — Date: '.e($row['doc_date']).'</div>';
$html .= '<div class="small">Generated: '.date('Y-m-d H:i').'</div></body></html>';

$opts = new Options(); $opts->set('isRemoteEnabled', true);
$dompdf = new Dompdf($opts); $dompdf->loadHtml($html, 'UTF-8'); $dompdf->setPaper('A4', 'portrait'); $dompdf->render();
$fname = 'CSS_'.$row['id'].'_'.preg_replace('/[^A-Za-z0-9_-]+/','_', $row['company']).'.pdf';
$dompdf->stream($fname, [ 'Attachment' => true ]);
