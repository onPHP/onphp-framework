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
					OqlToken::make(1., '1', OqlTokenType::NUMBER, 2, 0),
					OqlToken::make(1., '1', OqlTokenType::NUMBER, 3, 1),
					OqlToken::make(1., '1', OqlTokenType::NUMBER, 5, 0)
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
						OqlTokenType::STRING,
						1,
						0
					),
					OqlToken::make(
						'some string "substring1" \'substring2\' `substring3`',
						'"some string \"substring1\" \'substring2\' `substring3`"',
						OqlTokenType::STRING,
						1,
						3
					),
					OqlToken::make(
						"многа ' \" ` букаф",
						"'многа \' \" ` букаф'",
						OqlTokenType::STRING,
						1,
						59
					),
					OqlToken::make(
						'strange quotes',
						'`strange quotes`',
						OqlTokenType::STRING,
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
					OqlToken::make(123., '123', OqlTokenType::NUMBER, 1, 0),
					OqlToken::make('+', '+', OqlTokenType::OPERATOR, 1, 4),
					OqlToken::make(123., '123', OqlTokenType::NUMBER, 1, 5),
					OqlToken::make(.123, '.123', OqlTokenType::NUMBER, 1, 9),
					OqlToken::make(123.456, '123.456', OqlTokenType::NUMBER, 1, 14),
					OqlToken::make(1e23, '1e23', OqlTokenType::NUMBER, 1, 22),
					OqlToken::make(1e23, '1E+23', OqlTokenType::NUMBER, 1, 27),
					OqlToken::make(1e-23, '1E-23', OqlTokenType::NUMBER, 1, 33),
					OqlToken::make(1e22, '0.1e23', OqlTokenType::NUMBER, 1, 39),
					OqlToken::make('-', '-', OqlTokenType::OPERATOR, 1, 46),
					OqlToken::make(1e22, '.1e23', OqlTokenType::NUMBER, 1, 47)
				)
			);
		}
		
		public function testBoolean()
		{
			$this->assertTokens(
				'TrUe falSE',
				array(
					OqlToken::make(true, 'TrUe', OqlTokenType::BOOLEAN, 1, 0),
					OqlToken::make(false, 'falSE', OqlTokenType::BOOLEAN, 1, 5)
				)
			);
		}
		
		public function testNull()
		{
			$this->assertTokens(
				'null testNULL nullTest testNULLtest NULL',
				array(
					OqlToken::make('null', 'null', OqlTokenType::NULL, 1, 0),
					OqlToken::make('testNULL', 'testNULL', OqlTokenType::IDENTIFIER, 1, 5),
					OqlToken::make('nullTest', 'nullTest', OqlTokenType::IDENTIFIER, 1, 14),
					OqlToken::make('testNULLtest', 'testNULLtest', OqlTokenType::IDENTIFIER, 1, 23),
					OqlToken::make('null', 'NULL', OqlTokenType::NULL, 1, 36)
				)
			);
		}
		
		public function testSubstitution()
		{
			$this->assertTokens(
				' $1 $22 $ ',
				array(
					OqlToken::make(1, '$1', OqlTokenType::PLACEHOLDER, 1, 1),
					OqlToken::make(22, '$22', OqlTokenType::PLACEHOLDER, 1, 4)
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
					OqlToken::make('distinct', 'distinct', OqlTokenType::KEYWORD, 1, 0),
					OqlToken::make('from', 'From', OqlTokenType::KEYWORD, 1, 9),
					OqlToken::make('where', 'WHERE', OqlTokenType::KEYWORD, 1, 14),
					OqlToken::make('like', 'like', OqlTokenType::KEYWORD, 1, 20),
					OqlToken::make('between', 'between', OqlTokenType::KEYWORD, 1, 25),
					OqlToken::make('group by', "group \n\t\r by", OqlTokenType::KEYWORD, 1, 33),
					OqlToken::make('order by', 'ORDER BY', OqlTokenType::KEYWORD, 2, 6),
					OqlToken::make('asc', 'asc', OqlTokenType::KEYWORD, 2, 15),
					OqlToken::make('desc', 'desc', OqlTokenType::KEYWORD, 2, 19),
					OqlToken::make('having', 'having', OqlTokenType::KEYWORD, 2, 24),
					OqlToken::make('limit', 'limit', OqlTokenType::KEYWORD, 2, 31),
					OqlToken::make('offset', 'offset', OqlTokenType::KEYWORD, 2, 37),
					OqlToken::make('not', 'not', OqlTokenType::OPERATOR, 2, 44),
					OqlToken::make('and', 'and', OqlTokenType::OPERATOR, 2, 48),
					OqlToken::make('or', 'or', OqlTokenType::OPERATOR, 2, 52),
					OqlToken::make('as', 'as', OqlTokenType::KEYWORD, 2, 55),
					OqlToken::make('in', 'in', OqlTokenType::KEYWORD, 2, 58),
					OqlToken::make('is', 'is', OqlTokenType::KEYWORD, 2, 61),
					OqlToken::make('similar to', 'SIMILAR  TO', OqlTokenType::KEYWORD, 2, 64),
					OqlToken::make('ilike', 'ilike', OqlTokenType::KEYWORD, 2, 76)
				)
			);
		}
			
		public function testAggregateFunction()
		{
			$this->assertTokens(
				'SUM aVg min Max count',
				array(
					OqlToken::make('sum', 'SUM', OqlTokenType::AGGREGATE_FUNCTION, 1, 0),
					OqlToken::make('avg', 'aVg', OqlTokenType::AGGREGATE_FUNCTION, 1, 4),
					OqlToken::make('min', 'min', OqlTokenType::AGGREGATE_FUNCTION, 1, 8),
					OqlToken::make('max', 'Max', OqlTokenType::AGGREGATE_FUNCTION, 1, 12),
					OqlToken::make('count', 'count', OqlTokenType::AGGREGATE_FUNCTION, 1, 16)
				)
			);
		}
		
		public function testIdentifier()
		{
			$this->assertTokens(
				'User _prop1.prop2.prop3 .prop4..prop5 0prop',
				array(
					OqlToken::make('User', 'User', OqlTokenType::IDENTIFIER, 1, 0),
					OqlToken::make('_prop1.prop2.prop3', '_prop1.prop2.prop3', OqlTokenType::IDENTIFIER, 1, 5),
					OqlToken::make('prop4', 'prop4', OqlTokenType::IDENTIFIER, 1, 25),
					OqlToken::make('prop5', 'prop5', OqlTokenType::IDENTIFIER, 1, 32)
				)
			);
		}
		
		public function testSymbol()
		{
			$this->assertTokens(
				'(,)',
				array(
					OqlToken::make('(', '(', OqlTokenType::PARENTHESES, 1, 0),
					OqlToken::make(',', ',', OqlTokenType::PUNCTUATION, 1, 1),
					OqlToken::make(')', ')', OqlTokenType::PARENTHESES, 1, 2)
				)
			);
		}
		
		public function testOperator()
		{
			$this->assertTokens(
				'>= <= <> < > != = + - / *',
				array(
					OqlToken::make('>=', '>=', OqlTokenType::OPERATOR, 1, 0),
					OqlToken::make('<=', '<=', OqlTokenType::OPERATOR, 1, 3),
					OqlToken::make('!=', '<>', OqlTokenType::OPERATOR, 1, 6),
					OqlToken::make('<', '<', OqlTokenType::OPERATOR, 1, 9),
					OqlToken::make('>', '>', OqlTokenType::OPERATOR, 1, 11),
					OqlToken::make('!=', '!=', OqlTokenType::OPERATOR, 1, 13),
					OqlToken::make('=', '=', OqlTokenType::OPERATOR, 1, 16),
					OqlToken::make('+', '+', OqlTokenType::OPERATOR, 1, 18),
					OqlToken::make('-', '-', OqlTokenType::OPERATOR, 1, 20),
					OqlToken::make('/', '/', OqlTokenType::OPERATOR, 1, 22),
					OqlToken::make('*', '*', OqlTokenType::OPERATOR, 1, 24)
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
					OqlToken::make('avg', 'AVG', OqlTokenType::AGGREGATE_FUNCTION, 1, 0),
					OqlToken::make('(', '(', OqlTokenType::PARENTHESES, 1, 3),
					OqlToken::make('user.id', 'user.id', OqlTokenType::IDENTIFIER, 1, 4),
					OqlToken::make(')', ')', OqlTokenType::PARENTHESES, 1, 11),
					OqlToken::make('as', 'as', OqlTokenType::KEYWORD, 1, 13),
					OqlToken::make('avg', 'Avg', OqlTokenType::AGGREGATE_FUNCTION, 1, 16),	// identifier actually
					OqlToken::make(',', ',', OqlTokenType::PUNCTUATION, 1, 19),
					OqlToken::make('count', 'count', OqlTokenType::AGGREGATE_FUNCTION, 1, 21),
					OqlToken::make('(', '(', OqlTokenType::PARENTHESES, 1, 26),
					OqlToken::make('id', 'id', OqlTokenType::IDENTIFIER, 1, 27),
					OqlToken::make(')', ')', OqlTokenType::PARENTHESES, 1, 29),
					OqlToken::make(',', ',', OqlTokenType::PUNCTUATION, 1, 30),
					OqlToken::make('(', '(', OqlTokenType::PARENTHESES, 1, 32),
					OqlToken::make('id', 'id', OqlTokenType::IDENTIFIER, 1, 33),
					OqlToken::make('+', '+', OqlTokenType::OPERATOR, 1, 36),
					OqlToken::make(10., '10', OqlTokenType::NUMBER, 1, 38),
					OqlToken::make(')', ')', OqlTokenType::PARENTHESES, 1, 40),
					OqlToken::make('/', '/', OqlTokenType::OPERATOR, 1, 42),
					OqlToken::make(2., '2', OqlTokenType::NUMBER, 1, 44),
					OqlToken::make('from', 'from', OqlTokenType::KEYWORD, 2, 0),
					OqlToken::make('UserGroup', 'UserGroup', OqlTokenType::IDENTIFIER, 2, 5),
					OqlToken::make('where', 'where', OqlTokenType::KEYWORD, 3, 0),
					OqlToken::make('(', '(', OqlTokenType::PARENTHESES, 3, 6),
					OqlToken::make('id', 'id', OqlTokenType::IDENTIFIER, 3, 7),
					OqlToken::make('in', 'in', OqlTokenType::KEYWORD, 3, 10),
					OqlToken::make('(', '(', OqlTokenType::PARENTHESES, 3, 13),
					OqlToken::make(1., '1', OqlTokenType::NUMBER, 3, 14),
					OqlToken::make(',', ',', OqlTokenType::PUNCTUATION, 3, 15),
					OqlToken::make(1, '$1', OqlTokenType::PLACEHOLDER, 3, 17),
					OqlToken::make(')', ')', OqlTokenType::PARENTHESES, 3, 19),
					OqlToken::make('or', 'or', OqlTokenType::OPERATOR, 3, 21),
					OqlToken::make('id', 'id', OqlTokenType::IDENTIFIER, 3, 24),
					OqlToken::make('>=', '>=', OqlTokenType::OPERATOR, 3, 27),
					OqlToken::make(2, '$2', OqlTokenType::PLACEHOLDER, 3, 30),
					OqlToken::make(')', ')', OqlTokenType::PARENTHESES, 3, 32),
					OqlToken::make('and', 'and', OqlTokenType::OPERATOR, 3, 34),
					OqlToken::make('(', '(', OqlTokenType::PARENTHESES, 3, 38),
					OqlToken::make('name', 'name', OqlTokenType::IDENTIFIER, 3, 39),
					OqlToken::make('like', 'like', OqlTokenType::KEYWORD, 3, 44),
					OqlToken::make('%\'ы\'%', '"%\'ы\'%"', OqlTokenType::STRING, 3, 49),
					OqlToken::make(')', ')', OqlTokenType::PARENTHESES, 3, 56),
					OqlToken::make('order by', 'order by', OqlTokenType::KEYWORD, 3, 58),
					OqlToken::make('id', 'id', OqlTokenType::IDENTIFIER, 3, 67),
					OqlToken::make('desc', 'desc', OqlTokenType::KEYWORD, 3, 70)
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
			
			$this->assertEquals(count($tokens), count($expectedTokens));
			
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