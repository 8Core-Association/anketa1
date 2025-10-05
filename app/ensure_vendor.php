<?php
// Try to ensure Composer dependencies are installed (dompdf) without manual SSH.
// Attempts, in order:
// 1) Use system 'composer' if available.
// 2) Use 'composer.phar' in app/ if present.
// 3) Download composer.phar from getcomposer.org and run install.
//
// NOTE: This requires exec()/shell_exec() to be enabled and outbound HTTPS allowed.
// If not permitted, you'll need to run `composer install` manually in the app/ folder.

$ROOT = __DIR__;
$autoload = $ROOT . '/vendor/autoload.php';
if (file_exists($autoload)) { require $autoload; return; }

function can_exec(){ return function_exists('exec') || function_exists('shell_exec') || function_exists('passthru') || function_exists('system'); }
function run_cmd($cmd, $cwd){
    $out = []; $ret = 0;
    if (function_exists('exec')) { exec($cmd . ' 2>&1', $out, $ret); }
    elseif (function_exists('shell_exec')) { $out = explode("\n", shell_exec($cmd . ' 2>&1') ?? ''); $ret = 0; }
    elseif (function_exists('system')) { ob_start(); system($cmd . ' 2>&1', $ret); $out = explode("\n", ob_get_clean()); }
    elseif (function_exists('passthru')) { ob_start(); passthru($cmd . ' 2>&1', $ret); $out = explode("\n", ob_get_clean()); }
    return [$ret, implode("\n", $out)];
}

if (!can_exec()) {
    http_response_code(500);
    echo 'Composer auto-install unavailable: PHP exec functions disabled. Please run composer install in /app.';
    exit;
}

chdir($ROOT);
putenv('COMPOSER_ALLOW_SUPERUSER=1');

// 1) Try system composer
list($ret1, $out1) = run_cmd('composer --version', $ROOT);
if ($ret1 === 0) {
    list($rc, $out) = run_cmd('composer install --no-dev --prefer-dist --no-interaction --no-progress', $ROOT);
    if (file_exists($autoload)) { require $autoload; return; }
}

// 2) Try local composer.phar
if (file_exists($ROOT . '/composer.phar')) {
    list($rc, $out) = run_cmd('php composer.phar install --no-dev --prefer-dist --no-interaction --no-progress', $ROOT);
    if (file_exists($autoload)) { require $autoload; return; }
}

// 3) Download composer.phar
$installer = @file_get_contents('https://getcomposer.org/installer');
if ($installer) {
    file_put_contents($ROOT . '/composer-setup.php', $installer);
    list($rc, $out) = run_cmd('php composer-setup.php --install-dir='.$ROOT.' --filename=composer.phar', $ROOT);
    @unlink($ROOT . '/composer-setup.php');
    if (file_exists($ROOT . '/composer.phar')) {
        list($rc2, $out2) = run_cmd('php composer.phar install --no-dev --prefer-dist --no-interaction --no-progress', $ROOT);
        if (file_exists($autoload)) { require $autoload; return; }
    }
}

// If we got here, fail with instructions
http_response_code(500);
echo 'Composer auto-install failed. Please SSH: cd app && composer install';
exit;
