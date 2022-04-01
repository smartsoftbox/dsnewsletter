<?php
/**
 * 2016 Smart Soft.
 *
 * @author    Marcin Kubiak
 * @copyright Smart Soft
 * @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

require_once "BasicEnum.php";

class TargetCustomer extends BaseEnum
{
    const NONE = 0;
    const ALL_CUSTOMERS = 1;
    const NEWSLETTER_SUBSCRIBERS = 2;
    const CUSTOMERS_WITH_ORDER = 3;
    const CUSTOMERS_WITH_CART = 4;
    const CUSTOMERS_WITH_ABANDONED_CART = 5;
    const SELECTED_CUSTOMERS = 6;
    const CUSTOMERS_GROUPS = 7;
}
