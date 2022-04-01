<?php
include(dirname(__FILE__).'/../../../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../dsnewsletter.php');

// Insert into database
$id = (Tools::getValue('id_dsnewsletter') ? Dsnewsletter::encryptDecryptInfo(Tools::getValue('id_dsnewsletter'), 'decrypt') : null);

if ($id && ValidateCore::isInt($id)) {
    Db::getInstance()->Execute("UPDATE `"._DB_PREFIX_."dsnewsletter` SET open = open + 1 WHERE id_dsnewsletter = '".(int)$id."'");
}

header('Content-type: image/gif');
echo chr(71).chr(73).chr(70).chr(56).chr(57).chr(97).
      chr(1).chr(0).chr(1).chr(0).chr(128).chr(0).
      chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).
      chr(33).chr(249).chr(4).chr(1).chr(0).chr(0).
      chr(0).chr(0).chr(44).chr(0).chr(0).chr(0).chr(0).
      chr(1).chr(0).chr(1).chr(0).chr(0).chr(2).chr(2).
      chr(68).chr(1).chr(0).chr(59);
