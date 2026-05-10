<?php
require_once 'ambiente.php';

try {
    // ===== CONFIG BACKUP =====
    $DB_HOST = ($DEBUG_LOCAL == 1)
        ? '69.6.249.13'
        : 'localhost';

    $DB_NAME = 'luca5858_geral2';
    $DB_USER = 'luca5858_AutoDocVaa';
    $DB_PASS = 'MZeRT6aZ5Q2F4ww';
    $DB_CHARSET = 'utf8';

    // LOCAL
    if ($DEBUG_LOCAL == 1) {
        $dbGeralNET = new PDO(
            "mysql:host=69.6.249.13;dbname=luca5858_geral2;charset=utf8",
            "luca5858_AutoDocVaa",
            "MZeRT6aZ5Q2F4ww"
        );
    } else {
        // HOSTGATOR
        $dbGeralNET = new PDO(
            "mysql:host=localhost;dbname=luca5858_geral2;charset=utf8",
            "luca5858_AutoDocVaa",
            "MZeRT6aZ5Q2F4ww"
        );
    }
    $dbGeralNET->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbGeralNET->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>