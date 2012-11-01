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

	/**
	 * @ingroup OQL
	**/
	namespace Onphp;

	final class OqlTokenizer
	{
		private $tokens			= array();
		private $tokensCount	= 0;
		private $token			= null;
		private $prevToken		= null;
		private $index			= -1;
		
		private static $masks = array(
			OqlToken::NEW_LINE =>
				'\n',
			
			// "'`-quoted string constant
			OqlToken::STRING =>
				'"[^"\\\]*(?:\\\.[^"\\\]*)*"|\'[^\'\\\]*(?:\\\.[^\'\\\]*)*\'|`[^`\\\]*(?:\\\.[^`\\\]*)*`',
			
			// unsigned numeric constant
			OqlToken::NUMBER =>
				'(?:\b[\d]+)?\.?[\d]+(?:[eE][-+]?[\d]+)?\b',
			
			// boolean constant
			OqlToken::BOOLEAN =>
				'\b(?:true|false)\b',
			
			OqlToken::NULL =>
				'\bnull\b',
			
			// substitution
			OqlToken::SUBSTITUTION =>
				'\$[\d]+',
			
			// reserved word
			OqlToken::KEYWORD =>
				'\b(?:as|distinct|from|where|not|and|or|in|like|ilike|similar\s+to|between|is|group\s+by|order\s+by|asc|desc|having|limit|offset)\b',
			
			// aggregate function
			OqlToken::AGGREGATE_FUNCTION =>
				'\b(?:sum|avg|min|max|count)\b',
			
			// property, class name
			OqlToken::IDENTIFIER =>
				'\b[\\\\a-zA-Z_][\\\\a-zA-Z\d_]*(?:\.[\\\\a-zA-Z_][\\\\a-zA-Z\d_]+)*\b',
			
			// parentheses
			OqlToken::PARENTHESES =>
				'[\(\)]',
			
			// comma
			OqlToken::PUNCTUATION =>
				',',
			
			// comparison operators
			OqlToken::COMPARISON_OPERATOR =>
				'\>\=|\<\=|\<\>|\>|\<|\!\=|\=',
			
			// arithmetic operators
			OqlToken::ARITHMETIC_OPERATOR =>
				'\+|\-|\/|\*'
		);
		
		public function __construct($string)
		{
			$this->tokenize($string);
		}
		
		public function getList()
		{
			return $this->tokens;
		}
		
		public function getLine()
		{
			$token = $this->token;
			if (!$token)
				$token = $this->prevToken;
			
			return $token ? $token->getLine() : null;
		}
		
		public function getPosition()
		{
			$token = $this->token;
			if (!$token)
				$token = $this->prevToken;
			
			return $token ? $token->getPosition() : null;
		}
		
		public function getIndex()
		{
			return $this->index;
		}
		
		/**
		 * @return \Onphp\OqlTokenizer
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
		 * @return \Onphp\OqlToken
		**/
		public function get()
		{
			return $this->token;
		}
		
		/**
		 * @return \Onphp\OqlToken
		**/
		public function next()
		{
			$this->setIndex($this->index + 1);
			
			return $this->token;
		}
		
		/**
		 * @return \Onphp\OqlToken
		**/
		public function back()
		{
			$this->setIndex($this->index - 1);
			
			return $this->token;
		}
		
		/**
		 * @return \Onphp\OqlToken
		**/
		public function peek()
		{
			if ($this->token)
				$this->prevToken = $this->token;
			
			return $this->token = $this->getByIndex($this->index + 1);
		}
		
		/**
		 * @return \Onphp\OqlToken
		**/
		private function getByIndex($index)
		{
			return isset($this->tokens[$index]) ? $this->tokens[$index] : null;
		}
		
		/**
		 * @return \Onphp\OqlTokenizer
		**/
		private function tokenize($string)
		{
			Assert::isString($string);
			
			$maxMultibyteDelta = strlen($string) - mb_strlen($string);
			$isMultibyte = $maxMultibyteDelta > 0;
			
			$pattern = '/('.implode(')|(', self::$masks).')/is';
			if ($isMultibyte)
				$pattern .= 'u';
			
			preg_match_all(
				$pattern,
				$string,
				$matches,
				PREG_SET_ORDER | PREG_OFFSET_CAPTURE
			);
			
			$line = 1;
			$lineStart = 0;
			$multibyteDelta = 0;
			
			foreach ($matches as $match) {
				$type = count($match) - 1;
				$offset = $match[0][1] - $multibyteDelta;
				
				if ($type == OqlToken::NEW_LINE) {
					$line++;
					$lineStart = $offset + 1;
					continue;
				}
				
				$value = $match[0][0];
				$position = $offset - $lineStart;
				
				$this->tokens[] =
					OqlToken::make(
						$this->importTokenValue($value, $type),
						$value,
						$type,
						$line,
						$position
					);
				
				if (
					$type == OqlToken::KEYWORD
					&& ($pos = strpos($value, "\n")) !== false
				) {
					$line++;
					$lineStart = $offset + $pos + 1;
				}
				
				if ($isMultibyte && $type == OqlToken::STRING) {
					$multibyteDelta += (strlen($value) - mb_strlen($value));
					
					if ($multibyteDelta >= $maxMultibyteDelta)
						$isMultibyte = false;
				}
			}
			
			$this->tokensCount = count($this->tokens);
			
			return $this;
		}
		
		private static function importTokenValue($value, $type)
		{
			switch ($type) {
				case OqlToken::STRING:
					$quote = mb_substr($value, 0, 1);
					
					return mb_ereg_replace(
						'\\\\'.$quote,
						$quote,
						mb_substr($value, 1, mb_strlen($value) - 2)
					);
				
				case OqlToken::NUMBER:
					return floatval($value);
				
				case OqlToken::BOOLEAN:
					return strtolower($value) != 'false';
				
				case OqlToken::NULL:
					return 'null';
				
				case OqlToken::AGGREGATE_FUNCTION:
					return strtolower($value);
				
				case OqlToken::SUBSTITUTION:
					return intval(substr($value, 1));
				
				case OqlToken::KEYWORD:
					return strtolower(
						preg_replace('/\s+/', ' ', $value)
					);
				
				case OqlToken::COMPARISON_OPERATOR:
					return $value == '<>' ? BinaryExpression::NOT_EQUALS : $value;
			}
			
			return $value;
		}
	}
?>