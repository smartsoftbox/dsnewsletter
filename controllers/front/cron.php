<?php
/**
 * 2019 Smart Soft.
 *
 *  @author    Marcin Kubiak
 *  @copyright Smart Soft
 *  @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

class DsnewsletterCronModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (Configuration::getGlobalValue('DSNEWSLETTER_SECURE_KEY') !== Tools::getValue('token')
            || !Module::isInstalled('dsnewsletter')) {
            die();
        }

        ob_start();
        $this->runCron();
        ob_end_clean();

        die();
    }

    public function isTimeForRun($newsletter)
    {
        $hour = ($newsletter['cron_hour'] === '*') ? date('H') : $newsletter['cron_hour'];
        $day = ($newsletter['cron_day'] === '*') ? date('d') : $newsletter['cron_day'];
        $month = ($newsletter['cron_month'] === '*') ? date('m') : $newsletter['cron_month'];
        $day_of_week = ($newsletter['cron_week'] === '*') ? date('D') :
            date('D', strtotime('Sunday +' . $newsletter['cron_week'] . ' days'));

        $day = date('Y') . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
        $execution = $day_of_week . ' ' . $day . ' ' . str_pad($hour, 2, '0', STR_PAD_LEFT);
        $now = date('D Y-m-d H');

        // return !(bool)strcmp($now, $execution);
        // check if it is time and if it wasn't already exported
        if (!(bool)strcmp($now, $execution) ) {
            if (!isset($newsletter['last_date']) OR $newsletter['last_date'] === '') {
                return true;
            }

            $php_date = strtotime($newsletter['last_date']);
            $mysql_date = date('D Y-m-d H', $php_date);
            if ($mysql_date !== $execution) {
                return true;
            }
        }

        return false;
    }

    private function runCron()
    {
        $active_newsletters = $this->getActiveNewsletters();

        if (is_array($active_newsletters) and count($active_newsletters) > 0) {
            foreach ($active_newsletters as $active_newsletter) {
                if ($active_newsletter['cron'] && $this->isTimeForRun($active_newsletter)) {
                    $this->module->sendNewsletter($active_newsletter['id_dsnewsletter']);
                    $this->updateLastExport($active_newsletter);
                }
            }
        }
        //update time
        Configuration::updateValue('DSNEWSLETTER_CRON_TIME', date('Y-m-d H:i:s'));
    }

    /**
     * @return array|false|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public function getActiveNewsletters()
    {
        return Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'dsnewsletter WHERE active = 1');
    }

    /**
     * @param $newsletter
     * @return bool
     */
    public function updateLastExport($newsletter)
    {
        return Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'dsnewsletter SET `last_date` = NOW()
            WHERE `id_dsnewsletter` = "' . (int)$newsletter['id_dsnewsletter'] . '"'
        );
    }
}
