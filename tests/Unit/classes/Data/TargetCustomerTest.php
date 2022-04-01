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

require_once dirname(__FILE__) . '/../../../../classes/Data/TargetCustomer.php';

class TargetCustomerTest extends TestCase
{
    public function testTargetEnumValues()
    {
        //assert
        $this->assertSame(TargetCustomer::NONE, 0);
        $this->assertSame(TargetCustomer::ALL_CUSTOMERS, 1);
        $this->assertSame(TargetCustomer::NEWSLETTER_SUBSCRIBERS, 2);
        $this->assertSame(TargetCustomer:: CUSTOMERS_WITH_ORDER, 3);
        $this->assertSame(TargetCustomer:: CUSTOMERS_WITH_CART, 4);
        $this->assertSame(TargetCustomer:: CUSTOMERS_WITH_ABANDONED_CART, 5);
        $this->assertSame(TargetCustomer:: SELECTED_CUSTOMERS, 6);
        $this->assertSame(TargetCustomer:: CUSTOMERS_GROUPS, 7);
    }
}
