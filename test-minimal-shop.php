<?php
// Minimal test with bypassed Shop initialization
define('_PS_VERSION_', '1.7.0');
define('__PS_BASE_URI__', '/qloapps/qloapps-1.7.0/');
define('_PS_ADMIN_DIR_', dirname(__FILE__).DIRECTORY_SEPARATOR.'adminsetup');

// Mock Shop class to avoid heavy database operations
class MinimalShop {
    public $id = 1;
    public $id_shop_group = 1;
    public $name = 'Default Shop';
    public $color = '';
    public $id_category = 2;
    public $theme_name = 'hotel-reservation-theme';
    public $active = 1;
    public $deleted = 0;
    public $domain = 'localhost';
    public $domain_ssl = 'localhost';
    public $uri = '/qloapps/qloapps-1.7.0/';
    public $virtual_uri = '';
    
    public function __construct($id = null) {
        if ($id) {
            $this->id = (int)$id;
        }
    }
    
    public static function initialize() {
        // Create minimal context without database queries
        $shop = new MinimalShop(1);
        
        // Set minimal required globals
        if (!defined('_SHOP_ID_')) {
            define('_SHOP_ID_', 1);
        }
        
        return $shop;
    }
}

// Test minimal shop initialization
try {
    echo "Testing minimal Shop initialization...\n";
    
    $shop = MinimalShop::initialize();
    
    echo "Shop ID: " . $shop->id . "\n";
    echo "Shop Name: " . $shop->name . "\n";
    echo "Domain: " . $shop->domain . "\n";
    echo "URI: " . $shop->uri . "\n";
    
    echo "\nMinimal Shop initialization successful!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>