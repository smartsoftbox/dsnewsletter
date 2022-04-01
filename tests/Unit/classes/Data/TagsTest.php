<?php
/**
 * 2020 Smart Soft.
 *
 * @author    Marcin Kubiak
 * @copyright Smart Soft
 * @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__) . '/../../../../classes/Data/Tags.php';

class TagsTest extends TestCase
{
    public function testRemoveBrackets()
    {
        $tags = new Tags('', 1, 1, 1, 1, 1, 1);

        $result = $tags->removeBrackets('{TEST}');

        $this->assertSame('TEST', $result);
    }

    public function testProductPattern()
    {
        $tags = new Tags('', 1, 1, 1, 1, 1, 1);
        $correct_pattern = '{product\|new\:[0-99]\|name\:[0-99]}';

        $pattern = $tags->getProductPattern('new', 'name', 100);

        $this->assertSame($pattern, $correct_pattern);
    }

    public function testGetTagsFromContent_Should_Return_Shop_Name()
    {
        $content = '{shop_name}';
        $tags = new Tags($content, 1, 1, 1, 1, 1, 1);

        $pattern = $tags->getTagsFromContent($content);

        $this->assertSame($content, $pattern[0]);
    }

    public function testGetTagsFromContent_Should_Not_Return_Invalid_Tag()
    {
        $content = '{invalid}';
        $content .= 'invalid}';
        $tags = new Tags($content, 1, 1, 1, 1, 1, 1);

        $pattern = $tags->getTagsFromContent($content);

        $this->assertSame(false, isset($pattern[0]));
    }

    public function testGetTagsFromContent_Should_Return_Valid_Product_Tag()
    {
        $content = '{product|new:1|name}';
        $tags = new Tags($content, 1, 1, 1, 1, 1, 1);

        $pattern = $tags->getTagsFromContent($content);

        $this->assertSame($content, $pattern[0]);
    }

    public function testGetTagsFromContent_Should_Return_Valid_Product_Tag_With_Limit()
    {
        $content = '{product|new:1|name:100}';
        $tags = new Tags($content, 1, 1, 1, 1, 1, 1);

        $pattern = $tags->getTagsFromContent($content);

        $this->assertSame($content, $pattern[0]);
    }

    public function testGetTagsFromContent_Should_Not_Return_Invalid_Product_Tag()
    {
        $content = '{product|invalid:1|name:100}';
        $content .= '{product|invalid|name:100}';
        $content .= '{invalid|new:1|name:100}';
        $tags = new Tags($content, 1, 1, 1, 1, 1, 1);

        $pattern = $tags->getTagsFromContent($content);

        $this->assertSame(0, count($pattern));
    }
}
