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

	// TODO: use standart array iteration mechanism (current, next, prev)
	
	/**
	 * @ingroup OQL
	 * 
	 * NOTE: set mb_internal_encoding and mb_regex_encoding for correct multibyte strings parsing
	**/
	final class OqlTokenizer
	{
		private $string			= null;
		private $tokens			= array();
		private $tokensCount	= 0;
		private $token			= null;
		private $prevToken		= null;
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
				'[\S]+'
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
				
				for ($i = 0; $i <= $index; $i++) {
					if (!isset($this->tokens[$i]))
						break;
					
					$offset += $this->tokens[$i]->getRawValue();
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
			if ($index > $this->tokensCount - 1) {
				$index = $this->tokensCount - 1;
			
			} elseif ($index < -1) {
				$index = -1;
			}
			
			$this->index = $index;
			$this->token = $this->getByIndex($this->index);
			$this->prevToken = $this->getByIndex($this->index - 1);
			
			return $this;
		}
		
		/**
		 * @return OqlToken
		**/
		public function get()
		{
			return $this->token;
		}
		
		/**
		 * @return OqlToken
		**/
		public function next()
		{
			$this->setIndex($this->index + 1);
			
			return $this->token;
		}
		
		/**
		 * @return OqlToken
		**/
		public function back()
		{
			$this->setIndex($this->index - 1);
			
			return $this->token;
		}
		
		/**
		 * @return OqlToken
		**/
		public function peek()
		{
			if ($this->token)
				$this->prevToken = $this->token;
			
			return $this->token = $this->getByIndex($this->index + 1);
		}
		
		/**
		 * @return OqlToken
		**/
		private function getByIndex($index)
		{
			return isset($this->tokens[$index]) ? $this->tokens[$index] : null;
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
			
			foreach ($matches as $match) {
				$type = count($match) - 1;
				$value = $match[0];
				
				$this->tokens[] = OqlToken::create(
					$this->importTokenValue($value, $type),
					$value,
					$type
				);
			}
			
			$this->tokensCount = count($this->tokens);
			
			return $this;
		}
		
		private static function importTokenValue($value, $type)
		{
			switch ($type) {
				case OqlTokenType::STRING:
					$quote = mb_substr($value, 0, 1);
					
					return mb_ereg_replace(
						'\\\\'.$quote,
						$quote,
						mb_substr($value, 1, mb_strlen($value) - 2)
					);
				
				case OqlTokenType::NUMBER:
					return floatval($value);
				
				case OqlTokenType::BOOLEAN:
					return strtolower($value) != 'false';
				
				case OqlTokenType::NULL:
					return 'null';
				
				case OqlTokenType::AGGREGATE_FUNCTION:
					return strtolower($value);
				
				case OqlTokenType::PLACEHOLDER:
					return intval(substr($value, 1));
				
				case OqlTokenType::KEYWORD:
					return strtolower(
						preg_replace('/\s+/', ' ', $value)
					);
				
				case OqlTokenType::OPERATOR:
					return $value == '<>'
						? BinaryExpression::NOT_EQUALS
						: strtolower($value);
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