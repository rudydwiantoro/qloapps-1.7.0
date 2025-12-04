<?php
require_once dirname(__FILE__) . '/config/settings.inc.php';

echo "Testing PostgreSQL identifier translation:\n\n";

class TestDbPostgreSQL {
    protected function translateMySQLToPostgreSQL($sql) {
        // Remove MySQL backticks - PostgreSQL uses unquoted identifiers for better compatibility
        $sql = str_replace('`', '', $sql);
        
        // Handle double quotes around string values - convert to single quotes for SQL compliance
        // This handles cases like WHERE name = "PS_MULTISHOP_FEATURE_ACTIVE"
        $sql = preg_replace('/=\s*"([^"]+)"/', "= '$1'", $sql);
        $sql = preg_replace('/!=\s*"([^"]+)"/', "!= '$1'", $sql);
        $sql = preg_replace('/<>\s*"([^"]+)"/', "<> '$1'", $sql);
        
        // Remove any remaining double quotes around identifiers to avoid case sensitivity issues
        // But preserve single quotes around string values
        $sql = preg_replace('/"([a-zA-Z_][a-zA-Z0-9_]*)"(?!\s*=\s*\')/', '$1', $sql);
        
        return $sql;
    }
    
    public function testTranslation($sql) {
        return $this->translateMySQLToPostgreSQL($sql);
    }
}

$test = new TestDbPostgreSQL();

$test_cases = [
    'SELECT value FROM `configuration` WHERE `name` = "PS_MULTISHOP_FEATURE_ACTIVE"',
    'SELECT value FROM "configuration" WHERE "name" = "PS_MULTISHOP_FEATURE_ACTIVE"',
    'SELECT value FROM configuration WHERE name = \'PS_MULTISHOP_FEATURE_ACTIVE\'',
    'SELECT "id", "name" FROM "table" WHERE "name" = \'test\'',
    'SELECT value FROM configuration` WHERE `name` = "PS_MULTISHOP_FEATURE_ACTIVE"',
];

foreach ($test_cases as $sql) {
    echo "Original: " . $sql . "\n";
    echo "Translated: " . $test->testTranslation($sql) . "\n";
    echo "---\n";
}

// Test the actual query execution
try {
    $host = _DB_SERVER_;
    $port = _DB_PORT_;
    $dbname = _DB_NAME_;
    $user = _DB_USER_;
    $password = _DB_PASSWD_;
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->exec('SET search_path TO vhp2, public');
    
    echo "\nTesting actual queries:\n";
    
    $queries = [
        'SELECT value FROM configuration WHERE name = \'PS_MULTISHOP_FEATURE_ACTIVE\'',
        'SELECT value FROM "configuration" WHERE "name" = \'PS_MULTISHOP_FEATURE_ACTIVE\'',
    ];
    
    foreach ($queries as $query) {
        echo "Query: " . $query . "\n";
        try {
            $stmt = $pdo->query($query);
            $result = $stmt->fetch();
            echo "Result: " . ($result ? $result['value'] : 'No result') . "\n";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "Connection error: " . $e->getMessage() . "\n";
}
?>