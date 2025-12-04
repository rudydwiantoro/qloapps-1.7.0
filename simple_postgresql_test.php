<?php
/**
 * Simple PostgreSQL Connection Test for QloApps
 */

// Include settings
require_once dirname(__FILE__) . '/config/settings.inc.php';

echo "<h2>Simple PostgreSQL Connection Test</h2>\n";

// Test 1: Check if PostgreSQL extension is loaded
echo "<h3>1. Checking PostgreSQL PDO Extension</h3>\n";
if (extension_loaded('pdo_pgsql')) {
    echo "✓ PDO PostgreSQL extension is loaded<br>\n";
} else {
    echo "✗ PDO PostgreSQL extension is NOT loaded<br>\n";
    echo "Please install php-pgsql or php-pdo-pgsql package<br>\n";
    exit(1);
}

// Test 2: Check configuration constants
echo "<h3>2. Checking Configuration Constants</h3>\n";
$required_constants = ['_DB_SERVER_', '_DB_NAME_', '_DB_USER_', '_DB_PASSWD_', '_DB_TYPE_', '_DB_PORT_'];

foreach ($required_constants as $constant) {
    if (defined($constant)) {
        $value = ($constant === '_DB_PASSWD_') ? str_repeat('*', strlen(constant($constant))) : constant($constant);
        echo "✓ {$constant} is defined: {$value}<br>\n";
    } else {
        echo "✗ {$constant} is NOT defined<br>\n";
        exit(1);
    }
}

// Test 3: Direct PostgreSQL connection test
echo "<h3>3. Testing PostgreSQL Connection</h3>\n";
try {
    $host = _DB_SERVER_;
    $port = _DB_PORT_;
    $dbname = _DB_NAME_;
    $user = _DB_USER_;
    $password = _DB_PASSWD_;
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    
    echo "Connecting to: {$dsn} (user: {$user})<br>\n";
    
    $pdo = new PDO($dsn, $user, $password, array(
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ));
    
    echo "✓ PostgreSQL connection successful!<br>\n";
    
    // Get PostgreSQL version
    $stmt = $pdo->query('SELECT version()');
    $version = $stmt->fetchColumn();
    echo "✓ PostgreSQL version: " . substr($version, 0, 50) . "...<br>\n";
    
    // Test if database exists and is accessible
    $stmt = $pdo->query("SELECT current_database()");
    $current_db = $stmt->fetchColumn();
    echo "✓ Connected to database: {$current_db}<br>\n";
    
    // Test table creation (to check permissions)
    $test_table = (_DB_PREFIX_ ?: 'qlo_') . 'connection_test';
    
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS \"{$test_table}\" (id SERIAL PRIMARY KEY, test_column VARCHAR(50))");
        echo "✓ Table creation test passed<br>\n";
        
        // Clean up test table
        $pdo->exec("DROP TABLE \"{$test_table}\"");
        echo "✓ Table cleanup completed<br>\n";
        
    } catch (PDOException $e) {
        echo "⚠ Table creation test failed: " . $e->getMessage() . "<br>\n";
        echo "This might indicate permission issues<br>\n";
    }
    
} catch (PDOException $e) {
    echo "✗ PostgreSQL connection failed<br>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
    
    // Check if it's a database not found error
    if (strpos($e->getMessage(), 'database') !== false && strpos($e->getMessage(), 'does not exist') !== false) {
        echo "<br>The database '{$dbname}' does not exist.<br>\n";
        echo "You can create it manually with:<br>\n";
        echo "<code>CREATE DATABASE \"{$dbname}\" WITH ENCODING 'UTF8';</code><br>\n";
    }
    
    exit(1);
}

echo "<h3>✓ Connection Test Passed!</h3>\n";
echo "<p>Your PostgreSQL configuration is working correctly.</p>\n";
echo "<p><strong>Configuration Summary:</strong></p>\n";
echo "<ul>\n";
echo "<li>Server: " . _DB_SERVER_ . ":" . _DB_PORT_ . "</li>\n";
echo "<li>Database: " . _DB_NAME_ . "</li>\n";
echo "<li>User: " . _DB_USER_ . "</li>\n";
echo "<li>DB Type: " . _DB_TYPE_ . "</li>\n";
echo "<li>Prefix: " . (_DB_PREFIX_ ?: '(none)') . "</li>\n";
echo "</ul>\n";
?>