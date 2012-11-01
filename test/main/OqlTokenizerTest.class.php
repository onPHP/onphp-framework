<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class OqlTokenizerTest extends TestCase
	{
		public function testEmpty()
		{
			$this->
				assertTokens(
					'',
					array()
				)->
				assertTokens(
					" \t\n\r\n",
					array()
				);
		}
		
		public function testPosition()
		{
			$this->assertTokens(
				"\n1\n\r1\r\n\n1\n",
				array(
					\Onphp\OqlToken::make(1., '1', \Onphp\OqlToken::NUMBER, 2, 0),
					\Onphp\OqlToken::make(1., '1', \Onphp\OqlToken::NUMBER, 3, 1),
					\Onphp\OqlToken::make(1., '1', \Onphp\OqlToken::NUMBER, 5, 0)
				)
			);
		}
		
		public function testString()
		{
			$this->assertTokens(
				'"" "some string \"substring1\" \'substring2\' `substring3`"  '
				."'многа \' \" ` букаф' `strange quotes` ",
				array(
					\Onphp\OqlToken::make(
						'',
						'""',
						\Onphp\OqlToken::STRING,
						1,
						0
					),
					\Onphp\OqlToken::make(
						'some string "substring1" \'substring2\' `substring3`',
						'"some string \"substring1\" \'substring2\' `substring3`"',
						\Onphp\OqlToken::STRING,
						1,
						3
					),
					\Onphp\OqlToken::make(
						"многа ' \" ` букаф",
						"'многа \' \" ` букаф'",
						\Onphp\OqlToken::STRING,
						1,
						59
					),
					\Onphp\OqlToken::make(
						'strange quotes',
						'`strange quotes`',
						\Onphp\OqlToken::STRING,
						1,
						80
					)
				)
			);
		}
		
		public function testNumber()
		{
			$this->assertTokens(
				'123 +123 .123 123.456 1e23 1E+23 1E-23 0.1e23 -.1e23',
				array(
					\Onphp\OqlToken::make(123., '123', \Onphp\OqlToken::NUMBER, 1, 0),
					\Onphp\OqlToken::make('+', '+', \Onphp\OqlToken::ARITHMETIC_OPERATOR, 1, 4),
					\Onphp\OqlToken::make(123., '123', \Onphp\OqlToken::NUMBER, 1, 5),
					\Onphp\OqlToken::make(.123, '.123', \Onphp\OqlToken::NUMBER, 1, 9),
					\Onphp\OqlToken::make(123.456, '123.456', \Onphp\OqlToken::NUMBER, 1, 14),
					\Onphp\OqlToken::make(1e23, '1e23', \Onphp\OqlToken::NUMBER, 1, 22),
					\Onphp\OqlToken::make(1e23, '1E+23', \Onphp\OqlToken::NUMBER, 1, 27),
					\Onphp\OqlToken::make(1e-23, '1E-23', \Onphp\OqlToken::NUMBER, 1, 33),
					\Onphp\OqlToken::make(1e22, '0.1e23', \Onphp\OqlToken::NUMBER, 1, 39),
					\Onphp\OqlToken::make('-', '-', \Onphp\OqlToken::ARITHMETIC_OPERATOR, 1, 46),
					\Onphp\OqlToken::make(1e22, '.1e23', \Onphp\OqlToken::NUMBER, 1, 47)
				)
			);
		}
		
		public function testBoolean()
		{
			$this->assertTokens(
				'TrUe falSE',
				array(
					\Onphp\OqlToken::make(true, 'TrUe', \Onphp\OqlToken::BOOLEAN, 1, 0),
					\Onphp\OqlToken::make(false, 'falSE', \Onphp\OqlToken::BOOLEAN, 1, 5)
				)
			);
		}
		
		public function testNull()
		{
			$this->assertTokens(
				'null testNULL nullTest testNULLtest NULL',
				array(
					\Onphp\OqlToken::make('null', 'null', \Onphp\OqlToken::NULL, 1, 0),
					\Onphp\OqlToken::make('testNULL', 'testNULL', \Onphp\OqlToken::IDENTIFIER, 1, 5),
					\Onphp\OqlToken::make('nullTest', 'nullTest', \Onphp\OqlToken::IDENTIFIER, 1, 14),
					\Onphp\OqlToken::make('testNULLtest', 'testNULLtest', \Onphp\OqlToken::IDENTIFIER, 1, 23),
					\Onphp\OqlToken::make('null', 'NULL', \Onphp\OqlToken::NULL, 1, 36)
				)
			);
		}
		
		public function testSubstitution()
		{
			$this->assertTokens(
				' $1 $22 $ ',
				array(
					\Onphp\OqlToken::make(1, '$1', \Onphp\OqlToken::SUBSTITUTION, 1, 1),
					\Onphp\OqlToken::make(22, '$22', \Onphp\OqlToken::SUBSTITUTION, 1, 4)
				)
			);
		}
		
		public function testKeyword()
		{
			$this->assertTokens(
				"distinct From WHERE like between group \n\t\r by "
				."ORDER BY asc desc having limit offset not and or "
				."as in is SIMILAR  TO ilike",
				array(
					\Onphp\OqlToken::make('distinct', 'distinct', \Onphp\OqlToken::KEYWORD, 1, 0),
					\Onphp\OqlToken::make('from', 'From', \Onphp\OqlToken::KEYWORD, 1, 9),
					\Onphp\OqlToken::make('where', 'WHERE', \Onphp\OqlToken::KEYWORD, 1, 14),
					\Onphp\OqlToken::make('like', 'like', \Onphp\OqlToken::KEYWORD, 1, 20),
					\Onphp\OqlToken::make('between', 'between', \Onphp\OqlToken::KEYWORD, 1, 25),
					\Onphp\OqlToken::make('group by', "group \n\t\r by", \Onphp\OqlToken::KEYWORD, 1, 33),
					\Onphp\OqlToken::make('order by', 'ORDER BY', \Onphp\OqlToken::KEYWORD, 2, 6),
					\Onphp\OqlToken::make('asc', 'asc', \Onphp\OqlToken::KEYWORD, 2, 15),
					\Onphp\OqlToken::make('desc', 'desc', \Onphp\OqlToken::KEYWORD, 2, 19),
					\Onphp\OqlToken::make('having', 'having', \Onphp\OqlToken::KEYWORD, 2, 24),
					\Onphp\OqlToken::make('limit', 'limit', \Onphp\OqlToken::KEYWORD, 2, 31),
					\Onphp\OqlToken::make('offset', 'offset', \Onphp\OqlToken::KEYWORD, 2, 37),
					\Onphp\OqlToken::make('not', 'not', \Onphp\OqlToken::KEYWORD, 2, 44),
					\Onphp\OqlToken::make('and', 'and', \Onphp\OqlToken::KEYWORD, 2, 48),
					\Onphp\OqlToken::make('or', 'or', \Onphp\OqlToken::KEYWORD, 2, 52),
					\Onphp\OqlToken::make('as', 'as', \Onphp\OqlToken::KEYWORD, 2, 55),
					\Onphp\OqlToken::make('in', 'in', \Onphp\OqlToken::KEYWORD, 2, 58),
					\Onphp\OqlToken::make('is', 'is', \Onphp\OqlToken::KEYWORD, 2, 61),
					\Onphp\OqlToken::make('similar to', 'SIMILAR  TO', \Onphp\OqlToken::KEYWORD, 2, 64),
					\Onphp\OqlToken::make('ilike', 'ilike', \Onphp\OqlToken::KEYWORD, 2, 76)
				)
			);
		}
			
		public function testAggregateFunction()
		{
			$this->assertTokens(
				'SUM aVg min Max count',
				array(
					\Onphp\OqlToken::make('sum', 'SUM', \Onphp\OqlToken::AGGREGATE_FUNCTION, 1, 0),
					\Onphp\OqlToken::make('avg', 'aVg', \Onphp\OqlToken::AGGREGATE_FUNCTION, 1, 4),
					\Onphp\OqlToken::make('min', 'min', \Onphp\OqlToken::AGGREGATE_FUNCTION, 1, 8),
					\Onphp\OqlToken::make('max', 'Max', \Onphp\OqlToken::AGGREGATE_FUNCTION, 1, 12),
					\Onphp\OqlToken::make('count', 'count', \Onphp\OqlToken::AGGREGATE_FUNCTION, 1, 16)
				)
			);
		}
		
		public function testIdentifier()
		{
			$this->assertTokens(
				'User _prop1.prop2.prop3 .prop4..prop5 0prop',
				array(
					\Onphp\OqlToken::make('User', 'User', \Onphp\OqlToken::IDENTIFIER, 1, 0),
					\Onphp\OqlToken::make('_prop1.prop2.prop3', '_prop1.prop2.prop3', \Onphp\OqlToken::IDENTIFIER, 1, 5),
					\Onphp\OqlToken::make('prop4', 'prop4', \Onphp\OqlToken::IDENTIFIER, 1, 25),
					\Onphp\OqlToken::make('prop5', 'prop5', \Onphp\OqlToken::IDENTIFIER, 1, 32)
				)
			);
		}
		
		public function testSymbol()
		{
			$this->assertTokens(
				'(,)',
				array(
					\Onphp\OqlToken::make('(', '(', \Onphp\OqlToken::PARENTHESES, 1, 0),
					\Onphp\OqlToken::make(',', ',', \Onphp\OqlToken::PUNCTUATION, 1, 1),
					\Onphp\OqlToken::make(')', ')', \Onphp\OqlToken::PARENTHESES, 1, 2)
				)
			);
		}
		
		public function testOperator()
		{
			$this->assertTokens(
				'>= <= <> < > != = + - / *',
				array(
					\Onphp\OqlToken::make('>=', '>=', \Onphp\OqlToken::COMPARISON_OPERATOR, 1, 0),
					\Onphp\OqlToken::make('<=', '<=', \Onphp\OqlToken::COMPARISON_OPERATOR, 1, 3),
					\Onphp\OqlToken::make('!=', '<>', \Onphp\OqlToken::COMPARISON_OPERATOR, 1, 6),
					\Onphp\OqlToken::make('<', '<', \Onphp\OqlToken::COMPARISON_OPERATOR, 1, 9),
					\Onphp\OqlToken::make('>', '>', \Onphp\OqlToken::COMPARISON_OPERATOR, 1, 11),
					\Onphp\OqlToken::make('!=', '!=', \Onphp\OqlToken::COMPARISON_OPERATOR, 1, 13),
					\Onphp\OqlToken::make('=', '=', \Onphp\OqlToken::COMPARISON_OPERATOR, 1, 16),
					\Onphp\OqlToken::make('+', '+', \Onphp\OqlToken::ARITHMETIC_OPERATOR, 1, 18),
					\Onphp\OqlToken::make('-', '-', \Onphp\OqlToken::ARITHMETIC_OPERATOR, 1, 20),
					\Onphp\OqlToken::make('/', '/', \Onphp\OqlToken::ARITHMETIC_OPERATOR, 1, 22),
					\Onphp\OqlToken::make('*', '*', \Onphp\OqlToken::ARITHMETIC_OPERATOR, 1, 24)
				)
			);
		}
		
		public function testQuery()
		{
			$this->assertTokens(
				"AVG(user.id) as Avg, count(id), (id + 10) / 2\n"
				."from UserGroup\n"
				."where (id in (1, $1) or id >= $2) and (name like \"%'ы'%\") "
				."order by id desc",
				array(
					\Onphp\OqlToken::make('avg', 'AVG', \Onphp\OqlToken::AGGREGATE_FUNCTION, 1, 0),
					\Onphp\OqlToken::make('(', '(', \Onphp\OqlToken::PARENTHESES, 1, 3),
					\Onphp\OqlToken::make('user.id', 'user.id', \Onphp\OqlToken::IDENTIFIER, 1, 4),
					\Onphp\OqlToken::make(')', ')', \Onphp\OqlToken::PARENTHESES, 1, 11),
					\Onphp\OqlToken::make('as', 'as', \Onphp\OqlToken::KEYWORD, 1, 13),
					\Onphp\OqlToken::make('avg', 'Avg', \Onphp\OqlToken::AGGREGATE_FUNCTION, 1, 16),	// identifier actually
					\Onphp\OqlToken::make(',', ',', \Onphp\OqlToken::PUNCTUATION, 1, 19),
					\Onphp\OqlToken::make('count', 'count', \Onphp\OqlToken::AGGREGATE_FUNCTION, 1, 21),
					\Onphp\OqlToken::make('(', '(', \Onphp\OqlToken::PARENTHESES, 1, 26),
					\Onphp\OqlToken::make('id', 'id', \Onphp\OqlToken::IDENTIFIER, 1, 27),
					\Onphp\OqlToken::make(')', ')', \Onphp\OqlToken::PARENTHESES, 1, 29),
					\Onphp\OqlToken::make(',', ',', \Onphp\OqlToken::PUNCTUATION, 1, 30),
					\Onphp\OqlToken::make('(', '(', \Onphp\OqlToken::PARENTHESES, 1, 32),
					\Onphp\OqlToken::make('id', 'id', \Onphp\OqlToken::IDENTIFIER, 1, 33),
					\Onphp\OqlToken::make('+', '+', \Onphp\OqlToken::ARITHMETIC_OPERATOR, 1, 36),
					\Onphp\OqlToken::make(10., '10', \Onphp\OqlToken::NUMBER, 1, 38),
					\Onphp\OqlToken::make(')', ')', \Onphp\OqlToken::PARENTHESES, 1, 40),
					\Onphp\OqlToken::make('/', '/', \Onphp\OqlToken::ARITHMETIC_OPERATOR, 1, 42),
					\Onphp\OqlToken::make(2., '2', \Onphp\OqlToken::NUMBER, 1, 44),
					\Onphp\OqlToken::make('from', 'from', \Onphp\OqlToken::KEYWORD, 2, 0),
					\Onphp\OqlToken::make('UserGroup', 'UserGroup', \Onphp\OqlToken::IDENTIFIER, 2, 5),
					\Onphp\OqlToken::make('where', 'where', \Onphp\OqlToken::KEYWORD, 3, 0),
					\Onphp\OqlToken::make('(', '(', \Onphp\OqlToken::PARENTHESES, 3, 6),
					\Onphp\OqlToken::make('id', 'id', \Onphp\OqlToken::IDENTIFIER, 3, 7),
					\Onphp\OqlToken::make('in', 'in', \Onphp\OqlToken::KEYWORD, 3, 10),
					\Onphp\OqlToken::make('(', '(', \Onphp\OqlToken::PARENTHESES, 3, 13),
					\Onphp\OqlToken::make(1., '1', \Onphp\OqlToken::NUMBER, 3, 14),
					\Onphp\OqlToken::make(',', ',', \Onphp\OqlToken::PUNCTUATION, 3, 15),
					\Onphp\OqlToken::make(1, '$1', \Onphp\OqlToken::SUBSTITUTION, 3, 17),
					\Onphp\OqlToken::make(')', ')', \Onphp\OqlToken::PARENTHESES, 3, 19),
					\Onphp\OqlToken::make('or', 'or', \Onphp\OqlToken::KEYWORD, 3, 21),
					\Onphp\OqlToken::make('id', 'id', \Onphp\OqlToken::IDENTIFIER, 3, 24),
					\Onphp\OqlToken::make('>=', '>=', \Onphp\OqlToken::COMPARISON_OPERATOR, 3, 27),
					\Onphp\OqlToken::make(2, '$2', \Onphp\OqlToken::SUBSTITUTION, 3, 30),
					\Onphp\OqlToken::make(')', ')', \Onphp\OqlToken::PARENTHESES, 3, 32),
					\Onphp\OqlToken::make('and', 'and', \Onphp\OqlToken::KEYWORD, 3, 34),
					\Onphp\OqlToken::make('(', '(', \Onphp\OqlToken::PARENTHESES, 3, 38),
					\Onphp\OqlToken::make('name', 'name', \Onphp\OqlToken::IDENTIFIER, 3, 39),
					\Onphp\OqlToken::make('like', 'like', \Onphp\OqlToken::KEYWORD, 3, 44),
					\Onphp\OqlToken::make('%\'ы\'%', '"%\'ы\'%"', \Onphp\OqlToken::STRING, 3, 49),
					\Onphp\OqlToken::make(')', ')', \Onphp\OqlToken::PARENTHESES, 3, 56),
					\Onphp\OqlToken::make('order by', 'order by', \Onphp\OqlToken::KEYWORD, 3, 58),
					\Onphp\OqlToken::make('id', 'id', \Onphp\OqlToken::IDENTIFIER, 3, 67),
					\Onphp\OqlToken::make('desc', 'desc', \Onphp\OqlToken::KEYWORD, 3, 70)
				)
			);
		}
		
		/**
		 * @return \Onphp\Test\OqlTokenizerTest
		**/
		private function assertTokens($string, $expectedTokens)
		{
			$tokenizer = new \Onphp\OqlTokenizer($string);
			$tokens = $tokenizer->getList();
			
			$this->assertEquals(sizeof($tokens), sizeof($expectedTokens));
			
			reset($tokens);
			
			foreach ($expectedTokens as $expectedToken) {
				$token = current($tokens);
				
				$this->assertEquals($token->getValue(), $expectedToken->getValue());
				$this->assertEquals($token->getRawValue(), $expectedToken->getRawValue());
				$this->assertEquals($token->getType(), $expectedToken->getType());
				$this->assertEquals($token->getLine(), $expectedToken->getLine());
				$this->assertEquals($token->getPosition(), $expectedToken->getPosition());
				
				next($tokens);
			}
			
			return $this;
		}
	}
?>