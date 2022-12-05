<?php

/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * @since 1.5.0
 */
class Sz_PaymentLoginModuleFrontController extends ModuleFrontControllerCore {

    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent() {
        parent::initContent();
        $id_customer = (int) Tools::getValue('id_customer');
        $token = $this->module->makeToken($id_customer);
        if ($id_customer && (Tools::getValue('xtoken') == $token)) {
            $customer = new Customer((int) $id_customer);
            if (Validate::isLoadedObject($customer)) {
                Context::getContext()->updateCustomer($customer);
                Context::getContext()->cookie->__set('employee', 'AsCustomer');
                Tools::redirect('index.php?controller=my-account');
            }
        }
        $this->setTemplate('module:loginascustomer/views/templates/front/failed.tpl');
    }

}
