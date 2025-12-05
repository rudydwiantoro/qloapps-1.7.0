<?php
define('_PS_VERSION_', '1.7.0');
define('_PS_ROOT_DIR_', 'C:\xampp\htdocs\qloapps\qloapps-1.7.0');
require_once(_PS_ROOT_DIR_.'/config/settings.inc.php');

try {
    $pdo = new PDO('pgsql:host='._DB_SERVER_.';dbname='._DB_NAME_.';port=5432', _DB_USER_, _DB_PASSWD_);
    $pdo->exec("SET search_path TO vhp2");
    
    echo "=== htl_branch_info_lang table structure ===\n";
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'vhp2' AND table_name = 'htl_branch_info_lang' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  - " . $col['column_name'] . " (" . $col['data_type'] . ")\n";
    }
    
    echo "\n=== Sample data from htl_branch_info_lang ===\n";
    $stmt = $pdo->query("SELECT * FROM htl_branch_info_lang LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        foreach ($row as $key => $value) {
            echo "  " . $key . ": " . $value . "\n";
        }
    } else {
        echo "  No data found\n";
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>