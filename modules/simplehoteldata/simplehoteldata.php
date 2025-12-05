<?php
if (!defined('_PS_VERSION_'))
    exit;

class SimpleHotelData extends Module
{
    public function __construct()
    {
        $this->name = 'simplehoteldata';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'QloApps';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Simple Hotel Data');
        $this->description = $this->l('Displays simple hotel data from database');
    }

    public function install()
    {
        return parent::install() && $this->registerHook('displayHomeTabContent');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookDisplayHomeTabContent($params)
    {
        try {
            // Simple SQL query using direct PDO to avoid GROUP BY issues
            require_once(_PS_ROOT_DIR_.'/config/settings.inc.php');
            $pdo = new PDO('pgsql:host='._DB_SERVER_.';dbname='._DB_NAME_.';port=5432', _DB_USER_, _DB_PASSWD_);
            $pdo->exec("SET search_path TO vhp2");
            $sql = 'SELECT hotel_name FROM htl_branch_info_lang LIMIT 1';
            $stmt = $pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $hotel_name = isset($result['hotel_name']) ? $result['hotel_name'] : 'No Name';
                
                $html = '<div style="padding: 20px; border: 2px solid #007cba; margin: 20px 0; background: #f8f9fa;">
                    <h3>Hotel Information</h3>
                    <p><strong>Hotel ---> Name:</strong> ' . Tools::safeOutput($hotel_name) . '  []</p>
                    <p><small>Data retrieved from htl_branch_info_lang table</small></p>
                </div>';
                
                return $html;
            } else {
                return '<div style="padding: 20px; border: 2px solid #28a745; margin: 20px 0; background: #d4edda;">
                    <h3>Database Query Successful</h3>
                    <p>No data found in htl_branch_info_lang table, but query executed without errors!</p>
                    <p><small>SQL: SELECT hotel_name FROM htl_branch_info_lang LIMIT 1</small></p>
                </div>';
            }
        } catch (Exception $e) {
            return '<div style="padding: 20px; border: 2px solid #dc3545; margin: 20px 0; background: #f8d7da;">
                <h3>Database Error</h3>
                <p>Error: ' . Tools::safeOutput($e->getMessage()) . '</p>
            </div>';
        }
    }
}
?>