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

	// TODO: raise up rules on demand (not in constructor)
	/**
	 * @ingroup OQL
	**/
	final class OqlGrammar extends Singleton implements Instantiatable
	{
		const NULL						= 1;
		const IDENTIFIER				= 2;
		const NUMBER					= 3;
		const BOOLEAN					= 4;
		const STRING					= 5;
		const PLACEHOLDER				= 6;
		const PUNCTUATION				= 7;
		const CONSTANT					= 8;
		const PATTERN					= 9;
		const OPEN_PARENTHESES			= 10;
		const CLOSE_PARENTHESES			= 11;
		
		const ARITHMETIC_OPERAND		= 12;
		const ARITHMETIC_MUL_EXPRESSION	= 13;
		const ARITHMETIC_EXPRESSION		= 14;
		
		const LOGICAL_OPERAND			= 15;
		const LOGICAL_UNARY_OPERAND		= 16;
		const LOGICAL_TERM				= 17;
		const LOGICAL_AND_EXPRESSION	= 18;
		const LOGICAL_EXPRESSION		= 19;
		
		const MIXED_OPERAND				= 20;
		
		const PROPERTIES				= 21;
		const WHERE						= self::LOGICAL_EXPRESSION;
		const GROUP_BY					= 22;
		const ORDER_BY					= 23;
		const HAVING					= 24;
		const LIMIT						= 25;
		const OFFSET					= self::LIMIT;
		const SELECT					= 26;
		
		private $rules = array();
		
		/**
		 * @return OqlGrammar
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		protected function __construct()
		{
			$this->
				set($this->terminal(self::NULL, OqlTokenType::NULL))->
				set($this->terminal(self::NUMBER, OqlTokenType::NUMBER))->
				set($this->terminal(self::BOOLEAN, OqlTokenType::BOOLEAN))->
				set($this->terminal(self::STRING, OqlTokenType::STRING))->
				set(
					$this->terminal(self::PLACEHOLDER, OqlTokenType::PLACEHOLDER)->
						setMutator(OqlPlaceholderNodeMutator::me())
				)->
				set($this->terminal(self::PUNCTUATION, OqlTokenType::PUNCTUATION));
			
			//	<identifier> ::= <name> | <aggregate_function>
			$this->set(
				OqlAlternationRule::create()->
					setId(self::IDENTIFIER)->
					add($this->terminal(null, OqlTokenType::IDENTIFIER))->
					add($this->terminal(null, OqlTokenType::AGGREGATE_FUNCTION))
			);
			
			//	<constant> ::= <string> | ( [ "-" ] <number> ) | <placeholder> | <boolean> | <null>
			$this->set(
				OqlAlternationRule::create()->
					setId(self::CONSTANT)->
					add($this->get(self::STRING))->
					add(
						OqlSequenceRule::create()->
							add(
								OqlOptionalRule::create()->setRule(
									$this->operator('-')
								)->
								setMutator(OqlOperatorNodeMutator::me())
							)->
							add($this->get(self::NUMBER))->
							setMutator(OqlPrefixUnaryExpressionNodeMutator::me())
					)->
					add($this->get(self::PLACEHOLDER))->
					add($this->get(self::BOOLEAN))->
					add($this->get(self::NULL))
			);
			
			//	<pattern> ::= <string> | <placeholder>
			$this->set(
				OqlAlternationRule::create()->
					setId(self::PATTERN)->
					add($this->get(self::STRING))->
					add($this->get(self::PLACEHOLDER))
			);
			
			$this->
				set(
					$this->terminal(self::OPEN_PARENTHESES, OqlTokenType::PARENTHESES, '(')
				)->
				set(
					$this->terminal(self::CLOSE_PARENTHESES, OqlTokenType::PARENTHESES, ')')
				);
			
			//	<arithmetic_operand> ::=
			//		<identifier> | <number> | <placeholder> | ( "(" <arithmetic_expression> ")" )
			$this->set(
				OqlAlternationRule::create()->
					setId(self::ARITHMETIC_OPERAND)->
					add($this->get(self::IDENTIFIER))->
					add($this->get(self::NUMBER))->
					add($this->get(self::PLACEHOLDER))->
					add(
						OqlParenthesesRule::create()->setRule(
							OqlGrammarRuleWrapper::create()->
								setGrammar($this)->
								setRuleId(self::ARITHMETIC_EXPRESSION)
						)
					)
			);
			
			//	<arithmetic_mul_expression> ::=
			//		[ "-" ] <arithmetic_operand> [ ( "*" | "/" ) <arithmetic_mul_expression> ]
			$this->set(
				OqlSequenceRule::create()->
					setId(self::ARITHMETIC_MUL_EXPRESSION)->
					add(
						OqlSequenceRule::create()->
							add(
								OqlOptionalRule::create()->setRule(
									$this->operator('-')->
										setMutator(OqlOperatorNodeMutator::me())
								)
							)->
							add($this->get(self::ARITHMETIC_OPERAND))->
							setMutator(OqlPrefixUnaryExpressionNodeMutator::me())
					)->
					add(
						OqlOptionalRule::create()->setRule(
							OqlSequenceRule::create()->
								add(
									OqlAlternationRule::create()->
										add($this->operator('*'))->
										add($this->operator('/'))->
										setMutator(OqlOperatorNodeMutator::me())
								)->
								add(
									OqlGrammarRuleWrapper::create()->
										setGrammar($this)->
										setRuleId(self::ARITHMETIC_MUL_EXPRESSION)
								)
						)
					)->
					setMutator(OqlBinaryExpressionNodeMutator::me())
			);
			
			//	<arithmetic_expression> ::=
			//		<arithmetic_mul_expression> [ ( "+" | "-" ) <arithmetic_expression> ]
			$this->set(
				OqlSequenceRule::create()->
					setId(self::ARITHMETIC_EXPRESSION)->
					add($this->get(self::ARITHMETIC_MUL_EXPRESSION))->
					add(
						OqlOptionalRule::create()->setRule(
							OqlSequenceRule::create()->
								add(
									OqlAlternationRule::create()->
										add($this->operator('+'))->
										add($this->operator('-'))->
										setMutator(OqlOperatorNodeMutator::me())
								)->
								add(
									OqlGrammarRuleWrapper::create()->
										setGrammar($this)->
										setRuleId(self::ARITHMETIC_EXPRESSION)
								)
						)
					)->
					setMutator(OqlBinaryExpressionNodeMutator::me())
			);
			
			//	<logical_operand> ::=
			//		<arithmetic_expression> | <boolean> | <string> | <null>
			$this->set(
				OqlAlternationRule::create()->
					setId(self::LOGICAL_OPERAND)->
					add($this->get(self::ARITHMETIC_EXPRESSION))->
					add($this->get(self::BOOLEAN))->
					add($this->get(self::STRING))->
					add($this->get(self::NULL))
			);
			
			//	<logical_unary_operand> ::=
			//		<identifier> | <placeholder> | <boolean> | <null>
			$this->set(
				OqlAlternationRule::create()->
					setId(self::LOGICAL_UNARY_OPERAND)->
					add($this->get(self::IDENTIFIER))->
					add($this->get(self::PLACEHOLDER))->
					add($this->get(self::BOOLEAN))->
					add($this->get(self::NULL))
			);
			
			//	<logical_term> ::=
			//		(
			//			<logical_operand>
			//			(
			//				( <comparison_operator> <logical_operand> )
			//				| ( "is" ( ( [ "not" ] <null> ) | <boolean> ) )
			//				| ( [ "not" ] "in" "(" <constant> * ( "," <constant> ) ")" )
			//				| ( [ "not" ] ( "like" | "ilike" | "similar to" ) <pattern> )
			//				| ( "between" <logical_operand> "and" <logical_operand> )
			//			)
			//		)
			//		| <logical_unary_operand>
			//		| ( "(" <logical_expression> ")" )
			//		| ( "not" <logical_term> )
			$this->set(
				OqlAlternationRule::create()->
					setId(self::LOGICAL_TERM)->
					add(
						OqlSequenceRule::create()->
							add($this->get(self::LOGICAL_OPERAND))->
							add(
								OqlAlternationRule::create()->
									add(
										OqlSequenceRule::create()->
											add(
												OqlAlternationRule::create()->
													add($this->operator('='))->
													add($this->operator('!='))->
													add($this->operator('<'))->
													add($this->operator('>'))->
													add($this->operator('>='))->
													add($this->operator('<='))->
													setMutator(OqlOperatorNodeMutator::me())
											)->
											add($this->get(self::LOGICAL_OPERAND))
									)->
									add(
										OqlSequenceRule::create()->
											add(
												OqlSequenceRule::create()->
													add(
														OqlOptionalRule::create()->setRule(
															$this->operator('not')
														)
													)->
													add(
														OqlAlternationRule::create()->
															add($this->keyword('like'))->
															add($this->keyword('ilike'))->
															add($this->keyword('similar to'))
													)->
													setMutator(OqlOperatorNodeMutator::me())
											)->
											add(
												$this->get(self::PATTERN)
											)
									)
							)->
							setMutator(OqlBinaryExpressionNodeMutator::me())
					)->
					add(
						OqlSequenceRule::create()->
							add($this->get(self::LOGICAL_OPERAND))->
							add(
								OqlAlternationRule::create()->
									add(
										OqlSequenceRule::create()->
											add($this->keyword('is'))->
											add(
												OqlAlternationRule::create()->
													add(
														OqlSequenceRule::create()->
															add(
																OqlOptionalRule::create()->setRule(
																	$this->operator('not')
																)
															)->
															add($this->get(self::NULL))
													)->
													add($this->get(self::BOOLEAN))
											)->
											setMutator(OqlOperatorNodeMutator::me())
									)
							)->
							setMutator(OqlPostfixUnaryExpressionNodeMutator::me())
					)->
					add(
						OqlSequenceRule::create()->
							add($this->get(self::LOGICAL_OPERAND))->
							add(
								OqlSequenceRule::create()->
									add(
										OqlOptionalRule::create()->setRule(
											$this->operator('not')
										)
									)->
									add($this->keyword('in'))->
									setMutator(OqlOperatorNodeMutator::me())
							)->
							add(
								OqlParenthesesRule::create()->setRule(
									self::repetition(
										$this->get(self::CONSTANT),
										$this->get(self::PUNCTUATION)
									)
								)
							)->
							setMutator(OqlInExpressionNodeMutator::me())
					)->
					add(
						OqlSequenceRule::create()->
							add($this->get(self::LOGICAL_OPERAND))->
							add($this->keyword('between'))->
							add($this->get(self::LOGICAL_OPERAND))->
							add($this->operator('and'))->
							add($this->get(self::LOGICAL_OPERAND))->
							setMutator(OqlBetweenExpressionNodeMutator::me())
					)->
					add($this->get(self::LOGICAL_UNARY_OPERAND))->
					add(
						OqlParenthesesRule::create()->setRule(
							OqlGrammarRuleWrapper::create()->
								setGrammar($this)->
								setRuleId(self::LOGICAL_EXPRESSION)
						)
					)->
					add(
						OqlSequenceRule::create()->
							add(
								$this->operator('not')->
									setMutator(OqlOperatorNodeMutator::me())
							)->
							add(
								OqlGrammarRuleWrapper::create()->
									setGrammar($this)->
									setRuleId(self::LOGICAL_TERM)
							)->
							setMutator(OqlPrefixUnaryExpressionNodeMutator::me())
					)
			);
			
			//	<logical_and_expression> ::=
			//		<logical_term> [ "and" <logical_and_expression> ]
			$this->set(
				OqlSequenceRule::create()->
					setId(self::LOGICAL_AND_EXPRESSION)->
					add($this->get(self::LOGICAL_TERM))->
					add(
						OqlOptionalRule::create()->setRule(
							OqlSequenceRule::create()->
								add(
									$this->operator('and')->
										setMutator(OqlOperatorNodeMutator::me())
								)->
								add(
									OqlGrammarRuleWrapper::create()->
										setGrammar($this)->
										setRuleId(self::LOGICAL_AND_EXPRESSION)
								)
						)
					)->
					setMutator(OqlBinaryExpressionNodeMutator::me())
			);
			
			//	<logical_expression> ::=
			//		<logical_and_expression> [ "or" <logical_expression> ]
			$this->set(
				OqlSequenceRule::create()->
					setId(self::LOGICAL_EXPRESSION)->
					add($this->get(self::LOGICAL_AND_EXPRESSION))->
					add(
						OqlOptionalRule::create()->setRule(
							OqlSequenceRule::create()->
								add(
									$this->operator('or')->
										setMutator(OqlOperatorNodeMutator::me())
								)->
								add(
									OqlGrammarRuleWrapper::create()->
										setGrammar($this)->
										setRuleId(self::LOGICAL_EXPRESSION)
								)
						)
					)->
					setMutator(OqlBinaryExpressionNodeMutator::me())
			);
			
			//	<mixed_operand> ::= <arithmetic_expression> || <logical_expression>
			$this->set(
				OqlGreedyAlternationRule::create()->
					setId(self::MIXED_OPERAND)->
					add($this->get(self::ARITHMETIC_EXPRESSION))->
					add($this->get(self::LOGICAL_EXPRESSION))
			);
			
			//	<property> ::=
			//		(
			//			( ( "sum" | "avg" | "min" | "max" ) "(" <mixed_operand> ")" )
			//			| ( "count" "(" [ "distinct" ] <mixed_operand> ")" )
			//			| ( [ "distinct" ] <mixed_operand> )
			//		)
			//		[ "as" <identifier> ]
			
			//	<properties> ::= <property> * ( "," <property> )
			$this->set(
				self::repetition(
					OqlSequenceRule::create()->
						add(
							OqlAlternationRule::create()->
								add(
									OqlSequenceRule::create()->
										add(
											OqlAlternationRule::create()->
												add($this->aggregate('sum'))->
												add($this->aggregate('avg'))->
												add($this->aggregate('min'))->
												add($this->aggregate('max'))
										)->
										add($this->get(self::OPEN_PARENTHESES))->
										add($this->get(self::MIXED_OPERAND))->
										add($this->get(self::CLOSE_PARENTHESES))
								)->
								add(
									OqlSequenceRule::create()->
										add($this->aggregate('count'))->
										add($this->get(self::OPEN_PARENTHESES))->
										add(
											OqlOptionalRule::create()->setRule(
												$this->keyword('distinct')
											)
										)->
										add($this->get(self::MIXED_OPERAND))->
										add($this->get(self::CLOSE_PARENTHESES))
								)->
								add(
									OqlSequenceRule::create()->
										add(
											OqlOptionalRule::create()->setRule(
												$this->keyword('distinct')
											)
										)->
										add($this->get(self::MIXED_OPERAND))
								)
						)->
						add(
							OqlOptionalRule::create()->setRule(
								OqlSequenceRule::create()->
									add($this->keyword('as'))->
									add($this->get(self::IDENTIFIER))
							)
						)->
						setMutator(OqlProjectionNodeMutator::me()),
					$this->get(self::PUNCTUATION)
				)->
				setId(self::PROPERTIES)->
				setMutator(OqlProjectionChainNodeMutator::me())
			);
			
			// TODO: <group_by> ::= <mixed_operand> * ( "," <mixed_operand> )
			//	<group_by> ::= <identifier> * ( "," <identifier> )
			$this->set(
				self::repetition(
					OqlGrammarRuleWrapper::create()->
						setGrammar($this)->
						setRuleId(self::IDENTIFIER)->
						setMutator(OqlGroupByProjectionNodeMutator::me()),
					$this->get(self::PUNCTUATION)
				)->
				setId(self::GROUP_BY)->
				setMutator(OqlProjectionChainNodeMutator::me())
			);
			
			//	<order_by> ::=
			//		<mixed_operand> [ "asc" | "desc" ]
			//		* ( "," <mixed_operand> [ "asc" | "desc" ] )
			$this->set(
				self::repetition(
					OqlSequenceRule::create()->
						add($this->get(self::MIXED_OPERAND))->
						add(
							OqlOptionalRule::create()->setRule(
								OqlAlternationRule::create()->
									add($this->keyword('asc'))->
									add($this->keyword('desc'))
							)
						)->
						setMutator(OqlOrderByNodeMutator::me()),
					$this->get(self::PUNCTUATION)
				)->
				setId(self::ORDER_BY)->
				setMutator(OqlOrderChainNodeMutator::me())
			);
			
			//	<limit> ::= <number> | <placeholder>
			$this->set(
				OqlAlternationRule::create()->
					setId(self::LIMIT)->
					add($this->get(self::NUMBER))->
					add($this->get(self::PLACEHOLDER))
			);
			
			//	<having> ::= <logical_expression>
			$this->set(
				OqlGrammarRuleWrapper::create()->
					setId(self::HAVING)->
					setGrammar($this)->
					setRuleId(self::LOGICAL_EXPRESSION)->
					setMutator(
						OqlHavingProjectionNodeMutator::me()
					)
			);
			
			//	<where>  ::= <logical_expression>
			
			//	<offset> ::= <limit>
			
			//	<select> ::=
			//		[ <properties> ]
			//		"from" <identifier>
			//		[ "where" <where> ]
			//		[ "group by" <group_by> ]
			//		[ "order by" <order_by> ]
			//		[ "having" <having> ]
			//		[ "limit" <limit> ]
			//		[ "offset" <offset> ]
			$this->set(
				OqlSequenceRule::create()->
					setId(self::SELECT)->
					add(
						OqlOptionalRule::create()->setRule(
							$this->get(self::PROPERTIES)
						)
					)->
					add($this->keyword('from'))->
					add(
						OqlGrammarRuleWrapper::create()->
							setGrammar($this)->
							setRuleId(self::IDENTIFIER)->
							setMutator(OqlProtoDAONodeMutator::me())
					)->
					add(
						OqlOptionalRule::create()->setRule(
							OqlSequenceRule::create()->
								add($this->keyword('where'))->
								add($this->get(self::WHERE))
						)
					)->
					add(
						OqlOptionalRule::create()->setRule(
							OqlSequenceRule::create()->
								add($this->keyword('group by'))->
								add($this->get(self::GROUP_BY))
						)
					)->
					add(
						OqlOptionalRule::create()->setRule(
							OqlSequenceRule::create()->
								add($this->keyword('order by'))->
								add($this->get(self::ORDER_BY))
						)
					)->
					add(
						OqlOptionalRule::create()->setRule(
							OqlSequenceRule::create()->
								add($this->keyword('having'))->
								add($this->get(self::HAVING))
						)
					)->
					add(
						OqlOptionalRule::create()->setRule(
							OqlSequenceRule::create()->
								add($this->keyword('limit'))->
								add($this->get(self::LIMIT))
						)
					)->
					add(
						OqlOptionalRule::create()->setRule(
							OqlSequenceRule::create()->
								add($this->keyword('offset'))->
								add($this->get(self::OFFSET))
						)
					)->
					setMutator(OqlCriteriaNodeMutator::me())
			);
			
			$this->get(self::SELECT)->build();
		}
		
		/**
		 * @throws MissingElementException
		 * @return OqlGrammarRule
		**/
		public function get($id)
		{
			if (isset($this->rules[$id]))
				return $this->rules[$id];
			
			throw new MissingElementException(
				'knows nothing about rule '.$id
			);
		}
		
		public function has($id)
		{
			return isset($this->rules[$id]);
		}
		
		/**
		 * @return OqlGrammar
		**/
		private function set(OqlGrammarRule $rule)
		{
			Assert::isNotNull($rule->getId());
			
			$this->rules[$rule->getId()] = $rule;
			
			return $this;
		}
		
		/**
		 * @return OqlRepetitionRule
		**/
		private static function repetition(
			OqlGrammarRule $rule,
			OqlGrammarRule $separatorRule
		)
		{
			return OqlSequenceRule::create()->
				add($rule)->
				add(
					OqlOptionalRule::create()->setRule(
						OqlRepetitionRule::create()->setRule(
							OqlSequenceRule::create()->
								add($separatorRule)->
								add($rule)
						)
					)
				);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function keyword($value)
		{
			return self::terminal(null, OqlTokenType::KEYWORD, $value);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function aggregate($value)
		{
			return self::terminal(null, OqlTokenType::AGGREGATE_FUNCTION, $value);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function operator($value)
		{
			return self::terminal(null, OqlTokenType::OPERATOR, $value);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function terminal($ruleId, $typeId, $value = null)
		{
			return OqlTerminalRule::create()->
				setId($ruleId)->
				setType($typeId)->
				setValue($value);
		}
	}
?>