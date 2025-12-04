<?php
require_once dirname(__FILE__) . '/config/settings.inc.php';

echo "Database connection parameters:\n";
echo "Server: " . _DB_SERVER_ . "\n";
echo "Database: " . _DB_NAME_ . "\n";
echo "User: " . _DB_USER_ . "\n";
echo "Prefix: '" . _DB_PREFIX_ . "'\n";
echo "Port: " . _DB_PORT_ . "\n\n";

$host = _DB_SERVER_;
$port = _DB_PORT_;
$dbname = _DB_NAME_;
$user = _DB_USER_;
$password = _DB_PASSWD_;
$prefix = _DB_PREFIX_;

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
echo "DSN: {$dsn}\n\n";

$pdo = new PDO($dsn, $user, $password);

echo "All tables in database:\n";
$stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($tables)) {
    echo "No tables found!\n";
} else {
    foreach($tables as $table) {
        echo "- {$table}\n";
    }
}

echo "\nLooking specifically for shop_url related tables:\n";
$stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE '%shop%' ORDER BY table_name");
$shop_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($shop_tables)) {
    echo "No shop-related tables found!\n";
} else {
    foreach($shop_tables as $table) {
        echo "- {$table}\n";
    }
}

// Test direct query to the shop_url table
echo "\nTesting direct access to shop_url table:\n";
try {
    $test_query = "SELECT COUNT(*) as count FROM shop_url";
    $stmt = $pdo->query($test_query);
    $result = $stmt->fetch();
    echo "SUCCESS: shop_url table exists with {$result['count']} rows\n";
} catch (PDOException $e) {
    echo "ERROR accessing shop_url: " . $e->getMessage() . "\n";
}

// Test with prefix if it exists
if (!empty($prefix)) {
    echo "\nTesting with prefix '{$prefix}':\n";
    try {
        $test_query = "SELECT COUNT(*) as count FROM {$prefix}shop_url";
        $stmt = $pdo->query($test_query);
        $result = $stmt->fetch();
        echo "SUCCESS: {$prefix}shop_url table exists with {$result['count']} rows\n";
    } catch (PDOException $e) {
        echo "ERROR accessing {$prefix}shop_url: " . $e->getMessage() . "\n";
    }
}
?>