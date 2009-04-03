<?php
	/* $Id$ */
	
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
					OqlToken::make(1., '1', OqlToken::NUMBER, 2, 0),
					OqlToken::make(1., '1', OqlToken::NUMBER, 3, 1),
					OqlToken::make(1., '1', OqlToken::NUMBER, 5, 0)
				)
			);
		}
		
		public function testString()
		{
			$this->assertTokens(
				'"" "some string \"substring1\" \'substring2\' `substring3`"  '
				."'многа \' \" ` букаф' `strange quotes` ",
				array(
					OqlToken::make(
						'',
						'""',
						OqlToken::STRING,
						1,
						0
					),
					OqlToken::make(
						'some string "substring1" \'substring2\' `substring3`',
						'"some string \"substring1\" \'substring2\' `substring3`"',
						OqlToken::STRING,
						1,
						3
					),
					OqlToken::make(
						"многа ' \" ` букаф",
						"'многа \' \" ` букаф'",
						OqlToken::STRING,
						1,
						59
					),
					OqlToken::make(
						'strange quotes',
						'`strange quotes`',
						OqlToken::STRING,
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
					OqlToken::make(123., '123', OqlToken::NUMBER, 1, 0),
					OqlToken::make('+', '+', OqlToken::ARITHMETIC_OPERATOR, 1, 4),
					OqlToken::make(123., '123', OqlToken::NUMBER, 1, 5),
					OqlToken::make(.123, '.123', OqlToken::NUMBER, 1, 9),
					OqlToken::make(123.456, '123.456', OqlToken::NUMBER, 1, 14),
					OqlToken::make(1e23, '1e23', OqlToken::NUMBER, 1, 22),
					OqlToken::make(1e23, '1E+23', OqlToken::NUMBER, 1, 27),
					OqlToken::make(1e-23, '1E-23', OqlToken::NUMBER, 1, 33),
					OqlToken::make(1e22, '0.1e23', OqlToken::NUMBER, 1, 39),
					OqlToken::make('-', '-', OqlToken::ARITHMETIC_OPERATOR, 1, 46),
					OqlToken::make(1e22, '.1e23', OqlToken::NUMBER, 1, 47)
				)
			);
		}
		
		public function testBoolean()
		{
			$this->assertTokens(
				'TrUe falSE',
				array(
					OqlToken::make(true, 'TrUe', OqlToken::BOOLEAN, 1, 0),
					OqlToken::make(false, 'falSE', OqlToken::BOOLEAN, 1, 5)
				)
			);
		}
		
		public function testNull()
		{
			$this->assertTokens(
				'null testNULL nullTest testNULLtest NULL',
				array(
					OqlToken::make('null', 'null', OqlToken::NULL, 1, 0),
					OqlToken::make('testNULL', 'testNULL', OqlToken::IDENTIFIER, 1, 5),
					OqlToken::make('nullTest', 'nullTest', OqlToken::IDENTIFIER, 1, 14),
					OqlToken::make('testNULLtest', 'testNULLtest', OqlToken::IDENTIFIER, 1, 23),
					OqlToken::make('null', 'NULL', OqlToken::NULL, 1, 36)
				)
			);
		}
		
		public function testSubstitution()
		{
			$this->assertTokens(
				' $1 $22 $ ',
				array(
					OqlToken::make(1, '$1', OqlToken::SUBSTITUTION, 1, 1),
					OqlToken::make(22, '$22', OqlToken::SUBSTITUTION, 1, 4)
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
					OqlToken::make('distinct', 'distinct', OqlToken::KEYWORD, 1, 0),
					OqlToken::make('from', 'From', OqlToken::KEYWORD, 1, 9),
					OqlToken::make('where', 'WHERE', OqlToken::KEYWORD, 1, 14),
					OqlToken::make('like', 'like', OqlToken::KEYWORD, 1, 20),
					OqlToken::make('between', 'between', OqlToken::KEYWORD, 1, 25),
					OqlToken::make('group by', "group \n\t\r by", OqlToken::KEYWORD, 1, 33),
					OqlToken::make('order by', 'ORDER BY', OqlToken::KEYWORD, 2, 6),
					OqlToken::make('asc', 'asc', OqlToken::KEYWORD, 2, 15),
					OqlToken::make('desc', 'desc', OqlToken::KEYWORD, 2, 19),
					OqlToken::make('having', 'having', OqlToken::KEYWORD, 2, 24),
					OqlToken::make('limit', 'limit', OqlToken::KEYWORD, 2, 31),
					OqlToken::make('offset', 'offset', OqlToken::KEYWORD, 2, 37),
					OqlToken::make('not', 'not', OqlToken::KEYWORD, 2, 44),
					OqlToken::make('and', 'and', OqlToken::KEYWORD, 2, 48),
					OqlToken::make('or', 'or', OqlToken::KEYWORD, 2, 52),
					OqlToken::make('as', 'as', OqlToken::KEYWORD, 2, 55),
					OqlToken::make('in', 'in', OqlToken::KEYWORD, 2, 58),
					OqlToken::make('is', 'is', OqlToken::KEYWORD, 2, 61),
					OqlToken::make('similar to', 'SIMILAR  TO', OqlToken::KEYWORD, 2, 64),
					OqlToken::make('ilike', 'ilike', OqlToken::KEYWORD, 2, 76)
				)
			);
		}
			
		public function testAggregateFunction()
		{
			$this->assertTokens(
				'SUM aVg min Max count',
				array(
					OqlToken::make('sum', 'SUM', OqlToken::AGGREGATE_FUNCTION, 1, 0),
					OqlToken::make('avg', 'aVg', OqlToken::AGGREGATE_FUNCTION, 1, 4),
					OqlToken::make('min', 'min', OqlToken::AGGREGATE_FUNCTION, 1, 8),
					OqlToken::make('max', 'Max', OqlToken::AGGREGATE_FUNCTION, 1, 12),
					OqlToken::make('count', 'count', OqlToken::AGGREGATE_FUNCTION, 1, 16)
				)
			);
		}
		
		public function testIdentifier()
		{
			$this->assertTokens(
				'User _prop1.prop2.prop3 .prop4..prop5 0prop',
				array(
					OqlToken::make('User', 'User', OqlToken::IDENTIFIER, 1, 0),
					OqlToken::make('_prop1.prop2.prop3', '_prop1.prop2.prop3', OqlToken::IDENTIFIER, 1, 5),
					OqlToken::make('prop4', 'prop4', OqlToken::IDENTIFIER, 1, 25),
					OqlToken::make('prop5', 'prop5', OqlToken::IDENTIFIER, 1, 32)
				)
			);
		}
		
		public function testSymbol()
		{
			$this->assertTokens(
				'(,)',
				array(
					OqlToken::make('(', '(', OqlToken::PARENTHESES, 1, 0),
					OqlToken::make(',', ',', OqlToken::PUNCTUATION, 1, 1),
					OqlToken::make(')', ')', OqlToken::PARENTHESES, 1, 2)
				)
			);
		}
		
		public function testOperator()
		{
			$this->assertTokens(
				'>= <= <> < > != = + - / *',
				array(
					OqlToken::make('>=', '>=', OqlToken::COMPARISON_OPERATOR, 1, 0),
					OqlToken::make('<=', '<=', OqlToken::COMPARISON_OPERATOR, 1, 3),
					OqlToken::make('!=', '<>', OqlToken::COMPARISON_OPERATOR, 1, 6),
					OqlToken::make('<', '<', OqlToken::COMPARISON_OPERATOR, 1, 9),
					OqlToken::make('>', '>', OqlToken::COMPARISON_OPERATOR, 1, 11),
					OqlToken::make('!=', '!=', OqlToken::COMPARISON_OPERATOR, 1, 13),
					OqlToken::make('=', '=', OqlToken::COMPARISON_OPERATOR, 1, 16),
					OqlToken::make('+', '+', OqlToken::ARITHMETIC_OPERATOR, 1, 18),
					OqlToken::make('-', '-', OqlToken::ARITHMETIC_OPERATOR, 1, 20),
					OqlToken::make('/', '/', OqlToken::ARITHMETIC_OPERATOR, 1, 22),
					OqlToken::make('*', '*', OqlToken::ARITHMETIC_OPERATOR, 1, 24)
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
					OqlToken::make('avg', 'AVG', OqlToken::AGGREGATE_FUNCTION, 1, 0),
					OqlToken::make('(', '(', OqlToken::PARENTHESES, 1, 3),
					OqlToken::make('user.id', 'user.id', OqlToken::IDENTIFIER, 1, 4),
					OqlToken::make(')', ')', OqlToken::PARENTHESES, 1, 11),
					OqlToken::make('as', 'as', OqlToken::KEYWORD, 1, 13),
					OqlToken::make('avg', 'Avg', OqlToken::AGGREGATE_FUNCTION, 1, 16),	// identifier actually
					OqlToken::make(',', ',', OqlToken::PUNCTUATION, 1, 19),
					OqlToken::make('count', 'count', OqlToken::AGGREGATE_FUNCTION, 1, 21),
					OqlToken::make('(', '(', OqlToken::PARENTHESES, 1, 26),
					OqlToken::make('id', 'id', OqlToken::IDENTIFIER, 1, 27),
					OqlToken::make(')', ')', OqlToken::PARENTHESES, 1, 29),
					OqlToken::make(',', ',', OqlToken::PUNCTUATION, 1, 30),
					OqlToken::make('(', '(', OqlToken::PARENTHESES, 1, 32),
					OqlToken::make('id', 'id', OqlToken::IDENTIFIER, 1, 33),
					OqlToken::make('+', '+', OqlToken::ARITHMETIC_OPERATOR, 1, 36),
					OqlToken::make(10., '10', OqlToken::NUMBER, 1, 38),
					OqlToken::make(')', ')', OqlToken::PARENTHESES, 1, 40),
					OqlToken::make('/', '/', OqlToken::ARITHMETIC_OPERATOR, 1, 42),
					OqlToken::make(2., '2', OqlToken::NUMBER, 1, 44),
					OqlToken::make('from', 'from', OqlToken::KEYWORD, 2, 0),
					OqlToken::make('UserGroup', 'UserGroup', OqlToken::IDENTIFIER, 2, 5),
					OqlToken::make('where', 'where', OqlToken::KEYWORD, 3, 0),
					OqlToken::make('(', '(', OqlToken::PARENTHESES, 3, 6),
					OqlToken::make('id', 'id', OqlToken::IDENTIFIER, 3, 7),
					OqlToken::make('in', 'in', OqlToken::KEYWORD, 3, 10),
					OqlToken::make('(', '(', OqlToken::PARENTHESES, 3, 13),
					OqlToken::make(1., '1', OqlToken::NUMBER, 3, 14),
					OqlToken::make(',', ',', OqlToken::PUNCTUATION, 3, 15),
					OqlToken::make(1, '$1', OqlToken::SUBSTITUTION, 3, 17),
					OqlToken::make(')', ')', OqlToken::PARENTHESES, 3, 19),
					OqlToken::make('or', 'or', OqlToken::KEYWORD, 3, 21),
					OqlToken::make('id', 'id', OqlToken::IDENTIFIER, 3, 24),
					OqlToken::make('>=', '>=', OqlToken::COMPARISON_OPERATOR, 3, 27),
					OqlToken::make(2, '$2', OqlToken::SUBSTITUTION, 3, 30),
					OqlToken::make(')', ')', OqlToken::PARENTHESES, 3, 32),
					OqlToken::make('and', 'and', OqlToken::KEYWORD, 3, 34),
					OqlToken::make('(', '(', OqlToken::PARENTHESES, 3, 38),
					OqlToken::make('name', 'name', OqlToken::IDENTIFIER, 3, 39),
					OqlToken::make('like', 'like', OqlToken::KEYWORD, 3, 44),
					OqlToken::make('%\'ы\'%', '"%\'ы\'%"', OqlToken::STRING, 3, 49),
					OqlToken::make(')', ')', OqlToken::PARENTHESES, 3, 56),
					OqlToken::make('order by', 'order by', OqlToken::KEYWORD, 3, 58),
					OqlToken::make('id', 'id', OqlToken::IDENTIFIER, 3, 67),
					OqlToken::make('desc', 'desc', OqlToken::KEYWORD, 3, 70)
				)
			);
		}
		
		/**
		 * @return OqlTokenizerTest
		**/
		private function assertTokens($string, $expectedTokens)
		{
			$tokenizer = new OqlTokenizer($string);
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