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

if (!defined('_PS_VERSION_')){
    exit;
}

class Sz_payment extends PaymentModule
{

	public function __construct()
    {
		$this->name = 'sz_payment';
		$this->tab = 'payments_gateways';
		$this->version = '0.0.1';
		$this->author = 'Zekri';
		$this->controllers = ['login','payment', 'validation'];
		
		$this->bootstrap = true;
		parent::__construct();

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
		$this->displayName = $this->l('Sz Payment for Mo To');
		$this->description = $this->l('Allows customer service to login as customer without password and validate an order via MoTo (Mobile Order \ Email Order) mode Payment');

	}

	public function install()
	{
		if (!parent::install()
            || !$this->registerHook('displayAdminCustomers')
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('actionEmailSendBefore')) {
            return false;
        }
        $this->setDefaults();
		return true;
	}

    public function uninstall()
    {

            Configuration::deleteByName('SZPAYMENT_MODE');
            Configuration::deleteByName('SZPAYMENT_FRONT_TEXT');
            Configuration::deleteByName('SZPAYMENT_ORDER_STATUS_ID');

        return parent::uninstall();
    }

    public function setDefaults()
    {
        $values = array();
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == 'fr') {
                $values['SZPAYMENT_FRONT_TEXT'][$lang['id_lang']] = 'Paiement via MoTo';
            }
            else {
                $values['SZPAYMENT_FRONT_TEXT'][$lang['id_lang']] = 'Payment by MoTo';
            }
            $values['SZPAYMENT_MODE'][$lang['id_lang']] = 0;

            Configuration::updateValue('SZPAYMENT_FRONT_TEXT', $values['SZPAYMENT_FRONT_TEXT']);
            Configuration::updateValue('SZPAYMENT_MODE', $values['SZPAYMENT_MODE']);
        }
        Configuration::updateValue('SZPAYMENT_ORDER_STATUS_ID', 0);
    }

	public function hookDisplayAdminCustomers($request)
    {
        $customer = New CustomerCore ($request['id_customer']);
        $link = $this->context->link->getModuleLink($this->name, 'login', array('id_customer' => $customer->id, 'xtoken' => $this->makeToken($customer->id)));

        if (!Validate::isLoadedObject($customer)) {
            return;
        }
        return '<div class="col-md-3">
                <div class="card">
                  <h3 class="card-header text-center">
                    <i class="material-icons">lock_outline</i>
                    ' . $this->l("Connexion") . '
                  </h3>
                  <div class="card-body">
                    <p class="text-muted text-center">
                        <a href="' . $link . '" target="_blank" style="text-decoration: none;">
                            <i class="material-icons d-block">lock_outline</i>' . $this->l("Login As Customer") . '
                        </a>
                    </p>
                  </div>
                </div>
                </div>';
    }
    
    public function makeToken($id_customer) {
        return md5(_COOKIE_KEY_.$id_customer.date("Ymd"));
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitSzpayementConf')) {
            $languages = Language::getLanguages(false);
            $values = array();
            foreach ($languages as $lang) {
                $values['SZPAYMENT_MODE'][$lang['id_lang']] = Tools::getValue('SZPAYMENT_MODE');
                $values['SZPAYMENT_FRONT_TEXT'][$lang['id_lang']] = Tools::getValue('SZPAYMENT_FRONT_TEXT_'.$lang['id_lang']);
            }

            Configuration::updateValue('SZPAYMENT_MODE', $values['SZPAYMENT_MODE']);
            Configuration::updateValue('SZPAYMENT_FRONT_TEXT', $values['SZPAYMENT_FRONT_TEXT']);
            Configuration::updateValue('SZPAYMENT_ORDER_STATUS_ID', Tools::getValue('SZPAYMENT_ORDER_STATUS_ID'));

            return $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
        }
        return '';
    }

    public function getContent()
    {
        return $this->postProcess().$this->renderForm();
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable MOTO payment option ?'),
                        'desc' => $this->l('This activates the module and displays on the front a new payment method.'),
                        'name' => 'SZPAYMENT_MODE',
                        'is_bool' => true,
                        'lang' => false,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->l('Text to display on the front for the payment method'),
                        'name' => 'SZPAYMENT_FRONT_TEXT'
                    ),
                    array(
                        'type' => 'text',
                        'lang' => false,
                        'label' => $this->l('Choice of order status'),
                        'name' => 'SZPAYMENT_ORDER_STATUS_ID',
                        'desc' => $this->l('The order will be created with the selected status.'),
                        'required' => true,
                        'current_lang' => $this->context->language->id,
                        'value' => Configuration::get('SZPAYMENT_ORDER_STATUS_ID')
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions')
                )
            ),
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSzpayementConf';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'uri' => $this->getPathUri(),
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $languages = Language::getLanguages(false);

        $fields = array();
        foreach ($languages as $lang) {
            $fields['SZPAYMENT_MODE'] = Tools::getValue('SZPAYMENT_MODE', filter_var(Configuration::get('SZPAYMENT_MODE', $lang['id_lang']), FILTER_VALIDATE_BOOLEAN));
            $fields['SZPAYMENT_FRONT_TEXT'][$lang['id_lang']] = Tools::getValue('SZPAYMENT_FRONT_TEXT'.$lang['id_lang'], Configuration::get('SZPAYMENT_FRONT_TEXT', $lang['id_lang']));
            }
        return $fields;
    }

    public function hookPaymentOptions($params)
    {
        $cookie=&$this->context->cookie;

        if (!$this->active) {
            return;
        }
        if (filter_var(Configuration::get('SZPAYMENT_MODE', $this->context->language->id), FILTER_VALIDATE_BOOLEAN) == 0) {
            return;
        }
        if ($cookie->__get('employee') !== 'AsCustomer') {
            return;
        }
        $option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option->setCallToActionText(Configuration::get('SZPAYMENT_FRONT_TEXT', $this->context->language->id))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true));

        return [
            $option
        ];
    }

    public function hookActionEmailSendBefore($params)
    {
        if($params['template'] === 'order_conf') {
            return false;
        }
        return true;
    }

}
