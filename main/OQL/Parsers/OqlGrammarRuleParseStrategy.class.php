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
	abstract class OqlGrammarRuleParseStrategy extends Singleton
		implements Instantiatable
	{
		/**
		 * @throws SyntaxErrorException
		 * @return FIXME: syntax tree node
		**/
		abstract public function parse(
			OqlGrammarRule $rule,
			OqlTokenizer $tokenizer
		);
		
		/**
		 * @return FIXME: syntax tree node
		**/
		public function getNode(OqlGrammarRule $rule, OqlTokenizer $tokenizer)
		{
			try {
				return $this->parse($rule, $tokenizer);
			} catch (SyntaxErrorException $e) {
				return null;
			}
		}
		
		/**
		 * @throws SyntaxErrorException
		**/
		protected function raiseError(OqlTokenizer $tokenizer, $message)
		{
			throw new SyntaxErrorException(
				$message,
				$this->tokenizer->getLine(),
				$this->tokenizer->getPosition()
			);
		}
	}
?>