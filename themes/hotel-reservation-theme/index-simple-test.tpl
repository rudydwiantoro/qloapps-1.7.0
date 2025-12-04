{*
* Simple Test Template - Minimal Database Queries
* Use this template to test basic connection and data fetching
*}
<!DOCTYPE html>
<html>
<head>
    <title>Simple Test - {if isset($shop_name)}{$shop_name}{else}QloApps Test{/if}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>QloApps Simple Test Page</h1>
    
    <div class="test-section">
        <h2>Basic Connection Test</h2>
        <p class="success">✓ Page loaded successfully</p>
        <p class="success">✓ Template engine working</p>
    </div>
    
    <div class="test-section">
        <h2>Shop Information</h2>
        {if isset($shop_name)}
            <p><strong>Shop Name:</strong> {$shop_name}</p>
        {else}
            <p class="error">Shop name not available</p>
        {/if}
        
        {if isset($meta_title)}
            <p><strong>Meta Title:</strong> {$meta_title}</p>
        {/if}
        
        {if isset($meta_description)}
            <p><strong>Meta Description:</strong> {$meta_description}</p>
        {/if}
    </div>
    
    <div class="test-section">
        <h2>System Information</h2>
        <p><strong>Current Date:</strong> {date('Y-m-d H:i:s')}</p>
        <p><strong>PHP Version:</strong> {php_version()}</p>
        
        {if isset($smarty.server.SERVER_NAME)}
            <p><strong>Server:</strong> {$smarty.server.SERVER_NAME}</p>
        {/if}
        
        {if isset($currency)}
            <p><strong>Currency:</strong> {$currency->name} ({$currency->iso_code})</p>
        {/if}
    </div>
    
    <div class="test-section">
        <h2>Configuration Test</h2>
        {* These are basic configuration values that should be available *}
        {if isset($PS_SHOP_EMAIL)}
            <p><strong>Shop Email:</strong> {$PS_SHOP_EMAIL}</p>
        {/if}
        
        {if isset($base_dir)}
            <p><strong>Base Directory:</strong> {$base_dir}</p>
        {/if}
    </div>
    
    <div class="test-section">
        <h2>Simple Database Test</h2>
        {* Simple static content to test if page renders *}
        <p>If you see this section, the template is working correctly.</p>
        <p>Database connection status should be tested at the controller level.</p>
    </div>
    
    <div class="test-section">
        <h2>Next Steps</h2>
        <ol>
            <li>If this page loads, your basic setup is working</li>
            <li>Check the PostgreSQL connection in your settings</li>
            <li>Gradually enable more complex features</li>
            <li>Test individual modules one by one</li>
        </ol>
    </div>
    
    <footer style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #ccc;">
        <p>Simple Test Template - QloApps PostgreSQL Migration</p>
    </footer>
</body>
</html>