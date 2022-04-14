<?php
/**
 * 2017 Smart Soft.
 *
 * @author    Marcin Kubiak
 * @copyright Smart Soft
 * @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

class DslistClass extends ObjectModel
{
    public $id;
    public $name;
    public $color;
    public $id_lang;
    public $groups;
    public $target_customer;
    public $selected_customer;
    public $gender;
    public $age_compare;
    public $age_value;
    public $lang_customer;
    public $target_news;
    public $selected_news;
    public $ab_day;
    public $ab_hour;

    public static $definition = array(
        'table' => 'dslist',
        'primary' => 'id_dslist',
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'color' => array('type' => self::TYPE_STRING, 'validate' => 'isColor'),
            'id_lang' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'groups' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'target_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'selected_customer' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'gender' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'age_compare' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'age_value' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'lang_customer' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'target_news' => array('type' => self::TYPE_INT, 'validate' => 'isString'),
            'selected_news' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'ab_day' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'ab_hour' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
        )
    );


    /**
     * delete tables
     * @return bool
     */
    public static function deleteTable()
    {
        return Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'dslist');
    }

    /**
     * Create table
     * @return bool
     */
    public static function createTable()
    {
        if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dslist` (
                                          `id_dslist` int(10) unsigned NOT NULL auto_increment,
                                          `name` varchar(255) NOT NULL,           
                                          `color` varchar(255) NOT NULL,
                                          `id_lang` varchar(255) NOT NULL,          
                                          `groups` varchar(255) NOT NULL,          
                                          `target_customer` int(10) unsigned NOT NULL,
                                          `selected_customer` TEXT NOT NULL,          
                                          `gender` int(10) unsigned NOT NULL,
                                          `target_news` int(10) unsigned NOT NULL,
                                          `selected_news` TEXT NOT NULL,          
                                          `age_compare` int(10) unsigned NOT NULL,
                                          `age_value` int(10) unsigned NOT NULL,
                                          `lang_customer` varchar(255) NOT NULL,          
                                          `ab_day` int(10) unsigned NOT NULL,
                                          `ab_hour` int(10) unsigned NOT NULL,
                                          PRIMARY KEY  (`id_dslist`)
                                          ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }
        return true;
    }

    public static function getAllLists()
    {
        return Db::getInstance()->ExecuteS("SELECT * FROM " . _DB_PREFIX_ . "dslist");
    }

    public static function getListsWithTargetSelectedCustomers()
    {
        return Db::getInstance()->ExecuteS(
            "SELECT * FROM " . _DB_PREFIX_ . "dslist WHERE target_customer = " . TargetCustomer::SELECTED_CUSTOMERS
        );
    }

    /**
     * getNewsletters
     * @return array Newsletters
     */
    public static function getLists()
    {
        $lists = self::getAllLists();
        foreach ($lists as $key => $list) {
            $lists[$key]['target_customer_label'] = TargetCustomer::getLabelByValue($list['target_customer']);
            $lists[$key]['target_news_label'] = TargetNews::getLabelByValue($list['target_news']);
        }
        return $lists;
    }

    public function getFields()
    {
        parent::validateFields();
        $fields = array();
        $fields['id_dslist'] = (int)($this->id);
        $fields['name'] = (string)($this->name);
        $fields['color'] = (string)($this->color);
        $fields['id_lang'] = (string)($this->id_lang);
        $fields['groups'] = (string)($this->groups);
        $fields['target_customer'] = (int)($this->target_customer);
        $fields['selected_customer'] = (string)$this->selected_customer;
        $fields['gender'] = (int)$this->gender;
        $fields['age_compare'] = (int)$this->age_compare;
        $fields['age_value'] = (int)$this->age_value;
        $fields['lang_customer'] = (string)$this->lang_customer;
        $fields['target_news'] = (int)($this->target_news);
        $fields['selected_news'] = (string)$this->selected_news;
        $fields['ab_day'] = (int)($this->ab_day);
        $fields['ab_hour'] = (int)($this->ab_hour);

        return $fields;
    }
}
