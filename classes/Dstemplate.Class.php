<?php
/**
 * 2017 Smart Soft.
 *
 *  @author    Marcin Kubiak
 *  @copyright Smart Soft
 *  @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

class DstemplateClass extends ObjectModel
{
    public $id;
    public $name;

    public static $default_templates = array (
        array('name' => 'New Products', 'id' => 1),
        array('name' => 'Featured Products', 'id' => 2),
        array('name' => 'Voucher', 'id' => 3),
        array('name' => 'Abandoned Cart', 'id' => 4)
    );

    public static $definition = array(
        'table' => 'dstemplate',
        'primary' => 'id_dstemplate',
        'fields' => array(
            'name' =>                 array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        )
    );
  
    public static function deleteTable()
    {
        return Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'dstemplate');
    }
    
    public function copyFromPost()
    {
        /* Classical fields */
        foreach ($_POST as $key => $value) {
            if (key_exists($key, $this) and $key != 'id_'.$this->table) {
                $this->{$key} = $value;
            }
        }
    }

    public static function addDefaultTemplates()
    {
        foreach (self::$default_templates as $default_template) {
            $template = new DstemplateClass($default_template['id']);
            $template->name = $default_template['name'];
            $template->save();
        }
        return true;
    }

    /**
    * Create table  dstemplates
    * @return bool
    */
    public static function createTable()
    {
        if (!Db::getInstance()->Execute('
          CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dstemplate` (
          `id_dstemplate` int(10) unsigned NOT NULL auto_increment,
          `name` varchar(255) NOT NULL,           
          PRIMARY KEY  (`id_dstemplate`)
          ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')) {
            return false;
        }

//        if (!Db::getInstance()->Execute('
//            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dstemplate_lang` (
//            `id_dstemplate` int(10) unsigned NOT NULL,
//            `id_lang` int(10) unsigned NOT NULL,
//            PRIMARY KEY (`id_dstemplate`, `id_lang`))
//            ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
//            return false;
//        }

        return true;
    }

    public static function getTemplates()
    {
        return Db::getInstance()->ExecuteS(
            'SELECT * FROM '._DB_PREFIX_.'dstemplate'
        );
    }
 
    
    public function getFields()
    {
        parent::validateFields();
        $fields = array();
        $fields['id_dstemplate'] = (int)($this->id);
        $fields['name'] = (string)($this->name);

        return $fields;
    }
}
