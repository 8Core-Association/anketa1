<?php
require dirname(__DIR__) . '/config.php';

$u = $_SERVER['PHP_AUTH_USER'] ?? null;
$p = $_SERVER['PHP_AUTH_PW'] ?? null;
if ($u !== ADMIN_USER || $p !== ADMIN_PASS) {
    header('WWW-Authenticate: Basic realm="anketa-admin"');
    header('HTTP/1.0 401 Unauthorized'); echo 'Auth required'; exit;
}

$pdo = pdo_db();
$avg = $pdo->query('SELECT COUNT(*) AS total, AVG(r1) a1, AVG(r2) a2, AVG(r3) a3, AVG(r4) a4 FROM submissions')->fetch();
$total = (int)($avg['total'] ?? 0);

function dist(PDO $pdo, string $col): array {
    $stmt = $pdo->query("SELECT $col AS v, COUNT(*) c FROM submissions GROUP BY $col ORDER BY v");
    $out = [1=>0,2=>0,3=>0,4=>0]; foreach ($stmt as $r) $out[(int)$r['v']] = (int)$r['c']; return $out;
}
$d1=dist($pdo,'r1'); $d2=dist($pdo,'r2'); $d3=dist($pdo,'r3'); $d4=dist($pdo,'r4');
$rows = $pdo->query('SELECT id, created_at, company, email, r1, r2, r3, r4, lang FROM submissions ORDER BY id DESC LIMIT 200')->fetchAll();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
?><!doctype html><html lang="hr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Anketa – Admin</title><style>
body{font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial;margin:20px;color:#111}
.grid{display:grid;gap:16px}@media(min-width:1000px){.grid{grid-template-columns:2fr 1fr}}.card{border:1px solid #ddd;border-radius:12px;padding:14px}
h1{margin:.2em 0}.table{width:100%;border-collapse:collapse}.table th,.table td{border:1px solid #ddd;padding:8px;text-align:left}
.badge{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid #ccc;font-size:12px}
.mono{font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace}.bar{display:flex;gap:6px;align-items:center}
.segment{height:10px;background:#e5e7eb;border:1px solid #d1d5db}.segment>div{height:100%}.right{text-align:right}.small{color:#555;font-size:12px}
</style></head><body>
<h1>Admin – Anketa</h1><div class="small mono">DB: <?=h(DB_NAME)?> · total: <strong><?=$total?></strong></div>
<div class="grid">
  <div class="card">
    <h2>Poslani odgovori (zadnjih 200)</h2>
    <table class="table"><thead><tr>
      <th>ID</th><th>Vrijeme</th><th>Tvrtka</th><th>E-mail</th><th>R1</th><th>R2</th><th>R3</th><th>R4</th><th>Ø</th><th>Jezik</th><th>PDF</th>
    </tr></thead><tbody>
    <?php foreach($rows as $r): $avgRow = ($r['r1']+$r['r2']+$r['r3']+$r['r4'])/4; ?>
      <tr>
        <td class="mono"><?= (int)$r['id'] ?></td>
        <td class="mono"><?= h($r['created_at']) ?></td>
        <td><?= h($r['company']) ?></td>
        <td><?= h($r['email']) ?></td>
        <td><?= (int)$r['r1'] ?></td>
        <td><?= (int)$r['r2'] ?></td>
        <td><?= (int)$r['r3'] ?></td>
        <td><?= (int)$r['r4'] ?></td>
        <td><?= number_format($avgRow,2) ?></td>
        <td><span class="badge"><?= h(strtoupper($r['lang'])) ?></span></td>
        <td><a class="badge" href="/pdf.php?id=<?= (int)$r['id'] ?>" target="_blank">PDF</a></td>
      </tr>
    <?php endforeach; ?></tbody></table>
  </div>
  <div class="card"><h2>Statistika ocjena</h2>
    <?php $sections=[['R1','Karakteristike proizvoda/usluge',$d1,(float)$avg['a1']],['R2','Kooperativnost osoblja',$d2,(float)$avg['a2']],['R3','Rok isporuke',$d3,(float)$avg['a3']],['R4','Cijena i uvjeti plaćanja',$d4,(float)$avg['a4']]];
    foreach($sections as [$code,$label,$dist,$a]): $sum=array_sum($dist)?:1; ?>
      <div style="margin-bottom:12px"><div><strong><?=h($code)?> – <?=h($label)?></strong> <span class="small">Ø <?=number_format($a,2)?></span></div>
      <div class="bar"><?php for($i=1;$i<=4;$i++): $w=(100*$dist[$i])/$sum; ?>
        <div class="segment" style="flex:<?=$dist[$i]?>"><div style="width: <?=$w?>%"></div></div>
        <div class="small"><?=$i?> (<?=$dist[$i]?>)</div><?php endfor; ?></div></div>
    <?php endforeach; ?>
    <p class="small right"><em>Napomena:</em> širina segmenata proporcionalna je broju odgovora.</p>
  </div>
</div></body></html>
