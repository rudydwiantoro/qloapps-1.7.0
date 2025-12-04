<?php
// Test minimal system with Shop bypass
define('_PS_VERSION_', '1.7.0');
define('__PS_BASE_URI__', '/qloapps/qloapps-1.7.0/');
define('_PS_ADMIN_DIR_', dirname(__FILE__).DIRECTORY_SEPARATOR.'adminsetup');
define('MINIMAL_SHOP_INIT', true); // Enable Shop bypass

// Include minimal config without heavy initialization
require_once(dirname(__FILE__).'/config/config.inc.php');

echo "Testing system with Shop bypass...\n";

try {
    // Test Shop initialization
    echo "1. Testing Shop initialization with bypass...\n";
    $shop = Shop::initialize();
    echo "   Shop ID: " . $shop->id . "\n";
    echo "   Shop Name: " . $shop->name . "\n";
    echo "   Theme: " . $shop->theme_name . "\n";
    
    // Test if Context is available
    echo "\n2. Testing Context...\n";
    if (class_exists('Context')) {
        echo "   Context class exists\n";
    }
    
    // Test basic Hook functionality
    echo "\n3. Testing Hook class...\n";
    if (class_exists('Hook')) {
        echo "   Hook class exists\n";
        // Try a simple hook execution (if possible without errors)
    }
    
    echo "\nSystem test with Shop bypass completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>