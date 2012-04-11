<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Yaml offers convenience methods to load and dump YAML.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Yaml {

    /**
     * Parses YAML into a PHP array.
     *
     * The parse method, when supplied with a YAML stream (string or file),
     * will do its best to convert YAML in a file into a PHP array.
     *
     *  Usage:
     *  <code>
     *   $array = Yaml::parse('config.yml');
     *   print_r($array);
     *  </code>
     *
     * @param string $input Path to a YAML file or a string containing YAML
     *
     * @return array The YAML converted to a PHP array
     *
     * @throws \InvalidArgumentException If the YAML is not valid
     *
     * @api
     */
    static public function parse($input) {
        $file = '';

        // if input is a file, process it
        if (strpos($input, "\n") === false && is_file($input)) {
            if (false === is_readable($input)) {
                throw new ParseException(sprintf('Unable to parse "%s" as the file is not readable.', $input));
            }

            $file = $input;

            ob_start();
            $retval = include($input);
            $content = ob_get_clean();

            // if an array is returned by the config file assume it's in plain php form else in YAML
            $input = is_array($retval) ? $retval : $content;
        }

        // if an array is returned by the config file assume it's in plain php form else in YAML
        if (is_array($input)) {
            return $input;
        }

        $yaml = new Parser();

        try {
            return $yaml->parse($input);
        } catch (ParseException $e) {
            if ($file) {
                $e->setParsedFile($file);
            }

            throw $e;
        }
    }

    /**
     * Dumps a PHP array to a YAML string.
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.
     *
     * @param array   $array PHP array
     * @param integer $inline The level where you switch to inline YAML
     *
     * @return string A YAML string representing the original PHP array
     *
     * @api
     */
    static public function dump($array, $inline = 2) {
        $yaml = new Dumper();

        return $yaml->dump($array, $inline);
    }

}

/**
 * Dumper dumps PHP variables to YAML strings.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Dumper {

    /**
     * Dumps a PHP value to YAML.
     *
     * @param  mixed   $input  The PHP value
     * @param  integer $inline The level where you switch to inline YAML
     * @param  integer $indent The level of indentation (used internally)
     *
     * @return string  The YAML representation of the PHP value
     */
    public function dump($input, $inline = 0, $indent = 0) {
        $output = '';
        $prefix = $indent ? str_repeat(' ', $indent) : '';

        if ($inline <= 0 || !is_array($input) || empty($input)) {
            $output .= $prefix . Inline::dump($input);
        } else {
            $isAHash = array_keys($input) !== range(0, count($input) - 1);

            foreach ($input as $key => $value) {
                $willBeInlined = $inline - 1 <= 0 || !is_array($value) || empty($value);

                $output .= sprintf('%s%s%s%s', $prefix, $isAHash ? Inline::dump($key) . ':' : '-', $willBeInlined ? ' ' : "\n", $this->dump($value, $inline - 1, $willBeInlined ? 0 : $indent + 2)
                        ) . ($willBeInlined ? "\n" : '');
            }
        }

        return $output;
    }

}

/**
 * Unescaper encapsulates unescaping rules for single and double-quoted
 * YAML strings.
 *
 * @author Matthew Lewinski <matthew@lewinski.org>
 */
class Unescaper {
    // Parser and Inline assume UTF-8 encoding, so escaped Unicode characters
    // must be converted to that encoding.

    const ENCODING = 'UTF-8';

    // Regex fragment that matches an escaped character in a double quoted
    // string.
    const REGEX_ESCAPED_CHARACTER = "\\\\([0abt\tnvfre \\\"\\/\\\\N_LP]|x[0-9a-fA-F]{2}|u[0-9a-fA-F]{4}|U[0-9a-fA-F]{8})";

    /**
     * Unescapes a single quoted string.
     *
     * @param string $value A single quoted string.
     *
     * @return string The unescaped string.
     */
    public function unescapeSingleQuotedString($value) {
        return str_replace('\'\'', '\'', $value);
    }

    /**
     * Unescapes a double quoted string.
     *
     * @param string $value A double quoted string.
     *
     * @return string The unescaped string.
     */
    public function unescapeDoubleQuotedString($value) {
        // evaluate the string
        return preg_replace_callback('/' . self::REGEX_ESCAPED_CHARACTER . '/u', create_function('$match', 'return $this->unescapeCharacter($match[0]);'), $value);
    }

    /**
     * Unescapes a character that was found in a double-quoted string
     *
     * @param string $value An escaped character
     *
     * @return string The unescaped character
     */
    public function unescapeCharacter($value) {
        switch ($value{1}) {
            case '0':
                return "\x0";
            case 'a':
                return "\x7";
            case 'b':
                return "\x8";
            case 't':
                return "\t";
            case "\t":
                return "\t";
            case 'n':
                return "\n";
            case 'v':
                return "\xb";
            case 'f':
                return "\xc";
            case 'r':
                return "\xd";
            case 'e':
                return "\x1b";
            case ' ':
                return ' ';
            case '"':
                return '"';
            case '/':
                return '/';
            case '\\':
                return '\\';
            case 'N':
                // U+0085 NEXT LINE
                return $this->convertEncoding("\x00\x85", self::ENCODING, 'UCS-2BE');
            case '_':
                // U+00A0 NO-BREAK SPACE
                return $this->convertEncoding("\x00\xA0", self::ENCODING, 'UCS-2BE');
            case 'L':
                // U+2028 LINE SEPARATOR
                return $this->convertEncoding("\x20\x28", self::ENCODING, 'UCS-2BE');
            case 'P':
                // U+2029 PARAGRAPH SEPARATOR
                return $this->convertEncoding("\x20\x29", self::ENCODING, 'UCS-2BE');
            case 'x':
                $char = pack('n', hexdec(substr($value, 2, 2)));

                return $this->convertEncoding($char, self::ENCODING, 'UCS-2BE');
            case 'u':
                $char = pack('n', hexdec(substr($value, 2, 4)));

                return $this->convertEncoding($char, self::ENCODING, 'UCS-2BE');
            case 'U':
                $char = pack('N', hexdec(substr($value, 2, 8)));

                return $this->convertEncoding($char, self::ENCODING, 'UCS-4BE');
        }
    }

    /**
     * Convert a string from one encoding to another.
     *
     * @param string $value The string to convert
     * @param string $to    The input encoding
     * @param string $from  The output encoding
     *
     * @return string The string with the new encoding
     *
     * @throws \RuntimeException if no suitable encoding function is found (iconv or mbstring)
     */
    private function convertEncoding($value, $to, $from) {
        if (function_exists('iconv')) {
            return iconv($from, $to, $value);
        } elseif (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($value, $to, $from);
        }

        throw new RuntimeException('No suitable convert encoding function (install the iconv or mbstring extension).');
    }

}

/**
 * Parser parses YAML strings to convert them to PHP arrays.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Parser {

    private $offset = 0;
    private $lines = array();
    private $currentLineNb = -1;
    private $currentLine = '';
    private $refs = array();

    /**
     * Constructor
     *
     * @param integer $offset The offset of YAML document (used for line numbers in error messages)
     */
    public function __construct($offset = 0) {
        $this->offset = $offset;
    }

    /**
     * Parses a YAML string to a PHP value.
     *
     * @param  string $value A YAML string
     *
     * @return mixed  A PHP value
     *
     * @throws ParseException If the YAML is not valid
     */
    public function parse($value) {
        $this->currentLineNb = -1;
        $this->currentLine = '';
        $this->lines = explode("\n", $this->cleanup($value));

        if (function_exists('mb_detect_encoding') && false === mb_detect_encoding($value, 'UTF-8', true)) {
            throw new ParseException('The YAML value does not appear to be valid UTF-8.');
        }

        if (function_exists('mb_internal_encoding') && ((int) ini_get('mbstring.func_overload')) & 2) {
            $mbEncoding = mb_internal_encoding();
            mb_internal_encoding('UTF-8');
        }

        $data = array();
        while ($this->moveToNextLine()) {
            if ($this->isCurrentLineEmpty()) {
                continue;
            }

            // tab?
            if ("\t" === $this->currentLine[0]) {
                throw new ParseException('A YAML file cannot contain tabs as indentation.', $this->getRealCurrentLineNb() + 1, $this->currentLine);
            }

            $isRef = $isInPlace = $isProcessed = false;
            if (preg_match('#^\-((?P<leadspaces>\s+)(?P<value>.+?))?\s*$#u', $this->currentLine, $values)) {
                if (isset($values['value']) && preg_match('#^&(?P<ref>[^ ]+) *(?P<value>.*)#u', $values['value'], $matches)) {
                    $isRef = $matches['ref'];
                    $values['value'] = $matches['value'];
                }

                // array
                if (!isset($values['value']) || '' == trim($values['value'], ' ') || 0 === strpos(ltrim($values['value'], ' '), '#')) {
                    $c = $this->getRealCurrentLineNb() + 1;
                    $parser = new Parser($c);
                    $parser->refs = & $this->refs;
                    $data[] = $parser->parse($this->getNextEmbedBlock());
                } else {
                    if (isset($values['leadspaces'])
                            && ' ' == $values['leadspaces']
                            && preg_match('#^(?P<key>' . Inline::REGEX_QUOTED_STRING . '|[^ \'"\{\[].*?) *\:(\s+(?P<value>.+?))?\s*$#u', $values['value'], $matches)
                    ) {
                        // this is a compact notation element, add to next block and parse
                        $c = $this->getRealCurrentLineNb();
                        $parser = new Parser($c);
                        $parser->refs = & $this->refs;

                        $block = $values['value'];
                        if (!$this->isNextLineIndented()) {
                            $block .= "\n" . $this->getNextEmbedBlock($this->getCurrentLineIndentation() + 2);
                        }

                        $data[] = $parser->parse($block);
                    } else {
                        $data[] = $this->parseValue($values['value']);
                    }
                }
            } elseif (preg_match('#^(?P<key>' . Inline::REGEX_QUOTED_STRING . '|[^ \'"\[\{].*?) *\:(\s+(?P<value>.+?))?\s*$#u', $this->currentLine, $values)) {
                try {
                    $key = Inline::parseScalar($values['key']);
                } catch (ParseException $e) {
                    $e->setParsedLine($this->getRealCurrentLineNb() + 1);
                    $e->setSnippet($this->currentLine);

                    throw $e;
                }

                if ('<<' === $key) {
                    if (isset($values['value']) && 0 === strpos($values['value'], '*')) {
                        $isInPlace = substr($values['value'], 1);
                        if (!array_key_exists($isInPlace, $this->refs)) {
                            throw new ParseException(sprintf('Reference "%s" does not exist.', $isInPlace), $this->getRealCurrentLineNb() + 1, $this->currentLine);
                        }
                    } else {
                        if (isset($values['value']) && $values['value'] !== '') {
                            $value = $values['value'];
                        } else {
                            $value = $this->getNextEmbedBlock();
                        }
                        $c = $this->getRealCurrentLineNb() + 1;
                        $parser = new Parser($c);
                        $parser->refs = & $this->refs;
                        $parsed = $parser->parse($value);

                        $merged = array();
                        if (!is_array($parsed)) {
                            throw new ParseException('YAML merge keys used with a scalar value instead of an array.', $this->getRealCurrentLineNb() + 1, $this->currentLine);
                        } elseif (isset($parsed[0])) {
                            // Numeric array, merge individual elements
                            foreach (array_reverse($parsed) as $parsedItem) {
                                if (!is_array($parsedItem)) {
                                    throw new ParseException('Merge items must be arrays.', $this->getRealCurrentLineNb() + 1, $parsedItem);
                                }
                                $merged = array_merge($parsedItem, $merged);
                            }
                        } else {
                            // Associative array, merge
                            $merged = array_merge($merged, $parsed);
                        }

                        $isProcessed = $merged;
                    }
                } elseif (isset($values['value']) && preg_match('#^&(?P<ref>[^ ]+) *(?P<value>.*)#u', $values['value'], $matches)) {
                    $isRef = $matches['ref'];
                    $values['value'] = $matches['value'];
                }

                if ($isProcessed) {
                    // Merge keys
                    $data = $isProcessed;
                    // hash
                } elseif (!isset($values['value']) || '' == trim($values['value'], ' ') || 0 === strpos(ltrim($values['value'], ' '), '#')) {
                    // if next line is less indented or equal, then it means that the current value is null
                    if ($this->isNextLineIndented()) {
                        $data[$key] = null;
                    } else {
                        $c = $this->getRealCurrentLineNb() + 1;
                        $parser = new Parser($c);
                        $parser->refs = & $this->refs;
                        $data[$key] = $parser->parse($this->getNextEmbedBlock());
                    }
                } else {
                    if ($isInPlace) {
                        $data = $this->refs[$isInPlace];
                    } else {
                        $data[$key] = $this->parseValue($values['value']);
                    }
                }
            } else {
                // 1-liner followed by newline
                if (2 == count($this->lines) && empty($this->lines[1])) {
                    try {
                        $value = Inline::parse($this->lines[0]);
                    } catch (ParseException $e) {
                        $e->setParsedLine($this->getRealCurrentLineNb() + 1);
                        $e->setSnippet($this->currentLine);

                        throw $e;
                    }

                    if (is_array($value)) {
                        $first = reset($value);
                        if (is_string($first) && 0 === strpos($first, '*')) {
                            $data = array();
                            foreach ($value as $alias) {
                                $data[] = $this->refs[substr($alias, 1)];
                            }
                            $value = $data;
                        }
                    }

                    if (isset($mbEncoding)) {
                        mb_internal_encoding($mbEncoding);
                    }

                    return $value;
                }

                switch (preg_last_error()) {
                    case PREG_INTERNAL_ERROR:
                        $error = 'Internal PCRE error.';
                        break;
                    case PREG_BACKTRACK_LIMIT_ERROR:
                        $error = 'pcre.backtrack_limit reached.';
                        break;
                    case PREG_RECURSION_LIMIT_ERROR:
                        $error = 'pcre.recursion_limit reached.';
                        break;
                    case PREG_BAD_UTF8_ERROR:
                        $error = 'Malformed UTF-8 data.';
                        break;
                    case PREG_BAD_UTF8_OFFSET_ERROR:
                        $error = 'Offset doesn\'t correspond to the begin of a valid UTF-8 code point.';
                        break;
                    default:
                        $error = 'Unable to parse.';
                }

                throw new ParseException($error, $this->getRealCurrentLineNb() + 1, $this->currentLine);
            }

            if ($isRef) {
                $this->refs[$isRef] = end($data);
            }
        }

        if (isset($mbEncoding)) {
            mb_internal_encoding($mbEncoding);
        }

        return empty($data) ? null : $data;
    }

    /**
     * Returns the current line number (takes the offset into account).
     *
     * @return integer The current line number
     */
    private function getRealCurrentLineNb() {
        return $this->currentLineNb + $this->offset;
    }

    /**
     * Returns the current line indentation.
     *
     * @return integer The current line indentation
     */
    private function getCurrentLineIndentation() {
        return strlen($this->currentLine) - strlen(ltrim($this->currentLine, ' '));
    }

    /**
     * Returns the next embed block of YAML.
     *
     * @param integer $indentation The indent level at which the block is to be read, or null for default
     *
     * @return string A YAML string
     *
     * @throws ParseException When indentation problem are detected
     */
    private function getNextEmbedBlock($indentation = null) {
        $this->moveToNextLine();

        if (null === $indentation) {
            $newIndent = $this->getCurrentLineIndentation();

            if (!$this->isCurrentLineEmpty() && 0 == $newIndent) {
                throw new ParseException('Indentation problem.', $this->getRealCurrentLineNb() + 1, $this->currentLine);
            }
        } else {
            $newIndent = $indentation;
        }

        $data = array(substr($this->currentLine, $newIndent));

        while ($this->moveToNextLine()) {
            if ($this->isCurrentLineEmpty()) {
                if ($this->isCurrentLineBlank()) {
                    $data[] = substr($this->currentLine, $newIndent);
                }

                continue;
            }

            $indent = $this->getCurrentLineIndentation();

            if (preg_match('#^(?P<text> *)$#', $this->currentLine, $match)) {
                // empty line
                $data[] = $match['text'];
            } elseif ($indent >= $newIndent) {
                $data[] = substr($this->currentLine, $newIndent);
            } elseif (0 == $indent) {
                $this->moveToPreviousLine();

                break;
            } else {
                throw new ParseException('Indentation problem.', $this->getRealCurrentLineNb() + 1, $this->currentLine);
            }
        }

        return implode("\n", $data);
    }

    /**
     * Moves the parser to the next line.
     *
     * @return Boolean
     */
    private function moveToNextLine() {
        if ($this->currentLineNb >= count($this->lines) - 1) {
            return false;
        }

        $this->currentLine = $this->lines[++$this->currentLineNb];

        return true;
    }

    /**
     * Moves the parser to the previous line.
     */
    private function moveToPreviousLine() {
        $this->currentLine = $this->lines[--$this->currentLineNb];
    }

    /**
     * Parses a YAML value.
     *
     * @param  string $value A YAML value
     *
     * @return mixed  A PHP value
     *
     * @throws ParseException When reference does not exist
     */
    private function parseValue($value) {
        if (0 === strpos($value, '*')) {
            if (false !== $pos = strpos($value, '#')) {
                $value = substr($value, 1, $pos - 2);
            } else {
                $value = substr($value, 1);
            }

            if (!array_key_exists($value, $this->refs)) {
                throw new ParseException(sprintf('Reference "%s" does not exist.', $value), $this->currentLine);
            }

            return $this->refs[$value];
        }

        if (preg_match('/^(?P<separator>\||>)(?P<modifiers>\+|\-|\d+|\+\d+|\-\d+|\d+\+|\d+\-)?(?P<comments> +#.*)?$/', $value, $matches)) {
            $modifiers = isset($matches['modifiers']) ? $matches['modifiers'] : '';

            return $this->parseFoldedScalar($matches['separator'], preg_replace('#\d+#', '', $modifiers), intval(abs($modifiers)));
        }

        try {
            return Inline::parse($value);
        } catch (ParseException $e) {
            $e->setParsedLine($this->getRealCurrentLineNb() + 1);
            $e->setSnippet($this->currentLine);

            throw $e;
        }
    }

    /**
     * Parses a folded scalar.
     *
     * @param  string  $separator   The separator that was used to begin this folded scalar (| or >)
     * @param  string  $indicator   The indicator that was used to begin this folded scalar (+ or -)
     * @param  integer $indentation The indentation that was used to begin this folded scalar
     *
     * @return string  The text value
     */
    private function parseFoldedScalar($separator, $indicator = '', $indentation = 0) {
        $separator = '|' == $separator ? "\n" : ' ';
        $text = '';

        $notEOF = $this->moveToNextLine();

        while ($notEOF && $this->isCurrentLineBlank()) {
            $text .= "\n";

            $notEOF = $this->moveToNextLine();
        }

        if (!$notEOF) {
            return '';
        }

        if (!preg_match('#^(?P<indent>' . ($indentation ? str_repeat(' ', $indentation) : ' +') . ')(?P<text>.*)$#u', $this->currentLine, $matches)) {
            $this->moveToPreviousLine();

            return '';
        }

        $textIndent = $matches['indent'];
        $previousIndent = 0;

        $text .= $matches['text'] . $separator;
        while ($this->currentLineNb + 1 < count($this->lines)) {
            $this->moveToNextLine();

            if (preg_match('#^(?P<indent> {' . strlen($textIndent) . ',})(?P<text>.+)$#u', $this->currentLine, $matches)) {
                if (' ' == $separator && $previousIndent != $matches['indent']) {
                    $text = substr($text, 0, -1) . "\n";
                }
                $previousIndent = $matches['indent'];

                $text .= str_repeat(' ', $diff = strlen($matches['indent']) - strlen($textIndent)) . $matches['text'] . ($diff ? "\n" : $separator);
            } elseif (preg_match('#^(?P<text> *)$#', $this->currentLine, $matches)) {
                $text .= preg_replace('#^ {1,' . strlen($textIndent) . '}#', '', $matches['text']) . "\n";
            } else {
                $this->moveToPreviousLine();

                break;
            }
        }

        if (' ' == $separator) {
            // replace last separator by a newline
            $text = preg_replace('/ (\n*)$/', "\n$1", $text);
        }

        switch ($indicator) {
            case '':
                $text = preg_replace('#\n+$#s', "\n", $text);
                break;
            case '+':
                break;
            case '-':
                $text = preg_replace('#\n+$#s', '', $text);
                break;
        }

        return $text;
    }

    /**
     * Returns true if the next line is indented.
     *
     * @return Boolean Returns true if the next line is indented, false otherwise
     */
    private function isNextLineIndented() {
        $currentIndentation = $this->getCurrentLineIndentation();
        $notEOF = $this->moveToNextLine();

        while ($notEOF && $this->isCurrentLineEmpty()) {
            $notEOF = $this->moveToNextLine();
        }

        if (false === $notEOF) {
            return false;
        }

        $ret = false;
        if ($this->getCurrentLineIndentation() <= $currentIndentation) {
            $ret = true;
        }

        $this->moveToPreviousLine();

        return $ret;
    }

    /**
     * Returns true if the current line is blank or if it is a comment line.
     *
     * @return Boolean Returns true if the current line is empty or if it is a comment line, false otherwise
     */
    private function isCurrentLineEmpty() {
        return $this->isCurrentLineBlank() || $this->isCurrentLineComment();
    }

    /**
     * Returns true if the current line is blank.
     *
     * @return Boolean Returns true if the current line is blank, false otherwise
     */
    private function isCurrentLineBlank() {
        return '' == trim($this->currentLine, ' ');
    }

    /**
     * Returns true if the current line is a comment line.
     *
     * @return Boolean Returns true if the current line is a comment line, false otherwise
     */
    private function isCurrentLineComment() {
        //checking explicitly the first char of the trim is faster than loops or strpos
        $ltrimmedLine = ltrim($this->currentLine, ' ');

        return $ltrimmedLine[0] === '#';
    }

    /**
     * Cleanups a YAML string to be parsed.
     *
     * @param  string $value The input YAML string
     *
     * @return string A cleaned up YAML string
     */
    private function cleanup($value) {
        $value = str_replace(array("\r\n", "\r"), "\n", $value);

        if (!preg_match("#\n$#", $value)) {
            $value .= "\n";
        }

        // strip YAML header
        $count = 0;
        $value = preg_replace('#^\%YAML[: ][\d\.]+.*\n#su', '', $value, -1, $count);
        $this->offset += $count;

        // remove leading comments
        $trimmedValue = preg_replace('#^(\#.*?\n)+#s', '', $value, -1, $count);
        if ($count == 1) {
            // items have been removed, update the offset
            $this->offset += substr_count($value, "\n") - substr_count($trimmedValue, "\n");
            $value = $trimmedValue;
        }

        // remove start of the document marker (---)
        $trimmedValue = preg_replace('#^\-\-\-.*?\n#s', '', $value, -1, $count);
        if ($count == 1) {
            // items have been removed, update the offset
            $this->offset += substr_count($value, "\n") - substr_count($trimmedValue, "\n");
            $value = $trimmedValue;

            // remove end of the document marker (...)
            $value = preg_replace('#\.\.\.\s*$#s', '', $value);
        }

        return $value;
    }

}

/**
 * Inline implements a YAML parser/dumper for the YAML inline syntax.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Inline {

    const REGEX_QUOTED_STRING = '(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\']*(?:\'\'[^\']*)*)\')';

    /**
     * Converts a YAML string to a PHP array.
     *
     * @param string $value A YAML string
     *
     * @return array A PHP array representing the YAML string
     */
    static public function parse($value) {
        $value = trim($value);

        if (0 == strlen($value)) {
            return '';
        }

        if (function_exists('mb_internal_encoding') && ((int) ini_get('mbstring.func_overload')) & 2) {
            $mbEncoding = mb_internal_encoding();
            mb_internal_encoding('ASCII');
        }

        switch ($value[0]) {
            case '[':
                $result = self::parseSequence($value);
                break;
            case '{':
                $result = self::parseMapping($value);
                break;
            default:
                $result = self::parseScalar($value);
        }

        if (isset($mbEncoding)) {
            mb_internal_encoding($mbEncoding);
        }

        return $result;
    }

    /**
     * Dumps a given PHP variable to a YAML string.
     *
     * @param mixed $value The PHP variable to convert
     *
     * @return string The YAML string representing the PHP array
     *
     * @throws DumpException When trying to dump PHP resource
     */
    static public function dump($value) {
        switch (true) {
            case is_resource($value):
                throw new DumpException(sprintf('Unable to dump PHP resources in a YAML file ("%s").', get_resource_type($value)));
            case is_object($value):
                return '!!php/object:' . serialize($value);
            case is_array($value):
                return self::dumpArray($value);
            case null === $value:
                return 'null';
            case true === $value:
                return 'true';
            case false === $value:
                return 'false';
            case ctype_digit($value):
                return is_string($value) ? "'$value'" : (int) $value;
            case is_numeric($value):
                $locale = setlocale(LC_NUMERIC, 0);
                if (false !== $locale) {
                    setlocale(LC_NUMERIC, 'C');
                }
                $repr = is_string($value) ? "'$value'" : (is_infinite($value) ? str_ireplace('INF', '.Inf', strval($value)) : strval($value));

                if (false !== $locale) {
                    setlocale(LC_NUMERIC, $locale);
                }

                return $repr;
            case Escaper::requiresDoubleQuoting($value):
                return Escaper::escapeWithDoubleQuotes($value);
            case Escaper::requiresSingleQuoting($value):
                return Escaper::escapeWithSingleQuotes($value);
            case '' == $value:
                return "''";
            case preg_match(self::getTimestampRegex(), $value):
            case in_array(strtolower($value), array('null', '~', 'true', 'false')):
                return "'$value'";
            default:
                return $value;
        }
    }

    function dp($v, $w){
        return (integer) $v + $w;
    }

    /**
     * Dumps a PHP array to a YAML string.
     *
     * @param array $value The PHP array to dump
     *
     * @return string The YAML string representing the PHP array
     */
    static private function dumpArray($value) {
        // array
        $keys = array_keys($value);
        if ((1 == count($keys) && '0' == $keys[0])
                || (count($keys) > 1 && array_reduce($keys, $this->dp($v, $w), 0) == count($keys) * (count($keys) - 1) / 2)
        ) {
            $output = array();
            foreach ($value as $val) {
                $output[] = self::dump($val);
            }

            return sprintf('[%s]', implode(', ', $output));
        }

        // mapping
        $output = array();
        foreach ($value as $key => $val) {
            $output[] = sprintf('%s: %s', self::dump($key), self::dump($val));
        }

        return sprintf('{ %s }', implode(', ', $output));
    }

    /**
     * Parses a scalar to a YAML string.
     *
     * @param scalar  $scalar
     * @param string  $delimiters
     * @param array   $stringDelimiters
     * @param integer &$i
     * @param Boolean $evaluate
     *
     * @return string A YAML string
     *
     * @throws ParseException When malformed inline YAML string is parsed
     */
    static public function parseScalar($scalar, $delimiters = null, $stringDelimiters = array('"', "'"), &$i = 0, $evaluate = true) {
        if (in_array($scalar[$i], $stringDelimiters)) {
            // quoted scalar
            $output = self::parseQuotedScalar($scalar, $i);
        } else {
            // "normal" string
            if (!$delimiters) {
                $output = substr($scalar, $i);
                $i += strlen($output);

                // remove comments
                if (false !== $strpos = strpos($output, ' #')) {
                    $output = rtrim(substr($output, 0, $strpos));
                }
            } elseif (preg_match('/^(.+?)(' . implode('|', $delimiters) . ')/', substr($scalar, $i), $match)) {
                $output = $match[1];
                $i += strlen($output);
            } else {
                throw new ParseException(sprintf('Malformed inline YAML string (%s).', $scalar));
            }

            $output = $evaluate ? self::evaluateScalar($output) : $output;
        }

        return $output;
    }

    /**
     * Parses a quoted scalar to YAML.
     *
     * @param string  $scalar
     * @param integer &$i
     *
     * @return string A YAML string
     *
     * @throws ParseException When malformed inline YAML string is parsed
     */
    static private function parseQuotedScalar($scalar, &$i) {
        if (!preg_match('/' . self::REGEX_QUOTED_STRING . '/Au', substr($scalar, $i), $match)) {
            throw new ParseException(sprintf('Malformed inline YAML string (%s).', substr($scalar, $i)));
        }

        $output = substr($match[0], 1, strlen($match[0]) - 2);

        $unescaper = new Unescaper();
        if ('"' == $scalar[$i]) {
            $output = $unescaper->unescapeDoubleQuotedString($output);
        } else {
            $output = $unescaper->unescapeSingleQuotedString($output);
        }

        $i += strlen($match[0]);

        return $output;
    }

    /**
     * Parses a sequence to a YAML string.
     *
     * @param string  $sequence
     * @param integer &$i
     *
     * @return string A YAML string
     *
     * @throws ParseException When malformed inline YAML string is parsed
     */
    static private function parseSequence($sequence, &$i = 0) {
        $output = array();
        $len = strlen($sequence);
        $i += 1;

        // [foo, bar, ...]
        while ($i < $len) {
            switch ($sequence[$i]) {
                case '[':
                    // nested sequence
                    $output[] = self::parseSequence($sequence, $i);
                    break;
                case '{':
                    // nested mapping
                    $output[] = self::parseMapping($sequence, $i);
                    break;
                case ']':
                    return $output;
                case ',':
                case ' ':
                    break;
                default:
                    $isQuoted = in_array($sequence[$i], array('"', "'"));
                    $value = self::parseScalar($sequence, array(',', ']'), array('"', "'"), $i);

                    if (!$isQuoted && false !== strpos($value, ': ')) {
                        // embedded mapping?
                        try {
                            $value = self::parseMapping('{' . $value . '}');
                        } catch (InvalidArgumentException $e) {
                            // no, it's not
                        }
                    }

                    $output[] = $value;

                    --$i;
            }
            ++$i;
        }

        throw new ParseException(sprintf('Malformed inline YAML string %s', $sequence));
    }

    /**
     * Parses a mapping to a YAML string.
     *
     * @param string  $mapping
     * @param integer &$i
     *
     * @return string A YAML string
     *
     * @throws ParseException When malformed inline YAML string is parsed
     */
    static private function parseMapping($mapping, &$i = 0) {
        $output = array();
        $len = strlen($mapping);
        $i += 1;

        // {foo: bar, bar:foo, ...}
        while ($i < $len) {
            switch ($mapping[$i]) {
                case ' ':
                case ',':
                    ++$i;
                    continue 2;
                case '}':
                    return $output;
            }

            // key
            $key = self::parseScalar($mapping, array(':', ' '), array('"', "'"), $i, false);

            // value
            $done = false;
            while ($i < $len) {
                switch ($mapping[$i]) {
                    case '[':
                        // nested sequence
                        $output[$key] = self::parseSequence($mapping, $i);
                        $done = true;
                        break;
                    case '{':
                        // nested mapping
                        $output[$key] = self::parseMapping($mapping, $i);
                        $done = true;
                        break;
                    case ':':
                    case ' ':
                        break;
                    default:
                        $output[$key] = self::parseScalar($mapping, array(',', '}'), array('"', "'"), $i);
                        $done = true;
                        --$i;
                }
                ++$i;

                if ($done) {
                    continue 2;
                }
            }
        }

        throw new ParseException(sprintf('Malformed inline YAML string %s', $mapping));
    }

    /**
     * Evaluates scalars and replaces magic values.
     *
     * @param string $scalar
     *
     * @return string A YAML string
     */
    static private function evaluateScalar($scalar) {
        $scalar = trim($scalar);

        switch (true) {
            case 'null' == strtolower($scalar):
            case '' == $scalar:
            case '~' == $scalar:
                return null;
            case 0 === strpos($scalar, '!str'):
                return (string) substr($scalar, 5);
            case 0 === strpos($scalar, '! '):
                return intval(self::parseScalar(substr($scalar, 2)));
            case 0 === strpos($scalar, '!!php/object:'):
                return unserialize(substr($scalar, 13));
            case ctype_digit($scalar):
                $raw = $scalar;
                $cast = intval($scalar);

                return '0' == $scalar[0] ? octdec($scalar) : (((string) $raw == (string) $cast) ? $cast : $raw);
            case 'true' === strtolower($scalar):
                return true;
            case 'false' === strtolower($scalar):
                return false;
            case is_numeric($scalar):
                return '0x' == $scalar[0] . $scalar[1] ? hexdec($scalar) : floatval($scalar);
            case 0 == strcasecmp($scalar, '.inf'):
            case 0 == strcasecmp($scalar, '.NaN'):
                return -log(0);
            case 0 == strcasecmp($scalar, '-.inf'):
                return log(0);
            case preg_match('/^(-|\+)?[0-9,]+(\.[0-9]+)?$/', $scalar):
                return floatval(str_replace(',', '', $scalar));
            case preg_match(self::getTimestampRegex(), $scalar):
                return strtotime($scalar);
            default:
                return (string) $scalar;
        }
    }

    /**
     * Gets a regex that matches an unix timestamp
     *
     * @return string The regular expression
     */
    static private function getTimestampRegex() {
        return <<<EOF
        ~^
        (?P<year>[0-9][0-9][0-9][0-9])
        -(?P<month>[0-9][0-9]?)
        -(?P<day>[0-9][0-9]?)
        (?:(?:[Tt]|[ \t]+)
        (?P<hour>[0-9][0-9]?)
        :(?P<minute>[0-9][0-9])
        :(?P<second>[0-9][0-9])
        (?:\.(?P<fraction>[0-9]*))?
        (?:[ \t]*(?P<tz>Z|(?P<tz_sign>[-+])(?P<tz_hour>[0-9][0-9]?)
        (?::(?P<tz_minute>[0-9][0-9]))?))?)?
        $~x
EOF;
    }

}

/**
 * Escaper encapsulates escaping rules for single and double-quoted
 * YAML strings.
 *
 * @author Matthew Lewinski <matthew@lewinski.org>
 */
class Escaper {
    // Characters that would cause a dumped string to require double quoting.

    const REGEX_CHARACTER_TO_ESCAPE = "[\\x00-\\x1f]|\xc2\x85|\xc2\xa0|\xe2\x80\xa8|\xe2\x80\xa9";

    // Mapping arrays for escaping a double quoted string. The backslash is
    // first to ensure proper escaping because str_replace operates iteratively
    // on the input arrays. This ordering of the characters avoids the use of strtr,
    // which performs more slowly.
    static private $escapees = array('\\\\', '\\"',
        "\x00", "\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07",
        "\x08", "\x09", "\x0a", "\x0b", "\x0c", "\x0d", "\x0e", "\x0f",
        "\x10", "\x11", "\x12", "\x13", "\x14", "\x15", "\x16", "\x17",
        "\x18", "\x19", "\x1a", "\x1b", "\x1c", "\x1d", "\x1e", "\x1f",
        "\xc2\x85", "\xc2\xa0", "\xe2\x80\xa8", "\xe2\x80\xa9");
    static private $escaped = array('\\"', '\\\\',
        "\\0", "\\x01", "\\x02", "\\x03", "\\x04", "\\x05", "\\x06", "\\a",
        "\\b", "\\t", "\\n", "\\v", "\\f", "\\r", "\\x0e", "\\x0f",
        "\\x10", "\\x11", "\\x12", "\\x13", "\\x14", "\\x15", "\\x16", "\\x17",
        "\\x18", "\\x19", "\\x1a", "\\e", "\\x1c", "\\x1d", "\\x1e", "\\x1f",
        "\\N", "\\_", "\\L", "\\P");

    /**
     * Determines if a PHP value would require double quoting in YAML.
     *
     * @param string $value A PHP value
     *
     * @return Boolean True if the value would require double quotes.
     */
    static public function requiresDoubleQuoting($value) {
        return preg_match('/' . self::REGEX_CHARACTER_TO_ESCAPE . '/u', $value);
    }

    /**
     * Escapes and surrounds a PHP value with double quotes.
     *
     * @param string $value A PHP value
     *
     * @return string The quoted, escaped string
     */
    static public function escapeWithDoubleQuotes($value) {
        return sprintf('"%s"', str_replace(self::$escapees, self::$escaped, $value));
    }

    /**
     * Determines if a PHP value would require single quoting in YAML.
     *
     * @param string $value A PHP value
     *
     * @return Boolean True if the value would require single quotes.
     */
    static public function requiresSingleQuoting($value) {
        return preg_match('/[ \s \' " \: \{ \} \[ \] , & \* \# \?] | \A[ - ? | < > = ! % @ ` ]/x', $value);
    }

    /**
     * Escapes and surrounds a PHP value with single quotes.
     *
     * @param string $value A PHP value
     *
     * @return string The quoted, escaped string
     */
    static public function escapeWithSingleQuotes($value) {
        return sprintf("'%s'", str_replace('\'', '\'\'', $value));
    }

}

/**
 * Exception class thrown when an error occurs during parsing.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class ParseException extends RuntimeException implements ExceptionInterface {

    private $parsedFile;
    private $parsedLine;
    private $snippet;
    private $rawMessage;

    /**
     * Constructor.
     *
     * @param string    $message    The error message
     * @param integer   $parsedLine The line where the error occurred
     * @param integer   $snippet    The snippet of code near the problem
     * @param string    $parsedFile The file name where the error occurred
     * @param Exception $previous   The previous exception
     */
    public function __construct($message, $parsedLine = -1, $snippet = null, $parsedFile = null, Exception $previous = null) {
        $this->parsedFile = $parsedFile;
        $this->parsedLine = $parsedLine;
        $this->snippet = $snippet;
        $this->rawMessage = $message;

        $this->updateRepr();

        parent::__construct($this->message, 0, $previous);
    }

    /**
     * Gets the snippet of code near the error.
     *
     * @return string The snippet of code
     */
    public function getSnippet() {
        return $this->snippet;
    }

    /**
     * Sets the snippet of code near the error.
     *
     * @param string $snippet The code snippet
     */
    public function setSnippet($snippet) {
        $this->snippet = $snippet;

        $this->updateRepr();
    }

    /**
     * Gets the filename where the error occurred.
     *
     * This method returns null if a string is parsed.
     *
     * @return string The filename
     */
    public function getParsedFile() {
        return $this->parsedFile;
    }

    /**
     * Sets the filename where the error occurred.
     *
     * @param string $parsedFile The filename
     */
    public function setParsedFile($parsedFile) {
        $this->parsedFile = $parsedFile;

        $this->updateRepr();
    }

    /**
     * Gets the line where the error occurred.
     *
     * @return integer The file line
     */
    public function getParsedLine() {
        return $this->parsedLine;
    }

    /**
     * Sets the line where the error occurred.
     *
     * @param integer $parsedLine The file line
     */
    public function setParsedLine($parsedLine) {
        $this->parsedLine = $parsedLine;

        $this->updateRepr();
    }

    private function updateRepr() {
        $this->message = $this->rawMessage;

        $dot = false;
        if ('.' === substr($this->message, -1)) {
            $this->message = substr($this->message, 0, -1);
            $dot = true;
        }

        if (null !== $this->parsedFile) {
            $this->message .= sprintf(' in %s', json_encode($this->parsedFile));
        }

        if ($this->parsedLine >= 0) {
            $this->message .= sprintf(' at line %d', $this->parsedLine);
        }

        if ($this->snippet) {
            $this->message .= sprintf(' (near "%s")', $this->snippet);
        }

        if ($dot) {
            $this->message .= '.';
        }
    }

}

/**
 * Exception interface for all exceptions thrown by the component.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface ExceptionInterface {
    
}

/**
 * Exception class thrown when an error occurs during dumping.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class DumpException extends RuntimeException implements ExceptionInterface {
    
}
