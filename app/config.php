<?php
// Non-public app config (outside docroot)
const DB_HOST = 'localhost';
const DB_USER = 'motorpoi_anketa';
const DB_PASS = 'GrginDol4646';
const DB_NAME = 'motorpoi_anketa';

const ADMIN_USER = 'admin';
const ADMIN_PASS = 'change_this_strong_pass';

const RECORD_NO   = 'AZK SUK 0912';
const REVISION    = '1.00';
const ISSUE_DATE  = '2019-11-05';

function pdo_server(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
    $opt = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $opt);
}
function pdo_db(): PDO {
    $pdo = pdo_server();
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.DB_NAME.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `'.DB_NAME.'`');
    $pdo->exec('CREATE TABLE IF NOT EXISTS `submissions` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `record_no` VARCHAR(64) NOT NULL,
        `revision` VARCHAR(16) NOT NULL,
        `issue_date` DATE NOT NULL,
        `lang` ENUM("hr","en") NOT NULL DEFAULT "hr",
        `company` VARCHAR(255) NOT NULL,
        `address` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(50) NULL,
        `fax` VARCHAR(50) NULL,
        `web` VARCHAR(255) NULL,
        `email` VARCHAR(255) NOT NULL,
        `qms` ENUM("yes","no") NOT NULL DEFAULT "no",
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    return $pdo;
}
