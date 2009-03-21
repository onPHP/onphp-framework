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

	// TODO: use flyweights for terminals and top-level rules (beware of "optional()" for terminals)
	/**
	 * @ingroup OQL
	**/
	final class OqlGrammar extends StaticFactory
	{
		/**
		 * @return OqlChainRule
		**/
		public static function select()
		{
			return OqlChainRule::create()->
				add(
					self::properties()->
						optional()
				)->
				add(self::keyword('from'))->
				add(self::identifier())->
				add(
					OqlChainRule::create()->
						optional()->
						add(self::keyword('where'))->
						add(self::where())
				)->
				add(
					OqlChainRule::create()->
						optional()->
						add(self::keyword('group by'))->
						add(self::groupBy())
				)->
				add(
					OqlChainRule::create()->
						optional()->
						add(self::keyword('order by'))->
						add(self::orderBy())
				)->
				add(
					OqlChainRule::create()->
						optional()->
						add(self::keyword('having'))->
						add(self::having())
				)->
				add(
					OqlChainRule::create()->
						optional()->
						add(self::keyword('limit'))->
						add(self::limit())
				)->
				add(
					OqlChainRule::create()->
						optional()->
						add(self::keyword('offset'))->
						add(self::offset())
				);
		}
		
		/**
		 * @return OqlSequenceRule
		**/
		public static function properties()
		{
			return OqlSequenceRule::create()->
				setRule(
					OqlChainRule::create()->
						add(
							OqlAlternateRule::create()->
								add(
									OqlChainRule::create()->
										add(
											OqlAlternateRule::create()->
												add(self::keyword('sum'))->
												add(self::keyword('avg'))->
												add(self::keyword('min'))->
												add(self::keyword('max'))
										)->
										add(self::parentheses(true))->
										add(self::arithmeticExpression())->
										add(self::parentheses(false))
								)->
								add(
									OqlChainRule::create()->
										add(self::keyword('count'))->
										add(self::parentheses(true))->
										add(
											self::keyword('distinct')->
												optional()
										)->
										add(self::logicalExpression())->
										add(self::parentheses(false))
								)->
								add(
									OqlChainRule::create()->
										add(
											self::keyword('distinct')->
												optional()
										)->
										add(self::logicalExpression())
								)
						)->
						add(
							OqlChainRule::create()->
								optional()->
								add(self::keyword('as'))->
								add(self::identifier())
						)
				)->
				setSeparator(self::punctuation());
		}
		
		/**
		 * @return OqlChainRule
		**/
		public static function where()
		{
			return self::logicalExpression();
		}
		
		/**
		 * @return OqlSequenceRule
		**/
		public static function groupBy()
		{
			return OqlSequenceRule::create()->
				setRule(self::identifier())->
				setSeparator(self::punctuation());
		}
		
		/**
		 * @return OqlSequenceRule
		**/
		public static function orderBy()
		{
			return OqlSequenceRule::create()->
				setRule(
					OqlChainRule::create()->
						add(self::logicalExpression())->
						add(
							OqlAlternateRule::create()->
								optional()->
								add(self::keyword('asc'))->
								add(self::keyword('desc'))
						)
				)->
				setSeparator(self::punctuation());
		}
		
		/**
		 * @return OqlChainRule
		**/
		public static function having()
		{
			return self::logicalExpression();
		}
		
		/**
		 * @return OqlAlternateRule
		**/
		public static function limit()
		{
			return OqlAlternateRule::create()->
				add(self::number())->
				add(self::placeholder());
		}
		
		/**
		 * @return OqlAlternateRule
		**/
		public static function offset()
		{
			return OqlAlternateRule::create()->
				add(self::number())->
				add(self::placeholder());
		}
		
		/**
		 * @return OqlChainRule
		**/
		private static function arithmeticExpression()
		{
			return OqlChainRule::create()->
				add(self::operator('-'))->
				add(
					self::parenthesesRule(
						OqlSequenceRule::create()->
							setRule(
								OqlSequenceRule::create()->
									setRule(
										self::arithmeticTerm()
									)->
									setSeparator(
										self::operatorList(array('*', '/'))
									)
							)->
							setSeparator(
								self::operatorList(array('+', '-'))
							)
					)
				);
		}
		
		/**
		 * @return OqlAlternateRule
		**/
		private static function arithmeticTerm()
		{
			return OqlAlternateRule::create()->
				add(self::identifier())->
				add(self::number());
		}
		
		/**
		 * @return OqlChainRule
		**/
		private static function logicalExpression()
		{
			return OqlChainRule::create()->
				add(
					self::operator('not')->
						optional()
				)->
				add(
					self::parenthesesRule(
						OqlSequenceRule::create()->
							setRule(
								OqlSequenceRule::create()->
									setRule(self::logicalTerm())->
									setSeparator(self::operator('and'))
							)->
							setSeparator(self::operator('or'))
					)
				);
		}
		
		/**
		 * @return OqlChainRule
		**/
		private static function logicalTerm()
		{
			return OqlChainRule::create()->
				add(self::logicalOperand())->
				add(
					OqlAlternateRule::create()->
						add(
							OqlChainRule::create()->
								add(self::comparisonOperator())->
								add(self::logicalOperand())
						)->
						add(
							OqlChainRule::create()->
								add(self::keyword('is'))->
								add(
									self::operator('not')->
										optional()
								)->
								add(
									OqlAlternateRule::create()->
										add(self::null())->
										add(self::boolean())
								)
						)->
						add(
							OqlChainRule::create()->
								add(
									self::operator('not')->
										optional()
								)->
								add(self::keyword('in'))->
								add(
									OqlSequenceRule::create()->
										setRule(self::constant())->
										setSeparator(self::punctuation())
								)
						)->
						add(
							OqlChainRule::create()->
								add(
									self::operator('not')->
										optional()
								)->
								add(
									OqlAlternateRule::create()->
										add(self::keyword('like'))->
										add(self::keyword('ilike'))->
										add(self::keyword('similar to'))
								)->
								add(
									OqlAlternateRule::create()->
										add(self::string())->
										add(self::placeholder())
								)
						)->
						add(
							OqlChainRule::create()->
								add(self::keyword('between'))->
								add(self::logicalOperand())->
								add(self::operator('and'))->
								add(self::logicalOperand())
						)
					
				);
		}
		
		/**
		 * @return OqlAlternateRule
		**/
		private static function logicalOperand()
		{
			return OqlAlternateRule::create()->
				add(self::arithmeticExpression())->
				add(self::boolean())->
				add(self::string());
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function keyword($keyword)
		{
			return OqlTerminalRuleRule::create()->
				setType(OqlToken::KEYWORD)->
				setValue($keyword);
		}
		
		/**
		 * @return OqlAlternateRule
		**/
		private static function constant()
		{
			return OqlAlternateRule::create()->
				add(self::string())->
				add(self::number())->
				add(self::boolean())->
				add(self::placeholder())->
				add(self::null());
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function identifier()
		{
			return OqlTerminalRule::create()->
				setType(OqlToken::IDENTIFIER);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function number()
		{
			return OqlTerminalRule::create()->
				setType(OqlToken::NUMBER);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function boolean()
		{
			return OqlTerminalRule::create()->
				setType(OqlToken::BOOLEAN);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function null()
		{
			return OqlTerminalRule::create()->
				setType(OqlToken::NULL);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function string()
		{
			return OqlTerminalRule::create()->
				setType(OqlToken::STRING);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function placeholder()
		{
			return OqlTerminalRule::create()->
				setType(OqlToken::PLACEHOLDER);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function comparisonOperator()
		{
			return self::operatorList(
				array('=', '!=', '<', '>', '>=', '<=')
			);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function operator($value)
		{
			return OqlTerminalRule::create()->
				setType(OqlToken::OPERATOR);
				setValue($value);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function operatorList(array $list)
		{
			return OqlTerminalRule::create()->
				setType(OqlToken::OPERATOR);
				setList($list);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function punctuation()
		{
			return OqlTerminalRule::create()->
				setType(OqlToken::PUNCTUATION);
		}
		
		/**
		 * @return OqlTerminalRule
		**/
		private static function parentheses($open)
		{
			return OqlTerminalRule::create()->
				setType(OqlToken::PARENTHESES)->
				setValue($open ? '(' : ')');
		}
		
		/**
		 * @return OqlAlternateRule
		**/
		private static function parenthesesRule(OqlGrammarRule $rule)
		{
			return OqlAlternateRule::create()->
				add($rule)->
				add(
					OqlChainRule::create()->
						add(self::parentheses(true))->
						add($rule)->
						add(self::parentheses(false))
				);
		}
	}
?>