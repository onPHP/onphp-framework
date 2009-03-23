<?php
/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
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
	final class OqlParser
	{
		private $grammar = null;
		
		/**
		 * @return OqlParser
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return FIXME
		**/
		public function getGrammar()
		{
			return $this->grammar;
		}
		
		// FIXME: grammar is set of rules?
		/**
		 * @return OqlParser
		**/
		public function setGrammar($grammar)
		{
			$this->grammar = $grammar;
			
			return $this;
		}
		
		public function parse($string)
		{
			Assert::isString($string);
			Assert::isNotNull($this->grammar, 'grammar must be set');
			
			$this->tokenizer = new OqlTokenizer($string);
			
			return $this->grammar->getParseStrategy()->parse(
				$this->grammar,
				$this->tokenizer
			);
		}
	}
?>