<?php
	/* $Id$ */
	
	final class OqlTokenizerTest extends TestCase
	{
		public function testEmpty()
		{
			$this->assertTokens('', array());
			
			$this->assertTokens(" \t\n\r\n", array());
			
			try {
				$this->assertTokens(null, array());
				$this->fail();
			} catch (WrongArgumentException $e) {
				// pass
			}
		}
		
		public function testContext()
		{
			$this->assertContext('', array());
			
			$this->assertContext(' \n\r \t\n', array());
			
			$this->assertContext('', array(OqlTokenContext::create(null, null)));
			
			$this->assertContext(
				"\n \n1\n\r1\r\n \n  1\n",
				array(
					OqlTokenContext::create(3, 1),	// 1
					OqlTokenContext::create(4, 2),	// 1
					OqlTokenContext::create(6, 3)	// 1
				)
			);
			
			$this->assertContext(
				" similar  \n \r  to \t \n similar \t  to \n order by \ngroup\nby",
				array(
					OqlTokenContext::create(1, 2),	// similar to
					OqlTokenContext::create(3, 2),	// similar to
					OqlTokenContext::create(4, 2),	// order by
					OqlTokenContext::create(5, 1)	// group by
				)
			);
			
			$this->assertContext(
				" \tкЪВэре \n  \r безоПасно\nстЕ ",
				array(
					OqlTokenContext::create(1, 3),	// кЪВэре 
					OqlTokenContext::create(2, 5),	// безоПасно
					OqlTokenContext::create(3, 1)	// стЕ
				)
			);
			
			$this->assertContext(
				"AVG(user.id) as Avg, count(id), (id + 10) / 2\n"
				."from UserGroup\n"
				."where (id in (1, $1) or id >= $2) and (name like \"%'ы'%\") "
				."order by id desc",
				array(
					OqlTokenContext::create(1, 1),	// AVG
					OqlTokenContext::create(1, 4),	// (
					OqlTokenContext::create(1, 5),	// user.id
					OqlTokenContext::create(1, 12),	// )
					OqlTokenContext::create(1, 14),	// as
					OqlTokenContext::create(1, 17),	// Avg
					OqlTokenContext::create(1, 20),	// ,
					OqlTokenContext::create(1, 22),	// count
					OqlTokenContext::create(1, 27),	// (
					OqlTokenContext::create(1, 28),	// id
					OqlTokenContext::create(1, 30),	// )
					OqlTokenContext::create(1, 31),	// ,
					OqlTokenContext::create(1, 33),	// (
					OqlTokenContext::create(1, 34),	// id
					OqlTokenContext::create(1, 37),	// +
					OqlTokenContext::create(1, 39),	// 10
					OqlTokenContext::create(1, 41),	// )
					OqlTokenContext::create(1, 43),	// /
					OqlTokenContext::create(1, 45),	// 2
					OqlTokenContext::create(2, 1),	// from
					OqlTokenContext::create(2, 6),	// UserGroup
					OqlTokenContext::create(3, 1),	// where
					OqlTokenContext::create(3, 7),	// (
					OqlTokenContext::create(3, 8),	// id
					OqlTokenContext::create(3, 11),	// in
					OqlTokenContext::create(3, 14),	// (
					OqlTokenContext::create(3, 15),	// 1
					OqlTokenContext::create(3, 16),	// ,
					OqlTokenContext::create(3, 18),	// $1
					OqlTokenContext::create(3, 20),	// )
					OqlTokenContext::create(3, 22),	// or
					OqlTokenContext::create(3, 25),	// id
					OqlTokenContext::create(3, 28),	// >=
					OqlTokenContext::create(3, 31),	// $2
					OqlTokenContext::create(3, 33),	// )
					OqlTokenContext::create(3, 35),	// and
					OqlTokenContext::create(3, 39),	// (
					OqlTokenContext::create(3, 40),	// name
					OqlTokenContext::create(3, 45),	// like
					OqlTokenContext::create(3, 50),	// "%\'ы\'%"
					OqlTokenContext::create(3, 57),	// )
					OqlTokenContext::create(3, 59),	// order by
					OqlTokenContext::create(3, 68),	// id
					OqlTokenContext::create(3, 71),	// desc
					OqlTokenContext::create(null, null),
					OqlTokenContext::create(null, null)
				)
			);
		}
		
		public function testString()
		{
			$this->assertTokens(
				'"" "some string \"substring1\" \'substring2\' `substring3`"  '
				."'многа \' \" ` букаф' `strange quotes` ",
				array(
					OqlToken::create(
						'',
						'""',
						OqlTokenType::STRING
					),
					OqlToken::create(
						'some string "substring1" \'substring2\' `substring3`',
						'"some string \"substring1\" \'substring2\' `substring3`"',
						OqlTokenType::STRING
					),
					OqlToken::create(
						"многа ' \" ` букаф",
						"'многа \' \" ` букаф'",
						OqlTokenType::STRING
					),
					OqlToken::create(
						'strange quotes',
						'`strange quotes`',
						OqlTokenType::STRING
					)
				)
			);
		}
		
		public function testBoolean()
		{
			$this->assertTokens(
				'TrUe falSE',
				array(
					OqlToken::create(true, 'TrUe', OqlTokenType::BOOLEAN),
					OqlToken::create(false, 'falSE', OqlTokenType::BOOLEAN)
				)
			);
		}
		
		public function testNull()
		{
			$this->assertTokens(
				'null testNULL nullTest testNULLtest NULL',
				array(
					OqlToken::create('null', 'null', OqlTokenType::NULL),
					OqlToken::create('testNULL', 'testNULL', OqlTokenType::IDENTIFIER),
					OqlToken::create('nullTest', 'nullTest', OqlTokenType::IDENTIFIER),
					OqlToken::create('testNULLtest', 'testNULLtest', OqlTokenType::IDENTIFIER),
					OqlToken::create('null', 'NULL', OqlTokenType::NULL)
				)
			);
		}
		
		public function testPlaceholder()
		{
			$this->assertTokens(
				' $1 $22 $ ',
				array(
					OqlToken::create(1, '$1', OqlTokenType::PLACEHOLDER),
					OqlToken::create(22, '$22', OqlTokenType::PLACEHOLDER),
					OqlToken::create('$', '$', OqlTokenType::UNKNOWN)
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
					OqlToken::create('distinct', 'distinct', OqlTokenType::KEYWORD),
					OqlToken::create('from', 'From', OqlTokenType::KEYWORD),
					OqlToken::create('where', 'WHERE', OqlTokenType::KEYWORD),
					OqlToken::create('like', 'like', OqlTokenType::KEYWORD),
					OqlToken::create('between', 'between', OqlTokenType::KEYWORD),
					OqlToken::create('group by', "group \n\t\r by", OqlTokenType::KEYWORD),
					OqlToken::create('order by', 'ORDER BY', OqlTokenType::KEYWORD),
					OqlToken::create('asc', 'asc', OqlTokenType::KEYWORD),
					OqlToken::create('desc', 'desc', OqlTokenType::KEYWORD),
					OqlToken::create('having', 'having', OqlTokenType::KEYWORD),
					OqlToken::create('limit', 'limit', OqlTokenType::KEYWORD),
					OqlToken::create('offset', 'offset', OqlTokenType::KEYWORD),
					OqlToken::create('not', 'not', OqlTokenType::OPERATOR),
					OqlToken::create('and', 'and', OqlTokenType::OPERATOR),
					OqlToken::create('or', 'or', OqlTokenType::OPERATOR),
					OqlToken::create('as', 'as', OqlTokenType::KEYWORD),
					OqlToken::create('in', 'in', OqlTokenType::KEYWORD),
					OqlToken::create('is', 'is', OqlTokenType::KEYWORD),
					OqlToken::create('similar to', 'SIMILAR  TO', OqlTokenType::KEYWORD),
					OqlToken::create('ilike', 'ilike', OqlTokenType::KEYWORD)
				)
			);
		}
			
		public function testAggregateFunction()
		{
			$this->assertTokens(
				'SUM aVg min Max count',
				array(
					OqlToken::create('sum', 'SUM', OqlTokenType::AGGREGATE_FUNCTION),
					OqlToken::create('avg', 'aVg', OqlTokenType::AGGREGATE_FUNCTION),
					OqlToken::create('min', 'min', OqlTokenType::AGGREGATE_FUNCTION),
					OqlToken::create('max', 'Max', OqlTokenType::AGGREGATE_FUNCTION),
					OqlToken::create('count', 'count', OqlTokenType::AGGREGATE_FUNCTION)
				)
			);
		}
		
		public function testIdentifier()
		{
			$this->assertTokens(
				'User _prop1.prop2.prop3 .prop4..prop5 0prop f.oo.ba.r.buzz',
				array(
					OqlToken::create('User', 'User', OqlTokenType::IDENTIFIER),
					OqlToken::create('_prop1.prop2.prop3', '_prop1.prop2.prop3', OqlTokenType::IDENTIFIER),
					OqlToken::create('.prop4..prop5', '.prop4..prop5', OqlTokenType::UNKNOWN),
					OqlToken::create('0prop', '0prop', OqlTokenType::UNKNOWN),
					OqlToken::create('f.oo.ba.r.buzz', 'f.oo.ba.r.buzz', OqlTokenType::IDENTIFIER)
				)
			);
		}
		
		public function testSymbol()
		{
			$this->assertTokens(
				'(,)',
				array(
					OqlToken::create('(', '(', OqlTokenType::PARENTHESES),
					OqlToken::create(',', ',', OqlTokenType::PUNCTUATION),
					OqlToken::create(')', ')', OqlTokenType::PARENTHESES)
				)
			);
		}
		
		public function testOperator()
		{
			$this->assertTokens(
				'>= <= <> < > != = + - / * and Or not',
				array(
					OqlToken::create('>=', '>=', OqlTokenType::OPERATOR),
					OqlToken::create('<=', '<=', OqlTokenType::OPERATOR),
					OqlToken::create('!=', '<>', OqlTokenType::OPERATOR),
					OqlToken::create('<', '<', OqlTokenType::OPERATOR),
					OqlToken::create('>', '>', OqlTokenType::OPERATOR),
					OqlToken::create('!=', '!=', OqlTokenType::OPERATOR),
					OqlToken::create('=', '=', OqlTokenType::OPERATOR),
					OqlToken::create('+', '+', OqlTokenType::OPERATOR),
					OqlToken::create('-', '-', OqlTokenType::OPERATOR),
					OqlToken::create('/', '/', OqlTokenType::OPERATOR),
					OqlToken::create('*', '*', OqlTokenType::OPERATOR),
					OqlToken::create('and', 'and', OqlTokenType::OPERATOR),
					OqlToken::create('or', 'Or', OqlTokenType::OPERATOR),
					OqlToken::create('not', 'not', OqlTokenType::OPERATOR)
				)
			);
		}
		
		public function testNumber()
		{
			$this->assertTokens(
				'123 +123 .123 123.456 1e23 1E+23 1E-23 0.1e23 -.1e23',
				array(
					OqlToken::create(123., '123', OqlTokenType::NUMBER),
					OqlToken::create('+', '+', OqlTokenType::OPERATOR),
					OqlToken::create(123., '123', OqlTokenType::NUMBER),
					OqlToken::create(.123, '.123', OqlTokenType::NUMBER),
					OqlToken::create(123.456, '123.456', OqlTokenType::NUMBER),
					OqlToken::create(1e23, '1e23', OqlTokenType::NUMBER),
					OqlToken::create(1e23, '1E+23', OqlTokenType::NUMBER),
					OqlToken::create(1e-23, '1E-23', OqlTokenType::NUMBER),
					OqlToken::create(1e22, '0.1e23', OqlTokenType::NUMBER),
					OqlToken::create('-', '-', OqlTokenType::OPERATOR),
					OqlToken::create(1e22, '.1e23', OqlTokenType::NUMBER)
				)
			);
		}
		
		public function testUnknown()
		{
			$this->assertTokens(
				' ##3asdf ыфвафыв !! [ ~sdf } ',
				array(
					OqlToken::create('##3asdf', '##3asdf', OqlTokenType::UNKNOWN),
					OqlToken::create('ыфвафыв', 'ыфвафыв', OqlTokenType::UNKNOWN),
					OqlToken::create('!!', '!!', OqlTokenType::UNKNOWN),
					OqlToken::create('[', '[', OqlTokenType::UNKNOWN),
					OqlToken::create('~sdf', '~sdf', OqlTokenType::UNKNOWN),
					OqlToken::create('}', '}', OqlTokenType::UNKNOWN)
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
					OqlToken::create('avg', 'AVG', OqlTokenType::AGGREGATE_FUNCTION),
					OqlToken::create('(', '(', OqlTokenType::PARENTHESES),
					OqlToken::create('user.id', 'user.id', OqlTokenType::IDENTIFIER),
					OqlToken::create(')', ')', OqlTokenType::PARENTHESES),
					OqlToken::create('as', 'as', OqlTokenType::KEYWORD),
					OqlToken::create('avg', 'Avg', OqlTokenType::AGGREGATE_FUNCTION),	// identifier actually
					OqlToken::create(',', ',', OqlTokenType::PUNCTUATION),
					OqlToken::create('count', 'count', OqlTokenType::AGGREGATE_FUNCTION),
					OqlToken::create('(', '(', OqlTokenType::PARENTHESES),
					OqlToken::create('id', 'id', OqlTokenType::IDENTIFIER),
					OqlToken::create(')', ')', OqlTokenType::PARENTHESES),
					OqlToken::create(',', ',', OqlTokenType::PUNCTUATION),
					OqlToken::create('(', '(', OqlTokenType::PARENTHESES),
					OqlToken::create('id', 'id', OqlTokenType::IDENTIFIER),
					OqlToken::create('+', '+', OqlTokenType::OPERATOR),
					OqlToken::create(10., '10', OqlTokenType::NUMBER),
					OqlToken::create(')', ')', OqlTokenType::PARENTHESES),
					OqlToken::create('/', '/', OqlTokenType::OPERATOR),
					OqlToken::create(2., '2', OqlTokenType::NUMBER),
					OqlToken::create('from', 'from', OqlTokenType::KEYWORD),
					OqlToken::create('UserGroup', 'UserGroup', OqlTokenType::IDENTIFIER),
					OqlToken::create('where', 'where', OqlTokenType::KEYWORD),
					OqlToken::create('(', '(', OqlTokenType::PARENTHESES),
					OqlToken::create('id', 'id', OqlTokenType::IDENTIFIER),
					OqlToken::create('in', 'in', OqlTokenType::KEYWORD),
					OqlToken::create('(', '(', OqlTokenType::PARENTHESES),
					OqlToken::create(1., '1', OqlTokenType::NUMBER),
					OqlToken::create(',', ',', OqlTokenType::PUNCTUATION),
					OqlToken::create(1, '$1', OqlTokenType::PLACEHOLDER),
					OqlToken::create(')', ')', OqlTokenType::PARENTHESES),
					OqlToken::create('or', 'or', OqlTokenType::OPERATOR),
					OqlToken::create('id', 'id', OqlTokenType::IDENTIFIER),
					OqlToken::create('>=', '>=', OqlTokenType::OPERATOR),
					OqlToken::create(2, '$2', OqlTokenType::PLACEHOLDER),
					OqlToken::create(')', ')', OqlTokenType::PARENTHESES),
					OqlToken::create('and', 'and', OqlTokenType::OPERATOR),
					OqlToken::create('(', '(', OqlTokenType::PARENTHESES),
					OqlToken::create('name', 'name', OqlTokenType::IDENTIFIER),
					OqlToken::create('like', 'like', OqlTokenType::KEYWORD),
					OqlToken::create('%\'ы\'%', '"%\'ы\'%"', OqlTokenType::STRING),
					OqlToken::create(')', ')', OqlTokenType::PARENTHESES),
					OqlToken::create('order by', 'order by', OqlTokenType::KEYWORD),
					OqlToken::create('id', 'id', OqlTokenType::IDENTIFIER),
					OqlToken::create('desc', 'desc', OqlTokenType::KEYWORD)
				)
			);
		}
		
		/**
		 * @return OqlTokenizerTest
		**/
		private function assertTokens($string, array $expectedTokens)
		{
			$tokenizer = new OqlTokenizer($string);
			$tokens = $tokenizer->getList();
			reset($tokens);
			
			$this->assertEquals(sizeof($tokens), sizeof($expectedTokens));
			
			foreach ($expectedTokens as $expectedToken) {
				$token = current($tokens);
				$this->assertEquals($expectedToken, $token);
				next($tokens);
			}
			
			return $this;
		}
		
		/**
		 * @return OqlTokenizerTest
		**/
		private function assertContext($string, array $contexts)
		{
			$tokenizer = new OqlTokenizer($string);
			
			foreach ($contexts as $index => $context) {
				$this->assertEquals(
					$context,
					$tokenizer->getContext($index)
				);
			}
			
			return $this;
		}
	}
?>