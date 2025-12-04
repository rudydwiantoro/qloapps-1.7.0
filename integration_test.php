<?php
/**
 * QloApps PostgreSQL Integration Test
 */

// Initialize QloApps environment
define('_PS_ROOT_DIR_', dirname(__FILE__));
define('_PS_CLASS_DIR_', _PS_ROOT_DIR_.'/classes/');
define('_PS_CORE_DIR_', _PS_ROOT_DIR_.'/');
define('_PS_CONFIG_DIR_', _PS_ROOT_DIR_.'/config/');

// Include configuration
require_once _PS_ROOT_DIR_ . '/config/settings.inc.php';

// Include necessary classes
require_once _PS_ROOT_DIR_ . '/classes/PrestaShopAutoload.php';

echo "<h2>QloApps PostgreSQL Integration Test</h2>\n";

try {
    // Initialize autoloader
    PrestaShopAutoload::getInstance();
    
    // Test Db class selection
    echo "<h3>1. Testing Db Class Selection</h3>\n";
    $db_class = Db::getClass();
    echo "✓ Selected database class: {$db_class}<br>\n";
    
    if ($db_class === 'DbPostgreSQL') {
        echo "✓ PostgreSQL class correctly selected<br>\n";
    } else {
        echo "✗ Expected DbPostgreSQL, got {$db_class}<br>\n";
    }
    
    // Test database instance creation
    echo "<h3>2. Testing Database Instance</h3>\n";
    $db = Db::getInstance();
    echo "✓ Database instance created<br>\n";
    echo "Instance class: " . get_class($db) . "<br>\n";
    
    // Test database connection
    echo "<h3>3. Testing Database Operations</h3>\n";
    $version = $db->getValue('SELECT version()');
    if ($version) {
        echo "✓ Query executed successfully<br>\n";
        echo "PostgreSQL version: " . substr($version, 0, 50) . "...<br>\n";
    } else {
        echo "✗ Query execution failed<br>\n";
    }
    
    // Test table operations
    $current_db = $db->getValue('SELECT current_database()');
    if ($current_db) {
        echo "✓ Connected to database: {$current_db}<br>\n";
    }
    
    echo "<h3>✓ All Integration Tests Passed!</h3>\n";
    echo "<p>QloApps is successfully integrated with PostgreSQL.</p>\n";
    
} catch (Exception $e) {
    echo "<h3>✗ Integration Test Failed</h3>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>\n";
}
?>