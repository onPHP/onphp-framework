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
	**/
	final class OqlTokenizer
	{
		private $tokens			= array();
		private $tokensCount	= 0;
		private $token			= null;
		private $prevToken		= null;
		private $index			= -1;
		
		private static $masks = array(
			OqlTokenType::NEW_LINE =>
				'\n',
			
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
				'\b[a-zA-Z_][a-zA-Z\d_]*(?:\.[a-zA-Z_][a-zA-Z\d_]+)*\b'
		);
		
		private static $pattern = null;
		
		public function __construct($string)
		{
			if (!self::$pattern)
				self::$pattern = '/('.implode(')|(', self::$masks).')/is';
			
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
		private function tokenize($string)
		{
			Assert::isString($string);
			
			$maxMultibyteDelta = strlen($string) - mb_strlen($string);
			$isMultibyte = $maxMultibyteDelta > 0;
			
			$pattern = self::$pattern;
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
				
				if ($type == OqlTokenType::NEW_LINE) {
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
					$type == OqlTokenType::KEYWORD
					&& ($pos = strpos($value, "\n")) !== false
				) {
					$line++;
					$lineStart = $offset + $pos + 1;
				}
				
				if ($isMultibyte && $type == OqlTokenType::STRING) {
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
	}
?>