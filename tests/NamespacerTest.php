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
    $tests = array(
      array('cow', 'x:cow'),
      array('/cow/barn', '/x:cow/x:barn'),
      array('/cow/barn[@id = "asdfsaf"]', '/x:cow/x:barn[@id = "asdfsaf"]'),
      array('/cow/barn [@id = "asdfsaf"]', '/x:cow/x:barn [@id = "asdfsaf"]'),
      array('/cow/barn[@id=chair]', '/x:cow/x:barn[@id=x:chair]'),
      array('/cow:asdf', '/cow:asdf'),
      array('@cow', '@cow'),
      array('starts-with(@id, "cat")', 'starts-with(@id, "cat")'),
      array('starts-with(cat/dog/fire:breather, "cat")', 'starts-with(x:cat/x:dog/fire:breather, "cat")'),
      array('//state[@id = ../city[name="CityName"]/state_id]/name', '//x:state[@id = ../x:city[x:name="CityName"]/x:state_id]/x:name'),
      array('attribute::lang', 'attribute::lang'),
      array('attribute:: lang', 'attribute:: lang'),
      array('attribute ::lang', 'attribute ::lang'),
      array('attribute :: lang', 'attribute :: lang'),
      array('child::book', 'child::x:book'),
      array('child :: book', 'child :: x:book'),
      array('child::*', 'child::*'),
      array('child:: *', 'child:: *'),
      array('child ::*', 'child ::*'),
      array('child :: *', 'child :: *'),
      array('child::text()', 'child::text()'),
      array('child::text   ()', 'child::text   ()'),
      array('ancestor-or-self::book', 'ancestor-or-self::x:book'),
      array('child::*/child::price', 'child::*/child::x:price'),
      array("/asdfasfd[@id = 'a' or @id='b']", "/x:asdfasfd[@id = 'a' or @id='b']"),
      array("id('yui-gen2')/x:div[3]/x:div/x:a[1]", "id('yui-gen2')/x:div[3]/x:div/x:a[1]"),
      array("/descendant::a[@class='buttonCheckout']", "/descendant::x:a[@class='buttonCheckout']"),
      array("//a[@href='javascript:void(0)']", "//x:a[@href='javascript:void(0)']"),
      array('//*/@attribute', '//*/@attribute'),
      array('/descendant::*[attribute::attribute]', '/descendant::*[attribute::attribute]'),
      array('//Event[not(System/Level = preceding::Level) or not(System/Task = preceding::Task)]', '//x:Event[not(x:System/x:Level = preceding::x:Level) or not(x:System/x:Task = preceding::x:Task)]'),
      array("section[@type='cover']/line/@page", "x:section[@type='cover']/x:line/@page"),
      array('/articles/article/*[name()="title" or name()="short"]', '/x:articles/x:article/*[name()="title" or name()="short"]'),
      array("/*/article[@id='2']/*[self::title or self::short]", "/*/x:article[@id='2']/*[self::x:title or self::x:short]"),
      array('not(/asdfasfd/asdfasf//asdfasdf) | /asdfasf/sadfasf/@asdf', 'not(/x:asdfasfd/x:asdfasf//x:asdfasdf) | /x:asdfasf/x:sadfasf/@asdf'),
      array('Ülküdak', 'x:Ülküdak'),
      array('//textarea[@name="style[type]"]|//input[@name="style[type]"]|//select[@name="style[type]"]', '//x:textarea[@name="style[type]"]|//x:input[@name="style[type]"]|//x:select[@name="style[type]"]'),
      array('//a[@id="id"and 1]', '//x:a[@id="id"and 1]'),
      array('//*[@id and@class]', '//*[@id and@class]'),
      array('/or', '/x:or'),
      array('//and', '//x:and'),
      array('a-1', 'x:a-1'),
      array('//element [contains(@id, "1234")and contains(@id, 345)]', '//x:element [contains(@id, "1234")and contains(@id, 345)]'),
    );

    // Math related.
    foreach (array('+', '-', '*', '=', '!=', '<', '>', '<=', '>=') as $op) {
      $tests[] = array("1{$op}2", "1{$op}2");
      $tests[] = array("1 {$op}2", "1 {$op}2");
      $tests[] = array("1{$op} 2", "1{$op} 2");
      $tests[] = array("1 {$op} 2", "1 {$op} 2");
    }

    foreach (array('and', 'or', 'mod', 'div') as $op) {
      $tests[] = array("1{$op} 2", "1{$op} 2");
      $tests[] = array("1 {$op} 2", "1 {$op} 2");
    }

    return $tests;
  }

}
