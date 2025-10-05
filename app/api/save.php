<?php
require dirname(__DIR__) . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $input = file_get_contents('php://input');
    $data = [];
    if ($input && isset($_SERVER['CONTENT_TYPE']) && str_starts_with($_SERVER['CONTENT_TYPE'], 'application/json')) {
        $data = json_decode($input, true) ?? [];
    } else {
        $data = $_POST ?: [];
    }
    $f = fn($k) => isset($data[$k]) ? trim((string)$data[$k]) : '';
    $lang = in_array($f('lang'), ['hr','en']) ? $f('lang') : 'hr';
    $company=$f('company'); $address=$f('address'); $phone=$f('phone'); $fax=$f('fax'); $web=$f('web'); $email=$f('email');
    $qms = $f('qms') === 'yes' ? 'yes' : 'no'; $certificate = $qms === 'yes' ? $f('certificate') : null;
    $r1=(int)($f('r1')?:0); $r2=(int)($f('r2')?:0); $r3=(int)($f('r3')?:0); $r4=(int)($f('r4')?:0);
    $q1=$f('q1'); $q2=$f('q2'); $q3=$f('q3'); $filled_by=$f('filledBy'); $signature=$f('signature'); $doc_date=$f('date');
    $missing=[];
    foreach ([['company',$company],['address',$address],['email',$email]] as [$k,$v]) if ($v==='') $missing[]=$k;
    foreach ([['r1',$r1],['r2',$r2],['r3',$r3],['r4',$r4]] as [$k,$v]) if (!in_array($v,[1,2,3,4],true)) $missing[]=$k;
    if ($qms==='yes' && $certificate==='') $missing[]='certificate';
    if ($missing){ http_response_code(422); echo json_encode(['ok'=>false,'error'=>'missing_fields','fields'=>$missing],JSON_UNESCAPED_UNICODE); exit; }

    $payload = [
        'record_no' => RECORD_NO,
        'revision' => REVISION,
        'issue_date' => ISSUE_DATE,
        'lang' => $lang,
        'company' => $company,
        'address' => $address,
        'phone' => $phone ?: null,
        'fax' => $fax ?: null,
        'web' => $web ?: null,
        'email' => $email,
        'qms' => $qms,
        'certificate' => $certificate ?: null,
        'r1' => $r1,
        'r2' => $r2,
        'r3' => $r3,
        'r4' => $r4,
        'q1' => $q1 ?: null,
        'q2' => $q2 ?: null,
        'q3' => $q3 ?: null,
        'filled_by' => $filled_by ?: null,
        'signature' => $signature ?: null,
        'doc_date' => $doc_date ?: null,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];

    $ch = curl_init(SUPABASE_URL . '/rest/v1/submissions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'apikey: ' . SUPABASE_ANON_KEY,
            'Authorization: Bearer ' . SUPABASE_ANON_KEY,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 201) {
        throw new Exception('Supabase insert failed: ' . $response);
    }

    $result = json_decode($response, true);
    $id = $result[0]['id'] ?? 0;

    echo json_encode(['ok'=>true,'id'=>$id,'pdf_url'=>'/pdf.php?id='.$id], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'server_error','msg'=>$e->getMessage()]);
}
