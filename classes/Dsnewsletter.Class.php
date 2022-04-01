<?php
/**
 * 2017 Smart Soft.
 *
 *  @author    Marcin Kubiak
 *  @copyright Smart Soft
 *  @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

class DsnewsletterClass extends ObjectModel
{
    public $id;
    public $name;
    public $status;
    public $id_template;
    public $id_list;
    public $date_start;
    public $date_planned;
    public $id_lang;
    public $sender_name;
    public $sender_email;
    public $auto;
    public $frequency;

    public static $definition = array(
        'table' => 'dsnewsletter',
        'primary' => 'id_dsnewsletter',
        'fields' => array(
            'name' =>                   array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'status' =>                 array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedId'),
            'id_template' =>            array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_list' =>            array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_start' =>             array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_planned' =>           array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'id_lang' =>                array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'sender_name' =>            array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'auto' =>                   array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'frequency' =>              array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId')
        )
    );

    public static function getAll()
    {
        return  Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'dsnewsletter');
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

    /**
    * delete tables
    * @return bool
    */
    public static function deleteTable()
    {
        return Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'dsnewsletter');
    }

    /**
    * Create table
    * @return bool
    */
    public static function createTable()
    {
        if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dsnewsletter` (
                                        `id_dsnewsletter` int(10) unsigned NOT NULL auto_increment,
                                        `name` varchar(255) NOT NULL,
                                        `sender_name` varchar(255) NOT NULL,
                                        `sender_email` varchar(355) NOT NULL,
                                        `status` int(10) NOT NULL DEFAULT 0,
                                        `auto` BOOL NOT NULL DEFAULT 0,
                                        `frequency` int(10) NOT NULL,
                                        `id_template` int(10) NOT NULL,
                                        `id_list` int(10) NOT NULL,
                                        `date_start` DATETIME NOT NULL,
                                        `date_planned` DATETIME,
                                        `id_lang` varchar(255) NOT NULL, 
                                        PRIMARY KEY  (`id_dsnewsletter`)
                                        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')) {
            return false;
        }

        return true;
    }


    /**
    * getNewsletters
    * @return array Newsletters
    */
    public static function getNewsletters($news)
    {
        if (count($news)) {
            foreach ($news as $key => $new) {
                //Groups images
                if (is_array($new['name'])) {
                    $news[$key]['id_list']   = implode(',', $new['name']).' ';
                } else {
                    $news[$key]['id_list']   = $new['name'];
                }

                $template = new DstemplateClass($new['id_template']);
                $news[$key]['template_name'] = $template->name;
                $list = new DslistClass($new['id_list']);
                $news[$key]['list_name'] = $list->name;
                $news[$key]['status'] = $new['status'];
                $news[$key]['frequency'] = Frequency::$frequency[$news[$key]['frequency']]['name'];
            }
        }

        return $news;
    }

    public static function getNewslettersByListId($id)
    {
        $news = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS(
            'SELECT * FROM `'._DB_PREFIX_.'dsnewsletter` as dsn
            LEFT JOIN `'._DB_PREFIX_.'dslist_newsletter` as dsln
            ON(dsn.id_dsnewsletter = dsln.dsnewsletter_id)
            WHERE dsln.dslist_id = "'.(int)$id.'" GROUP BY dsln.dsnewsletter_id'
        );
        if (count($news)) {
            foreach ($news as $key => $value) {
                /**
                * Template
                */
                $template = new DstemplateClass($value['id_template']);
                $news[$key]['id_template'] = $template->name;
            }
        }

        return $news;
    }

    public static function addClickToNewsletter($id)
    {
        if (ValidateCore::isInt($id)) {
            Db::getInstance()->Execute("UPDATE `"._DB_PREFIX_."dsnewsletter` SET click = click + 1
                                        WHERE id_dsnewsletter = ".(int)$id);
        }

        return true;
    }

    public function getFields()
    {
        parent::validateFields();
        $fields = array();
        $fields['id_dsnewsletter'] = (int)($this->id);
        $fields['name'] = (string)($this->name);
        $fields['status'] = (int)($this->status);
        $fields['auto'] = (bool)($this->auto);
        $fields['id_template'] = (int)($this->id_template);
        $fields['id_list'] = (int)($this->id_list);
        $fields['date_start'] = (string)($this->date_start);
        $fields['date_planned'] = (string)($this->date_planned);
        $fields['sender_name'] = (string)($this->sender_name);
        $fields['sender_email'] = (string)($this->sender_email);
        $fields['id_lang'] = (string)($this->id_lang);
        $fields['frequency'] = (int)($this->frequency);

        return $fields;
    }
}
