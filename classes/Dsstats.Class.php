<?php
/**
 * 2017 Smart Soft.
 *
 * @author    Marcin Kubiak
 * @copyright Smart Soft
 * @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

class DsstatsClass extends ObjectModel
{
    public $id;
    public $id_news;
    public $open;
    public $click;
    public $date_sent;
    public $sent_number;
    public $failed;
    public $unsubscribe;

    public static $definition = array(
        'table' => 'dsstats',
        'primary' => 'id_dsstats',
        'fields' => array(
            'id_news' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'open' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'click' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'date_sent' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'sent_number' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'failed' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'unsubscribe' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
        )
    );


    public function copyFromPost()
    {
        /* Classical fields */
        foreach ($_POST as $key => $value) {
            if (key_exists($key, $this) and $key != 'id_' . $this->table) {
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
        return Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'dsstats');
    }

    /**
     * Create table
     * @return bool
     */
    public static function createTable()
    {
        if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dsstats` (
                                        `id_dsstats` int(10) unsigned not null auto_increment,
                                        `id_news` int(10) not null,
                                        `open` int(10) NOT NULL DEFAULT 0,
                                        `click` int(10) NOT NULL DEFAULT 0,
                                        `date_sent` DATETIME NOT NULL,
                                        `sent_number` int(10) NOT NULL,
                                        `failed` TEXT NOT NULL,
                                        `unsubscribe` int(10) NOT NULL,
                                        PRIMARY KEY  (`id_dsstats`)
                                        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }

        return true;
    }

    public function getFields()
    {
        parent::validateFields();
        $fields = array();
        $fields['id_dsstats'] = (int)($this->id);
        $fields['id_news'] = (int)($this->id_news);
        $fields['click'] = (int)($this->click);
        $fields['date_sent'] = (string)($this->date_sent);
        $fields['sent_number'] = (int)($this->sent_number);
        $fields['failed'] = (string)($this->failed);
        $fields['unsubscribe'] = (int)($this->unsubscribe);

        return $fields;
    }

    public static function getStatsByIdNews($id_newsletter)
    {
        return Db::getInstance()->ExecuteS(
            "SELECT * FROM " . _DB_PREFIX_ . "dsstats WHERE id_newsletter = " . $id_newsletter
        );
    }

    public static function getStatsByNewsletterID($id_newsletter = false, $last_30 = false, $order = "ASC", $failed_number = true)
    {
        if(!$id_newsletter) {
            return array();
        }

        $newsletter = Db::getInstance()->ExecuteS("SELECT s.*, d.name as news_name FROM " . _DB_PREFIX_ . "dsstats as s
            LEFT JOIN `" . _DB_PREFIX_ . "dsnewsletter` as d ON( d.id_dsnewsletter = s.id_news ) WHERE 1" .
            ($id_newsletter ? " AND s.id_news = " . $id_newsletter : "") .
            ($last_30 ? " AND  date_sent BETWEEN NOW() - INTERVAL 30 DAY AND NOW() " : "" ) .
            " ORDER BY s.id_dsstats " . $order);

        if($failed_number) {
            array_walk($newsletter, function (&$newsletter) {
               $newsletter['failed'] = count( explode(',', $newsletter['failed']) );
            });
        }

        return $newsletter;
    }

    public static function getDataForStats($id_newsletter)
    {
        $total = array(
            'total_sent_number' => 0,
            'total_open' => 0,
            'total_click' => 0,
            'total_failed' => 0,
            'total_unsubscribe' => 0,
        );

        $stats = self::getStatsByNewsletterID($id_newsletter, true, "ASC");

        foreach ($stats as $key => $stat) {
            foreach ($total as $field => $value) {
                $name = str_replace('total_', '' , $field);
                if(isset($stat[$name])) {
                    $total[$field] += (int)$stat[$name];
                }
            }
            $stats[$key]['date_sent'] = strtotime($stat['date_sent']); // for graph
        }

        $total['stats'] = json_encode($stats);

        return $total;
    }
}
