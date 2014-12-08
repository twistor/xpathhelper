<?php

/**
 * @file
 * Contains \XpathHelper\Tests\NamespacerTest.
 */

namespace XpathHelper\Tests;

use XpathHelper\Namespacer;

class NamespacerTest extends \PHPUnit_Framework_TestCase {

  /**
   * @dataProvider xpathProvider
   */
  public function test($input, $output) {
    $this->assertSame($output, Namespacer::prefix($input));
  }

  public function testCache() {
    $this->assertSame('x:a', Namespacer::prefix('a'));
    $this->assertSame('x:a', Namespacer::prefix('a'));

    $this->assertSame('*[local-name() = "a"]', Namespacer::localize('a'));
    $this->assertSame('*[local-name() = "a"]', Namespacer::localize('a'));
  }

  public function xpathProvider() {
    $tests = [
      ['cow', 'x:cow'],
      ['/cow/barn', '/x:cow/x:barn'],
      ['/cow/barn[@id = "asdfsaf"]', '/x:cow/x:barn[@id = "asdfsaf"]'],
      ['/cow/barn [@id = "asdfsaf"]', '/x:cow/x:barn [@id = "asdfsaf"]'],
      ['/cow/barn[@id=chair]', '/x:cow/x:barn[@id=x:chair]'],
      ['/cow:asdf', '/cow:asdf'],
      ['@cow', '@cow'],
      ['starts-with(@id, "cat")', 'starts-with(@id, "cat")'],
      ['starts-with(cat/dog/fire:breather, "cat")', 'starts-with(x:cat/x:dog/fire:breather, "cat")'],
      ['//state[@id = ../city[name="CityName"]/state_id]/name', '//x:state[@id = ../x:city[x:name="CityName"]/x:state_id]/x:name'],
      ['attribute::lang', 'attribute::lang'],
      ['attribute:: lang', 'attribute:: lang'],
      ['attribute ::lang', 'attribute ::lang'],
      ['attribute :: lang', 'attribute :: lang'],
      ['child::book', 'child::x:book'],
      ['child :: book', 'child :: x:book'],
      ['child::*', 'child::*'],
      ['child:: *', 'child:: *'],
      ['child ::*', 'child ::*'],
      ['child :: *', 'child :: *'],
      ['child::text()', 'child::text()'],
      ['child::text   ()', 'child::text   ()'],
      ['ancestor-or-self::book', 'ancestor-or-self::x:book'],
      ['child::*/child::price', 'child::*/child::x:price'],
      ["/asdfasfd[@id = 'a' or @id='b']", "/x:asdfasfd[@id = 'a' or @id='b']"],
      ["id('yui-gen2')/x:div[3]/x:div/x:a[1]", "id('yui-gen2')/x:div[3]/x:div/x:a[1]"],
      ["/descendant::a[@class='buttonCheckout']", "/descendant::x:a[@class='buttonCheckout']"],
      ["//a[@href='javascript:void(0)']", "//x:a[@href='javascript:void(0)']"],
      ['//*/@attribute', '//*/@attribute'],
      ['/descendant::*[attribute::attribute]', '/descendant::*[attribute::attribute]'],
      ['//Event[not(System/Level = preceding::Level) or not(System/Task = preceding::Task)]', '//x:Event[not(x:System/x:Level = preceding::x:Level) or not(x:System/x:Task = preceding::x:Task)]'],
      ["section[@type='cover']/line/@page", "x:section[@type='cover']/x:line/@page"],
      ['/articles/article/*[name()="title" or name()="short"]', '/x:articles/x:article/*[name()="title" or name()="short"]'],
      ["/*/article[@id='2']/*[self::title or self::short]", "/*/x:article[@id='2']/*[self::x:title or self::x:short]"],
      ['not(/asdfasfd/asdfasf//asdfasdf) | /asdfasf/sadfasf/@asdf', 'not(/x:asdfasfd/x:asdfasf//x:asdfasdf) | /x:asdfasf/x:sadfasf/@asdf'],
      ['Ülküdak', 'x:Ülküdak'],
      ['//textarea[@name="style[type]"]|//input[@name="style[type]"]|//select[@name="style[type]"]', '//x:textarea[@name="style[type]"]|//x:input[@name="style[type]"]|//x:select[@name="style[type]"]'],
      ['//a[@id="id"and 1]', '//x:a[@id="id"and 1]'],
      ['//*[@id and@class]', '//*[@id and@class]'],
      ['/or', '/x:or'],
      ['//and', '//x:and'],
      ['a-1', 'x:a-1'],
      ['//element [contains(@id, "1234")and contains(@id, 345)]', '//x:element [contains(@id, "1234")and contains(@id, 345)]'],
    ];

    // Math related.
    foreach (['+', '-', '*', '=', '!=', '<', '>', '<=', '>='] as $op) {
      $tests[] = ["1{$op}2", "1{$op}2"];
      $tests[] = ["1 {$op}2", "1 {$op}2"];
      $tests[] = ["1{$op} 2", "1{$op} 2"];
      $tests[] = ["1 {$op} 2", "1 {$op} 2"];
    }

    foreach (['and', 'or', 'mod', 'div'] as $op) {
      $tests[] = ["1{$op} 2", "1{$op} 2"];
      $tests[] = ["1 {$op} 2", "1 {$op} 2"];
    }

    return $tests;
  }

}
