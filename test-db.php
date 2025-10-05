<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST MYSQL KONEKCIJE ===\n\n";

$host = 'localhost';
$user = 'motorpoi_anketa';
$pass = 'GrginDol4646';
$dbname = 'motorpoi_anketa';

echo "Host: $host\n";
echo "User: $user\n";
echo "DB: $dbname\n\n";

try {
    echo "1. Testiranje PDO MySQL ekstenzije...\n";
    if (!extension_loaded('pdo_mysql')) {
        die("ERROR: PDO MySQL ekstenzija nije učitana!\n");
    }
    echo "   ✓ PDO MySQL ekstenzija OK\n\n";

    echo "2. Pokušaj konekcije na server...\n";
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "   ✓ Konekcija na MySQL server uspješna\n\n";

    echo "3. Kreiranje/provjera baze '$dbname'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Baza '$dbname' postoji/kreirana\n\n";

    echo "4. Odabir baze...\n";
    $pdo->exec("USE `$dbname`");
    echo "   ✓ Baza odabrana\n\n";

    echo "5. Kreiranje tablice 'submissions'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `submissions` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `record_no` VARCHAR(64) NOT NULL,
        `revision` VARCHAR(16) NOT NULL,
        `issue_date` DATE NOT NULL,
        `lang` ENUM('hr','en') NOT NULL DEFAULT 'hr',
        `company` VARCHAR(255) NOT NULL,
        `address` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(50) NULL,
        `fax` VARCHAR(50) NULL,
        `web` VARCHAR(255) NULL,
        `email` VARCHAR(255) NOT NULL,
        `qms` ENUM('yes','no') NOT NULL DEFAULT 'no',
        `certificate` VARCHAR(255) NULL,
        `r1` TINYINT UNSIGNED NOT NULL,
        `r2` TINYINT UNSIGNED NOT NULL,
        `r3` TINYINT UNSIGNED NOT NULL,
        `r4` TINYINT UNSIGNED NOT NULL,
        `q1` TEXT NULL,
        `q2` TEXT NULL,
        `q3` TEXT NULL,
        `filled_by` VARCHAR(255) NULL,
        `signature` VARCHAR(255) NULL,
        `doc_date` DATE NULL,
        `ip` VARCHAR(45) NULL,
        `user_agent` TEXT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "   ✓ Tablica 'submissions' postoji/kreirana\n\n";

    echo "6. Testiranje INSERT...\n";
    $stmt = $pdo->prepare("INSERT INTO submissions
        (record_no, revision, issue_date, lang, company, address, email, qms, r1, r2, r3, r4, ip)
        VALUES
        (:rno, :rev, :idate, :lang, :comp, :addr, :email, :qms, :r1, :r2, :r3, :r4, :ip)");

    $stmt->execute([
        ':rno' => 'TEST-001',
        ':rev' => '1.00',
        ':idate' => date('Y-m-d'),
        ':lang' => 'hr',
        ':comp' => 'Test Company',
        ':addr' => 'Test Address 123',
        ':email' => 'test@example.com',
        ':qms' => 'no',
        ':r1' => 3,
        ':r2' => 3,
        ':r3' => 3,
        ':r4' => 3,
        ':ip' => '127.0.0.1'
    ]);

    $insertId = $pdo->lastInsertId();
    echo "   ✓ TEST zapis uspješno umetnut (ID: $insertId)\n\n";

    echo "7. Testiranje SELECT...\n";
    $result = $pdo->query("SELECT COUNT(*) as cnt FROM submissions")->fetch();
    echo "   ✓ Broj zapisa u tablici: " . $result['cnt'] . "\n\n";

    echo "8. Brisanje test zapisa...\n";
    $pdo->exec("DELETE FROM submissions WHERE record_no = 'TEST-001'");
    echo "   ✓ Test zapis obrisan\n\n";

    echo "=== SVE PROVJERE USPJEŠNE! ===\n";
    echo "MySQL konekcija radi ispravno.\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ EXCEPTION: " . $e->getMessage() . "\n";
    exit(1);
}
