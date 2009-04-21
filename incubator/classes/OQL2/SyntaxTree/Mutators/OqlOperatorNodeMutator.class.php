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
	final class OqlOperatorNodeMutator extends OqlSyntaxNodeMutator
	{
		private static $operatorMap = array(
			'='				=> BinaryExpression::EQUALS,
			'!='			=> BinaryExpression::NOT_EQUALS,
			'and'			=> BinaryExpression::EXPRESSION_AND,
			'or'			=> BinaryExpression::EXPRESSION_OR,
			'>'				=> BinaryExpression::GREATER_THAN,
			'>='			=> BinaryExpression::GREATER_OR_EQUALS,
			'<'				=> BinaryExpression::LOWER_THAN,
			'<='			=> BinaryExpression::LOWER_OR_EQUALS,
			'like'			=> BinaryExpression::LIKE,
			'notlike'		=> BinaryExpression::NOT_LIKE,
			'ilike'			=> BinaryExpression::ILIKE,
			'notilike'		=> BinaryExpression::NOT_ILIKE,
			'similar to'	=> BinaryExpression::SIMILAR_TO,
			'notsimilar to'	=> BinaryExpression::NOT_SIMILAR_TO,
			'+'				=> BinaryExpression::ADD,
			'-'				=> BinaryExpression::SUBSTRACT,	// or PrefixUnaryExpression::MINUS
			'*'				=> BinaryExpression::MULTIPLY,
			'/'				=> BinaryExpression::DIVIDE,
			
			'isnull'		=> PostfixUnaryExpression::IS_NULL,
			'isnotnull'		=> PostfixUnaryExpression::IS_NOT_NULL,
			'istrue'		=> PostfixUnaryExpression::IS_TRUE,
			'isfalse'		=> PostfixUnaryExpression::IS_FALSE,
			
			'not'			=> PrefixUnaryExpression::NOT,
			
			'in'			=> InExpression::IN,
			'notin'			=> InExpression::NOT_IN
		);
		
		/**
		 * @return OqlOperatorNodeMutator
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlValueNode
		**/
		public function process(OqlSyntaxNode $node, OqlSyntaxNode $rootNode)
		{
			$iterator = OqlSyntaxTreeDeepRecursiveIterator::me();
			$node = $iterator->reset($node);
			
			$operator = '';
			
			do {
				$value = $node->toValue();
				$operator .= is_bool($value)
					? ($value === true ? 'true' : 'false')
					: $value;
			
			} while ($node = $iterator->next());
			
			Assert::isIndexExists(self::$operatorMap, $operator);
			
			return OqlValueNode::create()->setValue(
				self::$operatorMap[$operator]
			);
		}
	}
?>