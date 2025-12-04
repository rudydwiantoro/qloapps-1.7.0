<?php
/**
 * Test PostgreSQL SQL Translation
 */

// Initialize QloApps core
include(dirname(__FILE__).'/config/config.inc.php');

echo "<h2>PostgreSQL SQL Translation Test</h2>\n";

try {
    // Test the problematic query from the error
    $test_sql = "SELECT s.id_shop, CONCAT(su.physical_uri, su.virtual_uri) AS uri, su.domain, su.main
					FROM shop_url su
					LEFT JOIN shop s ON (s.id_shop = su.id_shop)
					WHERE (su.domain = 'localhost' OR su.domain_ssl = 'localhost')
						AND s.active = 1
						AND s.deleted = 0
					ORDER BY LENGTH(CONCAT(su.physical_uri, su.virtual_uri)) DESC";
    
    echo "<h3>Original MySQL Query:</h3>\n";
    echo "<pre>" . htmlspecialchars($test_sql) . "</pre>\n";
    
    // Get database instance
    $db = Db::getInstance();
    echo "✓ Database instance created: " . get_class($db) . "<br>\n";
    
    // Test the query
    echo "<h3>Testing Query Execution:</h3>\n";
    $result = $db->executeS($test_sql);
    
    if ($result !== false) {
        echo "✓ Query executed successfully!<br>\n";
        echo "✓ Result count: " . count($result) . " rows<br>\n";
        
        if (!empty($result)) {
            echo "<h4>Sample Results:</h4>\n";
            echo "<table border='1' style='border-collapse: collapse;'>\n";
            echo "<tr><th>ID Shop</th><th>URI</th><th>Domain</th><th>Main</th></tr>\n";
            
            foreach (array_slice($result, 0, 3) as $row) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id_shop'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['uri'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['domain'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['main'] ?? '') . "</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
        }
    } else {
        echo "✗ Query failed<br>\n";
        echo "Error: " . $db->getMsgError() . "<br>\n";
    }
    
} catch (Exception $e) {
    echo "✗ Exception occurred: " . $e->getMessage() . "<br>\n";
}

echo "<h3>Next Steps:</h3>\n";
echo "<p>If the query executed successfully, try accessing QloApps again:</p>\n";
echo "<p><a href='index.php'>Open QloApps Homepage</a></p>\n";
?>