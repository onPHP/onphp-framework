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
		const NULL					= 1;
		const IDENTIFIER			= 2;
		const NUMBER				= 3;
		const BOOLEAN				= 4;
		const STRING				= 5;
		const PLACEHOLDER			= 6;
		const PUNCTUATION			= 7;
		const CONSTANT				= 8;
		const PATTERN				= 9;
		const OPEN_PARENTHESES		= 10;
		const CLOSE_PARENTHESES		= 11;
		
		const ARITHMETIC_OPERAND	= 12;
		const ARITHMETIC_EXPRESSION	= 13;
		
		const LOGICAL_OPERAND		= 14;
		const LOGICAL_UNARY_OPERAND	= 15;
		const LOGICAL_TERM			= 16;
		const LOGICAL_EXPRESSION	= 17;
		
		const PROPERTIES			= 18;
		const WHERE					= self::LOGICAL_EXPRESSION;
		const GROUP_BY				= 19;
		const ORDER_BY				= 20;
		const HAVING				= self::LOGICAL_EXPRESSION;
		const LIMIT					= 21;
		const OFFSET				= self::LIMIT;
		const SELECT				= 22;
		
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
				// FIXME: identifier name may be equal to reserved word or aggregate function name 
				set($this->terminal(self::IDENTIFIER, OqlTokenType::IDENTIFIER))->
				set($this->terminal(self::NUMBER, OqlTokenType::NUMBER))->
				set($this->terminal(self::BOOLEAN, OqlTokenType::BOOLEAN))->
				set($this->terminal(self::STRING, OqlTokenType::STRING))->
				set($this->terminal(self::PLACEHOLDER, OqlTokenType::PLACEHOLDER))->
				set($this->terminal(self::PUNCTUATION, OqlTokenType::PUNCTUATION));
			
			$this->
				set(
					OqlAlternationRule::create()->
						setId(self::CONSTANT)->
						add($this->get(self::STRING))->
						add($this->get(self::NUMBER))->
						add($this->get(self::BOOLEAN))->
						add($this->get(self::PLACEHOLDER))->
						add($this->get(self::NULL))
				)->
				set(
					OqlAlternationRule::create()->
						setId(self::PATTERN)->
						add($this->get(self::STRING))->
						add($this->get(self::PLACEHOLDER))
				)->
				set(
					$this->terminal(self::OPEN_PARENTHESES, OqlTokenType::PARENTHESES)->
						setValue('(')
				)->
				set(
					$this->terminal(self::CLOSE_PARENTHESES, OqlTokenType::PARENTHESES)->
						setValue(')')
				);
			
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
								setId(self::ARITHMETIC_EXPRESSION)
						)
					)
			);
			
			$this->set(
				OqlSequenceRule::create()->
					setId(self::ARITHMETIC_EXPRESSION)->
					add(
						OqlRepetitionRule::create()->
							setRule(
								OqlRepetitionRule::create()->
									setRule(
										OqlSequenceRule::create()->
											add(
												OqlOptionalRule::create()->setRule(
													$this->operator('-')
												)
											)->
											add(
												$this->get(self::ARITHMETIC_OPERAND)
											)
									)->
									setSeparator(
										$this->operator(array('*', '/'))
									)
							)->
							setSeparator(
								$this->operator(array('+', '-'))
							)
					)
			);
			
			$this->set(
				OqlAlternationRule::create()->
					setId(self::LOGICAL_OPERAND)->
					add($this->get(self::ARITHMETIC_EXPRESSION))->
					add($this->get(self::BOOLEAN))->
					add($this->get(self::STRING))->
					add($this->get(self::NULL))
			);
			
			$this->set(
				OqlAlternationRule::create()->
					setId(self::LOGICAL_UNARY_OPERAND)->
					add($this->get(self::IDENTIFIER))->
					add($this->get(self::PLACEHOLDER))->
					add($this->get(self::BOOLEAN))->
					add($this->get(self::NULL))
			);
			
			$this->set(
				OqlAlternationRule::create()->
					setId(self::LOGICAL_TERM)->
					add(
						OqlSequenceRule::create()->
							add($this->get(self::LOGICAL_OPERAND))->
							add($this->comparisonOperator())->
							add($this->get(self::LOGICAL_OPERAND))
					)->
					add(
						OqlSequenceRule::create()->
							add($this->get(self::LOGICAL_OPERAND))->
							add($this->keyword('is'))->
							add(
								OqlOptionalRule::create()->setRule(
									$this->operator('not')
								)
							)->
							add(
								OqlAlternationRule::create()->
									add($this->get(self::NULL))->
									add($this->get(self::BOOLEAN))
							)
					)->
					add(
						OqlSequenceRule::create()->
							add($this->get(self::LOGICAL_OPERAND))->
							add($this->keyword('in'))->
							add(
								OqlRepetitionRule::create()->
									setRule($this->get(self::CONSTANT))->
									setSeparator($this->get(self::PUNCTUATION))
							)
					)->
					add(
						OqlSequenceRule::create()->
							add($this->get(self::LOGICAL_OPERAND))->
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
							add(
								$this->get(self::PATTERN)
							)
					)->
					add(
						OqlSequenceRule::create()->
							add($this->get(self::LOGICAL_OPERAND))->
							add($this->keyword('between'))->
							add($this->get(self::LOGICAL_OPERAND))->
							add($this->operator('and'))->
							add($this->get(self::LOGICAL_OPERAND))
					)->
					add($this->get(self::LOGICAL_UNARY_OPERAND))->
					add(
						OqlParenthesesRule::create()->setRule(
							OqlGrammarRuleWrapper::create()->
								setGrammar($this)->
								setId(self::LOGICAL_EXPRESSION)
						)
					)
			);
			
			$this->set(
				OqlRepetitionRule::create()->
					setId(self::LOGICAL_EXPRESSION)->
					setRule(
						OqlRepetitionRule::create()->
							setRule(
								OqlSequenceRule::create()->
									add(
										OqlOptionalRule::create()->setRule(
											$this->operator('not')
										)
									)->
									add($this->get(self::LOGICAL_TERM))
							)->
							setSeparator($this->operator('and'))
					)->
					setSeparator($this->operator('or'))
			);
			
			$this->set(
				OqlRepetitionRule::create()->
					setId(self::PROPERTIES)->
					setRule(
						OqlSequenceRule::create()->
							add(
								OqlAlternationRule::create()->
									add(
										OqlSequenceRule::create()->
											add(
												OqlOptionalRule::create()->setRule(
													$this->keyword('distinct')
												)
											)->
											add($this->get(self::LOGICAL_EXPRESSION))
									)->
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
											add($this->get(self::ARITHMETIC_EXPRESSION))->
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
											add($this->get(self::LOGICAL_EXPRESSION))->
											add($this->get(self::CLOSE_PARENTHESES))
									)->
									add(
										OqlSequenceRule::create()->
											add(
												OqlOptionalRule::create()->setRule(
													$this->keyword('distinct')
												)
											)->
											add($this->get(self::LOGICAL_EXPRESSION))
									)
							)->
							add(
								OqlOptionalRule::create()->setRule(
									OqlSequenceRule::create()->
										add($this->keyword('as'))->
										add($this->get(self::IDENTIFIER))
								)
							)
					)->
					setSeparator($this->get(self::PUNCTUATION))
			);
			
			$this->set(
				OqlRepetitionRule::create()->
					setId(self::GROUP_BY)->
					setRule($this->get(self::IDENTIFIER))->
					setSeparator($this->get(self::PUNCTUATION))
			);
			
			$this->set(
				OqlRepetitionRule::create()->
					setId(self::ORDER_BY)->
					setRule(
						OqlSequenceRule::create()->
							add($this->get(self::LOGICAL_EXPRESSION))->
							add(
								OqlOptionalRule::create()->setRule(
									OqlAlternationRule::create()->
										add($this->keyword('asc'))->
										add($this->keyword('desc'))
								)
							)
					)->
					setSeparator($this->get(self::PUNCTUATION))
			);
			
			$this->set(
				OqlAlternationRule::create()->
					setId(self::LIMIT)->
					add($this->get(self::NUMBER))->
					add($this->get(self::PLACEHOLDER))
			);
			
			$this->set(
				OqlSequenceRule::create()->
					setId(self::SELECT)->
					add($this->get(self::PROPERTIES, false))->
					add($this->keyword('from'))->
					add($this->get(self::IDENTIFIER))->
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
					)
			);
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
		 * @return OqlTerminalRule
		**/
		private static function keyword($keyword)
		{
			return self::terminal(null, OqlTokenType::KEYWORD)->
				setValue($keyword);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function aggregate($aggregate)
		{
			return self::terminal(null, OqlTokenType::AGGREGATE_FUNCTION)->
				setValue($aggregate);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function comparisonOperator()
		{
			return self::operator(
				array('=', '!=', '<', '>', '>=', '<=')
			);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function operator($value)
		{
			return self::terminal(null, OqlTokenType::OPERATOR)->
				setValue($value);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function terminal($ruleId, $tokenTypeId)
		{
			return OqlTerminalRule::create()->
				setId($ruleId)->
				setType($tokenTypeId);
		}
	}
?>