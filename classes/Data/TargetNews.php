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

class TargetNews extends BaseEnum
{
    const NONE = 0;
    const SUBSCRIBERS = 1;
    const UNSUBSCRIBERS = 2;
    const SUBSCRIBERS_AND_UNSUBSCRIBES = 3;
    const SELECTED_SUBSCRIBERS = 4;
}
