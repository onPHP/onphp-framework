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
						OqlTokenType::STRING,
						''
					),
					OqlToken::create(
						OqlTokenType::STRING,
						'some string "substring1" \'substring2\' `substring3`'
					),
					OqlToken::create(
						OqlTokenType::STRING,
						"многа ' \" ` букаф"
					),
					OqlToken::create(
						OqlTokenType::STRING,
						'strange quotes'
					)
				)
			);
		}
		
		public function testBoolean()
		{
			$this->assertTokens(
				'TrUe falSE',
				array(
					OqlToken::create(OqlTokenType::BOOLEAN, true),
					OqlToken::create(OqlTokenType::BOOLEAN, false)
				)
			);
		}
		
		public function testNull()
		{
			$this->assertTokens(
				'null testNULL nullTest testNULLtest NULL',
				array(
					OqlToken::create(OqlTokenType::NULL, 'null'),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'testNULL'),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'nullTest'),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'testNULLtest'),
					OqlToken::create(OqlTokenType::NULL, 'null')
				)
			);
		}
		
		public function testPlaceholder()
		{
			$this->assertTokens(
				' $1 $22 $ ',
				array(
					OqlToken::create(OqlTokenType::PLACEHOLDER, 1),
					OqlToken::create(OqlTokenType::PLACEHOLDER, 22),
					OqlToken::create(OqlTokenType::UNKNOWN, '$')
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
					OqlToken::create(OqlTokenType::KEYWORD, 'distinct'),
					OqlToken::create(OqlTokenType::KEYWORD, 'from'),
					OqlToken::create(OqlTokenType::KEYWORD, 'where'),
					OqlToken::create(OqlTokenType::KEYWORD, 'like'),
					OqlToken::create(OqlTokenType::KEYWORD, 'between'),
					OqlToken::create(OqlTokenType::KEYWORD, 'group by'),
					OqlToken::create(OqlTokenType::KEYWORD, 'order by'),
					OqlToken::create(OqlTokenType::KEYWORD, 'asc'),
					OqlToken::create(OqlTokenType::KEYWORD, 'desc'),
					OqlToken::create(OqlTokenType::KEYWORD, 'having'),
					OqlToken::create(OqlTokenType::KEYWORD, 'limit'),
					OqlToken::create(OqlTokenType::KEYWORD, 'offset'),
					OqlToken::create(OqlTokenType::OPERATOR, 'not'),
					OqlToken::create(OqlTokenType::OPERATOR, 'and'),
					OqlToken::create(OqlTokenType::OPERATOR, 'or'),
					OqlToken::create(OqlTokenType::KEYWORD, 'as'),
					OqlToken::create(OqlTokenType::KEYWORD, 'in'),
					OqlToken::create(OqlTokenType::KEYWORD, 'is'),
					OqlToken::create(OqlTokenType::KEYWORD, 'similar to'),
					OqlToken::create(OqlTokenType::KEYWORD, 'ilike')
				)
			);
		}
			
		public function testAggregateFunction()
		{
			$this->assertTokens(
				'SUM aVg min Max count',
				array(
					OqlToken::create(OqlTokenType::AGGREGATE_FUNCTION, 'sum'),
					OqlToken::create(OqlTokenType::AGGREGATE_FUNCTION, 'avg'),
					OqlToken::create(OqlTokenType::AGGREGATE_FUNCTION, 'min'),
					OqlToken::create(OqlTokenType::AGGREGATE_FUNCTION, 'max'),
					OqlToken::create(OqlTokenType::AGGREGATE_FUNCTION, 'count')
				)
			);
		}
		
		public function testIdentifier()
		{
			$this->assertTokens(
				'User _prop1.prop2.prop3 .prop4..prop5 0prop f.oo.ba.r.buzz',
				array(
					OqlToken::create(OqlTokenType::IDENTIFIER, 'User'),
					OqlToken::create(OqlTokenType::IDENTIFIER, '_prop1.prop2.prop3'),
					OqlToken::create(OqlTokenType::UNKNOWN, '.prop4..prop5'),
					OqlToken::create(OqlTokenType::UNKNOWN, '0prop'),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'f.oo.ba.r.buzz')
				)
			);
		}
		
		public function testSymbol()
		{
			$this->assertTokens(
				'(,)',
				array(
					OqlToken::create(OqlTokenType::PARENTHESES, '('),
					OqlToken::create(OqlTokenType::PUNCTUATION, ','),
					OqlToken::create(OqlTokenType::PARENTHESES, ')')
				)
			);
		}
		
		public function testOperator()
		{
			$this->assertTokens(
				'>= <= <> < > != = + - / * and Or not',
				array(
					OqlToken::create(OqlTokenType::OPERATOR, '>='),
					OqlToken::create(OqlTokenType::OPERATOR, '<='),
					OqlToken::create(OqlTokenType::OPERATOR, '!='),
					OqlToken::create(OqlTokenType::OPERATOR, '<'),
					OqlToken::create(OqlTokenType::OPERATOR, '>'),
					OqlToken::create(OqlTokenType::OPERATOR, '!='),
					OqlToken::create(OqlTokenType::OPERATOR, '='),
					OqlToken::create(OqlTokenType::OPERATOR, '+'),
					OqlToken::create(OqlTokenType::OPERATOR, '-'),
					OqlToken::create(OqlTokenType::OPERATOR, '/'),
					OqlToken::create(OqlTokenType::OPERATOR, '*'),
					OqlToken::create(OqlTokenType::OPERATOR, 'and'),
					OqlToken::create(OqlTokenType::OPERATOR, 'or'),
					OqlToken::create(OqlTokenType::OPERATOR, 'not')
				)
			);
		}
		
		public function testNumber()
		{
			$this->assertTokens(
				'123 +123 .123 123.456 1e23 1E+23 1E-23 0.1e23 -.1e23',
				array(
					OqlToken::create(OqlTokenType::NUMBER, 123.),
					OqlToken::create(OqlTokenType::OPERATOR, '+'),
					OqlToken::create(OqlTokenType::NUMBER, 123.),
					OqlToken::create(OqlTokenType::NUMBER, .123),
					OqlToken::create(OqlTokenType::NUMBER, 123.456),
					OqlToken::create(OqlTokenType::NUMBER, 1e23),
					OqlToken::create(OqlTokenType::NUMBER, 1e23),
					OqlToken::create(OqlTokenType::NUMBER, 1e-23),
					OqlToken::create(OqlTokenType::NUMBER, 1e22),
					OqlToken::create(OqlTokenType::OPERATOR, '-'),
					OqlToken::create(OqlTokenType::NUMBER, 1e22)
				)
			);
		}
		
		public function testUnknown()
		{
			$this->assertTokens(
				' ##3asdf ыфвафыв !! [ ~sdf } ',
				array(
					OqlToken::create(OqlTokenType::UNKNOWN, '##3asdf'),
					OqlToken::create(OqlTokenType::UNKNOWN, 'ыфвафыв'),
					OqlToken::create(OqlTokenType::UNKNOWN, '!!'),
					OqlToken::create(OqlTokenType::UNKNOWN, '['),
					OqlToken::create(OqlTokenType::UNKNOWN, '~sdf'),
					OqlToken::create(OqlTokenType::UNKNOWN, '}')
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
					OqlToken::create(OqlTokenType::AGGREGATE_FUNCTION, 'avg'),
					OqlToken::create(OqlTokenType::PARENTHESES, '('),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'user.id'),
					OqlToken::create(OqlTokenType::PARENTHESES, ')'),
					OqlToken::create(OqlTokenType::KEYWORD, 'as'),
					OqlToken::create(OqlTokenType::AGGREGATE_FUNCTION, 'avg'),	// identifier actually
					OqlToken::create(OqlTokenType::PUNCTUATION, ','),
					OqlToken::create(OqlTokenType::AGGREGATE_FUNCTION, 'count'),
					OqlToken::create(OqlTokenType::PARENTHESES, '('),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'id'),
					OqlToken::create(OqlTokenType::PARENTHESES, ')'),
					OqlToken::create(OqlTokenType::PUNCTUATION, ','),
					OqlToken::create(OqlTokenType::PARENTHESES, '('),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'id'),
					OqlToken::create(OqlTokenType::OPERATOR, '+'),
					OqlToken::create(OqlTokenType::NUMBER, 10.),
					OqlToken::create(OqlTokenType::PARENTHESES, ')'),
					OqlToken::create(OqlTokenType::OPERATOR, '/'),
					OqlToken::create(OqlTokenType::NUMBER, 2.),
					OqlToken::create(OqlTokenType::KEYWORD, 'from'),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'UserGroup'),
					OqlToken::create(OqlTokenType::KEYWORD, 'where'),
					OqlToken::create(OqlTokenType::PARENTHESES, '('),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'id'),
					OqlToken::create(OqlTokenType::KEYWORD, 'in'),
					OqlToken::create(OqlTokenType::PARENTHESES, '('),
					OqlToken::create(OqlTokenType::NUMBER, 1.),
					OqlToken::create(OqlTokenType::PUNCTUATION, ','),
					OqlToken::create(OqlTokenType::PLACEHOLDER, 1),
					OqlToken::create(OqlTokenType::PARENTHESES, ')'),
					OqlToken::create(OqlTokenType::OPERATOR, 'or'),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'id'),
					OqlToken::create(OqlTokenType::OPERATOR, '>='),
					OqlToken::create(OqlTokenType::PLACEHOLDER, 2),
					OqlToken::create(OqlTokenType::PARENTHESES, ')'),
					OqlToken::create(OqlTokenType::OPERATOR, 'and'),
					OqlToken::create(OqlTokenType::PARENTHESES, '('),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'name'),
					OqlToken::create(OqlTokenType::KEYWORD, 'like'),
					OqlToken::create(OqlTokenType::STRING, '%\'ы\'%'),
					OqlToken::create(OqlTokenType::PARENTHESES, ')'),
					OqlToken::create(OqlTokenType::KEYWORD, 'order by'),
					OqlToken::create(OqlTokenType::IDENTIFIER, 'id'),
					OqlToken::create(OqlTokenType::KEYWORD, 'desc')
				)
			);
		}
		
		public function testMovements()
		{
			$tokenizer = new OqlTokenizer('token1 token2 token3');
			
			$tokens = array(
				OqlToken::create(OqlTokenType::IDENTIFIER, 'token1'),
				OqlToken::create(OqlTokenType::IDENTIFIER, 'token2'),
				OqlToken::create(OqlTokenType::IDENTIFIER, 'token3')
			);
			
			$this->assertEquals(-1, $tokenizer->getIndex());
			
			for ($i = 0; $i < count($tokens) + 10; $i++) {
				$token = isset($tokens[$i]) ? $tokens[$i] : null;
				
				$this->assertEquals($token, $tokenizer->peek());
				$this->assertEquals($i - 1, $tokenizer->getIndex());
				
				$this->assertEquals($token, $tokenizer->next());
				$this->assertEquals($i, $tokenizer->getIndex());
			}
			
			try {
				$tokenizer->setIndex(-10);
				$this->fail();
			} catch (WrongArgumentException $e) {
				// pass
			}
			
			try {
				$tokenizer->setIndex(-1);
				// pass
			} catch (WrongArgumentException $e) {
				$this->fail();
			}
			
			try {
				$tokenizer->setIndex(0);
				// pass
			} catch (WrongArgumentException $e) {
				$this->fail();
			}
			
			try {
				$tokenizer->setIndex(count($tokens) - 1);
				// pass
			} catch (WrongArgumentException $e) {
				$this->fail();
			}
			
			try {
				$tokenizer->setIndex(count($tokens));
				$this->fail();
			} catch (WrongArgumentException $e) {
				// pass
			}
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