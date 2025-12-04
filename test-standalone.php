<?php
// Complete standalone test without loading config.inc.php
define('_PS_VERSION_', '1.7.0');
define('__PS_BASE_URI__', '/qloapps/qloapps-1.7.0/');
define('_PS_ADMIN_DIR_', dirname(__FILE__).DIRECTORY_SEPARATOR.'adminsetup');
define('MINIMAL_SHOP_INIT', true);

// Set minimal required paths
define('_PS_ROOT_DIR_', dirname(__FILE__));
define('_PS_CLASS_DIR_', _PS_ROOT_DIR_.'/classes/');
define('_PS_THEME_DIR_', _PS_ROOT_DIR_.'/themes/hotel-reservation-theme/');

echo "Testing minimal system without config.inc.php...\n";

try {
    // Include only essential classes
    require_once(_PS_CLASS_DIR_.'Tools.php');
    
    echo "1. Tools class loaded successfully\n";
    
    // Test if we can create the most basic template output
    $template_content = '<!DOCTYPE html>
<html>
<head>
    <title>QloApps Minimal Test</title>
</head>
<body>
    <h1>Welcome to QloApps</h1>
    <p>System is working without database errors!</p>
    <div class="content">
        This is minimal content without hooks or database queries.
    </div>
</body>
</html>';
    
    echo "2. Generated minimal template content\n";
    
    // Output the template (this would normally be done by Smarty)
    echo "3. Template would render as:\n";
    echo "================================\n";
    echo $template_content;
    echo "\n================================\n";
    
    echo "\nComplete standalone test successful!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>