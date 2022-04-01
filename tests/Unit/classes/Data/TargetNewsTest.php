<?php
/**
 * 2016 Smart Soft.
 *
 * @author    Marcin Kubiak
 * @copyright Smart Soft
 * @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__) . '/../../../../classes/Data/TargetNews.php';

class TargetNewsTest extends TestCase
{
    public function testTargetEnumValues()
    {
        //assert
        $this->assertSame(TargetNews::NONE, 0);
        $this->assertSame(TargetNews::SUBSCRIBERS, 1);
        $this->assertSame(TargetNews::UNSUBSCRIBERS, 2);
        $this->assertSame(TargetNews::SUBSCRIBERS_AND_UNSUBSCRIBES, 3);
        $this->assertSame(TargetNews::SELECTED_SUBSCRIBERS, 4);
    }
}
