<?php
	/* $Id$ */
	
	final class LogicTest extends UnitTestCase
	{
		public function testBaseSqlGeneration()
		{
			$dialect = ImaginaryDialect::me();
			
			$this->assertWantedPattern(
				'/^\(a (AND|and) b\)$/',
				Expression::expAnd('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a (OR|or) b\)$/',
				Expression::expOr('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEqual(
				Expression::eq('a', 'b')->toDialectString($dialect), 
				'(a = b)'
			);
			
			$some = IdentifiableObject::wrap(123);
			$this->assertEqual(
				Expression::eqId('a', $some)->toDialectString($dialect), 
				'(a = 123)'
			);
			
			$this->assertEqual(
				Expression::notEq('a', 'b')->toDialectString($dialect), 
				'(a != b)'
			);
			
			$this->assertEqual(
				Expression::gt('a', 'b')->toDialectString($dialect), 
				'(a > b)'
			);
			
			$this->assertEqual(
				Expression::gtEq('a', 'b')->toDialectString($dialect), 
				'(a >= b)'
			);
			
			$this->assertEqual(
				Expression::lt('a', 'b')->toDialectString($dialect), 
				'(a < b)'
			);
			
			$this->assertEqual(
				Expression::ltEq('a', 'b')->toDialectString($dialect), 
				'(a <= b)'
			);
			
			$this->assertWantedPattern(
				'/^\(a ((IS NOT NULL)|(is not null))\s*\)$/',
				Expression::notNull('a')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((IS NULL)|(is null))\s*\)$/',
				Expression::isNull('a')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((IS TRUE)|(is true))\s*\)$/',
				Expression::isTrue('a')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((IS FALSE)|(is false))\s*\)$/',
				Expression::isFalse('a')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a (LIKE|like) b\)$/',
				Expression::like('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((NOT LIKE)|(not like)) b\)$/',
				Expression::notLike('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a (ILIKE|ilike) b\)$/',
				Expression::ilike('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((NOT ILIKE)|(not like)) b\)$/',
				Expression::notIlike('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((SIMILAR TO)|(similar to)) b\)$/',
				Expression::similar('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((NOT SIMILAR TO)|(not similar to)) b\)$/',
				Expression::notSimilar('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(lower\(a\)\s+=\s+lower\(b\)\)$/',
				Expression::eqLower('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a (BETWEEN|between) b (AND|and) c\)$/',
				Expression::between('a', 'b', 'c')->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a = 123)',
				Expression::in('a', 123)->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a = 123)',
				Expression::in('a', array(123))->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a (in|IN) \(123, 456\)\)$/',
				Expression::in('a', array(123, 456))->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a != 123)',
				Expression::notIn('a', 123)->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a != 123)',
				Expression::notIn('a', array(123))->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((not in)|(NOT IN)) \(123, 456\)\)$/',
				Expression::notIn('a', array(123, 456))->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a + b)',
				Expression::add('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a - b)',
				Expression::sub('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a * b)',
				Expression::mul('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a / b)',
				Expression::div('a', 'b')->toDialectString($dialect)
			);
		}
		
		public function testPgGeneration()
		{
			$dialect = PostgresDialect::me();
			$this->assertWantedPattern(
				'/^\(\(\(\(\'asdf\' = "b"\) (AND|and) \("e" != \("i" \/ \'123\'\)\) (AND|and) \(\(lower\("a"\)  =  lower\("b"\)\) ((IS TRUE)|(is true)) \)\) (OR|or) \("table"\."c" ((IS NOT NULL)|(is not null)) \)\) (AND|and) \("sometable"\."a" ((not in)|(NOT IN)) \(\'q\', \'qwer\', \'xcvzxc\', \'wer\'\)\)\)$/',
				Expression::expAnd(
					Expression::expOr(
						Expression::andBlock(
							Expression::eq(
								new DBValue('asdf'),
								new DBField('b')
							),
							Expression::notEq(
								new DBField('e'),
								Expression::div(
									new DBField('i'),
									new DBValue('123')
								)
							),
							Expression::isTrue(
								Expression::eqLower('a', 'b')
							)
						),
						Expression::notNull(new DBField('c', 'table'))
					),
					Expression::notIn(
						new DBField('a', 'sometable'),
						array('q', 'qwer', 'xcvzxc', 'wer')
					)
				)->toDialectString($dialect)
			);
		}
		
		public function testFormCalculation()
		{
			$form = Form::create()->
				add(
					Primitive::string('a')
				)->
				add(
					Primitive::boolean('b')
				)->
				add(
					Primitive::integer('c')
				)->
				add(
					Primitive::integer('d')
				)->
				add(
					Primitive::integer('e')
				)->
				add(
					Primitive::boolean('f')
				)->
				import(
					array(
						'a' => 'asDfg',
						'b' => 'true',
						'c' => '1',
						'd' => '2',
						'e' => '3'
					)
				);
			
			$this->assertTrue(
				Expression::isTrue(new FormField('b'))->toBoolean($form)
			);
			
			$this->assertFalse(
				Expression::isTrue(new FormField('f'))->toBoolean($form)
			);
			
			$this->assertFalse(
				Expression::eq('asdf', new FormField('a'))->toBoolean($form)
			);
			
			$this->assertTrue(
				Expression::eqLower('asdfg', new FormField('a'))->toBoolean($form)
			);
			
			$this->assertTrue(
				Expression::eq('asDfg', new FormField('a'))->toBoolean($form)
			);
			
			$this->assertTrue(
				Expression::andBlock(
					Expression::expOr(
						new FormField('b'),
						new FormField('f')
					),
					Expression::eq(
						7,
						Expression::add(
							new FormField('c'),
							Expression::mul(
								new FormField('d'),
								new FormField('e')
							)
						)
					)
				)
			);
			
		}
	}
?>