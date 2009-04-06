<?php
/****************************************************************************
 *   Copyright (C) 2008-2009 by Vladlen Y. Koshelev                         *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OQL
	 * 
	 * NOTE: set mb_internal_encoding and mb_regex_encoding for correct multibyte strings parsing
	**/
	final class OqlTokenizer
	{
		private $string			= null;
		private $tokens			= array();
		private $spaces			= array();
		
		private $index			= -1;
		
		private static $masks = array(
			// parentheses
			OqlTokenType::PARENTHESES =>
				'[\(\)]',
			
			// comma
			OqlTokenType::PUNCTUATION =>
				',',
			
			// "'`-quoted string constant
			OqlTokenType::STRING =>
				'"[^"\\\]*(?:\\\.[^"\\\]*)*"|\'[^\'\\\]*(?:\\\.[^\'\\\]*)*\'|`[^`\\\]*(?:\\\.[^`\\\]*)*`',
			
			// unsigned numeric constant
			OqlTokenType::NUMBER =>
				'(?:\b[\d]+)?\.?[\d]+(?:[eE][-+]?[\d]+)?\b',
			
			// boolean constant
			OqlTokenType::BOOLEAN =>
				'\b(?:true|false)\b',
			
			OqlTokenType::NULL =>
				'\bnull\b',
			
			// placeholder
			OqlTokenType::PLACEHOLDER =>
				'\$[\d]+',
			
			// reserved word
			OqlTokenType::KEYWORD =>
				'\b(?:as|distinct|all|from|where|in|like|ilike|similar\s+to|between|is|group\s+by|order\s+by|asc|desc|having|limit|offset)\b',
			
			// operators
			OqlTokenType::OPERATOR =>
				'\>\=|\<\=|\<\>|\>|\<|\!\=|\=|\+|\-|\/|\*|and|or|not',
			
			// aggregate function
			OqlTokenType::AGGREGATE_FUNCTION =>
				'\b(?:sum|avg|min|max|count)\b',
			
			// property, class name
			OqlTokenType::IDENTIFIER =>
				'\b[a-zA-Z_][a-zA-Z\d_]*(?:\.[a-zA-Z_][a-zA-Z\d_]*)*\b',
			
			// unmatched
			OqlTokenType::UNKNOWN =>
				'\S+',
			
			OqlTokenType::WHITESPACE =>
				'\s+'
		);
		
		private static $pattern = null;
		
		public function __construct($string)
		{
			Assert::isString($string);
			
			$this->string = $string;
			
			if (!self::$pattern)
				self::$pattern = '/('.implode(')|(', self::$masks).')/is';
			
			$this->tokenize();
		}
		
		public function getList()
		{
			return $this->tokens;
		}
		
		/**
		 * @return OqlTokenContext
		**/
		public function getContext($index)
		{
			$line = null;
			$position = null;
			
			if (isset($this->tokens[$index])) {
				$offset = 0;
				
				for ($i = 0; $i < $index; $i++) {
					if (!isset($this->tokens[$i]))
						break;
					
					$offset += mb_strlen($this->tokens[$i]->getRawValue());
					
					if (isset($this->spaces[$i]))
						$offset += $this->spaces[$i];
				}
				
				$pos = mb_strpos(
					$this->string,
					$this->tokens[$index]->getRawValue(),
					$offset
				);
				
				if ($pos !== false) {
					$nlPositions = $this->getNewLinePositions($this->string, $pos);
					$line = count($nlPositions) + 1;
					
					if ($line > 1)
						$position = $pos - end($nlPositions);
					else
						$position = $pos + 1;
				}
			}
			
			return OqlTokenContext::create($line, $position);
		}
		
		public function getIndex()
		{
			return $this->index;
		}
		
		/**
		 * @return OqlTokenizer
		**/
		public function setIndex($index)
		{
			if ($index != -1)
				Assert::isIndexExists($this->tokens, $index);
			
			$this->index = $index;
			
			return $this;
		}
		
		/**
		 * @return OqlToken
		**/
		public function next()
		{
			$this->index++;
			
			return isset($this->tokens[$this->index])
				? $this->tokens[$this->index]
				: null;
		}
		
		/**
		 * @return OqlToken
		**/
		public function peek()
		{
			return isset($this->tokens[$this->index + 1])
				? $this->tokens[$this->index + 1]
				: null;
		}
		
		/**
		 * @return OqlTokenizer
		**/
		private function tokenize()
		{
			$pattern = self::$pattern;
			if (strlen($this->string) - mb_strlen($this->string) != 0)	// is multibyte
				$pattern .= 'u';
			
			preg_match_all($pattern, $this->string, $matches, PREG_SET_ORDER);
			
			$index = 0;
			foreach ($matches as $match) {
				$type = count($match) - 1;
				$value = $match[0];
				
				if ($type == OqlTokenType::WHITESPACE) {
					$this->spaces[$index] = strlen($value);
					continue;
				}
				
				$this->tokens[$index++] = OqlToken::create(
					$this->importTokenValue($value, $type),
					$value,
					$type
				);
			}
			
			return $this;
		}
		
		private static function importTokenValue($value, $type)
		{
			switch ($type) {
				case OqlTokenType::KEYWORD:
					return strtolower(
						preg_replace('/\s+/', ' ', $value)
					);
				
				case OqlTokenType::PLACEHOLDER:
					return intval(substr($value, 1));
				
				case OqlTokenType::NUMBER:
					return floatval($value);
				
				case OqlTokenType::OPERATOR:
					return $value == '<>'
						? BinaryExpression::NOT_EQUALS
						: strtolower($value);
				
				case OqlTokenType::AGGREGATE_FUNCTION:
					return strtolower($value);
				
				case OqlTokenType::BOOLEAN:
					return strtolower($value) != 'false';
				
				case OqlTokenType::NULL:
					return 'null';
				
				case OqlTokenType::STRING:
					$quote = mb_substr($value, 0, 1);
					
					return mb_ereg_replace(
						'\\\\'.$quote,
						$quote,
						mb_substr($value, 1, mb_strlen($value) - 2)
					);
			}
			
			return $value;
		}
		
		private static function getNewLinePositions($s, $length)
		{
			$s = mb_substr($s, 0, $length);
			
			$result = array();
			$offset = 0;
			
			while (($pos = mb_strpos($s, "\n", $offset)) !== false) {
				$result[] = $pos;
				$offset = $pos + 1;
			}
			
			return $result;
		}
	}
?>