<?php
/**
 * PostgreSQL Connection Test for QloApps
 * 
 * This script tests the PostgreSQL database connection
 * and verifies that the DbPostgreSQL class is working properly.
 */

// Define necessary constants
define('_PS_ROOT_DIR_', dirname(__FILE__));
define('_PS_CLASS_DIR_', _PS_ROOT_DIR_.'/classes/');
define('_PS_CORE_DIR_', _PS_ROOT_DIR_.'/');

// Include the necessary files
require_once _PS_ROOT_DIR_ . '/config/settings.inc.php';
require_once _PS_ROOT_DIR_ . '/classes/db/Db.php';
require_once _PS_ROOT_DIR_ . '/classes/db/DbPostgreSQL.php';

echo "<h2>QloApps PostgreSQL Connection Test</h2>\n";

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
$missing_constants = [];

foreach ($required_constants as $constant) {
    if (defined($constant)) {
        echo "✓ {$constant} is defined: " . constant($constant) . "<br>\n";
    } else {
        echo "✗ {$constant} is NOT defined<br>\n";
        $missing_constants[] = $constant;
    }
}

if (!empty($missing_constants)) {
    echo "Please define the missing constants in config/settings.inc.php<br>\n";
    exit(1);
}

// Test 3: Check database class selection
echo "<h3>3. Checking Database Class Selection</h3>\n";
try {
    $db_class = Db::getClass();
    echo "✓ Selected database class: {$db_class}<br>\n";
    
    if ($db_class !== 'DbPostgreSQL') {
        echo "✗ Expected DbPostgreSQL, but got {$db_class}<br>\n";
        echo "Make sure _DB_TYPE_ is set to 'PostgreSQL'<br>\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ Error selecting database class: " . $e->getMessage() . "<br>\n";
    exit(1);
}

// Test 4: Test PostgreSQL connection
echo "<h3>4. Testing PostgreSQL Connection</h3>\n";
try {
    $connection_result = DbPostgreSQL::tryToConnect(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);
    
    switch ($connection_result) {
        case 0:
            echo "✓ PostgreSQL connection successful<br>\n";
            break;
        case 1:
            echo "✗ PostgreSQL connection failed - Server not found or access denied<br>\n";
            echo "Please check server address, username, and password<br>\n";
            exit(1);
        case 2:
            echo "⚠ PostgreSQL server connected, but database '{$_DB_NAME_}' not found<br>\n";
            echo "Attempting to create database...<br>\n";
            
            if (DbPostgreSQL::createDatabase(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_)) {
                echo "✓ Database '{$_DB_NAME_}' created successfully<br>\n";
            } else {
                echo "✗ Failed to create database '{$_DB_NAME_}'<br>\n";
                echo "Please create the database manually or check user permissions<br>\n";
                exit(1);
            }
            break;
        default:
            echo "✗ Unknown connection error (code: {$connection_result})<br>\n";
            exit(1);
    }
} catch (Exception $e) {
    echo "✗ Exception during connection test: " . $e->getMessage() . "<br>\n";
    exit(1);
}

// Test 5: Test database instance creation
echo "<h3>5. Testing Database Instance Creation</h3>\n";
try {
    $db = Db::getInstance();
    echo "✓ Database instance created successfully<br>\n";
    echo "Instance class: " . get_class($db) . "<br>\n";
    
    // Test a simple query
    $version = $db->getValue('SELECT version()');
    if ($version) {
        echo "✓ PostgreSQL version: " . substr($version, 0, 50) . "...<br>\n";
    } else {
        echo "✗ Could not retrieve PostgreSQL version<br>\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error creating database instance: " . $e->getMessage() . "<br>\n";
    exit(1);
}

echo "<h3>✓ All tests passed!</h3>\n";
echo "<p>Your PostgreSQL configuration is working correctly with QloApps.</p>\n";
echo "<p><strong>Next steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Run the QloApps installation process if you haven't already</li>\n";
echo "<li>Make sure your PostgreSQL database has the necessary tables</li>\n";
echo "<li>Consider running SQL migration scripts if converting from MySQL</li>\n";
echo "</ul>\n";
?>