<?php
/*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * Class DbPostgreSQLCore
 */
class DbPostgreSQLCore extends DbCore
{
    /** @var string */
    protected $last_error;
    
    /** @var int */
    protected $last_error_number;

    /**
     * @see DbCore::connect()
     */
    public function connect()
    {
        if (!defined('_DB_SERVER_')) {
            return false;
        }
        
        $host = _DB_SERVER_;
        $port = defined('_DB_PORT_') ? _DB_PORT_ : 5432;
        $dsn = 'pgsql:host='.$host.';port='.$port.';dbname='.$this->database;
        
        try {
            $this->link = new PDO($dsn, $this->user, $this->password, array(
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ));
        } catch (PDOException $e) {
            return false;
        }
        
        if (!$this->link) {
            return false;
        }
        
        $this->link->exec('SET client_encoding = \'UTF8\'');
        $this->link->exec('SET search_path TO vhp2, public');
        return $this->link;
    }

    /**
     * @see DbCore::disconnect()
     */
    public function disconnect()
    {
        unset($this->link);
    }

    /**
     * @see DbCore::_query()
     */
    protected function _query($sql)
    {
        if ($sql instanceof DbQuery) {
            $sql = $sql->build();
        }

        // Store original SQL for debugging
        $original_sql = $sql;
        
        // Translate MySQL syntax to PostgreSQL
        $sql = $this->translateMySQLToPostgreSQL($sql);

        $this->result = $this->link->query($sql);
        if ($this->result === false) {
            $info = $this->link->errorInfo();
            $this->last_error = $info[2];
            $this->last_error_number = $info[1];
            
            // Log translation for debugging if error occurs
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
                error_log("PostgreSQL Translation Error:");
                error_log("Original: " . $original_sql);
                error_log("Translated: " . $sql);
                error_log("Error: " . $this->last_error);
            }
        } else {
            $this->last_error = '';
            $this->last_error_number = 0;
        }

        return $this->result;
    }

    /**
     * Translate MySQL-specific syntax to PostgreSQL equivalent
     *
     * @param string $sql
     * @return string
     */
    protected function translateMySQLToPostgreSQL($sql)
    {
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
        
        // Handle MySQL CONCAT function - PostgreSQL uses || operator
        // Handle nested CONCAT calls
        while (preg_match('/CONCAT\s*\(/i', $sql)) {
            $sql = preg_replace('/CONCAT\s*\(\s*([^,()]+(?:\([^)]*\))?[^,()]*),\s*([^,()]+(?:\([^)]*\))?[^,()]*)\s*\)/i', '($1 || $2)', $sql);
        }
        
        // Handle boolean comparisons - MySQL uses 1/0, PostgreSQL uses true/false
        $sql = preg_replace('/\b(\w+\.?\w+)\s*=\s*1\b/i', '$1 = true', $sql);
        $sql = preg_replace('/\b(\w+\.?\w+)\s*=\s*0\b/i', '$1 = false', $sql);
        
        // Specifically handle common boolean fields
        $boolean_fields = ['active', 'deleted', 'main', 'enabled', 'visible', 'available'];
        foreach ($boolean_fields as $field) {
            $sql = preg_replace('/\b(\w+\.?' . $field . ')\s*=\s*1\b/i', '$1 = true', $sql);
            $sql = preg_replace('/\b(\w+\.?' . $field . ')\s*=\s*0\b/i', '$1 = false', $sql);
        }
        
        // Handle domain matching - use LIKE for partial matches when searching for 'localhost'
        $sql = preg_replace('/\(\s*(\w+\.?domain)\s*=\s*\'localhost\'\s*OR\s*(\w+\.?domain_ssl)\s*=\s*\'localhost\'\s*\)/i', 
                           '($1 LIKE \'%localhost%\' OR $2 LIKE \'%localhost%\')', $sql);
        
        // Handle empty string comparisons in domain fields
        $sql = preg_replace('/\(\s*(\w+\.?domain)\s*=\s*\'\'\s*OR\s*(\w+\.?domain_ssl)\s*=\s*\'\'\s*\)/i', 
                           '($1 = \'\' OR $2 = \'\')', $sql);
        
        // Handle IFNULL function - PostgreSQL uses COALESCE
        $sql = preg_replace('/IFNULL\s*\(\s*([^,]+),\s*([^)]+)\s*\)/i', 'COALESCE($1, $2)', $sql);
        
        // Handle MySQL's GROUP_CONCAT - PostgreSQL uses STRING_AGG
        $sql = preg_replace('/GROUP_CONCAT\s*\(\s*([^)]+)\s*\)/i', 'STRING_AGG($1::text, \',\')', $sql);
        
        // Handle MySQL's UNIX_TIMESTAMP - PostgreSQL uses EXTRACT
        $sql = preg_replace('/UNIX_TIMESTAMP\s*\(\s*([^)]+)\s*\)/i', 'EXTRACT(epoch FROM $1)', $sql);
        $sql = preg_replace('/UNIX_TIMESTAMP\s*\(\s*\)/i', 'EXTRACT(epoch FROM NOW())', $sql);
        
        // Handle MySQL's CURDATE() - PostgreSQL equivalent
        $sql = preg_replace('/CURDATE\s*\(\s*\)/i', 'CURRENT_DATE', $sql);
        
        // Handle MySQL's DATE_ADD/DATE_SUB
        $sql = preg_replace('/DATE_ADD\s*\(\s*([^,]+),\s*INTERVAL\s+(\d+)\s+(\w+)\s*\)/i', '($1 + INTERVAL \'$2 $3\')', $sql);
        $sql = preg_replace('/DATE_SUB\s*\(\s*([^,]+),\s*INTERVAL\s+(\d+)\s+(\w+)\s*\)/i', '($1 - INTERVAL \'$2 $3\')', $sql);
        
        // Handle MySQL's FIND_IN_SET - PostgreSQL equivalent
        $sql = preg_replace('/FIND_IN_SET\s*\(\s*([^,]+),\s*([^)]+)\s*\)/i', '(POSITION(\',\' || $1 || \',\' IN \',\' || $2 || \',\') > 0)', $sql);
        
        // Handle MySQL IF function - PostgreSQL uses CASE WHEN
        $sql = preg_replace('/IF\s*\(\s*([^,]+)\s+IS\s+NULL\s*,\s*([^,]+)\s*,\s*([^)]+)\s*\)/i', 'CASE WHEN $1 IS NULL THEN $2 ELSE $3 END', $sql);
        $sql = preg_replace('/IF\s*\(\s*([^,]+)\s+IS\s+NOT\s+NULL\s*,\s*([^,]+)\s*,\s*([^)]+)\s*\)/i', 'CASE WHEN $1 IS NOT NULL THEN $2 ELSE $3 END', $sql);
        $sql = preg_replace('/IF\s*\(\s*([^,]+)\s*,\s*([^,]+)\s*,\s*([^)]+)\s*\)/i', 'CASE WHEN $1 THEN $2 ELSE $3 END', $sql);
        
        // Handle MySQL bitwise AND operations - PostgreSQL compatibility
        // Handle common device enable patterns first - if it's checking & 1, it's usually a boolean check
        $sql = preg_replace('/(\w+\.?enable_device)\s*&\s*1\b/i', '$1 = true', $sql);
        
        // Handle other bitwise AND operations - PostgreSQL needs explicit casting
        // More specific pattern to avoid double casting
        $sql = preg_replace('/\b(\w+\.\w+)\s*&\s*(\d+)\b/i', '($1::integer & $2)', $sql);
        $sql = preg_replace('/\b(\w+)\s*&\s*(\d+)\b/i', '($1::integer & $2)', $sql);
        
        // Handle MySQL's LIMIT with OFFSET
        $sql = preg_replace('/LIMIT\s+(\d+)\s*,\s*(\d+)/i', 'LIMIT $2 OFFSET $1', $sql);
        
        // Handle MySQL data types
        $sql = preg_replace('/tinyint\(?\d*\)?\s*(unsigned)?/i', 'smallint', $sql);
        $sql = preg_replace('/int\(?\d*\)?\s*unsigned/i', 'integer', $sql);
        $sql = preg_replace('/bigint\(?\d*\)?\s*unsigned/i', 'bigint', $sql);
        $sql = preg_replace('/datetime/i', 'timestamp', $sql);
        
        // Handle MySQL table options
        $sql = preg_replace('/ENGINE\s*=\s*\w+/i', '', $sql);
        $sql = preg_replace('/AUTO_INCREMENT\s*=\s*\d+/i', '', $sql);
        $sql = preg_replace('/DEFAULT\s+CHARSET\s*=\s*\w+/i', '', $sql);
        $sql = preg_replace('/COLLATE\s*=?\s*\w+/i', '', $sql);
        
        // Clean up extra whitespace
        $sql = preg_replace('/\s+/', ' ', $sql);
        $sql = trim($sql);
        
        return $sql;
    }

    /**
     * @see DbCore::nextRow()
     */
    public function nextRow($result = false)
    {
        if ($result === false) {
            $result = $this->result;
        }
        if (!is_object($result)) {
            return false;
        }
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @see DbCore::getAll()
     */
    protected function getAll($result = false)
    {
        if ($result === false) {
            $result = $this->result;
        }
        if (!is_object($result)) {
            return false;
        }
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @see DbCore::_numRows()
     */
    protected function _numRows($result)
    {
        if (!is_object($result)) {
            return false;
        }
        return $result->rowCount();
    }

    /**
     * @see DbCore::Insert_ID()
     */
    public function Insert_ID()
    {
        return $this->link->lastInsertId();
    }

    /**
     * @see DbCore::Affected_Rows()
     */
    public function Affected_Rows()
    {
        if ($this->result) {
            return $this->result->rowCount();
        }
        return 0;
    }

    /**
     * @see DbCore::getMsgError()
     */
    public function getMsgError()
    {
        $info = $this->link->errorInfo();
        return ($info[0] != '00000') ? $info[2] : '';
    }

    /**
     * @see DbCore::getNumberError()
     */
    public function getNumberError()
    {
        $info = $this->link->errorInfo();
        return isset($info[1]) ? $info[1] : 0;
    }

    /**
     * @see DbCore::getVersion()
     */
    public function getVersion()
    {
        return $this->getValue('SELECT version()');
    }

    /**
     * @see DbCore::_escape()
     */
    public function _escape($str)
    {
        if (null === $str) {
            return '';
        }

        $search = ['\\', "\0", "\n", "\r", "\x1a", "'", '"'];
        $replace = ['\\\\', '\\0', '\\n', '\\r', "\Z", "''", '\"'];

        return str_replace($search, $replace, $str);
    }

    /**
     * @see DbCore::set_db()
     */
    public function set_db($db_name)
    {
        // PostgreSQL doesn't support switching databases in the same connection
        // like MySQL's USE command. You need a new connection.
        return false;
    }

    /**
     * @see DbCore::getBestEngine()
     */
    public function getBestEngine()
    {
        // PostgreSQL doesn't use storage engines like MySQL
        return '';
    }

    /**
     * Try a connection to the database
     *
     * @param string $server Server address
     * @param string $user Login for database connection
     * @param string $pwd Password for database connection
     * @param string $db Database name
     * @param bool $newDbLink
     * @param string|bool $engine
     * @param int $timeout
     * @return int Error code or 0 if connection was successful
     */
    public static function tryToConnect($server, $user, $pwd, $db, $new_db_link = true, $engine = null, $timeout = 5)
    {
        $port = defined('_DB_PORT_') ? _DB_PORT_ : 5432;
        $dsn = 'pgsql:host='.$server.';port='.$port.';dbname='.$db;
        
        try {
            $link = new PDO($dsn, $user, $pwd, array(
                PDO::ATTR_TIMEOUT => $timeout,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ));
            $link->exec('SET search_path TO vhp2, public');
        } catch (PDOException $e) {
            // Check if database doesn't exist
            if (strpos($e->getMessage(), 'database') !== false && strpos($e->getMessage(), 'does not exist') !== false) {
                return 2; // Database doesn't exist
            }
            return 1; // Connection error
        }
        
        unset($link);
        return 0; // Success
    }

    /**
     * Try a connection to the database and set names to UTF-8
     *
     * @param string $server Server address
     * @param string $user Login for database connection
     * @param string $pwd Password for database connection
     * @return bool
     */
    public static function tryUTF8($server, $user, $pwd)
    {
        $port = defined('_DB_PORT_') ? _DB_PORT_ : 5432;
        $dsn = 'pgsql:host='.$server.';port='.$port;
        
        try {
            $link = new PDO($dsn, $user, $pwd, array(
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ));
            $link->exec('SET client_encoding = \'UTF8\'');
            $link->exec('SET search_path TO vhp2, public');
            unset($link);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Try a connection to the database and check if at least one table with same prefix exists
     *
     * @param string $server Server address
     * @param string $user Login for database connection
     * @param string $pwd Password for database connection
     * @param string $db Database name
     * @param string $prefix Tables prefix
     * @return bool
     */
    public static function hasTableWithSamePrefix($server, $user, $pwd, $db, $prefix)
    {
        $port = defined('_DB_PORT_') ? _DB_PORT_ : 5432;
        $dsn = 'pgsql:host='.$server.';port='.$port.';dbname='.$db;
        
        try {
            $link = new PDO($dsn, $user, $pwd, array(
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ));
            
            $sql = 'SELECT table_name FROM information_schema.tables WHERE table_schema = \'vhp2\' AND table_name LIKE \''.$prefix.'%\'';
            $result = $link->query($sql);
            return (bool)$result->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Tries to connect to the database and create a table (checking creation privileges)
     *
     * @param string $server
     * @param string $user
     * @param string $pwd
     * @param string $db
     * @param string $prefix
     * @param string|null $engine Table engine (not used in PostgreSQL)
     * @return bool|string True, false or error
     */
    public static function checkCreatePrivilege($server, $user, $pwd, $db, $prefix, $engine = null)
    {
        $port = defined('_DB_PORT_') ? _DB_PORT_ : 5432;
        $dsn = 'pgsql:host='.$server.';port='.$port.';dbname='.$db;
        
        try {
            $link = new PDO($dsn, $user, $pwd, array(
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ));
        } catch (PDOException $e) {
            return false;
        }

        $result = $link->query('
        CREATE TABLE "'.$prefix.'test" (
            "test" smallint NOT NULL
        )');
        
        if (!$result) {
            $error = $link->errorInfo();
            return $error[2];
        }
        
        $link->query('DROP TABLE "'.$prefix.'test"');
        return true;
    }

    /**
     * Check if auto increment value and offset is 1
     *
     * @param string $server
     * @param string $user
     * @param string $pwd
     * @return bool
     */
    public static function checkAutoIncrement($server, $user, $pwd)
    {
        // PostgreSQL uses sequences instead of auto_increment
        // This check is MySQL-specific, so we'll return true for PostgreSQL
        return true;
    }

    /**
     * Try to create a database
     *
     * @param string $server
     * @param string $user
     * @param string $pwd
     * @param string $database
     * @param bool $dropit
     * @return bool
     */
    public static function createDatabase($server, $user, $pwd, $database, $dropit = false)
    {
        $port = defined('_DB_PORT_') ? _DB_PORT_ : 5432;
        $dsn = 'pgsql:host='.$server.';port='.$port;
        
        try {
            $link = new PDO($dsn, $user, $pwd, array(
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ));
            
            if ($dropit) {
                $link->query('DROP DATABASE IF EXISTS "'.$database.'"');
            }
            
            $result = $link->query('CREATE DATABASE "'.$database.'" WITH ENCODING \'UTF8\'');
            return (bool)$result;
        } catch (PDOException $e) {
            return false;
        }
    }
}