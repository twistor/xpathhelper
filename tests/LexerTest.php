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
    return [
      ['cat', ['cat']],
      ['/cow/barn', ['/', 'cow', '/', 'barn']],
      ['""', ['""']],
      ['/cow/barn[@id = "asdfsaf"]', ['/', 'cow', '/', 'barn', '[', '@id', ' ', '=', ' ', '"asdfsaf"', ']']],
      ['/cow/barn[@id=chair]', ['/', 'cow', '/', 'barn', '[', '@id', '=', 'chair', ']']],
      ['/cow:asdf', ['/', 'cow', ':', 'asdf']],
      ['@cow', ['@cow']],
      ['starts-with(@id, "cat")', ['starts-with', '(', '@id' , ',', ' ', '"cat"', ')']],
      ['starts-with(cat/dog/fire:breather, "cat")', ['starts-with', '(', 'cat', '/', 'dog', '/', 'fire' , ':', 'breather', ',', ' ', '"cat"', ')']],
      ['child::book', ['child', '::', 'book']],
      ["//a[@href='javascript:void(0)']", ['//', 'a', '[', '@href', '=', "'javascript:void(0)'", ']']],
      ['1+1', ['1', '+', '1']],
      ['//a[@id="id"and 1]', ['//', 'a', '[', '@id', '=', '"id"', 'and', ' ', '1', ']']],
      ['0', ['0']],
    ];
  }

}
