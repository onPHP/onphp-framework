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
		protected $id			= null;
		protected $mutator		= null;
		
		protected $terminals	= null;
		
		// TODO: think about storing parse result in simplest structure (arrays) by default
		// (SyntaxNodeMutator -> SyntaxNodeBuilder)
		/**
		 * @return OqlSyntaxNode
		**/
		abstract protected function parse(
			OqlTokenizer $tokenizer,
			OqlSyntaxNode $rootNode,
			$silent = false
		);
		
		/**
		 * @return OqlGrammarRule
		**/
		abstract protected function buildTerminals();
		
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
		
		public function build()
		{
			if ($this->terminals === null) {
				$this->terminals = array();
				$this->buildTerminals();
			}
			
			return $this;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		public function process(
			OqlTokenizer $tokenizer,
			OqlSyntaxNode $rootNode,
			$silent = false
		)
		{
			$node = $this->parse($tokenizer, $rootNode, $silent);
			if ($node && $this->mutator)
				$node = $this->mutator->process($node, $rootNode);
			
			return $node;
		}
		
		protected function match($token)
		{
			Assert::isNotNull($this->terminals, 'build rule first');
			
			if ($token instanceof OqlToken) {
				foreach ($this->getTerminals() as $terminalToken) {
					if ($token->matchToken($terminalToken))
						return true;
				}
			}
			
			return false;
		}
		
		protected function getTerminals()
		{
			return $this->terminals;
		}
		
		/**
		 * @throws OqlSyntaxErrorException
		**/
		protected function raiseError(OqlTokenizer $tokenizer, $message)
		{
			throw new OqlSyntaxErrorException(
				$message, $tokenizer->getIndex()
			);
		}
	}
?>