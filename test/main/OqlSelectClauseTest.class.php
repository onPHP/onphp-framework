<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class OqlSelectClauseTest extends TestCaseDB
	{
		public function testEmpty()
		{
			$clauses = array(
				'select', 'properties', 'where', 'groupBy', 'orderBy', 'having'
			);
			
			foreach ($clauses as $clauseName) {
				try {
					call_user_func(array('\Onphp\OQL', $clauseName), null);
					$this->fail();
				
				} catch (\Onphp\WrongArgumentException $e) {
					// pass
				}
			}
			
			foreach ($clauses as $clauseName) {
				try {
					call_user_func(array('\Onphp\OQL', $clauseName), '');
					$this->fail();
				
				} catch (\Onphp\SyntaxErrorException $e) {
					// pass
				}
			}
		}

		public function testProperties()
		{
			$query = \Onphp\OQL::select('from \Onphp\Test\TestUser');
			$criteria = \Onphp\Criteria::create(TestUser::dao());
			
			$this->assertCriteria($query, $criteria);
			
			$this->assertCriteria(
				$query->addProperties(
					\Onphp\OQL::properties('id, count(id) as count')
				),
				$criteria->
					addProjection(\Onphp\Projection::property('id'))->
					addProjection(\Onphp\Projection::count('id', 'count'))
			);
			
			$this->assertCriteria(
				$query->addProperties(
					\Onphp\OQL::properties('city.id')
				),
				$criteria->addProjection(
					\Onphp\Projection::property('city.id')
				)
			);
			
			$properties = \Onphp\OQL::properties('id');
			$this->assertFalse($properties->isDistinct());
			$this->assertEquals(
				$properties->toProjection(),
				\Onphp\Projection::chain()->add(
					\Onphp\Projection::property('id')
				)
			);
			
			$properties = \Onphp\OQL::properties('id, distinct name');
			$this->assertTrue($properties->isDistinct());
			$this->assertEquals(
				$properties->toProjection(),
				\Onphp\Projection::chain()->
					add(
						\Onphp\Projection::property('id')
					)->
					add(
						\Onphp\Projection::property('name')
					)
			);
			
			$properties = \Onphp\OQL::properties('$1')->
				bind(1, 'foo');
			$this->assertEquals(
				$properties->toProjection(),
				\Onphp\Projection::chain()->add(
					\Onphp\Projection::property('foo')
				)
			);
			
			$properties->bind(1, 'bar');
			$this->assertEquals(
				$properties->toProjection(),
				\Onphp\Projection::chain()->add(
					\Onphp\Projection::property('bar')
				)
			);
			
			$this->assertCriteria(
				\Onphp\OQL::select('from \Onphp\Test\TestUser')->
					addProperties(
						$properties->bind(1, 'foo')
					)->
					bind(1, 'bar'),
				\Onphp\Criteria::create(TestUser::dao())->
					addProjection(
						\Onphp\Projection::property('bar')		// not foo!
					)
			);
			
			$properties =
				\Onphp\OQL::properties(
					'id, count(distinct city.id + $0), avg(some) as someAverage, '
					.'name not like "%Ы%", foo and (bar or baz), $1 / $2, '
					.'a in ($3, $0)'
				)->
				bindAll(
					array(1, 2, 'num', 'test')
				);
			$this->assertFalse($properties->isDistinct());
			$this->assertEquals(
				$properties->toProjection(),
				\Onphp\Projection::chain()->
					add(
						\Onphp\Projection::property('id')
					)->
					add(
						\Onphp\Projection::distinctCount(
							\Onphp\Expression::add('city.id', 1)
						)
					)->
					add(
						\Onphp\Projection::avg('some', 'someAverage')
					)->
					add(
						\Onphp\Projection::property(
							\Onphp\Expression::notLike('name', '%Ы%')
						)
					)->
					add(
						\Onphp\Projection::property(
							\Onphp\Expression::expAnd(
								'foo',
								\Onphp\Expression::expOr('bar', 'baz')
							)
						)
					)->
					add(
						\Onphp\Projection::property(
							\Onphp\Expression::div(2, 'num')
						)
					)->
					add(
						\Onphp\Projection::property(
							\Onphp\Expression::in('a', array('test', 1))
						)
					)
			);
		}
		
		public function testWhere()
		{
			$query = \Onphp\OQL::select('from \Onphp\Test\TestUser');
			$criteria = \Onphp\Criteria::create(TestUser::dao());
			
			$this->assertCriteria(
				$query->andWhere(
					\Onphp\OQL::where('id = 1')
				),
				$criteria->add(
					\Onphp\Expression::eq('id', 1)
				)
			);
			
			$this->assertCriteria(
				$query->orWhere(
					\Onphp\OQL::where('id = 2 and city.id is not null')
				),
				\Onphp\Criteria::create(TestUser::dao())->add(
					\Onphp\Expression::expOr(
						\Onphp\Expression::eq('id', 1),
						\Onphp\Expression::expAnd(
							\Onphp\Expression::eq('id', 2),
							\Onphp\Expression::notNull('city.id')
						)
					)
				)
			);
			
			$this->assertEquals(
				\Onphp\OQL::where('name similar to "test" and not $1')->
					bindNext('name')->
					toLogic(),
				\Onphp\Expression::expAnd(
					\Onphp\Expression::similar('name', 'test'),
					\Onphp\Expression::not('name')
				)
			);
			
			$this->assertCriteria(
				\Onphp\OQL::select('from \Onphp\Test\TestUser')->
					where(
						\Onphp\OQL::where('id > $1')->
							bindNext(1)
					)->
					bindNext(2),
				\Onphp\Criteria::create(TestUser::dao())->
					add(
						\Onphp\Expression::gt('id', 2)
					)
			);
		}
		
		public function testGroupBy()
		{
			$query = \Onphp\OQL::select('from \Onphp\Test\TestUser');
			$criteria = \Onphp\Criteria::create(TestUser::dao());
			
			$this->assertCriteria(
				$query->addGroupBy(
					\Onphp\OQL::groupBy('id')
				),
				$criteria->addProjection(
					\Onphp\Projection::group('id')
				)
			);
			
			$this->assertCriteria(
				$query->addGroupBy(
					\Onphp\OQL::groupBy('-name')
				),
				$criteria->addProjection(
					\Onphp\Projection::group(
						\Onphp\Expression::minus('name')
					)
				)
			);
			
			$this->assertEquals(
				\Onphp\OQL::groupBy('id, name')->
					toProjection(),
				\Onphp\Projection::chain()->
					add(
						\Onphp\Projection::group('id')
					)->
					add(
						\Onphp\Projection::group('name')
					)
			);
			
			$this->assertEquals(
				\Onphp\OQL::groupBy('id + 2')->
					toProjection(),
				\Onphp\Projection::chain()->
					add(
						\Onphp\Projection::group(
							\Onphp\Expression::add('id', 2)
						)
					)
			);
			
			$this->assertEquals(
				\Onphp\OQL::groupBy('id > 2')->
					toProjection(),
				\Onphp\Projection::chain()->
					add(
						\Onphp\Projection::group(
							\Onphp\Expression::gt('id', 2)
						)
					)
			);
			
			$this->assertEquals(
				\Onphp\OQL::groupBy('$1')->
					bindNext('id')->
					toProjection(),
				\Onphp\Projection::chain()->
					add(
						\Onphp\Projection::group('id')
					)
			);
			
			$this->assertEquals(
				\Onphp\OQL::groupBy('$1, $2 + 3')->
					bindNext('name')->
					bindNext('id')->
					toProjection(),
				\Onphp\Projection::chain()->
					add(
						\Onphp\Projection::group('name')
					)->
					add(
						\Onphp\Projection::group(
							\Onphp\Expression::add('id', 3)
						)
					)
			);
		}
		
		public function testOrderBy()
		{
			$query = \Onphp\OQL::select('from \Onphp\Test\TestUser');
			$criteria = \Onphp\Criteria::create(TestUser::dao());
			
			$this->assertCriteria(
				$query->addOrderBy(
					\Onphp\OQL::orderBy('id')
				),
				$criteria->addOrder(
					\Onphp\OrderBy::create('id')
				)
			);
			
			$this->assertCriteria(
				$query->addOrderBy(
					\Onphp\OQL::orderBy('name asc, city.id desc')
				),
				$criteria->
					addOrder(
						\Onphp\OrderBy::create('name')->asc()
					)->
					addOrder(
						\Onphp\OrderBy::create('city.id')->desc()
					)
			);
			
			$this->assertEquals(
				\Onphp\OQL::orderBy('id + city.id desc, name')->
					toOrder(),
				\Onphp\OrderChain::create()->
					add(
						\Onphp\OrderBy::create(
							\Onphp\Expression::add('id', 'city.id')
						)->
						desc()
					)->
					add(
						\Onphp\OrderBy::create('name')
					)
			);
			
			$order = \Onphp\OQL::orderBy('name ilike $1')->
				bindNext('%ЙЦуК');
			$this->assertEquals(
				$order->toOrder(),
				\Onphp\OrderChain::create()->
					add(
						\Onphp\OrderBy::create(
							\Onphp\Expression::ilike('name', '%ЙЦуК')
						)
					)
			);
			
			$this->assertCriteria(
				\Onphp\OQL::select('from \Onphp\Test\TestUser')->
					addOrderBy($order)->
					bindNext('test'),
				\Onphp\Criteria::create(TestUser::dao())->
					addOrder(
						\Onphp\OrderBy::create(
							\Onphp\Expression::ilike('name', 'test')
						)
					)
			);
		}
		
		public function testHaving()
		{
			$query = \Onphp\OQL::select('from \Onphp\Test\TestUser');
			$criteria = \Onphp\Criteria::create(TestUser::dao());
			
			$this->assertCriteria(
				$query->addHaving(
					\Onphp\OQL::having('id > 0')
				),
				$criteria->addProjection(
					\Onphp\Projection::having(
						\Onphp\Expression::gt('id', 0)
					)
				)
			);
			
			$this->assertCriteria(
				$query->addHaving(
					\Onphp\OQL::having('name is not null and (id <> $1 or id != $2)')->
						bindNext(4)->
						bindNext(8)
				),
				$criteria->addProjection(
					\Onphp\Projection::having(
						\Onphp\Expression::expAnd(
							\Onphp\Expression::notNull('name'),
							\Onphp\Expression::expOr(
								\Onphp\Expression::notEq('id', 4),
								\Onphp\Expression::notEq('id', 8)
							)
						)
					)
				)
			);
			
			$this->assertEquals(
				\Onphp\OQL::having('id + $15')->
					bind(15, 16)->
					toProjection(),
				\Onphp\Projection::having(
					\Onphp\Expression::add('id', 16)
				)
			);
			
			$this->assertCriteria(
				\Onphp\OQL::select('from \Onphp\Test\TestUser')->
					addHaving(
						\Onphp\OQL::having('id = $1')->
							bindNext(23)
					)->
					bindNext(42),
				\Onphp\Criteria::create(TestUser::dao())->
					addProjection(
						\Onphp\Projection::having(
							\Onphp\Expression::eq('id', 42)
						)
					)
			);
		}
		
		/**
		 * @return \Onphp\Test\OqlSelectClauseTest
		**/
		private function assertCriteria(\Onphp\OqlQuery $query, \Onphp\Criteria $criteria)
		{
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			$this->assertEquals(
				$query->toCriteria()->toDialectString($dialect),
				$criteria->toDialectString($dialect)
			);
			
			return $this;
		}
	}
?>