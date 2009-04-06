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
	final class OqlBinaryOperatorNodeMutator extends OqlSyntaxNodeMutator
	{
		private static $operatorMap = array(
			'='					=> BinaryExpression::EQUALS,
			'!='				=> BinaryExpression::NOT_EQUALS,
			'and'				=> BinaryExpression::EXPRESSION_AND,
			'or'				=> BinaryExpression::EXPRESSION_OR,
			'>'					=> BinaryExpression::GREATER_THAN,
			'>='				=> BinaryExpression::GREATER_OR_EQUALS,
			'<'					=> BinaryExpression::LOWER_THAN,
			'<='				=> BinaryExpression::LOWER_OR_EQUALS,
			'like'				=> BinaryExpression::LIKE,
			'not like'			=> BinaryExpression::NOT_LIKE,
			'ilike'				=> BinaryExpression::ILIKE,
			'not ilike'			=> BinaryExpression::NOT_ILIKE,
			'similar to'		=> BinaryExpression::SIMILAR_TO,
			'not similar to'	=> BinaryExpression::NOT_SIMILAR_TO,
			'+'					=> BinaryExpression::ADD,
			'-'					=> BinaryExpression::SUBSTRACT,
			'*'					=> BinaryExpression::MULTIPLY,
			'/'					=> BinaryExpression::DIVIDE
		);
		
		/**
		 * @return OqlBinaryOperatorNodeMutator
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlValueNode
		**/
		public function process(OqlSyntaxNode $node)
		{
			$iterator = OqlSyntaxTreeRecursiveIterator::me();
			$iterator->reset($node);
			
			if ($node instanceof OqlNonterminalNode)
				$node = $iterator->next();
			
			$operator = '';
			
			if ($node->toValue() == 'not') {
				$operator .= 'not ';
				$node = $iterator->next();
			}
			
			$operator .= $node->toValue();
			
			Assert::isIndexExists(self::$operatorMap, $operator);
			Assert::isNull($iterator->next());
			
			return OqlValueNode::create()->setValue(
				self::$operatorMap[$operator]
			);
		}
	}
?>