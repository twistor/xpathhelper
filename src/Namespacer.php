<?php

/**
 * @file
 * Contains \XpathHelper\Namespacer.
 */

namespace XpathHelper;

/**
 * Pseudo-parser for XPath expressions.
 *
 * When a DOMDocument has a default namespace it's not possible to parse it
 * using XPath unless the namespace is registered as something else.
 *
 * This takes an XPath expression, /div/li/[@id = "list"] and turns it into
 * /prefix:div/prefix:li[@id = "list"]
 */
class Namespacer {

  /**
   * The prefix to assign.
   *
   * @var string
   */
  protected $prefix;

  /**
   * The list of tokens from the XPath expression.
   *
   * @var []string
   */
  protected $tokens;

  /**
   * The current position in the token list.
   *
   * @var int
   */
  protected $cursor = 0;

  /**
   * A cache of rewritten expressions.
   *
   * @var array
   */
  protected static $cache = array();

  /**
   * Strings that have operational meaning and shouldn't be namespaced.
   *
   * @var array
   */
  protected static $operators = array(
    'or' => TRUE,
    'and' => TRUE,
    'div' => TRUE,
    'mod' => TRUE,
  );

  /**
   * Prefixes an XPath expression.
   *
   * Converts an expression from //div/a to //x:div/x:a.
   *
   * @param string $xpath
   *   The XPath expression to prefix.
   * @param string $prefix
   *   (optional) The prefix to use. Defaults to "x".
   *
   * @return string
   *   The prefixed XPath expression.
   */
  public static function prefix($xpath, $prefix = 'x') {
    if (!isset(static::$cache[$prefix][$xpath])) {
      $parser = new static($xpath, $prefix, new Lexer());
      static::$cache[$prefix][$xpath] = $parser->parse();
    }

    return static::$cache[$prefix][$xpath];
  }

  /**
   * Localizes an XPath expression.
   *
   * Converts an expression from //div/a to
   * //*[local-name() "div"]/*[local-name() "a"].
   *
   * @param string $xpath
   *   The XPath expression to prefix.
   *
   * @return string
   *   The localized XPath expression.
   */
  public static function localize($xpath) {
    if (!isset(static::$cache[NULL][$xpath])) {
      $parser = new static($xpath, NULL, new Lexer());
      static::$cache[NULL][$xpath] = $parser->parse();
    }

    return static::$cache[NULL][$xpath];
  }

  /**
   * Constructs a Namespacer object.
   *
   * @param string $expression
   *   The XPath expression.
   * @param string $prefix
   *   The prefix to use.
   * @param \XpathHelper\Lexer $lexer
   *   The lexer that will produce tokens.
   */
  public function __construct($expression, $prefix, Lexer $lexer) {
    $this->prefix = $prefix;
    $this->tokens = $lexer->lex($expression);
  }

  /**
   * Parses an XPath expression.
   *
   * @return string
   *   The rewritten XPath expression.
   */
  public function parse() {
    $output = '';

    $token_count = count($this->tokens);

    for ($this->cursor; $this->cursor < $token_count; $this->cursor++) {
      $token = $this->tokens[$this->cursor];

      // A token that should be copied directly to the output.
      if ($this->shouldCopy($token)) {
        $output .= $token;
      }
      // A namespaced element.
      elseif ($element = $this->getNamespacedElement($token)) {
        $output .= $element;
      }
      // Namespace the element.
      else {
        $output .= $this->rewrite($token);
      }
    }

    return $output;
  }

  /**
   * Rewrites the token.
   *
   * Either in the form prefix:element or *[local-name() = "element"]
   *
   * @param string $token
   *   The element to rewrite.
   *
   * @return string
   *   The rewritten string.
   */
  protected function rewrite($element) {
    if ($this->prefix) {
      return $this->prefix . ':' . $element;
    }
    return '*[local-name() = "' . $element . '"]';
  }

  /**
   * Determines if a token should be copied as-is to the output.
   *
   * @param string $token
   *   The token.
   *
   * @return bool
   *   Returns true if the token should be copied, and false if not.
   */
  protected function shouldCopy($token) {
    if (Lexer::isWordBoundary($token)) {
      return TRUE;
    }
    // Attribute or quoted string.
    elseif ($token[0] === '@' || $token[0] === '"' || $token[0] === "'") {
      return TRUE;
    }
    elseif (is_numeric($token) || is_numeric($token[0])) {
      return TRUE;
    }
    elseif ($this->isFunctionCall()) {
      return TRUE;
    }
    elseif ($this->isOperator($token)) {
      return TRUE;
    }
    elseif ($this->isAxis()) {
      return TRUE;
    }
    elseif ($this->wasAttributeAxis()) {
      return TRUE;
    }
    // Handles the edge case where subtraction is written like 2 - 1.
    elseif ($token === '-') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Returns the namespaced element.
   *
   * @param string $token
   *   The token.
   *
   * @return string|bool
   *   The namespaced element, or false if it doesn't exist.
   */
  protected function getNamespacedElement($token) {
    if ($this->peek(1) !== ':') {
      return FALSE;
    }

    // Build the namespaced element, prefix:element.
    $token .= ':' . $this->peek(2);
    $this->cursor += 2;
    return $token;
  }

  /**
   * Determines if the current token is a function call.
   *
   * @param string $token
   *   The token.
   *
   * @return bool
   *   Returns true if the token is a function call and false if not.
   */
  protected function isFunctionCall() {
    // Spaces before the opening parens of a function call are valid.
    // Ex: //div[contains   (@id, "thing")]
    return $this->nextNonSpace() === '(';
  }

  /**
   * Checks if a token is an operator, one of div, or, and, mod.
   *
   * @param string $token
   *   The token to check.
   *
   * @return bool
   *   Returns true if the token is an operator, and false if not.
   */
  protected function isOperator($token) {
    if (!isset(static::$operators[$token])) {
      return FALSE;
    }

    $prev = $this->peek(-1);
    return $prev !== '/' && $prev !== '//';
  }

  /**
   * Determines whether this token is an axis.
   *
   * descendant-or-self, attribute, etc.
   *
   * @return bool
   *   True if the token is an axis, false if not.
   */
  protected function isAxis() {
    return $this->nextNonSpace() === '::';
  }

  /**
   * Determines whether the preceding token was an attribute axis.
   *
   * attribute::
   *
   * @return bool
   *   True if the preceding token was an attribute axis, false if not.
   */
  protected function wasAttributeAxis() {
    return $this->prevNonSpace() === '::' && $this->prevNonSpace(2) === 'attribute';
  }

  /**
   * Returns the next non-space token.
   *
   * @param int $delta
   *   (optional) The delta of the next non-space character. Defaults to 1.
   *
   * @return string
   *   The nth next non-space character.
   */
  protected function nextNonSpace($delta = 1) {
    $n = 1;

    for ($i = 0; $i < $delta; $i++) {
      do {
        $next = $this->peek($n);
        $n++;
      } while ($next === ' ');
    }

    return $next;
  }

  /**
   * Returns the previous non-space token.
   *
   * @param int $delta
   *   (optional) The delta of the previous non-space character. Defaults to 1.
   *
   * @return string
   *   The nth previous non-space character.
   */
  protected function prevNonSpace($delta = 1) {
    $n = -1;

    for ($i = 0; $i < $delta; $i++) {
      do {
        $prev = $this->peek($n);
        $n--;
      } while ($prev === ' ');
    }

    return $prev;
  }

  /**
   * Returns a token from an offset of the current position.
   *
   * @param int $offset
   *   The offset from the current position.
   *
   * @return string
   *   Returns the token at the given offset.
   */
  protected function peek($offset) {
    return isset($this->tokens[$this->cursor + $offset]) ? $this->tokens[$this->cursor + $offset] : '';
  }

}
