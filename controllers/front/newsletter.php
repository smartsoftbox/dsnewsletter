<?php
/**
 * 2019 Smart Soft.
 *
 *  @author    Marcin Kubiak
 *  @copyright Smart Soft
 *  @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

class DsnewsletterNewsletterModuleFrontController extends ModuleFrontController
{
    private $message;

    /**
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        if (Tools::substr(Tools::encrypt('/dsnewsletter/token'), 0, 10) != Tools::getValue('token')
            || !Module::isInstalled('dsnewsletter')) {
            Tools::redirect('index.php');
            die();
        }

        $id_subscriber = (int)Dsnewsletter::decryptText( Tools::getValue('ids') );
        $id_customer = (int)Dsnewsletter::decryptText( Tools::getValue('idc') );
        $id_lang = (int)Dsnewsletter::decryptText( Tools::getValue('idl') );
        $id_stats = (int)Dsnewsletter::decryptText( Tools::getValue('idst') );
        $action = (string)Dsnewsletter::decryptText( Tools::getValue('action') );

        if($action === SUBSCRIBE || $action === UNSUBSCRIBE) {
            $sub = ($action === SUBSCRIBE ? 1 : 0);
            if ($id_customer) {
                $this->changeCustomerSub($id_customer, $sub);
            } elseif ($id_subscriber) {
                $this->changeNewsletterSub($id_subscriber, $sub);
            }
        }

        if (Tools::getValue('href')) {
            $this->addClick($id_stats);  //count click numbers
            Tools::redirect(urldecode(Tools::getValue('href')));  // redirect to given url
        }
    }


    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign('message', $this->message);

        $template = 'newsletter.tpl';
        if (_PS_VERSION_ > 1.6) {
            $template = 'module:dsnewsletter/views/templates/front/' . $template;
        }

        $this->setTemplate($template);
    }

    /**
     * @param $id_customer
     * @param $sub
     * @return void
     * @throws PrestaShopException
     */
    private function changeCustomerSub($id_customer, $sub)
    {
        $customer = new Customer((int)$id_customer);
        if ($customer->id) {
            $customer->newsletter = (int)$sub;
            $customer->save();
            $this->getConfirmationMessage((int)$sub);
        }
    }

    private function getConfirmationMessage($sub)
    {
        $sub = ($sub ? 'subscribe to' : 'unsubscribe from');
        $this->message = 'You are successfully ' . $sub . ' newsletter.';
    }

    /**
     * @param $id_subscriber
     * @param $sub
     * @return void
     */
    private function changeNewsletterSub($id_subscriber, $sub)
    {
        $news_table = (_PS_VERSION_ > 1.6 ? 'emailsubscription' : 'newsletter');

        Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . pSQL($news_table) . ' SET active = ' . (int)$sub .
            ' WHERE id = ' . (int)$id_subscriber
        );
        $this->getConfirmationMessage($sub);
    }

    /**
     * @param $id_stats
     * @return void
     */
    private function addClick($id_stats)
    {
        if (!isset($_COOKIE["is_already_click"])) {
            if ($id_stats && Validate::isInt($id_stats)) {
                try {
                    $stats = new DsstatsClass($id_stats);
                    $stats->click += 1;
                    $stats->save();
                } catch (PrestaShopException $e) {
                }
                setcookie("is_already_click", 1, strtotime('+1 day'));
            }
        }
    }
}
