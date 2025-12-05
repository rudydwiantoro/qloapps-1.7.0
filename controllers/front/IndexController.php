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

class IndexControllerCore extends FrontController
{
    public $php_self = 'index';

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $this->addJS(_THEME_JS_DIR_.'index.js');

        // Simple hook content with database query
        try {
            // Load our simple hotel data module
            require_once(_PS_ROOT_DIR_.'/modules/simplehoteldata/simplehoteldata.php');
            $module = new SimpleHotelData();
            $hook_content = $module->hookDisplayHomeTabContent(array());
        } catch (Exception $e) {
            $hook_content = '<div style="padding: 20px; border: 2px solid #dc3545; margin: 20px 0;">Error loading hotel data: ' . $e->getMessage() . '</div>';
        }

        $this->context->smarty->assign(array(
            'HOOK_HOME' => '',
            'HOOK_HOME_TAB' => '<li class="active"><a href="#home_tab_content" data-toggle="tab">Hotel Info</a></li>',
            'HOOK_HOME_TAB_CONTENT' => '<div id="home_tab_content" class="tab-pane active">' . $hook_content . '</div>'
        ));
        $this->setTemplate(_PS_THEME_DIR_.'index.tpl');
    }
}
