<?php

/**
 * @file
 * Contains \XpathHelper\Lexer.
 */

namespace XpathHelper;

/**
 * Turns an XPath expression into a list of tokens.
 */
class Lexer {

  /**
   * The XPath expression being lexed.
   *
   * @var string
   */
  protected $expression;

  /**
   * The current position in the expression.
   *
   * @var int
   */
  protected $cursor;

  /**
   * The length of the XPath expression.
   *
   * @var int
   */
  protected $length;

  /**
   * Characters that represent word boundaries.
   *
   * @var array
   */
  protected static $wordBoundaries = array(
    '[' => TRUE,
    ']' => TRUE,
    '=' => TRUE,
    '(' => TRUE,
    ')' => TRUE,
    '.' => TRUE,
    '<' => TRUE,
    '>' => TRUE,
    '*' => TRUE,
    '+' => TRUE,
    // Used in element names and functions. It's easier to just make a special
    // case in the parser than to have the minus be a word boundary.
    // '-' => TRUE,
    '!' => TRUE,
    '|' => TRUE,
    ',' => TRUE,
    ' ' => TRUE,
    '"' => TRUE,
    "'" => TRUE,
    ':' => TRUE,
    '::' => TRUE,
    '/' => TRUE,
    '//' => TRUE,
    '@' => TRUE,
  );

  /**
   * Lexes an XPath expression.
   *
   * @param string $expression
   *   An XPath expression.
   *
   * @return []string
   *   A list of tokens from the XPath expression.
   */
  public function lex($expression) {
    $this->expression = $expression;
    $this->length = strlen($expression);
    $this->cursor = 0;

    $tokens = array();
    while (TRUE) {
      $token = $this->readToken();
      if ($token === '') {
        break;
      }
      $tokens[] = $token;
    }

    return $tokens;
  }

  /**
   * Determines if a token is boundary for a word.
   *
   * @param string $token
   *   The token.
   *
   * @return bool
   *   Returns true if the token is a word boundary, and false if not.
   */
  public static function isWordBoundary($token) {
    return isset(static::$wordBoundaries[$token]);
  }

  /**
   * Reads the next token from the expression.
   *
   * @return string
   *   The next token, or an empty string on completion.
   */
  protected function readToken() {
    while ($this->cursor < $this->length) {
      $char = $this->expression[$this->cursor];

      if ($char === '/') {
        return $this->readOneOrTwoSlashes($char);
      }

      if ($char === '"' || $char === "'") {
        return $this->consumeQuotes($char);
      }

      if ($char === ':') {
        return $this->readNamespaceOrAxis();
      }

      if ($char === '@') {
        return $this->readAttribute();
      }

      if ($this->isWordBoundary($char)) {
        $this->cursor++;
        return $char;
      }

      return $this->readWord();
    }

    return '';
  }

  /**
   * Reads the next word from the expression.
   *
   * A word is considered anything that isn't a word boundary.
   *
   * @return string
   *   The next word.
   */
  protected function readWord() {
    $word = '';

    while ($this->cursor < $this->length) {
      $char = $this->expression[$this->cursor];

      // Found a boundary.
      if ($this->isWordBoundary($char)) {
        break;
      }

      $word .= $char;
      $this->cursor++;
    }

    return $word;
  }

  /**
   * Reads a quoted string from an XPath expression.
   *
   * @param string $start_quote
   *   The character that started the quoted string.
   *
   * @return string
   *   The quoted string.
   */
  protected function consumeQuotes($start_quote) {
    $output = $start_quote;
    do {
      $next_char = $this->readNextChar();
      $output .= $next_char;
    } while ($next_char !== '' && $next_char !== $start_quote);

    $this->cursor++;
    return $output;
  }

  /**
   * Reads a namespace token or an axis token.
   *
   * @return string
   *   Either a namespace separator or an axis separator. One or two colons.
   */
  protected function readNamespaceOrAxis() {
    if ($this->readNextChar() === ':') {
      $this->cursor++;
      return '::';
    }
    return ':';
  }

  /**
   * Reads on or two slashes.
   *
   * @return string
   *  Returns / or //.
   */
  protected function readOneOrTwoSlashes() {
    if ($this->readNextChar() === '/') {
      $this->cursor++;
      return '//';
    }
    return '/';
  }

  /**
   * Reads a shorthand attribute.
   *
   * @return string
   *   An attribute string starting with @.
   */
  protected function readAttribute() {
    $this->cursor++;
    return '@' . $this->readWord();
  }

  /**
   * Returns the next character advancing the cursor.
   *
   * @return string
   *   The next character.
   */
  protected function readNextChar() {
    $this->cursor++;
    return isset($this->expression[$this->cursor]) ? $this->expression[$this->cursor] : '';
  }

}
