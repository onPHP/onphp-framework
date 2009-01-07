<?php
/****************************************************************************
 *   Copyright (C) 2008 by Vladlen Y. Koshelev                              *
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
		
		private $string			= null;
		private $offset			= 0;
		private $nlPositions	= array();
		
		private static $masks = array(
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
				'\b[a-zA-Z_][a-zA-Z\d_]*(?:\.[a-zA-Z_][a-zA-Z\d_]+)*\b',
			
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
			
			$this->string = $string;
			
			$offset = 0;
			while (
				($position = mb_strpos($string, "\n", $offset)) !== false
			) {
				$this->nlPositions[] = $position;
				$offset = $position + 1;
			}
			$this->nlPositions[] = mb_strlen($string);
			
			preg_replace_callback(
				'/('.implode(')|(', self::$masks).')/ius',
				array($this, 'makeToken'),
				$string
			);
			
			$this->tokensCount = count($this->tokens);
			
			return $this;
		}
		
		private function makeToken($matches)
		{
			$value = $matches[0];
			$line = $position = null;
			$absolutePosition = mb_strpos($this->string, $value, $this->offset);
			
			if ($absolutePosition !== false) {
				$this->offset = $absolutePosition + mb_strlen($value);
				
				$prevNlPosition = -1;
				
				foreach ($this->nlPositions as $lineIndex => $nlPosition) {
					if (
						$absolutePosition > $prevNlPosition
						&& $absolutePosition <= $nlPosition
					) {
						$line = $lineIndex + 1;
						
						$position = $absolutePosition;
						if ($prevNlPosition >= 0)
							$position -= $prevNlPosition + 1;
						
						break;
					}
					
					$prevNlPosition = $nlPosition;
				}
			}
			
			$keys = array_keys($matches, $value);
			$type = $keys[1];
			
			$this->tokens[] =
				OqlToken::make(
					$this->importTokenValue($value, $type),
					$value,
					$type,
					$line,
					$position
				);
			
			return $value;
		}
		
		private function importTokenValue($value, $type)
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
					return mb_strtolower($value) == 'false' ? false : true;
				
				case OqlToken::NULL:
				case OqlToken::AGGREGATE_FUNCTION:
					return mb_strtolower($value);
				
				case OqlToken::SUBSTITUTION:
					return intval(mb_substr($value, 1));
				
				case OqlToken::KEYWORD:
					return mb_strtolower(
						preg_replace('/\s+/u', ' ', $value)
					);
				
				case OqlToken::COMPARISON_OPERATOR:
					return $value == '<>' ? BinaryExpression::NOT_EQUALS : $value;
			}
			
			return $value;
		}
	}
?>