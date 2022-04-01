<?php
/**
 * 2019 Smart Soft.
 *
 *  @author    Marcin Kubiak
 *  @copyright Smart Soft
 *  @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

define('NEWSLETTER', 'newsletter');
define('TEMPLATE', 'template');
define('FILETYPE_TXT', 'txt');
define('FILETYPE_HTML', 'html');
define('TAG_CLICK', '{Click_Wrapper}');
define('TAG_TRACK', '{Track}');
define('PRODUCT_IMAGE', 'tag.jpg');

class Dsnewsletter extends Module
{
    private $html;

    public function __construct()
    {
        $this->name = 'dsnewsletter';
        $this->tab = 'administration';
        $this->version = '1.9.0';
        $this->author = 'DevSoft';
        $this->module_key = '65056cdb693e390a5cd199335c27dcdb';
        $this->bootstrap = true;


        $this->controllers = array('newsletter');
        parent::__construct();

        $this->displayName = $this->l('Professional Newsletter');
        $this->description = $this->l('Professional Newsletter System. ');

        $this->ps_versions_compliancy = array('min' => '1.6.0.0', 'max' => _PS_VERSION_);

        $path = dirname(__FILE__);
        if (strpos(__FILE__, 'Module.php') !== false) {
            $path .= '/../modules/'.$this->name;
        }

        include_once($path.'/classes/Dsnewsletter.Class.php');
        include_once($path.'/classes/Dstemplate.Class.php');
        include_once($path.'/classes/Dslist.Class.php');
        include_once($path.'/classes/Dsstats.Class.php');
        include_once($path.'/classes/Data/TargetCustomer.php');
        include_once($path.'/classes/Data/TargetNews.php');
        include_once($path.'/classes/Data/Tags.php');
        include_once($path.'/classes/Data/Frequency.php');
    }

    public function install()
    {
        if (!DsnewsletterClass::createTable()) {
            $this->_errors[] = 'Error creating newsletter table.';
            return false;
        }
        if (!DsstatsClass::createTable()) {
            $this->_errors[] = 'Error creating stats table.';
            return false;
        }
        if (!DstemplateClass::createTable()) {
            $this->_errors[] = ('Error creating template table.');
            return false;
        }
        if (!DstemplateClass::addDefaultTemplates()) {
            $this->_errors[] = ('Error add default templates.');
            return false;
        }
        if (!DslistClass::createTable()) {
            $this->_errors[] = ('Error creating list table.');
            return false;
        }

        $langs = Language::getLanguages(false);
        foreach ($langs as $lang) {
            $this->makePath(dirname(__FILE__) . '/views/mails/' . $lang['iso_code']);
            $this->recurseCopy(
                dirname(__FILE__) . '/mails/xx/',
                dirname(__FILE__) . '/mails/' . $lang['iso_code'] . '/'
            );
        }

        $this->makePath(dirname(__FILE__) . '/views/img/mails/template');

        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $key_object = \Defuse\Crypto\Key::createNewRandomKey();
            $key = $key_object->saveToAsciiSafeString();
        } else {
            $key = uniqid();
        }

        if (!Configuration::updateValue('DSNEWSLETTER_SECURE_KEY', Tools::strtoupper(Tools::passwdGen(16))) ||
        !Configuration::updateValue('DSNEWSLETTER_CRON_TIME', date('Y-m-d H:i:s')) ||   // Cron time
        !Configuration::updateValue('DSNEWSLETTER_TEST_EMAIL', Configuration::get('PS_SHOP_EMAIL')) ||
        !Configuration::updateValue('DSNEWSLETTER_EMAIL_NUMBER', '50') ||
        !Configuration::updateValue('DSNEWSLETTER_REPORT_EMAIL', Configuration::get('PS_SHOP_EMAIL')) ||
        !Configuration::updateValue('DSNEWSLETTER_SENT_REPORT', '') ||
        !Configuration::updateValue('DSNEWSLETTER_MAIL_ENCODE', 1) ||
        !Configuration::updateGlobalValue('DSNEWSLETTER_PROGRESS', 1) ||
        !Configuration::updateValue('DSNEWSLETTER_ENCRYPT_ID', $key)) {
            $this->_errors[] = ('Error update value.');
            return false;
        }

        if (!parent::install()) {
            return false;
        }

        return true;
    }

    public function makePath($path)
    {
        if (is_dir($path)) {
            return true;
        }
        $prev_path = Tools::substr($path, 0, strrpos($path, '/', -2) + 1);
        $return = $this->makePath($prev_path);

        return $return && is_writable($prev_path) && mkdir($path);
    }

    public function uninstall()
    {
        if (!DsnewsletterClass::deleteTable()) {
            $this->_erros[] = ('Error delete newsletter table.');
            return false;
        }

        if (!DstemplateClass::deleteTable()) {
            $this->_errors[] = ('Error delete template table.');
            return false;
        }

        if (!DslistClass::deleteTable()) {
            $this->_errors[] = ('Error delete list table.');
            return false;
        }

        if (!DsstatsClass::deleteTable()) {
            $this->_errors[] = ('Error delete stats table.');
            return false;
        }

        //Delete attachments when unistall
        if (is_dir(dirname(__FILE__) . '/upload/')) {
            $this->deleteDir(dirname(__FILE__) . '/upload', false, true);
        }

        if (is_dir(dirname(__FILE__) . '/views/img/mails')) {
            $this->deleteDir(dirname(__FILE__) . '/views/img/mails', false, true);
        }

        $langs = Language::getLanguages(false);
        foreach ($langs as $lang) {
            $this->deleteDir(dirname(__FILE__) . '/mails/' . $lang['iso_code'] . '/', false);
        }

        if (!Configuration::deleteByName('DSNEWSLETTER_SECURE_KEY') ||   // Set security key for ajax call
           !Configuration::deleteByName('DSNEWSLETTER_CRON_TIME') ||   // Cron time
           !Configuration::deleteByName('DSNEWSLETTER_TEST_EMAIL') ||   // test email
           !Configuration::deleteByName('DSNEWSLETTER_EMAIL_NUMBER') ||
           !Configuration::deleteByName('DSNEWSLETTER_REPORT_EMAIL') ||
           !Configuration::deleteByName('DSNEWSLETTER_SENT_REPORT') ||
           !Configuration::deleteByName('DSNEWSLETTER_MAIL_ENCODE') ||
           !Configuration::deleteByName('DSNEWSLETTER_PROGRESS') ||
           !Configuration::deleteByName('DSNEWSLETTER_ENCRYPT_ID')) {
            $this->_errors[] = ('Error update value.');
            return false;
        }

        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
    * Save content to file
    * @param string $content HTML content from TinyMCE editor
    * @param string $path File you want to write into
    * @return boolean TRUE on success
    */
    public function saveHtmlToFile($content, $path)
    {
        if ($content) {
            //add tracking image
            $content = $content.TAG_TRACK;

            $content = Tools::htmlentitiesUTF8($content);
            $content = htmlspecialchars_decode($content);
            // replace correct end of line
            $content = str_replace("\r\n", PHP_EOL, $content);

            // Magic Quotes shall... not.. PASS!
            if (_PS_MAGIC_QUOTES_GPC_) {
                $content = Tools::stripslashes($content);
            }
        }

        return (bool) $this->filePutContent($path, $content);
    }

    /**
     * encrypt string
     * @param string $text
     * @return string
     */
    public static function encryptText($text)
    {
        $key = Configuration::get('DSNEWSLETTER_ENCRYPT_ID');

        if (_PS_VERSION_ >= 1.7) {
            $key_object = \Defuse\Crypto\Key::loadFromAsciiSafeString($key);
            return \Defuse\Crypto\Crypto::encrypt($text, $key_object);
        }

        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        return mcrypt_encrypt(MCRYPT_BLOWFISH, $key, utf8_encode($text), MCRYPT_MODE_ECB, $iv);
    }

    /**
     * decrypt string
     * @param string $text
     * @return string
     */
    public static function decryptText($text)
    {
        $key = Configuration::get('DSNEWSLETTER_ENCRYPT_ID');

        if (_PS_VERSION_  >= 1.7) {
            $key_object = \Defuse\Crypto\Key::loadFromAsciiSafeString($key);
            return \Defuse\Crypto\Crypto::decrypt($text, $key_object);
        }

        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        return mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $text, MCRYPT_MODE_ECB, $iv);
    }

    /**
    * Copy recurse folder
    * @param string $src
    * @param string $dst
    */
    public function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * deleteDir remove directory and all files inside directory
     *
     * @param  string $dirPath directory
     * @param  boolean $deleteparent delete directory too
     * @param bool $leaveIndex
     *
     * @return null
     */
    private function deleteDir($dirPath, $deleteparent = true, $leaveIndex = false)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (Tools::substr($dirPath, Tools::strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                if ($leaveIndex == true and basename($file) == "index.php") {
                } else {
                    unlink($file);
                }
            }
        }
        if ($deleteparent == true || $leaveIndex == false) {
            rmdir($dirPath);
        }
    }

    /**
     * is dir exists
     * @param string $dirname
     * @return bool
     */
    public function isDirExists($dirname)
    {
        return is_dir($dirname);
    }

    /**
     * get Attachments
     * @param int $id_newsletter
     * @return null
     */
    public function getAttachments($id_newsletter)
    {
        $attachments = null;
        //get attachment's names
        $dirName = _PS_ROOT_DIR_.'/modules/dsnewsletter/upload/attachments/'.$id_newsletter;
        if ($this->isDirExists($dirName)) {
            $images = scandir($dirName);
            $ignore = array(".", "..");

            foreach ($images as $key => $curImg) {
                if (!in_array($curImg, $ignore)) {
                    $attachments[$key]['content'] = $this->fileGetContent($dirName.'/'.$curImg);
                    $attachments[$key]['name'] = pathinfo($dirName.'/'.$curImg, PATHINFO_FILENAME);
                    $attachments[$key]['mime'] = pathinfo($dirName.'/'.$curImg, PATHINFO_EXTENSION);
                }
            }
        }
        return $attachments;
    }

    /**
     * save newsletter data
     * @param object $newsletter
     * @param $info
     * @internal param int $total
     */
    public function saveNewsletterData($newsletter, $info)
    {
        $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $newsletter->date_planned);
        if (is_object($newsletter) and $newsletter->frequency != "none" and $newsletter->auto and
            $newsletter->frequency != "one") {
            switch ($newsletter->frequency) {
                case 'hour':
                    $newsletter->date_planned = date('Y-m-d H:i:s', strtotime('+1 hour', $dateTime->getTimestamp()));
                    break;
                case 'day':
                    $newsletter->date_planned = date('Y-m-d H:i:s', strtotime('+1 day', $dateTime->getTimestamp()));
                    break;
                case 'week':
                    $newsletter->date_planned = date('Y-m-d H:i:s', strtotime('+1 week', $dateTime->getTimestamp()));
                    break;
                case 'two_weeks':
                    $newsletter->date_planned = date('Y-m-d H:i:s', strtotime('+2 weeks', $dateTime->getTimestamp()));
                    break;
                case 'month':
                    $newsletter->date_planned = date('Y-m-d H:i:s', strtotime('+1 month', $dateTime->getTimestamp()));
                    break;
                case 'year':
                    $newsletter->date_planned = date('Y-m-d H:i:s', strtotime('+1 year', $dateTime->getTimestamp()));
                    break;
            }
        }

        $newsletter->status += 1;
        $newsletter->date_sent = date('Y-m-d H:i:s');
        $newsletter->failed += count($info['errors']);
        $newsletter->sent_number += $info['total'];
        $newsletter->save();
    }

    /**
    * cron task
    */
    public function cronTask()
    {
        if ($this->active) {
            //All users from newsletter and queue
            $users = null;

            //get newsletters
            $subscribers = DsnewsletterClass::getSubscribers(true);
            //get queue
            $queues = Db::getInstance()->executeS(
                "SELECT id_lang, email, id_newsletter, id_customer, id_subscriber
                FROM `"._DB_PREFIX_."dsqueue`
                WHERE status = 0 AND publish = 1"
            );
            if ($queues) {
                foreach ($queues as $queue) {
                    $users[$queue['id_newsletter']][] = array(
                        'email' => $queue['email'],
                        'id_lang' => $queue['id_lang'],
                        'tags' => $this->getTags(
                            $queue['id_newsletter'],
                            true,
                            $queue['id_customer'],
                            $queue['id_subscriber'],
                            null,
                            $queue['id_lang']
                        )
                    );
                }
            }

            //get subscribers
            if ($subscribers) {
                foreach ($subscribers as $sub) {
                    $users[$sub['id_newsletter']][] = array(
                        'email' => $sub['email'],
                        'id_lang' => $sub['id_lang'],
                        'tags' => $this->getTags(
                            $sub['id_newsletter'],
                            true,
                            $sub['id_customer'],
                            $sub['id_subscriber'],
                            $sub['dslist_id'],
                            $sub['id_lang']
                        )
                    );
                }
            }

            if ($users) {
                $info = $this->sentCronEmails($users);
                //UPDATE QUEUE STATUS AND DATE SENT
                $date = date('Y-m-d H:i:s');
                if (count($queues)) {
                    Db::getInstance()->Execute(
                        "UPDATE "._DB_PREFIX_."dsqueue SET status = 1, date_sent = '".pSQL($date) ."'"
                    );
                }

                //CRON REPORT
                if (Configuration::get('DSNEWSLETTER_SENT_REPORT') and $users) {
                    $this->sentReport($info);
                }
            }
        }

        //update time
        Configuration::updateValue('DSNEWSLETTER_CRON_TIME', date('Y-m-d H:i:s'));
    }

    /**
    * get newsletter object
    * @param int $id
    * @return DsnewsletterClass
    */
    public function getNewsletterObject($id)
    {
        $newsletter = new DsnewsletterClass($id);

        if (is_object($newsletter)) {
            return $newsletter;
        } else {
            return null;
        }
    }

    /**
    * get template object
    * @param int $id
    * @return DstemplateClass
    */
    public function getTemplateObject($id)
    {
        $template = new DstemplateClass($id);

        if (is_object($template)) {
            return $template;
        } else {
            return null;
        }
    }

    public function sentCronEmails($users)
    {
        $result = null;

        foreach ($users as $key => $new) {
            $info = array(
                'total' => null,
                'correct' => null,
                'errors' => array(),
                'id' => null,
                'name' => null
            );

            $attachments = null;
            //create chunks
            if ($number = Configuration::get('DSNEWSLETTER_EMAIL_NUMBER') and count($users)) {
                $chunks = array_chunk($new, $number);
            }

            $newsletter = $this->getNewsletterObject($key);
            $langs = explode(',', $newsletter->id_lang);

            //attachments
            if (isset($newsletter)) {
                $attachments = Dsnewsletter::getAttachments($newsletter->id);
            }

            foreach ($chunks as $key => $chunk) {
                $info['total'] += count($chunk);
                foreach ($chunk as $key => $user) {
                    set_time_limit(30);

                    if (isset($user['id_lang']) and in_array($user['id_lang'], $langs)) {
                        $id_lang = (int)$user['id_lang'];
                    } else {
                        $id_lang = Configuration::get('PS_LANG_DEFAULT');
                    }

                    if (Mail::Send(
                        $id_lang,
                        'newsletter-'.$newsletter->id,
                        Mail::l($newsletter->name, $id_lang),
                        $user['tags'],
                        $user['email'],
                        null,
                        $newsletter->sender_email,
                        $newsletter->sender_name,
                        $attachments,
                        null,
                        dirname(__FILE__).'/mails/'
                    )
                    ) {
                        $info['correct'] += 1;
                    } else {
                        $info['errors'][] =  $user['email'];
                    }
                }
            }

            //save newsletter data like date sent, status etc...
            $this->saveNewsletterData($newsletter, $info);

            $info['id'] = $newsletter->id;
            $info['name'] = $newsletter->name;

            $result[] = $info;
        }

        return $result;
    }

    public function sentReport($result)
    {
        $this->smarty->assign(array(
            'result' => $result
        ));

        $template_vars = array();
        $template_vars[ '{$report}' ] = $this->display(__FILE__, '/views/templates/admin/email_tr_report.tpl');

        if (!Mail::Send(
            Configuration::get('PS_LANG_DEFAULT'),
            'report',
            Mail::l('Mailing report', Configuration::get('PS_LANG_DEFAULT')),
            $template_vars,
            Configuration::get('DSNEWSLETTER_REPORT_EMAIL'),
            null,
            Configuration::get('PS_SHOP_EMAIL'),
            $this->l('Professional Newsletter'),
            null,
            null,
            dirname(__FILE__).'/mails/'
        )
        ) {
            Tools::dieOrLog(Tools::displayError('Error: Problem sending report.'), false);
        } else {
            return true;
        }
    }

    protected function postValidation()
    {
        $this->_errors = array();

        if (Tools::isSubmit('submitAddDslist') or Tools::isSubmit('submitEditDslist')) {
            if (!Validate::isName(Tools::getValue('name')) or Tools::getValue('name') == '') {
                $this->_errors[] = $this->l('Invalid or empty name.');
            }
        } elseif (Tools::isSubmit('submitAddDstemplate') or Tools::isSubmit('submitEditDstemplate')) {
            if (!Validate::isName(Tools::getValue('name')) or Tools::getValue('name') == '') {
                $this->_errors[] = $this->l('Invalid or empty name.');
            }
            if (!Validate::isColor(Tools::getValue('header_background'))) {
                $this->_errors[] = $this->l('Incorrect header background color.');
            }
            if (!Validate::isColor(Tools::getValue('content_background'))) {
                $this->_errors[] = $this->l('Incorrect content background color.');
            }
            if (!Validate::isColor(Tools::getValue('footer_background'))) {
                $this->_errors[] = $this->l('Incorrect footer background color.');
            }
            if (!Validate::isCleanHtml(Tools::getValue('style'))) {
                $this->_errors[] = $this->l('Incorrect styles field format.');
            }
        } elseif (Tools::isSubmit('submitAddDsnewsletter')) {
            if (!Validate::isString(Tools::getValue('name')) or Tools::getValue('name') == '') {
                $this->_errors[] = $this->l('Invalid or empty name.');
            }
            if (!Validate::isName(Tools::getValue('sender_name')) or Tools::getValue('sender_name') == '') {
                $this->_errors[] = $this->l('Invalid or empty sender name.');
            }
            if (!Validate::isEmail(Tools::getValue('sender_email')) or Tools::getValue('sender_email') == '') {
                $this->_errors[] = $this->l('Invalid or empty sender email.');
            }
//            if (!Validate::isName(Tools::getValue('title_'.Configuration::get('PS_LANG_DEFAULT'))) or
//                Tools::getValue('title_'.Configuration::get('PS_LANG_DEFAULT')) == '') {
//                $this->_errors[] = $this->l('Invalid or empty name for default langguge.');
//            }
//            if (!Validate::isCleanHtml(Tools::getValue('template_'.Configuration::get('PS_LANG_DEFAULT'))) or
//                Tools::getValue('template_'.Configuration::get('PS_LANG_DEFAULT')) == '') {
//                $this->_errors[] = $this->l('Invalid or empty template for default langguge.');
//            }
            if (Tools::getValue('id_lang[]')) {
                $this->_errors[] = $this->l('Invalid or empty language.');
            }
        } elseif (Tools::isSubmit('updateSettings')) {
            if (!Validate::isEmail(Tools::getValue('test_email')) or Tools::getValue('test_email') == '') {
                $this->_errors[] = $this->l('Invalid or empty test email.');
            }
            if (!Validate::isInt(Tools::getValue('email_number')) or Tools::getValue('email_number') == '') {
                $this->_errors[] = $this->l('Invalid or empty email number.');
            }
            if (!Validate::isEmail(Tools::getValue('report_email')) or Tools::getValue('report_email') == '') {
                $this->_errors[] = $this->l('Invalid or empty report email.');
            }
        }

        //display errors
        if (count($this->_errors)) {
            foreach ($this->_errors as $err) {
                $this->html .= $this->displayError($err);
            }

            return false;
        }
        return true;
    }

    /**
     * method call when ajax request is made with the details row action
     * @see AdminController::postProcess()
     */
    public function ajaxProcess()
    {
        $action = Tools::getValue('action');
        switch ($action) {
            case 'tags':
                echo $this->displayTagsForm();
                break;
            case 'defaultdsnewsletter':
                $this->defaultdsnewsletter();
                break;
            case 'details':
                $this->details();
                break;
            case 'uploadImage':
                $this->uploadImage();
                break;
            case 'removeImage':
                $this->removeImage();
                break;
            case 'getCustomer':
                $this->getCustomer();
                break;
            case 'getNews':
                $this->getAutcompleteNews();
                break;
            case 'getProduct':
                $this->getProduct();
                break;
            case 'getProgress':
                $this->getProgress();
                break;
            default:
        }
    }

    public function getProgress()
    {
        $progress_current = Configuration::getGlobalValue('DSNEWSLETTER_PROGRESS');
        if(!$progress_current) {
            echo '-1';
        }
        $progress = explode(',', $progress_current);
        echo (int)(($progress[0] * 100) / $progress[1]);
        exit();
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    private function postProcess()
    {
        if ($this->postValidation() == false) { return false; }
        $this->_errors = array();

        if (Tools::isSubmit('ajax') || Tools::isSubmit('defaultdsnewsletter')) {
            $this->ajaxProcess();
            exit();
        } elseif (Tools::isSubmit('duplicatedstemplate')) {
            $this->duplicateTemplate(Tools::getValue('id_dstemplate'));
        } elseif (Tools::isSubmit('deletedstemplate')) {
            $this->deleteTemplate(Tools::getValue('id_dstemplate'));
        } elseif (Tools::isSubmit('submitAddTemplateAndStay') or Tools::isSubmit('submitAddDstemplate')) {
            $this->saveTemplateAndTxt();
        } elseif ($file = Tools::getValue('deleteimagetemplate')) {
            $this->deleteImageTemplate($file, Tools::getValue('id_dstemplate'), Tools::getValue('iso'));
        } elseif (Tools::isSubmit('submitAddDsnewsletter') or Tools::isSubmit('submitEditDsnewsletter')
            or Tools::isSubmit('submitUpdateDsnewsletter')) {
            $this->submitAddNewsletter(Tools::getValue('id_dsnewsletter'));
        } elseif ($file = Tools::getValue('deleteattachment')) {
            $this->deleteAttachment($file, Tools::getValue('id_dsnewsletter'));
        } elseif (Tools::isSubmit('deletedsnewsletter')) {
            $this->deleteNewsletter(Tools::getValue('id_dsnewsletter'));
        } elseif (Tools::isSubmit('updateSettings')) {
            $this->updateSettings();
        } elseif (Tools::isSubmit('submitAddDslist')) {
            $this->submitAddList();
        } elseif (Tools::isSubmit('deletedslist')) {
            $this->deleteList(Tools::getValue('id_dslist'));
        } elseif (Tools::isSubmit('sent_test_template')) {
            $template = new DstemplateClass( Tools::getValue('id_dstemplate') );
            $this->sentTestEmail(TEMPLATE, $template, Tools::getValue('template_id_lang'));
        } elseif (Tools::isSubmit('sent_test_newsletter')) {
            $newsletter = new DsnewsletterClass( Tools::getValue('id_dsnewsletter') );
            $this->sentTestEmail(
                NEWSLETTER,
                $newsletter,
                Configuration::get('PS_LANG_DEFAULT'),
                true
            );
            //confirmation messages
        } elseif (Tools::isSubmit('addDslistConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('List saved successfully.'));
        } elseif (Tools::isSubmit('addDstemplateConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Template saved successfully.'));
        } elseif (Tools::isSubmit('deleteDstemplateConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Template deleted successfully.'));
        } elseif (Tools::isSubmit('duplicateDstemplateConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Template duplicate successfully.'));
        } elseif (Tools::isSubmit('deleteThumbConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Template thumbnail deleted successfully.'));
        } elseif (Tools::isSubmit('statusDstemplateConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Template publish status has changed successfully.'));
        } elseif (Tools::isSubmit('deleteImageConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Image was deleted successfully.'));
        } elseif (Tools::isSubmit('addDsnewsletterConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Newsletter saved successfully.'));
        } elseif (Tools::isSubmit('deleteDsnewsletterConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Newsletter was deleted successfully.'));
        } elseif (Tools::isSubmit('deleteAttachmentConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Attachment was deleted successfully.'));
        } elseif (Tools::isSubmit('deleteQueueConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Queue item/s deleted successfully.'));
        } elseif (Tools::isSubmit('updateSettingsConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Settings was updated successfully.'));
        } elseif (Tools::isSubmit('newsletterAddToListConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Subscriber/s added to list successfully.'));
        } elseif (Tools::isSubmit('customerAddToListConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Customer/s added to list successfully.'));
        } elseif (Tools::isSubmit('customerRemoveFromListConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Customer/s remove from list successfully.'));
        } elseif (Tools::isSubmit('newsletterRemoveFromListConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Subscriber/s remove from list successfully.'));
        } elseif (Tools::isSubmit('sentTestTemplateConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Test email was send successfully.'));
        } elseif (Tools::isSubmit('sentTestConfirmation')) {
            $this->html .= $this->displayConfirmation($this->l('Test email was send successfully.'));
        }

        if (count($this->_errors)) {
            foreach ($this->_errors as $err) {
                $this->html .= $this->displayError($err);
            }
        }
    }

    public function addTemplateImage($id_template)
    {
        $name =  $_FILES['images']['name'];
        $iso = Language::getIsoById((int)Context::getContext()->cookie->employee_form_lang);

        if (isset($_FILES['images']) && isset($_FILES['images']['tmp_name'])
            && !empty($_FILES['images']['tmp_name'])) {
            if (file_exists(dirname(__FILE__)."/views/img/mails/template/$id_template/$iso/images/$name")) {
                unlink(dirname(__FILE__)."/views/img/mails/template/$id_template/$iso/images/$name");
            }
            if (!move_uploaded_file(
                $_FILES['images']['tmp_name'],
                dirname(__FILE__)."/views/img/mails/template/$id_template/$iso/images/$name"
            )) {
                return false;
            }
            $languages = Language::getLanguages(false);
            if (Tools::getValue('alllang')) {
                foreach ($languages as $language) {
                    copy(
                        dirname(__FILE__)."/views/img/mails/template/$id_template/$iso/images/$name",
                        dirname(__FILE__)."/views/img/mails/template/$id_template/".
                        $language['iso_code']."/images/$name"
                    );
                }
            }
        }
    }

    public function displayPanel($form)
    {
        $this->smarty->assign(array(
            'currentIndex' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.
                Tools::getAdminTokenLite('AdminModules'),
            'form' => $form,
            'AdminCustomerController' => $this->context->link->getAdminLink('AdminDsnewsletterCustomer')
        ));

        $html = $this->display(__FILE__, '/views/templates/admin/links.tpl');
        $html .= $form;
        $html .=  $this->display(__FILE__, '/views/templates/admin/links_bottom.tpl');

        return $html;
    }

    public function getContent()
    {
        if ((int)Tools::getValue('ajax')) {
            $this->ajaxProcess();
            exit();
        }

        //toolbar bottom
        $this->html .= '<script type="text/javascript">
                             var urlJson = "' . $this->context->link->getAdminLink('AdminModules', false).
            '&ajax=1&configure='.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules').'";
                         </script>';

        $this->context->controller->addJS(_MODULE_DIR_.'dsnewsletter/views/js/admin-min.js');
        $this->context->controller->addCSS(_MODULE_DIR_.'dsnewsletter/views/css/admin.css');

        $this->postProcess();

        if (Tools::getValue('get-design')) {
            $this->html .= $this->displayPanel($this->displayTemplatesForm());
        } elseif (Tools::isSubmit('duplicatedstemplate')) {
            $this->html .= $this->displayPanel($this->displayTemplatesForm());
        } elseif (Tools::isSubmit('adddstemplate') or Tools::isSubmit('updatedstemplate')) {
            $this->html .= $this->displayAddtemplateForm();
        } elseif (Tools::isSubmit('deletedstemplate')) {
            $this->html .= $this->displayPanel($this->displayTemplatesForm());
        } elseif (Tools::isSubmit('templates')) {
            $this->html .= $this->displayPanel($this->displayTemplatesForm());
        } elseif (Tools::isSubmit('adddsnewsletter') or Tools::isSubmit('updatedsnewsletter')) {
            $this->html .= $this->displayAddNewsletterForm();
        } elseif (Tools::getValue('deleteattachment')) {
            $this->html .= $this->displayAddNewsletterForm(Tools::getValue('id_dsnewsletter'));
        } elseif (Tools::isSubmit('deletedsnewsletter')) {
            $this->html .= $this->displayPanel($this->displayNewslettersForm());
        } elseif (Tools::isSubmit('newsletters')) {
            $this->html .= $this->displayPanel($this->displayNewslettersForm());
        } elseif (Tools::isSubmit('settings')) {
            $this->html .= $this->displayPanel($this->displaySettingsForm());
        } elseif (Tools::isSubmit('adddslist') or Tools::isSubmit('updatedslist')) {
            $this->html .= $this->displayAddListForm();
        } elseif (Tools::isSubmit('deletedslist')) {
            $this->html .= $this->displayPanel($this->displayListsForm());
        } elseif (Tools::isSubmit('lists')) {
            $this->html .= $this->displayPanel($this->displayListsForm());
        } elseif (Tools::isSubmit('docs')) {
            $this->html .= $this->displayPanel($this->displayDocsForm());
        } elseif (Tools::isSubmit('support')) {
            $this->html .= $this->displayPanel($this->displaySupportForm());
        } elseif (Tools::isSubmit('statistics')) {
            $this->html .= $this->displayPanel($this->displayStatisticsForm());
        } else {
            $this->html .= $this->displayPanel($this->displayListsForm());
        }

        return $this->html;
    }

    public function displayAddListForm()
    {
        $id_dslist = (int)Tools::getValue('id_dslist');

        $languages = Language::getLanguages(false);
        foreach ($languages as $k => $language) {
            $languages[$k]['is_default'] = (int)($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }

        $helper = new HelperForm();
        $helper->name_controller = 'dsnewsletter';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages = $languages;
        $helper->table = 'dslist';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = true;
        $helper->show_toolbar = true;
        $helper->title = 'Add/Edit List';

        $list = new DslistClass($id_dslist);
        if ($id_dslist) {
            $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.
                '&id_dslist='.$id_dslist;
            $helper->submit_action = 'updatedslist';
        } else {
            $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
            $helper->submit_action = 'adddslist';
        }

        $this->fields_form[0]['form'] = array(
            'tinymce' => true,
            'legend' => array(
                'title' => ($id_dslist ? $this->l('Edit List') . ':' . $list->name : $this->l('Add List'))
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'size' => 40,
                    'required' => true,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Sent to customers'),
                    'name' => 'target_customer',
                    'id' => 'target_customer',
                    'class' => 'chosen',
                    'options' => array(
                        'query' => TargetCustomer::getConstantsForSelect(),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'free',
                    'name' => 'ab_date',
                    'id' => 'ab_date',
                    'label' => $this->l('Abandoned cart days before')
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Customer groups'),
                    'name' => 'groups[]',
                    'id' => 'groups',
                    'class' => 'chosen',
                    'multiple' => true,
                    'options' => array(
                        'query' => Group::getGroups(Configuration::get('PS_LANG_DEFAULT')),
                        'id' => 'id_group',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Gender'),
                    'name' => 'gender',
                    'required' => false,
                    'class' => 'chosen',
                    'options' => array(
                        'query' => array(
                            array('value' => 3, 'label' => $this->l('Male')),
                            array('value' => 1, 'label' => $this->l('Female')),
                            array('value' => 2, 'label' => $this->l('Neutral'))
                        ),
                        'default' => array(
                            'label' => $this->l('All'),
                            'value' => 10
                        ),
                        'id' => 'value',
                        'name' => 'label'
                    )
                ),
                array(
                    'type' => 'free',
                    'name' => 'age',
                    'label' => $this->l('Age')
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Customer language'),
                    'name' => 'lang_customer',
                    'default' => '0',
                    'class' => 'chosen',
                    'multiple' => true,
                    'options' => array(
                        'query' => Language::getLanguages(true),
                        'id' => 'id_lang',
                        'name' => 'name',
                        'default' => array(
                            'label' => $this->l('All languages'),
                            'value' => 0
                        )
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Sent to newsletter module users'),
                    'name' => 'target_news',
                    'id' => 'target_news',
                    'class' => 'chosen',
                    'options' => array(
                        'query' => TargetNews::getConstantsForSelect(),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Selected newsletter module'),
                    'name' => 'selected_news',
                    'icon' => 'icon-search',
                    'id' => 'newsletter_autocomplete_input',
                    'class' => 'chosen',
                    'multiple' => true,
                    'hint' => $this->l('Search by email'),
                    'options' => array(
                        'query' => $this->getNewsByIds($list->selected_news),
                        'id' => 'id',
                        'name' => 'email',
                    ),
                )
            ),
            'submit' => array(
                'name' => 'submitAddDslist',
                'title' => $this->l('Save ')
            ),
            'buttons' => array(
                'cancelBlock' => array(
                    'title' => $this->l('Cancel'),
                    'href' => $this->getAdminUrl( array('lists' => 1) ),
                    'icon' => 'process-icon-cancel'
                ),
            )
        );

        $this->getFieldsValue($helper, $list, 'name', false, '');
        $this->getFieldsValue($helper, $list, 'target_customer');
        $this->getFieldsValue($helper, $list, 'selected_customer', true);
        $this->getFieldsValue($helper, $list, 'target_news');
        $this->getFieldsValue($helper, $list, 'selected_news', true);
        $this->getFieldsValue($helper, $list, 'gender');
        $this->getFieldsValue($helper, $list, 'groups', true);
        $this->getFieldsValue($helper, $list, 'lang_customer', true);
        $this->getFieldsValue($helper, $list, 'description');
        $this->getFieldsValue($helper, $list, 'age_value');
        $this->getFieldsValue($helper, $list, 'age_compare');

        $helper->fields_value['age'] = $this->createTextCompare(
            'age_compare',
            (isset($list->age_compare) ? $list->age_compare : ''),
            'age_value',
            (isset($list->age_value) ? $list->age_value : '')
        );

        $helper->fields_value['ab_date'] = $this->createFromToField(
            'ab_day',
            (isset($list->ab_day) ? $list->ab_day : ''),
            'ab_hour',
            (isset($list->ab_hour) ? $list->ab_hour : '')
        );
        return $helper->generateForm($this->fields_form);
    }

    protected function createTextCompare($compare_name, $compare_value, $name, $value, $class = '')
    {
        $this->context->smarty->assign(array(
            'compare_name' => $compare_name,
            'compare_value' => $compare_value,
            'name' => $name,
            'value' => $value,
            'class' => $class
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ .
            'dsnewsletter/views/templates/admin/text_with_compare.tpl');
    }

    protected function createFromToField($from_name, $from_value, $to_name, $to_value, $class = '')
    {
        $this->context->smarty->assign(array(
            'from_name' => $from_name,
            'from_value' => $from_value,
            'to_name' => $to_name,
            'to_value' => $to_value,
            'class' => $class
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ .
            'dsnewsletter/views/templates/admin/fromto.tpl');
    }

    /**
    * display list form
    */
    public function displayListsForm()
    {
        $this->fields_list = array(
            'id_dslist' => array(
                'title' => $this->l('ID'),
                'width' => 25,
                'orderby' => false,
                'search' => false
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'color' => 'color',
                'width' => 100,
                'orderby' => false,
                'search' => false
            ),
            'target_customer_label' => array(
                'title' => $this->l('Customer'),
                'orderby' => false,
                'search' => false,
            ),
            'target_news_label' => array(
                'title' => $this->l('Newsletter'),
                'orderby' => false,
                'search' => false
            ),
        );

        $lists = DslistClass::getLists();

        $helper = new HelperList();
        $helper->module = $this;
        $helper->shopLinkType = '';
        $helper->listTotal = count($lists);
        $helper->identifier = 'id_dslist';
        $helper->actions = array('edit', 'delete');
        $helper->ajax_params = array('configure' => 'dsnewsletter');
        $helper->show_toolbar = true;
        $helper->toolbar_btn['new'] =  array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.
                '&adddslist&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new')
        );
        $helper->title = 'Lists Manager';
        $helper->table = 'dslist';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&lists=1&configure='.$this->name;

        return $helper->generateList($lists, $this->fields_list);
    }

    /**
    * Display Tags Form
    * @return string form
    */
    public function displayTagsForm()
    {
        return $this->display(__FILE__, '/views/templates/admin/tags.tpl');
    }

    /**
    * Display Index Form
    * @return string form
    */
    public function displayIndexForm()
    {
        $this->context->controller->addJS(_MODULE_DIR_.'dsnewsletter/views/js/flot/jquery.flot.min.js');

        //newsletters
        $sql = 'SELECT sum(sent_number),sum(failed),sum(open), sum(click), sum(unsubscribe)
                FROM `'._DB_PREFIX_.'dsnewsletter`
                WHERE date_sent BETWEEN date_sub( now( ) , INTERVAL 1 MONTH ) AND now()
                GROUP BY WEEK(date_sent)';
        $news = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $this->html .= '<script type="text/javascript">';
        $this->html .= ' $(function() {
                            var sent_number = []; 
                            var failed = []; 
                            var open = []; 
                            var click = [];
                         ';

        foreach ($news as $key => $new) {
            $this->html .= 'sent_number.push(['.$key.', '. $new['sum(sent_number)'].']);';

            $this->html .= 'failed.push(['.$key.', '. $new['sum(failed)'].']);';

            $this->html .= 'open.push(['.$key.', '. $new['sum(open)'].']);';

            $this->html .= 'click.push(['.$key.', '. $new['sum(click)'].']);';
        }

        $this->html .= '$.plot($("#dashboard-stats"), [
                                {label: "Total sent", data: sent_number}, 
                                {label: "Total faild", data: failed}, 
                                {label: "Total open", data: open}, 
                                {label: "Total click", data: click}],{
                                       grid: {
                                         backgroundColor: { colors: [ "#fff", "#EDF5FF" ] },
                                         borderWidth: {
                                             top: 1,
                                             right: 1,
                                             bottom: 2,
                                             left: 2
                                       }
                                    } 
                                }); 
                         });
                         </script>';

        //customers
        $sql = 'SELECT COUNT(*) as total FROM `'._DB_PREFIX_.'customer`';
        $customers =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        //subscribers
        $sql = 'SELECT COUNT(*) as total FROM `'._DB_PREFIX_.$this->getNewsletterTableName().'`';
        $subscribers =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        //lists
        $sql = 'SELECT COUNT(*) as total FROM `'._DB_PREFIX_.'dslist`';
        $lists =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        //newsletters
        $sql = 'SELECT COUNT(*) as total FROM `'._DB_PREFIX_.'dsnewsletter` WHERE auto = 0';
        $newsletters =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        //automatic
        $sql = 'SELECT COUNT(*) as total FROM `'._DB_PREFIX_.'dsnewsletter` WHERE auto = 1';
        $automatic =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        //queue
        $sql = 'SELECT COUNT(*) as total FROM `'._DB_PREFIX_.'dsqueue`';
        $queue =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $this->smarty->assign(array(
            'currentIndex' => AdminController::$currentIndex.'&configure='.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            'customers' => $customers[0]['total'],
            'subscribers' => $subscribers[0]['total'],
            'lists' => $lists[0]['total'],
            'newsletters' => $newsletters[0]['total'],
            'automatic' => $automatic[0]['total'],
            'queue' => $queue[0]['total']
        ));

        $this->html .= $this->display(__FILE__, '/views/templates/admin/panel.tpl');
    }

    /**
     * Display Support Form
     * @return string form
     */
    public function displaySupportForm()
    {
        return $this->display(__FILE__, '/views/templates/admin/support.tpl');
    }

    /**
     * Display Docs Form
     * @return string form
     */
    public function displayDocsForm()
    {
        return $this->display(__FILE__, '/views/templates/admin/docs.tpl');
    }

    /**
    * Toolbar
    * @return string toolbar
    */
    public function initToolbar()
    {
        $current_index = AdminController::$currentIndex;
        $token = Tools::getAdminTokenLite('AdminModules');

        $back = Tools::safeOutput(Tools::getValue('back', ''));

        if (!isset($back) || empty($back)) {
            $back = $current_index.'&token='.$token;
        }


        $this->toolbar_btn['templates'] =  array(
            'href' => $current_index.'&configure='.$this->name.'&token='.$token.'&templates=1',
            'desc' => $this->l('Templates')
        );
        $this->toolbar_btn['newsletters'] = array(
            'href' => $current_index.'&configure='.$this->name.'&token='.$token.'&newsletter=1',
            'desc' => $this->l('Newsletters')
        );
        $this->toolbar_btn['settings'] = array(
            'href' => $current_index.'&configure='.$this->name.'&token='.$token.'&settings=1',
            'desc' => $this->l('Settings')
        );
        $this->toolbar_btn['statistics'] = array(
            'href' => $current_index.'&configure='.$this->name.'&token='.$token.'&statistics=1',
            'desc' => $this->l('Statistics')
        );
        $this->toolbar_btn['userguide'] = array(
            'href' => $current_index.'&configure='.$this->name.'&token='.$token.'&userguide=1',
            'desc' => $this->l('User guide')
        );
        $this->toolbar_btn['support'] = array(
            'href' => $current_index.'&configure='.$this->name.'&token='.$token.'&support=1',
            'desc' => $this->l('Support')
        );
        $this->toolbar_btn['back'] = array(
            'href' => $back,
            'desc' => $this->l('Back to modules')
        );

        return $this->toolbar_btn;
    }

    /**
     * get file content
     * @param string $path
     * @return bool|string
     */
    public function fileGetContent($path)
    {
        if(!$this->checkIsFileExists($path)) {
            return false;
        }
        return Tools::file_get_contents($path);
    }

    public function filePutContent($path, $content)
    {
        return file_put_contents($path, $content);
    }

    /**
     * check if file exists
     * @param string $path
     * @return bool
     */
    public function checkIsFileExists($path)
    {
        return file_exists($path);
    }

    public function getMailFilePath($type, $iso_code, $id, $file_type)
    {
        return dirname(__FILE__) . '/mails/'. $iso_code . '/' . $type . '-' . $id . '.' . $file_type;
    }


    public function getMailFiles($id, $type, $template_id_lang = null, $show_tag_click = false, $show_tag_track = false)
    {
        $mails = array();
        if(!$template_id_lang) {
            $template_id_lang = Configuration::get('PS_LANG_DEFAULT');
        }
        $iso_code = Language::getIsoById($template_id_lang);
        $content = $this->fileGetContent(
            $this->getMailFilePath($type, $iso_code, $id, FILETYPE_HTML)
        );
        $plaintext = $this->fileGetContent(
            $this->getMailFilePath($type, $iso_code, $id, FILETYPE_TXT)
        );

        //remove tracking image
        if(isset($matches[1])) {
            if(!$show_tag_track) {
                $content = str_replace(TAG_TRACK, '', $matches[1]);
            }
            if(!$show_tag_click) {
                $content = str_replace(TAG_CLICK, '', $matches[1]);
            }
        }

        $mails['plaintext'] = $plaintext;
        $mails['content'] = $content;

        return $mails;
    }

    /**
    * Add Templates Form
    * @return void
     */
    public function displayAddTemplateForm()
    {
        $id_template = (int)Tools::getValue('id_dstemplate');
        $current_index = AdminController::$currentIndex;
        $token = Tools::getAdminTokenLite('AdminModules');

        $this->context->controller->addJS(_MODULE_DIR_.'dsnewsletter/views/js/clipboard.min.js');
        $this->context->controller->addJS(_MODULE_DIR_.'dsnewsletter/views/js/add_template-min.js');

        $languages = Language::getLanguages(false);
        foreach ($languages as $k => $language) {
            $languages[$k]['is_default'] = (int)($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'dsnewsletter';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages = $languages;
        $helper->table = 'dstemplate';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = true;
        $helper->title = $this->l('Edit Template');
        $helper->toolbar_scroll = true;
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.
            '&updatedstemplate'.($id_template ? '&id_dstemplate='.$id_template : '');
        $helper->submit_action = 'submitAddDstemplate';
        $template_id_lang = (Tools::getValue('template_id_lang')?: Configuration::get('PS_LANG_DEFAULT'));

        $template = new DstemplateClass($id_template);
        $files = $this->getMailFiles($id_template, TEMPLATE, $template_id_lang);

        $fields_form = array(
            'form' => array (
                'tinymce' => true,
                'legend' => array(
                    'title' => $this->l('Edit Template') . ': ' . $template->name
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('design'),
                        'name' => 'design',
                        'id' => 'design'
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('html'),
                        'name' => 'html',
                        'id' => 'html'
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('Text'),
                        'name' => 'plain-text',
                        'id' => 'plain-text'
                    ),
                    array(
                        'type' => 'free',
                        'name' => 'email_design',
                        'label' => $this->l('Template')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'name' => 'name',
                        'size' => 40,
                        'required' => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Save languages'),
                        'name' => 'save_template_id_lang[]',
                        'id' => 'save_template_id_lang',
                        'multiple' => true,
                        'class' => 'chosen',
                        'desc' => $this->l('You can overwrite over languages if you need'),
                        'options' => array(
                            'query' => Language::getLanguages(false),
                            'id' => 'id_lang',
                            'name' => 'name'
                        )
                    ),
                ),
                'submit' => array(
                    'name' => 'submitAddDstemplate',
                    'title' => $this->l('Save ')
                ),
                'buttons' => array(
                    'cancelBlock' => array(
                        'title' => $this->l('Cancel'),
                        'href' => $this->getAdminUrl( array('templates' => 1) ),
                        'icon' => 'process-icon-cancel'
                    ),
                    'save-and-stay' => array(
                        'title' => $this->l('Save and stay'),
                        'name' => 'submitAddTemplateAndStay',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                    ),
                    'sent-test' => array(
                        'title' => $this->l('Sent test'),
                        'href' => $current_index.'&configure='.$this->name.'&token='.$token.
                            '&sent_test_template=1&id_dstemplate='.$id_template,
                        'class' => 'pull-right',
                    ),
                )
            )
        );

        $this->getFieldsValue($helper, $template, 'name', false, '');

        if (Tools::getValue('design')) {
            $helper->fields_value['design'] = Tools::getValue('design');
        } elseif (isset($template->design)) {
            $helper->fields_value['design'] = $template->design[$template_id_lang];
        } else {
            $helper->fields_value['design'] = '';
        }

        $helper->fields_value['html'] = '';
        $helper->fields_value['email_design'] = $this->createEmailDesign($files);
        $helper->fields_value['save_template_id_lang[]'] = Tools::getValue('template_id_lang');

        $this->html .= $helper->generateForm(array($fields_form));
    }

    protected function createEmailDesign($files)
    {
        $this->context->smarty->assign(array(
            'html_content_with_tags' => $this->addTagsToContentForDesign($files['content']),
            'text_content' => $files['plaintext'],
            'tags' => Tags::$tags_label,
            'placeholder' => self::getProductImagePlaceholder(),
            'mail_name' => 'test',
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ .
            'dsnewsletter/views/templates/admin/email_design.tpl');
    }

    public static function getProductImagePlaceholder()
    {
        return Tools::getHttpHost(true) .  __PS_BASE_URI__ . 'img/' . PRODUCT_IMAGE . '?';
    }

    public function addTagsToContentForDesign($content)
    {
        $keys = array();
        $values = array();
        $template_id_lang = Tools::getValue('template_id_lang');
        $template_id_lang = ($template_id_lang ? $template_id_lang : Configuration::get('PS_LANG_DEFAULT'));
        $tags = new Tags($content, $template_id_lang, 'John', 'DOE', 0, 1, 0);
        $tags = $tags->getAllWithValue();

        foreach ($tags as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }
        //remove placeholder product image
        $content = $this->removePlaceholderProductImage($content);
        return str_replace($keys, $values, $content);
    }

    public function getAdminUrl($params = null)
    {
        return AdminController::$currentIndex.'&configure='.$this->name.'&token='.
        Tools::getAdminTokenLite('AdminModules') . '&'. $this->getParams($params);
    }

    /**
    * Settings Form
    * @return string
    */
    public function displaySettingsForm()
    {
        $helper = new HelperForm();
        $helper->name_controller = 'dsnewsletter';
        $helper->table = 'settings';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->show_toolbar = true;
        $helper->title = $this->l('Settings');
        $helper->currentIndex = AdminController::$currentIndex.'&settings=1&configure='.$this->name;
        $helper->submit_action = 'updateSettings';
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');

        $this->fields_form[0]['form'] = array(
            'tinymce' => false,
            'legend' => array(
                'title' => $this->l('Settings')
            ),
            'input' => array(
                 array(
                    'type' => 'text',
                    'label' => $this->l('Next time cron run'),
                    'name' => 'cron_time',
                    'readonly' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Cron url'),
                    'name' => 'cron_url',
                    'readonly' => true,
                    'hint' => $this->l('Add this url to your cron job. Please look at'.
                        ' tutorials tab, module setup video.')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Test email'),
                    'required' => true,
                    'name' => 'test_email',
                    'size' => 55,
                    'hint' => $this->l('This is email adress for testing templates.')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Email number'),
                    'required' => true,
                    'name' => 'email_number',
                    'size' => 55,
                    'desc' => $this->l('Email number sent at one time.')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Sent report'),
                    'name' => 'sent_report',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'sent_report_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'sent_report_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                 array(
                    'type' => 'text',
                    'label' => $this->l('Report email'),
                    'name' => 'report_email',
                ),
                 array(
                    'type' => 'select',
                    'label' => $this->l('Email encode:'),
                    'name' => 'mail_encode',
                    'required' => false,
                    'class' => 't',
                    'options' => array(
                        'query' => array(
                            array(
                                'value' => 1,
                                'label' => $this->l('UTF-8')
                            ),
                            array(
                                'value' => 0,
                                'label' => $this->l('ISO-8859-2')
                            )
                        ),
                        'id' => 'value',
                        'name' => 'label',
                    ),
                )
            ),
            'submit' => array(
                'name' => 'submitUpdateSettings',
                'title' => $this->l('Save '),
            )
        );

        $helper->fields_value['test_email'] = Configuration::get('DSNEWSLETTER_TEST_EMAIL');
        /* CRON TIME*/
        $helper->fields_value['cron_time'] =  (string)Configuration::get('DSNEWSLETTER_CRON_TIME');

        $helper->fields_value['cron_url'] =  $this->context->link->getModuleLink(
            $this->name,
            'cron',
            array('token' => (string)Configuration::get('DSNEWSLETTER_SECURE_KEY'))
        );
        /* Number sent at one time */
        $helper->fields_value['email_number'] = Configuration::get('DSNEWSLETTER_EMAIL_NUMBER');
        /* Number report email string */
        $helper->fields_value['report_email'] = Configuration::get('DSNEWSLETTER_REPORT_EMAIL');
        /* Number sent report bool */
        $helper->fields_value['sent_report'] = Configuration::get('DSNEWSLETTER_SENT_REPORT');
        /* Number encode type */
        $helper->fields_value['mail_encode'] = Configuration::get('DSNEWSLETTER_MAIL_ENCODE');

        return $helper->generateForm($this->fields_form);
    }


    /**
     * Add Newsletter Form
     *
     * @param int $id_template
     *
     * @return string
     * @throws \PrestaShopDatabaseException
     */
    public function displayAddNewsletterForm()
    {
        $newsletter = null;
        $this->context->controller->addJqueryUi('ui.datepicker');
        $this->context->controller->addJqueryUi('ui.slider');
        $this->context->controller->addJqueryUi('ui.accordion');
        $this->context->controller->addJqueryUi('effects.fade');
        $this->context->controller->addJS(_PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.js');
        $this->context->controller->addCSS(
            _PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.css'
        );
        $this->context->controller->addJS(_PS_JS_DIR_.'date.js');

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'dsnewsletter';
        $helper->table = 'dsnewsletter';
        $helper->languages = $this->getLanguages();
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->allow_employee_form_lang = true;
        $helper->title = $this->l('Add/Edit Newsletter');

        $id_newsletter = Tools::getValue('id_dsnewsletter');
        $helper->currentIndex = $this->getAdminUrl( array('adddsnewsletter' => 1) );
        if ($id_newsletter) {
            $newsletter = new DsnewsletterClass($id_newsletter);
            $helper->currentIndex = $this->getAdminUrl(
                array('updatedsnewsletter' => 1 ,'id_dsnewsletter' => $id_newsletter)
            );
        }

        $helper->submit_action = 'submitAddDsnewsletter';
        $this->fields_form[0]['form'] = array(
            'tinymce' => true,
            'legend' => array(
                'title' => ($id_newsletter ? $this->l('Edit Newsletter') : $this->l('Add Newsletter'))
            ),
            'tabs' => array(
                'general' => $this->l('General'),
                'attachment' => $this->l('Attachment'),
                'start' => $this->l('Duration'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Newsletter name'),
                    'required' => true,
                    'name' => 'name',
                    'size' => 55,
                    'tab' => 'general',
                    'hint' => $this->l('This is internal name of your newsletter.')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Sender name'),
                    'name' => 'sender_name',
                    'tab' => 'general',
                    'size' => 55,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Sender email'),
                    'name' => 'sender_email',
                    'tab' => 'general',
                    'size' => 55,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Template'),
                    'name' => 'id_template',
                    'default_value' => 0,
                    'tab' => 'general',
                    'class' => 'chosen select-template',
                    'options' => array(
                        'query' => DstemplateClass::getTemplates(),
                        'id' => 'id_dstemplate',
                        'name' => 'name',
                        'default' => array(
                            'label' => $this->l('Please select template'),
                            'value' => 0
                        )
                    ),
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('File'),
                    'name' => 'file',
                    'tab' => 'attachment',
                    'id' => 'file',
                    'size' => 40,
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Email attachment'),
                    'name' => 'attachment',
                    'tab' => 'attachment',
                    'size' => 40,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Lists'),
                    'name' => 'id_list',
                    'class' => 'chosen',
                    'tab' => 'general',
                    'options' => array(
                        'query' => DslistClass::getAllLists(),
                        'id' => 'id_dslist',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Newsletter languages'),
                    'name' => 'id_lang',
                    'id' => 'id_lang',
                    'class' => 'chosen',
                    'multiple' => true,
                    'tab' => 'general',
                    'desc' => $this->l('Module sent newsletter with language match customer language otherwise with default Shop language'),
                    'options' => array(
                        'query' => Language::getLanguages(false),
                        'id' => 'id_lang',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Frequency:'),
                    'name' => 'frequency',
                    'tab' => 'start',
                    'class' => 'chosen',
                    'default_value' => 'none',
                    'options' => array(
                        'query' => Frequency::getForSelect(),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'date',
                    'label' => $this->l('Date start send'),
                    'required' => true,
                    'name' => 'date_planned',
                    'class' => 'timepicker',
                    'tab' => 'start',
                    'size' => 55,
                    'hint' => $this->l('Please select date when you plan to sent newsletters.')
                )
            ),
            'submit' => array(
                'name' => 'submitAddDsnewsletter',
                'title' => $this->l('Save ')
            ),
            'buttons' => array(
                'cancelBlock' => array(
                    'title' => $this->l('Cancel'),
                    'href' => $this->getAdminUrl().'&newsletter=1',
                    'icon' => 'process-icon-cancel'
                ),
                'save-and-stay' => array(
                    'title' => $this->l('Save and stay'),
                    'name' => 'submitAddNewsletterAndStay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save',
                )
            )
        );

        if ($id_newsletter) {
            $this->fields_form[0]['form']['buttons']['sent-test'] = array(
                'id' => 'desc-dsnewsletter-sent-test',
                'title' => $this->l('Sent test email'),
                'href' => $this->getAdminUrl(
                    array('sent_test_newsletter' => 1, 'id_dsnewsletter' => $newsletter->id)
                ),
                'icon' => 'process-icon-cancel',
                'class' => 'pull-right',
            );
        }
        $this->getFieldsValue($helper, $newsletter, 'sender_name', false, '');
        $this->getFieldsValue($helper, $newsletter, 'name', false, '');
        $this->getFieldsValue($helper, $newsletter, 'date_planned', false, '');
        $this->getFieldsValue($helper, $newsletter, 'date_start', false, '');
        $this->getFieldsValue($helper, $newsletter, 'sender_email', false, '');
        $this->getFieldsValue($helper, $newsletter, 'frequency');
        $this->getFieldsValue($helper, $newsletter, 'id_list');
        $this->getFieldsValue($helper, $newsletter, 'newsletter_id_lang');
        $this->getFieldsValue($helper, $newsletter, 'id_lang', true);
        $this->getFieldsValue($helper, $newsletter, 'id_template');

        if ($attachment = Tools::getValue('attachment')) {
            $helper->fields_value['attachment'] = $attachment;
        } elseif (isset($newsletter)) {
            $dirname = $this->getAttachmentPath($newsletter->id);
            if (is_dir($dirname)) {
                //get attachment names
                $images = scandir($dirname);
                $ignore = array(".", "..");
                $attachments = '';
                foreach ($images as $curimg) {
                    if (!in_array($curimg, $ignore)) {
                        $img = $this->getAdminUrl(
                           array('id_dsnewsletter' => $newsletter->id, 'deleteattachment' => $curimg)
                        );
                        $attachments .= $curimg ."<a href='$img'><img alt='' src='../img/admin/delete.gif' /></a></br>";
                    }
                }

                $helper->fields_value['attachment']  = $attachments;
            } else {
                $helper->fields_value['attachment'] = '';
            }
        } else {
            $helper->fields_value['attachment'] = 'no attachment';
        }

        $helper->fields_value['newListLink'] = $this->getAdminUrl( array('adddslist' => 1) );

        $this->html .= $helper->generateForm($this->fields_form);
    }

    /**
    * Templates Form
    * @return string
    */
    public function displayTemplatesForm()
    {
        $this->fields_list = array(
            'id_dstemplate' => array(
                'title' => $this->l('ID'),
                'width' => 25,
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true
            ),
            'image' => array(
                'title' => $this->l('Thumbnail'),
                'align' => 'center',
                'image' => '../modules/dsnewsletter/views/img/templates',
                'image_id' => 'id_dstemplate',
                'width' => 25,
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true
            ),
            'lang' => array(
                'title' => $this->l('Edit Language'),
                'width' => 25,
                'type' => 'lang',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true
            ),
        );

        $templates = DstemplateClass::getTemplates();
        $languages = Language::getIDs();
        foreach ($templates as $key => $template) {
            $templates[$key]['lang'] = $languages;
        }

        $helper = new HelperList();
        $helper->module = $this;
        $helper->show_toolbar = true;
        $helper->shopLinkType = '';
        $helper->identifier = 'id_dstemplate';
        $helper->actions = array('duplicate', 'delete');
        $helper->imageType = 'jpg';
        $helper->listTotal = count($templates);
        $helper->title = 'Templates Manager';
        $helper->table = 'dstemplate';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&templates=1&configure='.$this->name;

        return $helper->generateList($templates, $this->fields_list);
    }

    /**
    * Newsletters List
    * @return string
    */
    public function displayNewslettersForm()
    {
        $this->fields_list = array(
            'id_dsnewsletter' => array(
                'title' => $this->l('ID'),
                'width' => 25,
                'orderby' => false,
                'search' => false
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 100,
                'orderby' => false,
                'search' => false
            ),
            'list_name' => array(
                'title' => $this->l('Lists'),
                'width' => 25,
                'orderby' => false,
                'search' => false
            ),
            'template_name' => array(
                'title' => $this->l('Template'),
                'width' => 25,
                'type' => 'html',
                'orderby' => false,
                'search' => false
            ),
            'date_planned' => array(
                'title' => $this->l('Date Planned'),
                'width' => 100,
                'orderby' => false,
                'search' => false
            ),
            'frequency' => array(
                'title' => $this->l('Frequency'),
                'width' => 50,
                'orderby' => false,
                'search' => false
            ),
        );

        $news = DsnewsletterClass::getAll();
        $newsletters = DsnewsletterClass::getNewsletters($news);

        $helper = new HelperList();
        $helper->module = $this;
        $helper->show_toolbar = true;
        $helper->shopLinkType = '';
        $helper->identifier = 'id_dsnewsletter';
        $helper->actions = array('edit', 'delete', 'default');
        $helper->listTotal = count($newsletters);
        $helper->imageType = 'jpg';
        $helper->toolbar_btn['new'] =  array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.
            '&adddsnewsletter&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new')
        );
        $helper->title = 'Newsletters Manager';
        $helper->table = 'dsnewsletter';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&newsletters=1';

        return $helper->generateList($newsletters, $this->fields_list);
    }

    /**
    * Stats Form - Ajax call
    * @return string
    */
    public function displayStatisticsForm()
    {
        $this->context->controller->addJS(_MODULE_DIR_.'dsnewsletter/views/js/flot/jquery.flot.min.js');
        $this->context->controller->addJS(_MODULE_DIR_.'dsnewsletter/views/js/flot/jquery.flot.categories.js');

        $this->fields_list = array(
            'news_name' => array('title' => $this->l('Name'), 'width' => 25),
            'date_sent' => array('title' => $this->l('Date Sent'), 'width' => 25),
            'sent_number' => array('title' => $this->l('Total Send'), 'width' => 25),
            'failed' => array('title' => $this->l('Total Failed'), 'width' => 25),
            'open' => array('title' => $this->l('Email Open'), 'width' => 25),
            'click' => array('title' => $this->l('Link Clicked'), 'width' => 25),
            'unsubscribe' => array('title' => $this->l('Unsubscribe'), 'width' => 25)
        );

        $helper = new HelperList();
        $helper->module = $this;
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id_dsnewsletter';
        $helper->show_toolbar = true;
        $helper->toolbar_btn['panel'] = array( 'href' => $this->getAdminUrl(), 'desc' => $this->l('Panel') );
        $helper->title = $this->l('Statistics');
        $helper->table = 'statistics';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $id_newsletter = Tools::getValue('id_newsletter');
        return $this->getTotalStatistics($id_newsletter) .
            $helper->generateList(
                DsstatsClass::getStatsByNewsletterID($id_newsletter, true, "DESC"),
                $this->fields_list
            );
    }

    public function getTotalStatistics($id_newsletter)
    {
        $data = DsstatsClass::getDataForStats($id_newsletter);
        $newsletters = DsnewsletterClass::getAll();
        $this->smarty->assign($data);
        $this->smarty->assign(array(
            'id_newsletter' => $id_newsletter,
            'newsletters' => $newsletters,
            'base' => $this->getAdminUrl(array(
                'statistics' => 1
            ))
        ));

        return $this->display(__FILE__, '/views/templates/admin/statistics.tpl');
    }

    /**
     * get Products as html - Ajax call
     * @return string
     * @throws PrestaShopDatabaseException
     */
    public function displayProductsForm()
    {
        $ids = explode('-', Tools::substr(Tools::getValue('ids'), 0, -1));

        $products = $this->getProducts(Context::getContext()->shop->id, implode(',', $ids), false);
        $link = new Link();
        $this->smarty->assign(array(
            'products' => $products,
            'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
            'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
            'link' => $link,
            'number' =>   Tools::getValue('number')
        ));

        echo $this->display(__FILE__, '/views/templates/hook/front/api/products.tpl');
    }


    /**
     * get Products
     * @param $id_lang
     * @param $ids
     * @param bool $id_category
     * @param bool $active
     * @return array products
     * @throws PrestaShopDatabaseException
     * @internal param id_lang $int language id
     * @internal param id_category $int category id
     * @internal param active $bool
     * @internal param ids $string product ids as form 1,2,3,4
     */
    public static function getProducts($id_lang, $ids, $active = false)
    {
        $sql = 'SELECT p.*, pl.`description_short`, pl.`link_rewrite`, pl.`name`, i.`id_image`
        FROM `'._DB_PREFIX_.'product` p
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
            ON (p.`id_product` = pl.`id_product`
            AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').')
        LEFT JOIN `'._DB_PREFIX_.'image` i
            ON (i.`id_product` = p.`id_product`
            AND i.`cover` = 1)
        WHERE  p.`id_product` IN('.$ids.')'
            .($active ? ' AND product_shop.`active` = 1' : '');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (!$result) {
            return false;
        }

        return Product::getProductsProperties((int)$id_lang, $result);
    }

    public function getLanguages()
    {
        $languages = Language::getLanguages(false);
        foreach ($languages as $k => $language) {
            $languages[$k]['is_default'] = (int)($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }

        return $languages;
    }

    public function setTotalProgress($total)
    {
        $progress = '0,' . $total;
        Configuration::updateGlobalValue('DSNEWSLETTER_PROGRESS', $progress);
    }

    public function incrementProgress()
    {
        $progress = explode(',', Configuration::getGlobalValue('DSNEWSLETTER_PROGRESS'));
        $progress[0] += 1;
        Configuration::updateGlobalValue('DSNEWSLETTER_PROGRESS', $progress[0] . ',' .$progress[1]);
    }

    public function cleanProgress()
    {
        Configuration::updateGlobalValue('DSNEWSLETTER_PROGRESS', '');
    }

    private function defaultdsnewsletter()
    {
        $email_correct = null;
        $email_errors = null;
        $email_attachments = null;
        $emails = array();

        $this->cleanProgress();
        session_write_close(); // close the session
        //get all subscribers
        $newsletter = new DsnewsletterClass(Tools::getValue('id_dsnewsletter'));
        $list = new DslistClass($newsletter->id_list);
        $customers = $this->getCustomers($list);
        $news = $this->getNews($list);
        $subscribers = ($customers ? array_merge($customers, $news) : $news);
        $this->setTotalProgress(count($subscribers));
        //get languages newsletter want to send
        $newsletter_languages = explode(',', $newsletter->id_lang);
        foreach ($newsletter_languages as $id_lang) {
            $emails[$id_lang] = $this->getMailFiles($newsletter->id, NEWSLETTER, $id_lang, true, true);
        }

        if ($newsletter->id) {
            //get attachment names
            $dirname = _PS_ROOT_DIR_ . '/modules/dsnewsletter/upload/attachments/' . $newsletter->id;
            if (is_dir($dirname)) {
                $images = scandir($dirname);
                $ignore = array(".", "..");

                foreach ($images as $key => $curimg) {
                    if (!in_array($curimg, $ignore)) {
                        $email_attachments[$key]['content'] = Tools::file_get_contents($dirname . '/' . $curimg);
                        $email_attachments[$key]['name'] = pathinfo($dirname . '/' . $curimg, PATHINFO_FILENAME);
                        $email_attachments[$key]['mime'] = pathinfo($dirname . '/' . $curimg, PATHINFO_EXTENSION);
                    }
                }
            }
        }

        if ($subscribers) {
            foreach ($subscribers as $subscriber) {
                set_time_limit(30);

                if (isset($subscriber['id_lang']) and in_array($subscriber['id_lang'], $newsletter_languages)) {
                    $id_lang = (int) $subscriber['id_lang'];
                } else {
                    $id_lang = Configuration::get('PS_LANG_DEFAULT');
                }

                $first_name = (isset($subscriber['first_name']) ?: '');
                $last_name = (isset($subscriber['last_name']) ?: '');

                $tags = new Tags(
                    $emails[$id_lang]['content'],
                    $id_lang,
                    $first_name,
                    $last_name,
                    $newsletter->id,
                    $subscriber['id_customer'],
                    $subscriber['id_subscriber'],
                    true,
                    true
                );
                $tags = $tags->getAllWithValue();

                if (Mail::Send(
                    $id_lang,
                    'newsletter-'.$newsletter->id,
                    Mail::l($newsletter->name, $id_lang),
                    $tags,
                    $subscriber['email'],
                    null,
                    $newsletter->sender_email,
                    $newsletter->sender_name,
                    $email_attachments,
                    null,
                    dirname(__FILE__) . '/mails/'
                )) {
                    $email_correct[] = $subscriber['email'];
                } else {
                    $email_errors[] = $subscriber['email'];
                }
                $this->incrementProgress();
            }
        }

        $this->addStatistics($newsletter, $email_correct, $email_errors);
        $this->smarty->assign(array(
            'id'      => $newsletter->id,
            'name'    => $newsletter->name,
            'total'   => count($subscribers),
            'correct' => count($email_correct),
            'error'   => count($email_errors),
            'errors'  => $email_errors
        ));

        echo $this->display(__FILE__, '/views/templates/admin/report.tpl');
    }

    private function sentTestEmail($type, $object, $id_lang, $click_wrapper = false)
    {
        $emails = $this->getMailFiles($object->id, $type, $id_lang, $click_wrapper);
        $tags = new Tags(
            $emails['content'],
            $id_lang,
            'John',
            'DOE',
            ($type === NEWSLETTER ? $object->id : 0),
            1,
            0
        );

        if (Mail::Send(
            $this->context->language->id,
            'template-'.$object->id,
            Mail::l('Test template', $this->context->language->id),
            $tags->getAllWithValue(),
            Configuration::get('DSNEWSLETTER_TEST_EMAIL'),
            null,
            null,
            null,
            null,
            null,
            dirname(__FILE__) . '/mails/'
        )) {
            $this->_errors[] = $this->l('There was a problem sending test template.');
        }

        $type = strtolower($type);
        $this->redirect( array(
            'updateds' . $type => 1,
            $type . 's' => 1,
            'id_ds' . $type => $object->id,
            'template_id_lang' => $id_lang,
            'sentTestConfirmation' => 1
        ) );
    }

    /**
     * @return void
     */
    private function details()
    {
        $id = Tools::getValue('id');

        $this->fields_list = array(
            'id_dsnewsletter' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
            'name'            => array('title' => $this->l('Name'), 'align' => 'center', 'width' => 25),
            'status'          => array('title' => $this->l('Status'), 'align' => 'center', 'width' => 25),
            'id_template'     => array(
                'title' => $this->l('Template'),
                'align' => 'center',
                'width' => 25,
                'type'  => 'html'
            ),
            'date_planned'    => array('title' => $this->l('Date Planned'), 'align' => 'center', 'width' => 25),
            'auto'            => array('title' => $this->l('Automatic'), 'align' => 'center', 'width' => 25)
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id_dsnewsletter';
        $helper->bulk_actions = array();
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = false;
        $helper->no_link = true;
        $helper->table = 'newsletter';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        $content = $helper->generateList(DsnewsletterClass::getNewslettersByListId($id), $this->fields_list);

        echo Tools::jsonEncode(array(
            'data' => $content,
            'use_parent_structure' => false,
        ));
        die();
    }

    /**
     * @return bool
     */
    private function uploadImage()
    {
        $name = $_FILES['images']['name'][0];
        $id_template = Tools::getValue('id_template');

        if (!$id_template) {
            $id_template = 0;
        }

        $this->makePath(dirname(__FILE__) . "/views/img/mails/template/" . $id_template);

        if (isset($name) && isset($_FILES['images']['tmp_name'][0]) &&
            !empty($_FILES['images']['tmp_name'][0])) {
            if (file_exists(dirname(__FILE__) . "/views/img/mails/template/$id_template/$name")) {
                unlink(dirname(__FILE__) . "/views/img/mails/template/$id_template/$name");
            }
            if (!move_uploaded_file($_FILES['images']['tmp_name'][0], dirname(__FILE__) .
                "/views/img/mails/template/$id_template/$name")) {
                return false;
            }
            list($width, $height) = getimagesize(dirname(__FILE__) .
                "/views/img/mails/template/$id_template/$name");
        }

        $this->smarty->assign(array(
            'name' => $name,
            'id_template' => $id_template,
            'width' => $width,
            'height' => $height,
            'base' => Tools::getHttpHost(true) .  __PS_BASE_URI__
        ));

        echo json_encode($this->display(__FILE__, '/views/templates/admin/image.tpl'));
    }

    /**
     * @return void
     */
    private function removeImage()
    {
        $id_template = Tools::getValue('id_template');
        $name = Tools::getValue('image');

        if (isset($name) && ! empty($name) && isset($id_template) && ! empty($id_template)) {
            if (file_exists(dirname(__FILE__) . "/views/img/mails/template/$id_template/$name")) {
                unlink(dirname(__FILE__) . "/views/img/mails/template/$id_template/$name");
            }
        }

        echo json_encode('deleted');
    }

    /**
     * @param $user
     *
     * @internal param $users
     * @internal param $i
     *
     */
    private function addListBadge($user)
    {
        if ($user['dslist_id']) {
            $ids = explode(',', $user['dslist_id']);
            foreach ($ids as $id) {
                $this->smarty->assign(array(
                    'id_list' => $id
                ));
                $user['list'] .= $this->display(__FILE__, '/views/templates/admin/badge.tpl');
            }
        }
        return $user;
    }

    private function getNextTemplateId()
    {
        $result = Db::getInstance()->ExecuteS('SELECT MAX(id_dstemplate) as max_id FROM '._DB_PREFIX_.'dstemplate');
        return $result[0]['max_id'] + 1;
    }

    private function getProduct()
    {
        $data = array();
        $query = Tools::getValue('q');
        if (!$query or $query == '' or Tools::strlen($query) < 1) {
            die();
        }

        if ($pos = strpos($query, ' (ref:')) {
            $query = Tools::substr($query, 0, $pos);
        }

        $product = Db::getInstance()->ExecuteS(
            'SELECT p.id_product, pl.name FROM `' . _DB_PREFIX_ . 'product` p
			'.Shop::addSqlAssociation('product', 'p').'
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
            WHERE (name LIKE \'%' . pSQL($query) . '%\')
             GROUP BY p.id_product LIMIT 5'
        );

        if ($product) {
            foreach ($product as $item) {
                $data[] = array('value' => $item['id_product'], 'caption' => $item['name']);
            }
        }

        echo json_encode($data);
    }

    private function getCustomer()
    {
        $data = array();
        $query = Tools::getValue('q');
        if (!$query or $query == '' or Tools::strlen($query) < 1) {
            die();
        }

        if ($pos = strpos($query, ' (ref:')) {
            $query = Tools::substr($query, 0, $pos);
        }

        $customers = Db::getInstance()->ExecuteS(
            'SELECT id_customer, email FROM `' . _DB_PREFIX_ . 'customer` 
            WHERE (email LIKE \'%' . pSQL($query) . '%\')
            AND id_shop = '. Context::getContext()->shop->id .
            (!empty($excludeIds) ? ' AND id_category NOT IN (' . $excludeIds . ') ' : ' ') .
            'GROUP BY id_customer LIMIT 5'
        );

        if ($customers) {
            foreach ($customers as $item) {
                $data[] = array('value' => $item['id_customer'], 'caption' => $item['email']);
            }
        }

        echo json_encode($data);
    }

    private function getAutcompleteNews()
    {
        $data = array();
        $query = Tools::getValue('q');
        if (!$query or $query == '' or Tools::strlen($query) < 1) {
            die();
        }

        if ($pos = strpos($query, ' (ref:')) {
            $query = Tools::substr($query, 0, $pos);
        }

        $tableName = $this->getNewsletterTableName();

        $newsletters = Db::getInstance()->ExecuteS(
            'SELECT id, email FROM `' . _DB_PREFIX_ . $tableName . '`
            WHERE (email LIKE \'%' . pSQL($query) . '%\')
            AND id_shop = '. Context::getContext()->shop->id .
            (!empty($excludeIds) ? ' AND id NOT IN (' . $excludeIds . ') ' : ' ') .
            'GROUP BY id LIMIT 5'
        );

        if ($newsletters) {
            foreach ($newsletters as $item) {
                $data[] = array('value' => $item['id'], 'caption' => $item['email']);
            }
        }

        echo json_encode($data);
    }

    function randomColor ($minVal = 175, $maxVal = 255)
    {
        $minVal = $minVal < 0 || $minVal > 255 ? 0 : $minVal;
        $maxVal = $maxVal < 0 || $maxVal > 255 ? 255 : $maxVal;

        $r = mt_rand($minVal, $maxVal);
        $g = mt_rand($minVal, $maxVal);
        $b = mt_rand($minVal, $maxVal);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    public function getFieldsValue(&$helper, $list, $name, $array = false, $default = 0)
    {
        $name_array = $name;
        if($array) {
            $name_array = $name . '[]';
        }
        if (Tools::getValue($name_array)) {
            $helper->fields_value[$name_array] = Tools::getValue($name);
        } elseif (isset($list->$name)) {
            $helper->fields_value[$name_array] = ($array ? explode(',', $list->$name) : $list->$name);
        } else {
            $helper->fields_value[$name_array] = ($array ? array() : $default);
        }
    }

    /**
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function saveTemplateAndTxt()
    {
        $template = new DstemplateClass(Tools::getValue('id_dstemplate'));
        $template->name = Tools::getValue('name');
        $template->design = Tools::getValue('design');
        $template->save();

        $this->addTemplateImage($template->id);
        $iso_code = LanguageCore::getIsoById(1);

        if (!$this->saveHtmlToFile(
            Tools::getValue('html'),
            $this->getMailFilePath(TEMPLATE, $iso_code, $template->id, FILETYPE_HTML)
        )) {
            $this->_errors[] = $this->l('Error save template file');
        }
        if (!file_put_contents(
            $this->getMailFilePath(TEMPLATE, $iso_code, $template->id, FILETYPE_TXT),
            Tools::getValue('plaintext')
        )) {
            $this->_errors[] = $this->l('Error save text version');
        }

        $redirect = 'templates=1&addDstemplateConfirmation';
        if (Tools::isSubmit('submitAddTemplateAndStay')) {
            $redirect = 'updatedstemplate=1&id_dstemplate=' . $template->id . '&addDstemplateConfirmation&'.
            'template_id_lang=' . Tools::getValue('template_id_lang');
        }

        $module_url = AdminController::$currentIndex . '&configure=' . $this->name .
            '&token=' . Tools::getAdminTokenLite('AdminModules') . '&';
        Tools::redirectAdmin($module_url . $redirect);
    }

    /**
     * @param DsnewsletterClass $newsletter
     * @param array $email_correct
     * @param array $email_errors
     * @return void
     * @throws PrestaShopException
     */
    private function addStatistics($newsletter, $email_correct, $email_errors)
    {
        $stats = new DsstatsClass();
        $stats->id_news = $newsletter->id;
        $stats->date_sent = date('Y-m-d H:i:s');
        $stats->sent_number = ($email_correct ? count($email_correct) : 0);
        $stats->failed = ($email_errors ? $this->implode($email_errors) : 0);
        $stats->save();
    }

    public function getCustomers($list)
    {
        $ids = null;
        if($list->target_customer === TargetCustomer::NONE) {
            return null;
        }
        if($list->target_customer === TargetCustomer::SELECTED_CUSTOMERS && $list->selected_customer &&
            !is_array($list->selected_customer)) {
            $ids = explode(',', $list->selected_customer);
        }
        $sql = 'SELECT c.id_customer, c.email FROM `' . _DB_PREFIX_ . 'customer` as c';
        if($list->target_customer === TargetCustomer::CUSTOMERS_WITH_ORDER) {
            $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'orders AS o ON (c.id_customer = o.id_customer)';
        }
        if($list->target_customer === TargetCustomer::CUSTOMERS_WITH_CART) {
            $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'cart AS ca ON (c.id_customer = ca.id_customer)';
        }
        if($list->target_customer === TargetCustomer::CUSTOMERS_WITH_ABANDONED_CART) {
            $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'cart AS ca ON (c.id_customer = ca.id_customer)';
        }
        $sql .= ' WHERE 1';
        if($list->target_customer === TargetCustomer::CUSTOMERS_WITH_ABANDONED_CART) {
            $sql .= ' AND ca.`date_add` BETWEEN DATE_SUB(DATE(NOW()), INTERVAL ' . (int)$list->ab_day . ' DAY)' .
                ' AND DATE_SUB(NOW(), INTERVAL ' . (int)$list->ab_hour . ' HOUR)' .
                ' AND NOT EXISTS (SELECT id_order FROM `' . _DB_PREFIX_ . 'orders`' .
		        ' WHERE `' . _DB_PREFIX_ . 'orders`.id_cart = ca.id_cart)';
        }
        if($list->target_customer === TargetCustomer::SELECTED_CUSTOMERS && !empty($ids)) {
            $sql .= ' AND c.id_customer IN ("' . implode('","', $ids) . '")';
        }
        $sql .= ($list->target_customer === TargetCustomer::NEWSLETTER_SUBSCRIBERS ? ' AND c.newsletter = 1' : '');
        if($list->target_customer === TargetCustomer::CUSTOMERS_GROUPS) {
            $sql .= ' AND c.id_default_group = ' . (int)$list->group;
        }
        if($list->age_value && $list->age_compare) {
            $year_from_now = new \DateTime('- ' . $list->age_value . ' years');
            $sql .= ' AND c.birthday ' . $this->pSQL($list->age_compare) . ' ' . $year_from_now->format('Y');
        }
        $sql .= ($list->gender && (int)$list->gender !== 10 ? ' AND c.id_gender = ' . (int)$list->gender : '');
        if($list->lang_customer) {
            $id_lang = explode(',', $list->lang_customer);
            $sql .= ' AND c.id_lang IN ("' . implode('","', $id_lang) . '")';
        }
        $sql .= ' GROUP BY c.id_customer';

        return $this->DbExecuteS($sql);
    }

    public function getNewsByIds($ids)
    {
        if(!$ids) { return; }
        $list = new DslistClass();
        $list->selected_news = $ids;
        return self::getNews($list);
    }

    public function getCustomersByIds($ids)
    {
        if(!$ids) { return; }
        $list = new DslistClass();
        $list->selected_news = $ids;
        return $this->getCustomers($list);
    }

    public function getNews($list)
    {
        $ids = null;
        $subscribe = null;
        $tableName = $this->getNewsletterTableName();

        if(!$list || $list->target_news === TargetNews::NONE) {
            return null;
        }
        if($list->target_news === TargetNews::SELECTED_SUBSCRIBERS && $list->selected_news && !is_array($list->selected_news)) {
            $ids = explode(',', $list->selected_news);
        }
        $sql = 'SELECT id, email FROM `' . _DB_PREFIX_ . $tableName . '` WHERE 1';

        if($list->target_news === TargetNews::SELECTED_SUBSCRIBERS && $ids && is_array($ids)) {
            $sql .= ' AND id IN ("' . implode('","', $ids) . '") GROUP BY id';
        }
        if($list->target_news === TargetNews::SUBSCRIBERS) {
            $subscribe[] = 1;
        }
        if($list->target_news === TargetNews::UNSUBSCRIBERS) {
            $subscribe[] = 0;
        }
        if($subscribe && is_array($subscribe)) {
            $sql .= ' AND active IN ("' . implode('","', $subscribe) . '") GROUP BY id';
        }
        return $this->DbExecuteS($sql);
    }

    public function DbExecuteS($sql)
    {
        return Db::getInstance()->ExecuteS($sql);
    }

    public function pSQL($sql)
    {
        return pSQL($sql);
    }

    /**
     * @return string
     */
    private function getNewsletterTableName(): string
    {
        $tableName = 'newsletter';
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $tableName = 'emailsubscription';
        }
        return $tableName;
    }

    /**
     * @param $content
     * @return array|string|string[]
     */
    private function removePlaceholderProductImage($content)
    {
        $content = str_replace(Dsnewsletter::getProductImagePlaceholder(), '', $content);
        return $content;
    }

    /**
     * @return void
     */
    private function updateSettings(): void
    {
        Configuration::updateValue('DSNEWSLETTER_TEST_EMAIL', (string)Tools::getValue("test_email"));
        Configuration::updateValue('DSNEWSLETTER_EMAIL_NUMBER', (string)Tools::getValue("email_number"));
        Configuration::updateValue('DSNEWSLETTER_REPORT_EMAIL', (string)Tools::getValue("report_email"));
        Configuration::updateValue('DSNEWSLETTER_SENT_REPORT', (string)Tools::getValue("sent_report"));
        Configuration::updateValue('DSNEWSLETTER_MAIL_ENCODE', (string)Tools::getValue("mail_encode"));

        $this->redirect(array('settings' => 1, 'updateSettingsConfirmation' => 1));
    }

    public function redirect($redirect)
    {
        Tools::redirectAdmin($this->getAdminUrl() . '&' . $this->getParams($redirect));
    }

    /**
     * @param $redirect
     * @return string
     */
    function getParams($redirect)
    {
        $params = $redirect;
        if ( is_array($redirect) ) {
            $params = http_build_query($redirect);
        }
        return $params;
    }

    /**
     * @param $file
     * @return void
     */
    private function deleteAttachment($file, $id_newsletter)
    {
        $url = $this->getAttachmentPath($id_newsletter) . '/' . $file;
        if (file_exists($url) === true) {
            unlink($url);
        }

        $this->redirect(array(
            'updatedsnewsletter' => 1,
            'id_dsnewsletter' =>  $id_newsletter,
            'deleteAttachmentConfirmation' => 1
        ));
    }

    private function getAttachmentPath($id_newsletter)
    {
        return dirname(__FILE__) . '/upload/attachments/' . $id_newsletter;
    }

    /**
     * @param $file
     * @return void
     */
    private function deleteImageTemplate($file, $id_template, $iso_code)
    {
        $image = $this->getTemplateImagePath($id_template, $iso_code) . '/' . $file;
        if (file_exists($image) == true) {
            unlink($image);
        }

        $this->redirect(array(
            'updatedstemplate' => 1,
            'id_dstemplate' => $id_template,
            'deleteImageConfirmation' => 1
        ));
    }

    private function getTemplateImagePath($id_template, $iso_code)
    {
        return dirname(__FILE__) . '/mails/template/' . $id_template . '/' . $iso_code . '/images';
    }

    private function deleteList($id_list)
    {
        if ($id_list) {
            $list = new DslistClass($id_list);
            $list->delete();
        }
    }

    private function submitAddList()
    {
        $list = new DslistClass(Tools::getValue('id_dslist'));
        $list->name = Tools::getValue('name');
        $list->description = Tools::getValue('description');
        $list->groups = implode(',', Tools::getValue('groups'));
        $list->target_customer = Tools::getValue('target_customer');
        $list->selected_customer = implode(',', Tools::getValue('selected_customer'));
        $list->gender = ((int)Tools::getValue('gender') === 3 ? 0 : Tools::getValue('gender'));
        $list->age_compare = Tools::getValue('age_compare');
        $list->age_value = Tools::getValue('age_value');
        $list->ab_day = Tools::getValue('ab_day');
        $list->ab_hour = Tools::getValue('ab_hour');
        $list->lang_customer = implode(',', Tools::getValue('lang_customer'));
        $list->target_news = Tools::getValue('target_news');
        $list->selected_news = implode(',', Tools::getValue('selected_news'));
        $list->color = $this->randomColor();
        $list->save();

        $redirect = null;
        if (Tools::isSubmit('adddslist') or Tools::isSubmit('updatedslist')) {
            $redirect = array('lists' => 1, 'addDslistConfirmation' => 1);
        }
        $this->redirect($redirect);
    }

    private function deleteNewsletter($id_newsletter)
    {
        $newsletter = new DsnewsletterClass($id_newsletter);
        $newsletter->delete();

        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            ToolsCore::deleteFile(  // delete html file
                $this->getMailFilePath(NEWSLETTER, $languages['iso_code'], $newsletter->id, FILETYPE_HTML)
            );
            ToolsCore::deleteFile(   // delete txt file
                $this->getMailFilePath(NEWSLETTER, $languages['iso_code'], $newsletter->id, FILETYPE_TXT)
            );
        }

        $attachment_path = $this->getAttachmentPath($newsletter->id);
        if (file_exists( $attachment_path )) { $this->deleteDir( $attachment_path ); } // delete attachment path

        $this->redirect(array('newsletters' => 1, 'deleteDsnewsletterConfirmation' => 1));
    }

    private function deleteTemplate($id_dstemplate)
    {
        $template = new DstemplateClass($id_dstemplate);
        $template->delete();

        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            ToolsCore::deleteFile(
                $this->getMailFilePath(TEMPLATE, $language['iso_code'], $template->id, FILETYPE_HTML)
            );
            ToolsCore::deleteFile(
                $this->getMailFilePath(TEMPLATE, $language['iso_code'], $template->id, FILETYPE_TXT)
            );
        }

        $this->redirect(array('templates' => 1, 'deleteTemplateConfirmation' => 1));
    }

    private function duplicateTemplate($id_template)
    {
        if ( empty($id_template) ) { return; }

        $template = new DstemplateClass($id_template);
        $duplicate = $template->duplicateObject();
        $duplicate->name = 'copy ' . $template->name;
        $duplicate->save();

        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            $srcHtml = $this->getMailFilePath(TEMPLATE, $language['iso_code'], $template->id, FILETYPE_HTML);
            $srcTxt = $this->getMailFilePath(TEMPLATE, $language['iso_code'], $template->id, FILETYPE_TXT);
            $dstHtml = $this->getMailFilePath(TEMPLATE, $language['iso_code'], $duplicate->id, FILETYPE_HTML);
            $dstTxt = $this->getMailFilePath(TEMPLATE, $language['iso_code'], $duplicate->id, FILETYPE_TXT);

            copy($srcHtml, $dstHtml); // copy html
            copy($srcTxt, $dstTxt);  // copy txt

            $content = Tools::file_get_contents($dstHtml);
            //write base
            $content = str_replace(
                '/mails/' . $language['iso_code'] . '/template-' . $template->id . '/',
                '/mails/' . $language['iso_code'] . '/template-' . $duplicate->id . '/',
                $content
            );
            if ( !file_put_contents($dstHtml, $content) ) {
                $this->_errors[] = ('Error write images url.');
            }
        }

        ToolsCore::copy( $this->getTemplateThumbnail($template->id), $this->getTemplateThumbnail($duplicate->id) );

        $this->redirect( array('templates' => 1, 'duplicateTemplateConfirmation' => 1) );
    }

    private function getTemplateThumbnail($id_template)
    {
        return dirname(__FILE__) . '/views/img/templates/' . $id_template . '.jpg';
    }

    private function implode($array, $delimiter = ',')
    {
        return implode($delimiter, $array);
    }

    private function explode($string, $delimiter = ',')
    {
        return explode($delimiter, $string);
    }

    private function submitAddNewsletter($id_newsletter)
    {
        $frequency = Tools::getValue('frequency');
        $newsletter = new DsnewsletterClass($id_newsletter);
        $newsletter->name = Tools::getValue('name');
        $newsletter->id_template = Tools::getValue('id_template');
        $newsletter->date_planned = Tools::getValue('date_planned');
        $newsletter->id_lang = $this->implode(Tools::getValue('id_lang'));
        $newsletter->id_list = Tools::getValue('id_list');
        $newsletter->sender_name = (string)Tools::getValue('sender_name');
        $newsletter->sender_email = (string)Tools::getValue('sender_email');
        $newsletter->frequency = (int)$frequency;

        if ($this->isNotOneTimeOrManual($frequency)) {
            $date_planned = Tools::getValue('date_planned');
            $date_start = Tools::getValue('date_start');
            $newsletter->date_start = $date_planned;
            $newsletter->date_planned = $this->getDatePlanned($date_planned, $date_start, $frequency);
            $newsletter->auto = 1;
        }
        $newsletter->save();

        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            $iso_code = $language['iso_code'];
            $this->saveNewsletterFiles($iso_code, $newsletter);
        }
        $this->uploadAttachment($newsletter);

        if (Tools::isSubmit('submitAddNewsletterAndStay')) {
            $this->redirect(array(
                'updatedsnewsletter' => 1,
                'id_dsnewsletter' => $newsletter->id,
                'addDsnewsletterConfirmation' => 1,
                'newsletter_id_lang' => Tools::getValue('newsletter_id_lang')
            ));
        }

        $this->redirect( array('newsletters' => 1, 'addDsnewsletterConfirmation' => 1) );
    }

    private function isNotOneTimeOrManual($frequency)
    {
        return (int)$frequency !== 0 && (int)$frequency !== 1;
    }

    private function getDatePlanned($date_planned, $date_start, $frequency)
    {
        if ( !$date_planned or $date_planned !== '0000-00-00 00:00:00' ) { return null; }

        $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $date_start);
        $add_time = Frequency::$frequency[$frequency]['add_time'];
        $date_change = constant($add_time);
        return date( 'Y-m-d H:i:s', strtotime( $date_change, $dateTime->getTimestamp() ) );
    }

    /**
     * @param DsnewsletterClass $newsletter
     * @return void
     */
    private function uploadAttachment($newsletter)
    {
        if ( empty( $_FILES['file']['tmp_name']) && !is_uploaded_file($_FILES['file']['tmp_name'] ) ) {
            $this->_errors[] = $this->l('Can not upload attachment');
            return;
        }
        //upload attachment
        if ($_FILES['file']['size'] > (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024)) {
            $this->errors[] = sprintf(
                $this->l('The file is too large. Maximum size allowed is: %1$d kB. ' .
                    'The file you\'re trying to upload is:  %2$d kB.'),
                (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024),
                number_format(($_FILES['file']['size'] / 1024), 2, '.', '')
            );
            return;
        }
        $attachment_path = $this->getAttachmentPath($newsletter->id);
        if (!file_exists($attachment_path)) {
            mkdir($attachment_path . '/', 0777, true);
        }
        if ( !move_uploaded_file( $_FILES['file']['tmp_name'], $attachment_path . '/' . $_FILES['file']['name'] ) ) {
            $this->_errors[] = $this->l('Failed to copy the file.');
        }
        @unlink($_FILES['file']['tmp_name']);
    }

    /**
     * @param $iso_code
     * @param DsnewsletterClass $newsletter
     * @return void
     */
    private function saveNewsletterFiles($iso_code, $newsletter)
    {
        $html = $this->getMailFilePath(NEWSLETTER, $iso_code, $newsletter->id, FILETYPE_HTML);
        $text = $this->getMailFilePath(NEWSLETTER, $iso_code, $newsletter->id, FILETYPE_TXT);
        if (!file_exists($html)) {
            copy(
                $this->getMailFilePath(TEMPLATE, $iso_code, $newsletter->id_template, FILETYPE_HTML),
                $html
            );
            copy(
                $this->getMailFilePath(TEMPLATE, $iso_code, $newsletter->id_template, FILETYPE_TXT),
                $text
            );
        }
        //save text version
        file_put_contents($text, Tags::addWrapperTagToPlainTextLinks(Tools::getValue('plaintext')));
        //save html version
        if (!$this->saveHtmlToFile(Tags::addWrapperTagToLinks(Tools::getValue('template')), $html)) {
            $this->_errors[] = $this->l('error save html version');
        }
    }
}
