<?php
/**
 * 2016 Smart Soft.
 *
 * @author    Marcin Kubiak
 * @copyright Smart Soft
 * @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__) . '/../../dsnewsletter.php';

class DsnewsletterTest extends TestCase
{
    const DSNEWSLETTER = 'Dsnewsletter';
    public $ds;

    public function setup()
    {
        $this->ds = $this->getMockBuilder(self::DSNEWSLETTER)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockShort($methods)
    {
        return $this->getMock(self::DSNEWSLETTER, $methods, [], '', false);
    }

    public function testGetCustomers_Should_Return_False_When_Target_Is_None()
    {
        //arrange
        $sql = false;

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->never())
            ->method('DbExecuteS');

        $list = new \DslistClass();
        $list->target_customer = 0; //None

        //act
        $mock->getCustomers($list);
    }


    public function testGetCustomers_Check_Query_When_Target_Is_All()
    {
        //arrange
        $sql = 'SELECT c.id_customer, c.email FROM `_DB_PREFIX_customer` as c WHERE 1 GROUP BY c.id_customer';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_customer = 1; //ALL

        //act
        $mock->getCustomers($list);
    }

    public function testGetCustomers_Check_Query_When_Target_Is_Newsletter_Subscribers()
    {
        //arrange
        $sql = 'SELECT c.id_customer, c.email FROM `_DB_PREFIX_customer` as c' .
            ' WHERE 1 AND c.newsletter = 1 GROUP BY c.id_customer';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_customer = 2; //NEWSLETTER_SUBSCRIBERS

        //act
        $mock->getCustomers($list);

    }

    public function testGetCustomers_Check_Query_When_Target_Is_Customers_With_Orders()
    {
        //arrange
        $sql = 'SELECT c.id_customer, c.email FROM `_DB_PREFIX_customer` as c' .
            ' INNER JOIN _DB_PREFIX_orders AS o ON (c.id_customer = o.id_customer) WHERE 1 GROUP BY c.id_customer';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_customer = 3; //NEWSLETTER_SUBSCRIBERS

        //act
        $mock->getCustomers($list);
    }

    public function testGetCustomers_Check_Query_When_Target_Is_Customers_With_Cart()
    {
        //arrange
        $sql = 'SELECT c.id_customer, c.email FROM `_DB_PREFIX_customer` as c' .
            ' INNER JOIN _DB_PREFIX_cart AS ca ON (c.id_customer = ca.id_customer) WHERE 1 GROUP BY c.id_customer';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_customer = 4; //NEWSLETTER_SUBSCRIBERS

        //act
        $mock->getCustomers($list);
    }


    public function testGetCustomers_Check_Query_When_Target_Is_Customers_With_Abandoned_Cart()
    {
        //arrange
        $sql = 'SELECT c.id_customer, c.email FROM `_DB_PREFIX_customer` as c' .
            ' INNER JOIN _DB_PREFIX_cart AS ca ON (c.id_customer = ca.id_customer)' .
            ' WHERE 1 AND ca.`date_add` BETWEEN DATE_SUB(DATE(NOW()), INTERVAL 1 DAY)' .
            ' AND DATE_SUB(NOW(), INTERVAL 0 HOUR) AND NOT EXISTS (SELECT id_order FROM `_DB_PREFIX_orders`' .
            ' WHERE `_DB_PREFIX_orders`.id_cart = ca.id_cart) GROUP BY c.id_customer';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_customer = 5; //customers_with_abandoned_cart
        $list->ab_day = 1; //customers_with_abandoned_cart
        $list->ab_hour = 0; //customers_with_abandoned_cart

        //act
        $mock->getCustomers($list);
    }


    public function testGetCustomers_Check_Query_When_Target_Is_Selected_Customers()
    {
        //arrange
        $sql = 'SELECT c.id_customer, c.email FROM `_DB_PREFIX_customer` as c' .
            ' WHERE 1 AND c.id_customer IN ("1","2") GROUP BY c.id_customer';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_customer = 6; //selected customers
        $list->selected_customer = '1,2'; //selected customers

        //act
        $mock->getCustomers($list);
    }

    public function testGetCustomers_Check_Query_When_Target_Is_Customers_Group()
    {
        //arrange
        $sql = 'SELECT c.id_customer, c.email FROM `_DB_PREFIX_customer` as c' .
            ' WHERE 1 AND c.id_default_group = 3 GROUP BY c.id_customer';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_customer = 7; //selected customers
        $list->group = '3'; //selected customers

        //act
        $mock->getCustomers($list);
    }

    public function testGetCustomers_Check_Query_When_Age_Is_Selected()
    {
        //arrange
        $sql = 'SELECT c.id_customer, c.email FROM `_DB_PREFIX_customer` as c' .
            ' WHERE 1 AND c.newsletter = 1 AND c.birthday > 1970 GROUP BY c.id_customer';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('pSQL', 'DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $mock->expects($this->once())
            ->method('pSQL')
            ->will($this->returnValue('>'));

        $list = new \DslistClass();
        $list->target_customer = 2; //age customers
        $list->age_value = '52'; //age customers
        $list->age_compare = '>'; //age customers

        //act
        $mock->getCustomers($list);
    }

    public function testGetCustomers_Check_Query_When_Gender()
    {
        //arrange
        $sql = 'SELECT c.id_customer, c.email FROM `_DB_PREFIX_customer` as c' .
            ' WHERE 1 AND c.newsletter = 1 AND c.id_gender = 1 GROUP BY c.id_customer';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('pSQL', 'DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_customer = 2;
        $list->gender = 1;

        //act
        $mock->getCustomers($list);
    }

    public function testGetCustomers_Check_Query_When_Lang()
    {
        //arrange
        $sql = 'SELECT c.id_customer, c.email FROM `_DB_PREFIX_customer` as c' .
            ' WHERE 1 AND c.newsletter = 1 AND c.id_lang IN ("1") GROUP BY c.id_customer';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('pSQL', 'DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_customer = 2;
        $list->lang_customer = '1';

        //act
        $mock->getCustomers($list);
    }

    public function testGetNews_Should_Return_False_When_Target_Is_None()
    {
        //arrange
        $sql = false;

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->never())
            ->method('DbExecuteS');

        $list = new \DslistClass();
        $list->target_news = 0; //None

        //act
        $mock->getNews($list);
    }

    public function testGetNews_Should_Return_False_When_Target_Is_Selected_News()
    {
        //arrange
        $sql = 'SELECT id, email FROM `_DB_PREFIX_emailsubscription`' .
            ' WHERE 1 AND id IN ("1") GROUP BY id';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_news = 4; //None
        $list->selected_news = 1; //None

        //act
        $mock->getNews($list);
    }

    public function testGetNews_Should_Return_False_When_Target_Subscribers()
    {
        //arrange
        $sql = 'SELECT id, email FROM `_DB_PREFIX_emailsubscription`' .
            ' WHERE 1 AND active IN ("1") GROUP BY id';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_news = 1; //Subscribers

        //act
        $mock->getNews($list);
    }

    public function testGetNews_Should_Return_False_When_Target_Unsubscribers()
    {
        //arrange
        $sql = 'SELECT id, email FROM `_DB_PREFIX_emailsubscription`' .
            ' WHERE 1 AND active IN ("0") GROUP BY id';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_news = 2; // Unsubscribers

        //act
        $mock->getNews($list);
    }

    public function testGetNews_Should_Return_False_When_Target_Subscribers_And_Unsubscribers()
    {
        //arrange
        $sql = 'SELECT id, email FROM `_DB_PREFIX_emailsubscription` WHERE 1';

        $mock = $this->createPartialMock(self::DSNEWSLETTER, array('DbExecuteS'));
        $mock->expects($this->once())
            ->method('DbExecuteS')
            ->with($this->equalTo($sql));

        $list = new \DslistClass();
        $list->target_news = 3; // Subscribers and Unsubscribers

        //act
        $mock->getNews($list);
    }
}
