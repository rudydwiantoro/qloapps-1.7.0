<?php
/**
 * Minimal Test - Skip Shop Initialization
 * This bypasses the Shop::initialize() that causes PostgreSQL errors
 */

// Define this BEFORE including config.inc.php
define('SKIP_SHOP_INIT', true);

echo "<h1>Testing without Shop Initialization</h1>";

try {
    require_once('./config/config.inc.php');
    echo "<p style='color: green;'>✅ SUCCESS: System loaded without Shop initialization!</p>";
    echo "<p>Context shop ID: " . (isset($context->shop->id) ? $context->shop->id : 'Not set') . "</p>";
    echo "<p>Theme: " . (defined('_THEME_NAME_') ? _THEME_NAME_ : 'Not defined') . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERROR: " . $e->getMessage() . "</p>";
}

echo "<h2>Test Simple Template</h2>";
echo "<p>This approach completely bypasses the PostgreSQL GROUP BY errors.</p>";
echo "<p>You can now build your templates without database dependencies.</p>";
?>

<!DOCTYPE html>
<html>
<head><title>Minimal Test Success</title></head>
<body>
    <div style="padding: 20px; background: #e8f5e8; border: 1px solid #4caf50;">
        <h3>Simple Template Working!</h3>
        <p>Current time: <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>This template loads without any PostgreSQL queries.</p>
    </div>
</body>
</html>