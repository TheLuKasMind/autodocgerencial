<?php

try {
    $dbGeralNET = new PDO(
        "mysql:host=localhost;dbname=luca5858_geral2;charset=utf8",
        "luca5858_AutoDocVaa",
        "MZeRT6aZ5Q2F4ww"
    );

    $dbGeralNET->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbGeralNET->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro de conexПлкo: " . $e->getMessage());
}

?>