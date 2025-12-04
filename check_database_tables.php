<?php
/**
 * Check PostgreSQL Database Tables for QloApps
 */

// Include settings
require_once dirname(__FILE__) . '/config/settings.inc.php';

echo "<h2>PostgreSQL Database Table Check</h2>\n";

try {
    $host = _DB_SERVER_;
    $port = _DB_PORT_;
    $dbname = _DB_NAME_;
    $user = _DB_USER_;
    $password = _DB_PASSWD_;
    $prefix = _DB_PREFIX_;
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $user, $password, array(
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ));
    
    echo "✓ Connected to PostgreSQL database: {$dbname}<br>\n";
    
    // Check if any QloApps tables exist
    $sql = "SELECT table_name FROM information_schema.tables 
            WHERE table_schema = 'vhp2' 
            AND table_type = 'BASE TABLE'";
    
    if (!empty($prefix)) {
        $sql .= " AND table_name LIKE '" . $prefix . "%'";
    }
    
    $sql .= " ORDER BY table_name";
    
    $stmt = $pdo->query($sql);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Current Tables in Database:</h3>\n";
    
    if (empty($tables)) {
        echo "<p style='color: red;'>⚠ <strong>No tables found!</strong></p>\n";
        echo "<p>The database exists but contains no tables. You need to:</p>\n";
        echo "<ol>\n";
        echo "<li>Run the QloApps installation process</li>\n";
        echo "<li>Or import the database structure manually</li>\n";
        echo "</ol>\n";
        
        // Check specifically for shop_url table that was mentioned in the error
        echo "<h3>Checking for Critical Tables:</h3>\n";
        $critical_tables = ['shop', 'shop_url', 'configuration', 'employee'];
        
        foreach ($critical_tables as $table) {
            $check_table = $prefix ? $prefix . $table : $table;
            $stmt = $pdo->prepare("SELECT table_name FROM information_schema.tables 
                                 WHERE table_schema = 'vhp2' 
                                 AND table_name = ?");
            $stmt->execute([$check_table]);
            
            if ($stmt->fetch()) {
                echo "✓ Table '{$check_table}' exists<br>\n";
            } else {
                echo "✗ Table '{$check_table}' missing<br>\n";
            }
        }
        
    } else {
        echo "<p style='color: green;'>Found " . count($tables) . " tables:</p>\n";
        echo "<ul>\n";
        foreach ($tables as $table) {
            echo "<li>{$table}</li>\n";
        }
        echo "</ul>\n";
    }
    
    // Show database info
    echo "<h3>Database Information:</h3>\n";
    echo "<ul>\n";
    echo "<li>Database: {$dbname}</li>\n";
    echo "<li>Prefix: " . ($prefix ?: '(none)') . "</li>\n";
    echo "<li>Total tables: " . count($tables) . "</li>\n";
    echo "</ul>\n";
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>\n";
    exit(1);
}

echo "<h3>Next Steps:</h3>\n";
if (empty($tables)) {
    echo "<p><strong>Your database is empty. You have two options:</strong></p>\n";
    echo "<ol>\n";
    echo "<li><strong>Run QloApps Installation:</strong><br>\n";
    echo "   Visit: <a href='install___/'>http://localhost/qloapps/qloapps-1.7.0/install___/</a><br>\n";
    echo "   This will create all necessary tables with PostgreSQL compatibility.</li>\n";
    echo "<li><strong>Manual Database Import:</strong><br>\n";
    echo "   Convert the MySQL structure file to PostgreSQL format and import it.</li>\n";
    echo "</ol>\n";
} else {
    echo "<p>Your database has tables. If you're still getting errors, there might be:</p>\n";
    echo "<ul>\n";
    echo "<li>Missing specific tables</li>\n";
    echo "<li>SQL syntax compatibility issues between MySQL and PostgreSQL</li>\n";
    echo "</ul>\n";
}
?>