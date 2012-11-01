<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class LogicTest extends TestCaseDB
	{
		public function testBaseSqlGeneration()
		{
			$dialect = \Onphp\ImaginaryDialect::me();
			$pgDialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			$this->assertRegExp(
				'/^\(a (AND|and) b\)$/',
				\Onphp\Expression::expAnd('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a (OR|or) b\)$/',
				\Onphp\Expression::expOr('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEquals(
				\Onphp\Expression::eq('a', 'b')->toDialectString($dialect),
				'(a = b)'
			);
			
			$some = \Onphp\IdentifiableObject::wrap(123);
			$this->assertEquals(
				\Onphp\Expression::eqId('a', $some)->toDialectString($dialect),
				'(a = 123)'
			);
			
			$this->assertEquals(
				\Onphp\Expression::notEq('a', 'b')->toDialectString($dialect),
				'(a != b)'
			);
			
			$this->assertEquals(
				\Onphp\Expression::gt('a', 'b')->toDialectString($dialect),
				'(a > b)'
			);
			
			$this->assertEquals(
				\Onphp\Expression::gtEq('a', 'b')->toDialectString($dialect),
				'(a >= b)'
			);
			
			$this->assertEquals(
				\Onphp\Expression::lt('a', 'b')->toDialectString($dialect),
				'(a < b)'
			);
			
			$this->assertEquals(
				\Onphp\Expression::ltEq('a', 'b')->toDialectString($dialect),
				'(a <= b)'
			);
			
			$this->assertRegExp(
				'/^\(a ((IS NOT NULL)|(is not null)) *\)$/',
				\Onphp\Expression::notNull('a')->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a ((IS NULL)|(is null)) *\)$/',
				\Onphp\Expression::isNull('a')->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a ((IS TRUE)|(is true)) *\)$/',
				\Onphp\Expression::isTrue('a')->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a ((IS FALSE)|(is false)) *\)$/',
				\Onphp\Expression::isFalse('a')->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a (LIKE|like) b\)$/',
				\Onphp\Expression::like('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a ((NOT LIKE)|(not like)) b\)$/',
				\Onphp\Expression::notLike('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a (ILIKE|ilike) b\)$/',
				\Onphp\Expression::ilike('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a ((NOT ILIKE)|(not like)) b\)$/',
				\Onphp\Expression::notIlike('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a ((SIMILAR TO)|(similar to)) b\)$/',
				\Onphp\Expression::similar('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a ((NOT SIMILAR TO)|(not similar to)) b\)$/',
				\Onphp\Expression::notSimilar('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(lower(a) = b)',
				\Onphp\Expression::eqLower('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(lower(a) = lower(b))',
				
				\Onphp\Expression::eqLower(new \Onphp\DBValue('a'), new \Onphp\DBValue('b'))->
				toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(lower(\'a\') = lower(\'b\'))',
				
				\Onphp\Expression::eqLower(new \Onphp\DBValue('a'), new \Onphp\DBValue('b'))->
				toDialectString($pgDialect)
			);
			
			$this->assertEquals(
				'(lower(\'a\') = lower("b"))',
				
				\Onphp\Expression::eqLower(new \Onphp\DBValue('a'), new \Onphp\DBField('b'))->
				toDialectString($pgDialect)
			);
			
			$this->assertEquals(
				'(lower("a") = lower(\'b\'))',
				
				\Onphp\Expression::eqLower(new \Onphp\DBField('a'), new \Onphp\DBValue('b'))->
				toDialectString($pgDialect)
			);
			
			$this->assertRegExp(
				'/^\(a (BETWEEN|between) b (AND|and) c\)$/',
				\Onphp\Expression::between('a', 'b', 'c')->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(a = 123)',
				\Onphp\Expression::in('a', 123)->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(a = 123)',
				\Onphp\Expression::in('a', array(123))->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a (in|IN) \(123, 456\)\)$/',
				\Onphp\Expression::in('a', array(123, 456))->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(a != 123)',
				\Onphp\Expression::notIn('a', 123)->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(a != 123)',
				\Onphp\Expression::notIn('a', array(123))->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a ((not in)|(NOT IN)) \(123, 456\)\)$/',
				\Onphp\Expression::notIn('a', array(123, 456))->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(a + b)',
				\Onphp\Expression::add('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(a - b)',
				\Onphp\Expression::sub('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(a * b)',
				\Onphp\Expression::mul('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(a / b)',
				\Onphp\Expression::div('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertRegExp(
				'/^\(a (between|BETWEEN) b (and|AND) c\)$/',
				\Onphp\Expression::between('a', 'b', 'c')->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(-1 IS NULL)',
				\Onphp\Expression::isNull(-1)->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(NOT a)',
				\Onphp\Expression::not('a')->toDialectString($dialect)
			);
			
			$this->assertEquals(
				'(- a)',
				\Onphp\Expression::minus('a')->toDialectString($dialect)
			);
			
			try {
				\Onphp\Expression::eq('id', null)->toDialectString($dialect);
				
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {
				//it's Ok
			}
		}
		
		public function testPgGeneration()
		{
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			$this->assertRegExp(
				'/^\(\(\(\(\'asdf\' = "b"\) (AND|and) \("e" != \("i" \/ \'123\'\)\) (AND|and) \(\(lower\("a"\) += +lower\("b"\)\) ((IS TRUE)|(is true))\) (AND|and) \("g" = \'12\'\) (AND|and) \("j" (BETWEEN|between) \'3\' (AND|and) "p"\)\) (OR|or) \("table"\."c" ((IS NOT NULL)|(is not null))\)\) (AND|and) \("sometable"\."a" ((not in)|(NOT IN)) \(\'q\', \'qwer\', \'xcvzxc\', \'wer\'\)\)\)$/',
 				\Onphp\Expression::expAnd(
					\Onphp\Expression::expOr(
						\Onphp\Expression::andBlock(
							\Onphp\Expression::eq(
								new \Onphp\DBValue('asdf'),
								new \Onphp\DBField('b')
							),
							\Onphp\Expression::notEq(
								new \Onphp\DBField('e'),
								\Onphp\Expression::div(
									new \Onphp\DBField('i'),
									new \Onphp\DBValue(123)
								)
							),
							\Onphp\Expression::isTrue(
								\Onphp\Expression::eqLower(new \Onphp\DBField('a'), new \Onphp\DBField('b'))
							),
							
							\Onphp\Expression::eq(new \Onphp\DBField('g'), new \Onphp\DBValue(12)),
							
							\Onphp\Expression::between('j', new \Onphp\DBValue(3), new \Onphp\DBField('p'))
						),
						\Onphp\Expression::notNull(new \Onphp\DBField('c', 'table'))
					),
					\Onphp\Expression::notIn(
						new \Onphp\DBField('a', 'sometable'),
						array('q', 'qwer', 'xcvzxc', 'wer')
					)
				)->toDialectString($dialect)
			);
		}
		
		public function testFormCalculation()
		{
			$form = \Onphp\Form::create()->
				add(
					\Onphp\Primitive::string('a')
				)->
				add(
					\Onphp\Primitive::boolean('b')
				)->
				add(
					\Onphp\Primitive::integer('c')
				)->
				add(
					\Onphp\Primitive::integer('d')
				)->
				add(
					\Onphp\Primitive::integer('e')
				)->
				add(
					\Onphp\Primitive::boolean('f')
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
				\Onphp\Expression::isTrue(new \Onphp\FormField('b'))->toBoolean($form)
			);
			
			$this->assertFalse(
				\Onphp\Expression::isTrue(new \Onphp\FormField('f'))->toBoolean($form)
			);
			
			$this->assertFalse(
				\Onphp\Expression::eq('asdf', new \Onphp\FormField('a'))->toBoolean($form)
			);
			
			$this->assertTrue(
				\Onphp\Expression::eqLower('asdfg', new \Onphp\FormField('a'))->toBoolean($form)
			);
			
			$this->assertTrue(
				\Onphp\Expression::eq('asDfg', new \Onphp\FormField('a'))->toBoolean($form)
			);
			
			$this->assertTrue(
				\Onphp\Expression::andBlock(
					\Onphp\Expression::expOr(
						new \Onphp\FormField('b'),
						new \Onphp\FormField('f')
					),
					\Onphp\Expression::eq(
						7,
						\Onphp\Expression::add(
							new \Onphp\FormField('c'),
							\Onphp\Expression::mul(
								new \Onphp\FormField('d'),
								new \Onphp\FormField('e')
							)
						)
					)
				)->
				toBoolean($form)
			);
			
			$this->assertTrue(
				\Onphp\Expression::between(new \Onphp\FormField('d'), new \Onphp\FormField('c'), new \Onphp\FormField('e'))->toBoolean($form)
			);
			
			$this->assertFalse(
				\Onphp\Expression::between(new \Onphp\FormField('c'), new \Onphp\FormField('d'), new \Onphp\FormField('e'))->toBoolean($form)
			);
			
			$this->assertFalse(
				\Onphp\Expression::not(new \Onphp\FormField('b'))->toBoolean($form)
			);
			
			$this->assertTrue(
				\Onphp\Expression::not(new \Onphp\FormField('f'))->toBoolean($form)
			);
			
		}
		
		public function testChainSQL()
		{
			$this->assertRegExp(
				'/^\(\(a (OR|or) \(b ((IS NOT NULL)|(is not null)) *\)\) (AND|and) \(c = d\) (AND|and) \(e ((IS FALSE)|(is false)) *\)\)$/',
				\Onphp\Expression::chain()->
					expAnd(
						\Onphp\Expression::expOr(
							'a',
							\Onphp\Expression::notNull('b')
						)
					)->
					expAnd(
						\Onphp\Expression::eq('c', 'd')
					)->
					expAnd(
						\Onphp\Expression::isFalse('e')
					)->
					toDialectString(\Onphp\ImaginaryDialect::me())
			);
			
			$this->assertRegExp(
				'/^\(\(a = b\) (OR|or) \(d (OR|or) \(c > e\)\) (OR|or) \(f (in|IN) \(qwer, asdf, zxcv\)\)\)$/',
				\Onphp\Expression::chain()->
					expOr(
						\Onphp\Expression::eq('a', 'b')
					)->
					expOr(
						\Onphp\Expression::expOr('d', \Onphp\Expression::gt('c', 'e'))
					)->
					expOr(
						\Onphp\Expression::in('f', array('qwer', 'asdf', 'zxcv'))
					)->
					toDialectString(\Onphp\ImaginaryDialect::me())
			);
		}

		public function testChainForm()
		{
			$form = \Onphp\Form::create()->
				add(
					\Onphp\Primitive::string('a')
				)->
				add(
					\Onphp\Primitive::string('b')
				)->
				add(
					\Onphp\Primitive::integer('c')
				)->
				add(
					\Onphp\Primitive::integer('d')
				)->
				add(
					\Onphp\Primitive::boolean('e')
				)->
				add(
					\Onphp\Primitive::string('f')
				)->
				import(
					array(
						'a' => 'true',
						'c' => 123,
						'd'	=> 123,
					)
				);
			
			$andChain = \Onphp\Expression::chain()->
				expAnd(
					\Onphp\Expression::expOr(
						new \Onphp\FormField('a'),
						\Onphp\Expression::notNull(new \Onphp\FormField('b'))
					)
				)->
				expAnd(
					\Onphp\Expression::eq(
						new \Onphp\FormField('c'),
						new \Onphp\FormField('d'))
				)->
				expAnd(
					\Onphp\Expression::isFalse(new \Onphp\FormField('e'))
				);

			$this->assertTrue($andChain->toBoolean($form));
			
			$form->importMore(array('e' => 'on'));
			$this->assertFalse($andChain->toBoolean($form));

			$orChain = \Onphp\Expression::chain()->
				expOr(
					\Onphp\Expression::eq(new \Onphp\FormField('a'), new \Onphp\FormField('b'))
				)->
				expOr(
					\Onphp\Expression::expOr(
						new \Onphp\FormField('e'),
						\Onphp\Expression::gt(
							new \Onphp\FormField('c'),
							new \Onphp\FormField('d')
						)
					)
				)->
				expOr(
					\Onphp\Expression::in(new \Onphp\FormField('f'), array('qwer', 'asdf', 'zxcv'))
				);

			$form->import(array());
			$this->assertFalse($orChain->toBoolean($form));
			
			$form->import(array(
				'e' => '1'
			));
			$this->assertTrue($orChain->toBoolean($form));
			
			$form->import(array(
				'a' => 'asdf',
				'b' => 'qwerq',
				'c' => '13',
				'd' => '1313',
				'f' => 'iukj'
			));
			$this->assertFalse($orChain->toBoolean($form));
			
			$form->import(array(
				'c' => '13',
				'd' => '12'
			));
			$this->assertTrue($orChain->toBoolean($form));
			
			$form->import(array(
				'f' => 'asdfwer'
			));
			$this->assertFalse($orChain->toBoolean($form));
			
			$form->import(array(
				'f' => 'qwer'
			));
			$this->assertTrue($orChain->toBoolean($form));
		}
		
		public function testCallbackLogicalObject()
		{
			if (mb_substr(PHP_VERSION, 0, 3) < '5.3') {
				$this->markTestSkipped('only php 5.3 or later');
			}
			$callBack = function(\Onphp\Form $form) {
				return $form->getValue('repository') == 'git';
			};
			
			$form = \Onphp\Form::create()->
				add(\Onphp\Primitive::string('repository'))->
				addRule('isOurRepository', \Onphp\CallbackLogicalObject::create($callBack));
			
			$form->import(array('repository' => 'svn'))->checkRules();
			$this->assertEquals(array('isOurRepository' => \Onphp\Form::WRONG), $form->getErrors());
			
			$form->clean()->dropAllErrors();
			
			$form->import(array('repository' => 'git'))->checkRules();
			$this->assertEquals(array(), $form->getErrors());
		}
	}
?>