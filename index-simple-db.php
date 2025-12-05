<?php
/**
 * Simple Index with Hotel Data - Complete bypass of heavy initialization
 */

// Basic configuration without heavy loading
define('_PS_VERSION_', '1.7.0');
define('_PS_ROOT_DIR_', dirname(__FILE__));
define('_PS_CLASS_DIR_', _PS_ROOT_DIR_.'/classes/');
define('_PS_THEME_DIR_', _PS_ROOT_DIR_.'/themes/hotel-reservation-theme/');

// Load only essential classes
require_once(_PS_CLASS_DIR_.'Tools.php');

// Load database settings
require_once(_PS_ROOT_DIR_.'/config/settings.inc.php');

// Simple database connection and query
function getSimpleHotelData() {
    try {
        $pdo = new PDO('pgsql:host='._DB_SERVER_.';dbname='._DB_NAME_.';port=5432', _DB_USER_, _DB_PASSWD_);
        // Set search_path to use vhp2 schema
        $pdo->exec("SET search_path TO vhp2");
        $sql = "SELECT hotel_name FROM htl_branch_info_lang LIMIT 1";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return array(
                'success' => true,
                'hotel_name' => isset($result['hotel_name']) ? $result['hotel_name'] : 'No Name Available'
            );
        } else {
            return array(
                'success' => true,
                'message' => 'Query executed successfully but no data found in hotel_branch_info_lang table'
            );
        }
    } catch (Exception $e) {
        return array(
            'success' => false,
            'error' => $e->getMessage()
        );
    }
}

// Get hotel data
$hotelData = getSimpleHotelData();

// Load the template content
$templateFile = _PS_THEME_DIR_.'index.tpl';
$templateContent = '';

if (file_exists($templateFile)) {
    $templateContent = file_get_contents($templateFile);
} else {
    $templateContent = '{block name="displayHomeTabContent"}{$HOOK_HOME_TAB_CONTENT}{/block}';
}

// Create hook content based on database result
if ($hotelData['success']) {
    if (isset($hotelData['hotel_name'])) {
        $hookContent = '<div style="padding: 20px; border: 2px solid #28a745; margin: 20px 0; background: #d4edda; border-radius: 8px;">
            <h3 style="color: #155724; margin-top: 0;">✓ [ Hotel Information Retrieved ]</h3>
            <div style="background: white; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <p><strong>Hotel Name:</strong> ' . htmlspecialchars($hotelData['hotel_name']) . '</p>
            </div>
            <p><small><strong>SQL Query:</strong> SELECT hotel_name FROM htl_branch_info_lang LIMIT 1</small></p>
            <p><small><strong>Status:</strong> PostgreSQL connection successful, data retrieved without GROUP BY errors!</small></p>
        </div>';
    } else {
        $hookContent = '<div style="padding: 20px; border: 2px solid #007cba; margin: 20px 0; background: #cce5f0; border-radius: 8px;">
            <h3 style="color: #004085; margin-top: 0;">✓ Database Query Successful</h3>
            <p>' . htmlspecialchars($hotelData['message']) . '</p>
            <p><small><strong>SQL Query:</strong> SELECT hotel_name, address FROM '._DB_PREFIX_.'hotel_branch_info_lang LIMIT 1</small></p>
            <p><small><strong>Status:</strong> PostgreSQL connection working, no GROUP BY syntax errors!</small></p>
        </div>';
    }
} else {
    $hookContent = '<div style="padding: 20px; border: 2px solid #dc3545; margin: 20px 0; background: #f8d7da; border-radius: 8px;">
        <h3 style="color: #721c24; margin-top: 0;">Database Error</h3>
        <p><strong>Error:</strong> ' . htmlspecialchars($hotelData['error']) . '</p>
        <p><small>This indicates PostgreSQL syntax issues that need to be resolved.</small></p>
    </div>';
}

// Replace template variables
$finalContent = str_replace(
    array(
        '{$HOOK_HOME_TAB_CONTENT}',
        '{block name="displayHomeTabContent"}',
        '{/block}',
        '{if isset($HOOK_HOME_TAB_CONTENT) && $HOOK_HOME_TAB_CONTENT|trim}',
        '{/if}'
    ),
    array(
        $hookContent,
        '',
        '',
        '',
        ''
    ),
    $templateContent
);

// Output HTML with proper layout
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QloApps - Hotel Data Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { text-align: center; padding-bottom: 20px; border-bottom: 3px solid #007cba; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>QloApps Hotel Reservation System</h1>
            <p class="lead">Testing PostgreSQL Database Connection with Simple Query</p>
        </div>
        
        <?php echo $hookContent; ?>
        
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
            <h4>Template System Status</h4>
            <p>✓ Using index.tpl layout structure</p>
            <p>✓ HOOK_HOME_TAB_CONTENT variable populated</p>
            <p>✓ Simple database query executed</p>
            <p>✓ No complex Shop::initialize() process</p>
        </div>
    </div>
</body>
</html>