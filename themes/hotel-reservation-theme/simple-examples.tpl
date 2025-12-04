<!DOCTYPE html>
<html>
<head>
    <title>Simple Template Examples</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .example { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; }
        .code { background: #e9ecef; padding: 10px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🎨 Simple TPL & Hook Examples</h1>
    
    <div class="example">
        <h2>1. Direct HTML in Template</h2>
        <p>This is just plain HTML - no PHP, no database, no hooks!</p>
        <div class="code">
            &lt;p&gt;Hello World from TPL!&lt;/p&gt;
        </div>
    </div>
    
    <div class="example">
        <h2>2. Smarty Variables (from Controller)</h2>
        <p>Shop: <strong>{$SHOP_NAME|default:'Not Set'}</strong></p>
        <p>Message: <strong>{$TEST_MESSAGE|default:'No message'}</strong></p>
        <div class="code">
            // In controller: $this->context->smarty->assign('SHOP_NAME', 'My Hotel');<br>
            // In template: {$SHOP_NAME}
        </div>
    </div>
    
    <div class="example">
        <h2>3. Simple Hook Content (No DB)</h2>
        {$SIMPLE_HOOK_CONTENT}
        <div class="code">
            // In controller: $content = '&lt;div&gt;Simple content&lt;/div&gt;';<br>
            // Assign: 'SIMPLE_HOOK_CONTENT' => $content<br>
            // In template: {$SIMPLE_HOOK_CONTENT}
        </div>
    </div>
    
    <div class="example">
        <h2>4. PHP Functions in Smarty</h2>
        <p>Current Date: <strong>{date('Y-m-d')}</strong></p>
        <p>Random Number: <strong>{rand(1,100)}</strong></p>
        <p>PHP Version: <strong>{php_version()}</strong></p>
        <div class="code">
            {date('Y-m-d')} or {php_version()}
        </div>
    </div>
    
    <div class="example">
        <h2>5. Conditional Display</h2>
        {if isset($SHOP_NAME)}
            <p style="color: green;">✅ Shop name is set: {$SHOP_NAME}</p>
        {else}
            <p style="color: red;">❌ Shop name not set</p>
        {/if}
        <div class="code">
            {if isset($VARIABLE)}<br>
            &nbsp;&nbsp;&nbsp;&nbsp;Show this<br>
            {else}<br>
            &nbsp;&nbsp;&nbsp;&nbsp;Show that<br>
            {/if}
        </div>
    </div>
    
    <div class="example">
        <h2>📋 How to Create Your Own:</h2>
        <ol>
            <li><strong>Template (.tpl):</strong> Create HTML with {$VARIABLES}</li>
            <li><strong>Controller:</strong> Assign data with $this->context->smarty->assign()</li>
            <li><strong>Module Hook:</strong> Return HTML string from hookFunction()</li>
            <li><strong>No Database:</strong> Just return hardcoded strings or simple PHP</li>
        </ol>
    </div>
    
    <footer style="margin-top: 30px; padding: 15px; background: #e9ecef; text-align: center;">
        <p>Simple Template Demo - No Database Required!</p>
    </footer>
</body>
</html>