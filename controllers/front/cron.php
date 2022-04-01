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
        $token = Configuration::get('DSNEWSLETTER_SECURE_KEY');

        if (empty($token) or $token !== Tools::getValue('token') or !$this->module->active) {
            Tools::redirect('index.php');
        }

        parent::initContent();

        $this->module->cronTask();

        die();
    }
}
