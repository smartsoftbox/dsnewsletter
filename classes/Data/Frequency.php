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
    private $cron_hour = array(
        array('name' => 'every hour', 'value' => '*'),
        array('name' => 'every six', 'value' => '*/6'),
        array('name' => 'every twelve', 'value' => '0,12'),
        array('name' => '1 am', 'value' => '1'),
        array('name' => '2 am', 'value' => '2'),
        array('name' => '3 am', 'value' => '3'),
        array('name' => '4 am', 'value' => '4'),
        array('name' => '5 am', 'value' => '5'),
        array('name' => '6 am', 'value' => '6'),
        array('name' => '7 am', 'value' => '7'),
        array('name' => '8 am', 'value' => '8'),
        array('name' => '9 am', 'value' => '9'),
        array('name' => '10 am', 'value' => '10'),
        array('name' => '11 am', 'value' => '11'),
        array('name' => '12 am', 'value' => '0'),
        array('name' => '1 pm', 'value' => '13'),
        array('name' => '2 pm', 'value' => '14'),
        array('name' => '3 pm', 'value' => '15'),
        array('name' => '4 pm', 'value' => '16'),
        array('name' => '5 pm', 'value' => '17'),
        array('name' => '6 pm', 'value' => '18'),
        array('name' => '7 pm', 'value' => '19'),
        array('name' => '8 pm', 'value' => '20'),
        array('name' => '9 pm', 'value' => '21'),
        array('name' => '10 pm', 'value' => '22'),
        array('name' => '11 pm', 'value' => '23'),
        array('name' => '12 pm', 'value' => '12')
    );

    private $cron_day = array(
        array('name' => 'every day', 'value' => '*'),
        array('name' => '1', 'value' => '1'),
        array('name' => '2', 'value' => '2'),
        array('name' => '3', 'value' => '3'),
        array('name' => '4', 'value' => '4'),
        array('name' => '5', 'value' => '5'),
        array('name' => '6', 'value' => '6'),
        array('name' => '7', 'value' => '7'),
        array('name' => '8', 'value' => '8'),
        array('name' => '9', 'value' => '9'),
        array('name' => '10', 'value' => '10'),
        array('name' => '11', 'value' => '11'),
        array('name' => '12', 'value' => '12'),
        array('name' => '13', 'value' => '13'),
        array('name' => '14', 'value' => '14'),
        array('name' => '15', 'value' => '15'),
        array('name' => '16', 'value' => '16'),
        array('name' => '17', 'value' => '17'),
        array('name' => '18', 'value' => '18'),
        array('name' => '19', 'value' => '19'),
        array('name' => '20', 'value' => '20'),
        array('name' => '21', 'value' => '21'),
        array('name' => '22', 'value' => '22'),
        array('name' => '23', 'value' => '23'),
        array('name' => '24', 'value' => '24'),
        array('name' => '25', 'value' => '25'),
        array('name' => '26', 'value' => '26'),
        array('name' => '27', 'value' => '27'),
        array('name' => '28', 'value' => '28'),
        array('name' => '29', 'value' => '29'),
        array('name' => '30', 'value' => '30'),
        array('name' => '31', 'value' => '31')
    );

    private $cron_month = array(
        array('name' => 'every month', 'value' => '*'),
        array('name' => 'every six month', 'value' => '1,7'),
        array('name' => 'january', 'value' => '1'),
        array('name' => 'february', 'value' => '2'),
        array('name' => 'march', 'value' => '3'),
        array('name' => 'april', 'value' => '4'),
        array('name' => 'may', 'value' => '5'),
        array('name' => 'june', 'value' => '6'),
        array('name' => 'july', 'value' => '7'),
        array('name' => 'august', 'value' => '8'),
        array('name' => 'september', 'value' => '9'),
        array('name' => 'october', 'value' => '10'),
        array('name' => 'november', 'value' => '11'),
        array('name' => 'december', 'value' => '12')
    );

    private $cron_week = array(
        array('name' => 'every day', 'value' => '*'),
        array('name' => 'every weekday', 'value' => '1-5'),
        array('name' => 'every weekend', 'value' => '6,0'),
        array('name' => 'sunday', 'value' => '0'),
        array('name' => 'monday', 'value' => '1'),
        array('name' => 'tuesday', 'value' => '2'),
        array('name' => 'wednesday', 'value' => '3'),
        array('name' => 'thursday', 'value' => '4'),
        array('name' => 'friday', 'value' => '5'),
        array('name' => 'saturday', 'value' => '6')
    );

    /**
     * @return array
     */
    public function getCronHour()
    {
        return $this->cron_hour;
    }

    /**
     * @return array
     */
    public function getCronDay()
    {
        return $this->cron_day;
    }

    /**
     * @return array
     */
    public function getCronMonth()
    {
        return $this->cron_month;
    }

    /**
     * @return array
     */
    public function getCronWeek()
    {
        return $this->cron_week;
    }
}
