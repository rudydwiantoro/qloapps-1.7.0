<?php
/**
 * Complete bypass configuration - loads minimal system for index.php
 */

// Define bypass mode before any includes
define('COMPLETE_BYPASS_MODE', true);
define('MINIMAL_SHOP_INIT', true);

// Basic constants
define('_PS_VERSION_', '1.7.0');
define('_PS_ROOT_DIR_', dirname(__FILE__));
define('_PS_CLASS_DIR_', _PS_ROOT_DIR_.'/classes/');
define('_PS_CONTROLLER_DIR_', _PS_ROOT_DIR_.'/controllers/');
define('_PS_THEME_DIR_', _PS_ROOT_DIR_.'/themes/hotel-reservation-theme/');
define('_THEME_NAME_', 'hotel-reservation-theme');
define('__PS_BASE_URI__', '/qloapps/qloapps-1.7.0/');

// Load essential classes only
require_once(_PS_CLASS_DIR_.'Tools.php');

// Mock essential classes to avoid database initialization
class Context {
    public static $instance;
    public $smarty;
    public $shop;
    public $theme;
    
    public static function getContext() {
        if (!self::$instance) {
            self::$instance = new Context();
            self::$instance->shop = (object)array('id' => 1, 'name' => 'Default');
            self::$instance->theme = (object)array('name' => 'hotel-reservation-theme');
        }
        return self::$instance;
    }
}

class Dispatcher {
    public static function getInstance() {
        return new Dispatcher();
    }
    
    public function dispatch() {
        // Load and execute our simple controller
        $controller = new SimpleIndexController();
        $controller->run();
    }
}

class SimpleIndexController {
    public function run() {
        // Simple database query
        $hotelData = $this->getHotelData();
        
        // Create hook content
        if ($hotelData['success']) {
            if (isset($hotelData['hotel_name'])) {
                $hookContent = '<div class="tab-pane active" id="home_tab_content">
                    <div style="padding: 20px; border: 2px solid #28a745; margin: 20px 0; background: #d4edda; border-radius: 8px;">
                        <h3 style="color: #155724;">Hotel Information from Database</h3>
                        <div style="background: white; padding: 15px; border-radius: 4px; margin: 15px 0;">
                            <p><strong>Hotel Name:</strong> ' . htmlspecialchars($hotelData['hotel_name']) . '</p>
                        </div>
                        <p><small>SQL: SELECT hotel_name FROM htl_branch_info_lang LIMIT 1</small></p>
                    </div>
                </div>';
            } else {
                $hookContent = '<div class="tab-pane active" id="home_tab_content">
                    <div style="padding: 20px; border: 2px solid #007cba; margin: 20px 0; background: #cce5f0;">
                        <h3>Database Connection Successful</h3>
                        <p>' . htmlspecialchars($hotelData['message']) . '</p>
                    </div>
                </div>';
            }
        } else {
            $hookContent = '<div class="tab-pane active" id="home_tab_content">
                <div style="padding: 20px; border: 2px solid #dc3545; margin: 20px 0; background: #f8d7da;">
                    <h3>Database Error</h3>
                    <p>' . htmlspecialchars($hotelData['error']) . '</p>
                </div>
            </div>';
        }
        
        // Load template
        $this->renderTemplate($hookContent);
    }
    
    private function getHotelData() {
        try {
            require_once(_PS_ROOT_DIR_.'/config/settings.inc.php');
            $pdo = new PDO('pgsql:host='._DB_SERVER_.';dbname='._DB_NAME_.';port=5432', _DB_USER_, _DB_PASSWD_);
            // Set search_path to use vhp2 schema
            $pdo->exec("SET search_path TO vhp2");
            $sql = "SELECT hotel_name FROM htl_branch_info_lang LIMIT 1";
            $stmt = $pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return array(
                    'success' => true,
                    'hotel_name' => $result['hotel_name'] ?? 'No Name'
                );
            } else {
                return array(
                    'success' => true,
                    'message' => 'Query successful but no data found'
                );
            }
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
    
    private function renderTemplate($hookContent) {
        $templatePath = _PS_THEME_DIR_.'index.tpl';
        
        if (file_exists($templatePath)) {
            $template = file_get_contents($templatePath);
            
            // Replace Smarty variables with our content
            $template = preg_replace('/\{[^}]*\}/', '', $template);
            $template = str_replace(
                array('{block name="displayHomeTabContent"}', '{/block}'),
                array($hookContent, ''),
                $template
            );
            
            // Wrap in proper HTML structure
            echo '<!DOCTYPE html>
<html>
<head>
    <title>QloApps Hotel System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container" style="padding: 20px;">
        <h1>QloApps - Index.tpl Layout</h1>
        ' . $hookContent . '
    </div>
</body>
</html>';
        } else {
            echo '<!DOCTYPE html>
<html>
<head><title>QloApps</title></head>
<body>
    <div style="padding: 20px;">
        <h1>Template Not Found</h1>
        ' . $hookContent . '
    </div>
</body>
</html>';
        }
    }
}
?>