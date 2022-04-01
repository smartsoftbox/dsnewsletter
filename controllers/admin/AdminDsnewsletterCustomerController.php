<?php
/**
 * 2020 Smart Soft.
 *
 * @author    Marcin Kubiak
 * @copyright Smart Soft
 * @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

include_once dirname(__FILE__) . '/../../classes/Dslist.Class.php';

class AdminDsnewsletterCustomerController extends ModuleAdminController
{
    private $selected_customer_lists;
    private $id_list;
    private $lists;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'customer';
        $this->className = 'Customer';
        $this->lang = false;
        $this->_use_found_rows = false;
        $this->lists = DslistClass::getAllLists();
        $this->id_list = $this->getListId($this->lists);
        $this->toolbar_title = 'Add customers to list';

        $this->fields_value = array(
            'list_id' => $this->id_list,
        );

        $titles_array = array();
        $genders = Gender::getGenders(Context::getContext()->language->id);
        foreach ($genders as $gender) {
            /** @var Gender $gender */
            $titles_array[$gender->id_gender] = $gender->name;
        }

        if($this->id_list && $this->lists) {
            $this->_select = '(l.id_dslist IS NOT NULL) AS list_active';
            $this->_join = 'LEFT JOIN ' . _DB_PREFIX_ . 'gender_lang gl ON (a.id_gender = gl.id_gender AND gl.id_lang = ' .
                (int)Context::getContext()->language->id . ')';
            $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'dslist l ON (find_in_set(a.`id_customer`, l.selected_customer)' .
                ' AND l.id_dslist = ' . $this->id_list . ')';


            $this->fields_list = array(
                'id_customer' => array(
                    'title' => $this->l('ID'),
                    'width' => 40,
                ),
                'title' => array(
                    'title' => $this->l('Social title'),
                    'filter_key' => 'a!id_gender',
                    'type' => 'select',
                    'list' => $titles_array,
                    'filter_type' => 'int',
                    'order_key' => 'gl!name'
                ),
                'firstname' => array(
                    'title' => $this->l('First name')
                ),
                'lastname' => array(
                    'title' => $this->l('Last name')
                ),
                'email' => array(
                    'title' => $this->l('Email address')
                ),
                'list_active' => array(
                    'title' => $this->l('List enabled'),
                    'active' => 'list_action',
                    'align' => 'center',
                    'type' => 'bool',
                    'orderby' => false,
                    'class' => 'fixed-width-sm',
                    'havingFilter' => true
                ),
            );

        }

        $this->context = Context::getContext();
        $this->context->controller = $this;

        parent::__construct();

        $this->bulk_actions = array(
            'addToList' => array(
                'text' => $this->l('Add to list')
            ),
            'removeFromList' => array(
                'text' => $this->l('Remove from list')
            ),
        );
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = 'No lists';

        foreach ($this->lists as $list) {
            $this->page_header_toolbar_btn['list_' . $list['id_dslist']] = array(
                'href' => $this->context->link->getAdminLink('AdminDsnewsletterCustomer') . '&id_list=' .
                    $list['id_dslist'],
                'desc' => $list['name'],
                'icon' => 'process-icon-toggle-' . ($this->id_list === $list['id_dslist'] ? 'on' : 'off')
            );

            if($this->id_list === $list['id_dslist']) {
                $this->page_header_toolbar_title = $this->l('List ') . $list['name'];
            }
        }

        $this->page_header_toolbar_btn['back_to_module'] = array(
            'href' => '?controller=AdminModules&configure=dsnewsletter&token='. Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('back'),
            'icon' => 'process-icon-back'
        );

        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        unset($this->toolbar_btn['new']);
        return parent::renderList();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS(array(
            dirname(__FILE__) . '/../../views/js/admin.js'
        ));
    }

    public function initProcess()
    {
        parent::initProcess();

        if ((isset($_GET['list_action' . $this->table]) || isset($_GET['list_action'])) && Tools::getValue($this->identifier)) {
            $this->updateListSelectedCustomer(
                new DslistClass($this->id_list),
                array(Tools::getValue('id_customer')),
                'toggle'
            );
        }

        foreach ($this->bulk_actions as $bulk_action => $params) {
            if (Tools::isSubmit('submitBulk' . $bulk_action . $this->table)) {
                if (str_contains($bulk_action, 'addToList') || str_contains($bulk_action, 'removeFromList')) {
                    $this->boxes = Tools::getValue($this->table . 'Box');
                    $this->updateListSelectedCustomer(new DslistClass($this->id_list), $this->boxes, $bulk_action);
                    break;
                } else {
                    $this->errors[] = $this->trans('You do not have permission to edit this.', [],
                        'Admin.Notifications.Error');
                }

                break;
            }
        }
    }

    /**
     * @param string $string
     * @param null $class
     * @param bool $addslashes
     * @param bool $htmlentities
     * @return string
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (_PS_VERSION_ >= '1.7') {
            return Context::getContext()->getTranslator()->trans($string);
        }
        return parent::l($string, $class, $addslashes, $htmlentities);
    }

    private function getListId($lists)
    {
        if(!$lists) { return null; }

        $cookie = Context::getContext()->cookie;
        if ($id_list = Tools::getValue('id_list')) {
            $cookie->id_dslist = $id_list;
            $cookie->write();
            return $id_list;
        }
        if ($cookie->id_dslist) {
            return $cookie->id_dslist;
        }

        return $lists[0]['id_dslist'];
    }

    /**
     * @param $bulk_action
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function updateListSelectedCustomer($list, $boxes, $action)
    {
        $selected_customers = explode(',', $list->selected_customer);
        foreach ($boxes as $id_customer) {
            if ( str_contains($action, 'addToList') && !in_array($id_customer, $selected_customers) ) {
                $selected_customers[] = $id_customer;
            }
            if ( str_contains($action, 'removeFromList') ) { // remove
                $this->removeCustomerFromList($selected_customers, $id_customer);
            }
            if ( $action === 'toggle' ) {
                if(in_array($id_customer, $selected_customers)) {
                    $this->removeCustomerFromList($selected_customers, $id_customer);
                } else {
                    $selected_customers[] = $id_customer;
                }
            }
        }
        $list->selected_customer = implode(',', $selected_customers);
        $list->save();
    }

    /**
     * @param array $selected_customers
     * @param $id_customer
     * @return array
     */
    private function removeCustomerFromList(&$selected_customers, $id_customer)
    {
        foreach ($selected_customers as $key => $selected_customer) {
            if ($selected_customer === $id_customer) {
                unset($selected_customers[$key]);
            }
        }
    }
}
