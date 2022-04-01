<?php
/**
 * 2016 Smart Soft.
 *
 * @author    Marcin Kubiak
 * @copyright Smart Soft
 * @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

class Frequency
{
    public static $frequency = array(
        array('name' => 'MANUALLY'),
        array('name' => 'ONE_TIME'),
        array('name' => 'EVERY_HOUR', 'add_time' => '+1 hour'),
        array('name' => 'EVERY_DAY', 'add_time' => '+1 day'),
        array('name' => 'EVERY_WEEK', 'add_time' => '+1 week'),
        array('name' => 'EVERY_TWO_WEEKS', 'add_time' => '+2 weeks'),
        array('name' => 'EVERY_MONTH', 'add_time' => '+1 month'),
        array('name' => 'EVERY_YEAR', 'add_time' => '+1 year')
    );

    public static function getForSelect()
    {
        array_walk(self::$frequency, function (&$value, $key) {
            $value['id'] = $key;
        });
        return self::$frequency;
    }
}
