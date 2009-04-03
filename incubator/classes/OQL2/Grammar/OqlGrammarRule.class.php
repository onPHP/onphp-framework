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
	abstract class OqlGrammarRule implements Identifiable
	{
		protected $id		= null;
		protected $mutator	= null;
		
		/**
		 * @return OqlGrammarRuleParseStrategy
		**/
		abstract public function getParseStrategy();
		
		public function getId()
		{
			return $this->id;
		}
		
		/**
		 * @return OqlGrammarRule
		**/
		public function setId($id)
		{
			$this->id = $id;
			
			return $this;
		}
		
		/**
		 * @return OqlSyntaxNodeMutator
		**/
		public function getMutator()
		{
			return $this->mutator;
		}
		
		/**
		 * @return OqlGrammarRule
		**/
		public function setMutator(OqlSyntaxNodeMutator $mutator)
		{
			$this->mutator = $mutator;
			
			return $this;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function process(OqlTokenizer $tokenizer, $silent = false)
		{
			$node = $this->getParseStrategy()->parse($this, $tokenizer, $silent);
			if ($node && $this->mutator)
				$node = $this->mutator->process($node);
			
			return $node;
		}
	}
?>