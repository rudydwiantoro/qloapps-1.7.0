<?php
/**
 * Quick install script for simpletest module
 * Run this to install the module directly without admin panel
 */

require_once('./config/config.inc.php');

try {
    // Load the module
    $module = new SimpleTest();
    
    echo "Installing SimpleTest module...\n";
    
    // Install the module
    if ($module->install()) {
        echo "✅ Module installed successfully!\n";
        echo "Module ID: " . $module->id . "\n";
        echo "Module Name: " . $module->displayName . "\n";
        
        // Check if hooks are registered
        $hooks = array('displayHome', 'displayHomeTabContent', 'displayHeader');
        echo "\nHook Registration Status:\n";
        echo "========================\n";
        
        foreach ($hooks as $hookName) {
            $hookId = Hook::getIdByName($hookName);
            if ($hookId) {
                echo "✅ Hook '$hookName' exists (ID: $hookId)\n";
                
                // Check if module is registered to this hook
                $modules = Hook::getModulesFromHook($hookId);
                $registered = false;
                foreach ($modules as $mod) {
                    if ($mod['name'] === 'simpletest') {
                        $registered = true;
                        break;
                    }
                }
                echo "   Module registered: " . ($registered ? "YES" : "NO") . "\n";
            } else {
                echo "❌ Hook '$hookName' not found\n";
            }
        }
        
    } else {
        echo "❌ Module installation failed!\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>