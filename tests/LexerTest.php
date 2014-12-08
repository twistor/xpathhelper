<?php

/**
 * @file
 * Contains \XpathHelper\Tests\LexerTest.
 */

namespace XpathHelper\Tests;

use XpathHelper\Lexer;

class LexerTest extends \PHPUnit_Framework_TestCase {

  /**
   * @dataProvider xpathProvider
   */
  public function test($input, $output) {
    $lexer = new Lexer();
    $this->assertSame($output, $lexer->lex($input));
  }

  public function xpathProvider() {
    return array(
      array('cat', array('cat')),
      array('/cow/barn', array('/', 'cow', '/', 'barn')),
      array('""', array('""')),
      array('/cow/barn[@id = "asdfsaf"]', array('/', 'cow', '/', 'barn', '[', '@id', ' ', '=', ' ', '"asdfsaf"', ']')),
      array('/cow/barn[@id=chair]', array('/', 'cow', '/', 'barn', '[', '@id', '=', 'chair', ']')),
      array('/cow:asdf', array('/', 'cow', ':', 'asdf')),
      array('@cow', array('@cow')),
      array('starts-with(@id, "cat")', array('starts-with', '(', '@id' , ',', ' ', '"cat"', ')')),
      array('starts-with(cat/dog/fire:breather, "cat")', array('starts-with', '(', 'cat', '/', 'dog', '/', 'fire' , ':', 'breather', ',', ' ', '"cat"', ')')),
      array('child::book', array('child', '::', 'book')),
      array("//a[@href='javascript:void(0)']", array('//', 'a', '[', '@href', '=', "'javascript:void(0)'", ']')),
      array('1+1', array('1', '+', '1')),
      array('//a[@id="id"and 1]', array('//', 'a', '[', '@id', '=', '"id"', 'and', ' ', '1', ']')),
      array('0', array('0')),
    );
  }

}
