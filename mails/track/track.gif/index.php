<?php
/**
 * 2022 Smart Soft.
 *
 *  @author    Marcin Kubiak
 *  @copyright Smart Soft
 *  @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

include(dirname(__FILE__).'/../../../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../dsnewsletter.php');

// Insert into database
$id = (Tools::getValue('idst') ? Dsnewsletter::decryptText(Tools::getValue('idst')) : null);

if ($id && ValidateCore::isInt($id)) {
    Db::getInstance()->Execute("UPDATE `"._DB_PREFIX_."dsstats` SET open = open + 1 WHERE id_dsstats = " . (int)$id);
}

header('Content-type: image/gif');
echo chr(71).chr(73).chr(70).chr(56).chr(57).chr(97).
      chr(1).chr(0).chr(1).chr(0).chr(128).chr(0).
      chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).
      chr(33).chr(249).chr(4).chr(1).chr(0).chr(0).
      chr(0).chr(0).chr(44).chr(0).chr(0).chr(0).chr(0).
      chr(1).chr(0).chr(1).chr(0).chr(0).chr(2).chr(2).
      chr(68).chr(1).chr(0).chr(59);
