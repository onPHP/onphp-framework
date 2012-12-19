<?php
	/* $Id$ */
	
	final class OqlSelectClauseTest extends TestCaseDB
	{
		public function testEmpty()
		{
			$clauses = array(
				'select', 'properties', 'where', 'groupBy', 'orderBy', 'having'
			);
			
			foreach ($clauses as $clauseName) {
				try {
					call_user_func(array('OQL', $clauseName), null);
					$this->fail();
				
				} catch (WrongArgumentException $e) {
					// pass
				}
			}
			
			foreach ($clauses as $clauseName) {
				try {
					call_user_func(array('OQL', $clauseName), '');
					$this->fail();
				
				} catch (SyntaxErrorException $e) {
					// pass
				}
			}
		}
		
		public function testProperties()
		{
			$query = OQL::select('from TestUser');
			$criteria = Criteria::create(TestUser::dao());
			
			$this->assertCriteria($query, $criteria);
			
			$this->assertCriteria(
				$query->addProperties(
					OQL::properties('id, count(id) as count')
				),
				$criteria->
					addProjection(Projection::property('id'))->
					addProjection(Projection::count('id', 'count'))
			);
			
			$this->assertCriteria(
				$query->addProperties(
					OQL::properties('city.id')
				),
				$criteria->addProjection(
					Projection::property('city.id')
				)
			);
			
			$properties = OQL::properties('id');
			$this->assertFalse($properties->isDistinct());
			$this->assertEquals(
				$properties->toProjection(),
				Projection::chain()->add(
					Projection::property('id')
				)
			);
			
			$properties = OQL::properties('id, distinct name');
			$this->assertTrue($properties->isDistinct());
			$this->assertEquals(
				$properties->toProjection(),
				Projection::chain()->
					add(
						Projection::property('id')
					)->
					add(
						Projection::property('name')
					)
			);
			
			$properties = OQL::properties('$1')->
				bind(1, 'foo');
			$this->assertEquals(
				$properties->toProjection(),
				Projection::chain()->add(
					Projection::property('foo')
				)
			);
			
			$properties->bind(1, 'bar');
			$this->assertEquals(
				$properties->toProjection(),
				Projection::chain()->add(
					Projection::property('bar')
				)
			);
			
			$this->assertCriteria(
				OQL::select('from TestUser')->
					addProperties(
						$properties->bind(1, 'foo')
					)->
					bind(1, 'bar'),
				Criteria::create(TestUser::dao())->
					addProjection(
						Projection::property('bar')		// not foo!
					)
			);
			
			$properties =
				OQL::properties(
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
				Projection::chain()->
					add(
						Projection::property('id')
					)->
					add(
						Projection::distinctCount(
							Expression::add('city.id', 1)
						)
					)->
					add(
						Projection::avg('some', 'someAverage')
					)->
					add(
						Projection::property(
							Expression::notLike('name', '%Ы%')
						)
					)->
					add(
						Projection::property(
							Expression::expAnd(
								'foo',
								Expression::expOr('bar', 'baz')
							)
						)
					)->
					add(
						Projection::property(
							Expression::div(2, 'num')
						)
					)->
					add(
						Projection::property(
							Expression::in('a', array('test', 1))
						)
					)
			);
		}
		
		public function testWhere()
		{
			$query = OQL::select('from TestUser');
			$criteria = Criteria::create(TestUser::dao());
			
			$this->assertCriteria(
				$query->andWhere(
					OQL::where('id = 1')
				),
				$criteria->add(
					Expression::eq('id', 1)
				)
			);
			
			$this->assertCriteria(
				$query->orWhere(
					OQL::where('id = 2 and city.id is not null')
				),
				Criteria::create(TestUser::dao())->add(
					Expression::expOr(
						Expression::eq('id', 1),
						Expression::expAnd(
							Expression::eq('id', 2),
							Expression::notNull('city.id')
						)
					)
				)
			);
			
			$this->assertEquals(
				OQL::where('name similar to "test" and not $1')->
					bindNext('name')->
					toLogic(),
				Expression::expAnd(
					Expression::similar('name', 'test'),
					Expression::not('name')
				)
			);
			
			$this->assertCriteria(
				OQL::select('from TestUser')->
					where(
						OQL::where('id > $1')->
							bindNext(1)
					)->
					bindNext(2),
				Criteria::create(TestUser::dao())->
					add(
						Expression::gt('id', 2)
					)
			);
		}
		
		public function testGroupBy()
		{
			$query = OQL::select('from TestUser');
			$criteria = Criteria::create(TestUser::dao());
			
			$this->assertCriteria(
				$query->addGroupBy(
					OQL::groupBy('id')
				),
				$criteria->addProjection(
					Projection::group('id')
				)
			);
			
			$this->assertCriteria(
				$query->addGroupBy(
					OQL::groupBy('-name')
				),
				$criteria->addProjection(
					Projection::group(
						Expression::minus('name')
					)
				)
			);
			
			$this->assertEquals(
				OQL::groupBy('id, name')->
					toProjection(),
				Projection::chain()->
					add(
						Projection::group('id')
					)->
					add(
						Projection::group('name')
					)
			);
			
			$this->assertEquals(
				OQL::groupBy('id + 2')->
					toProjection(),
				Projection::chain()->
					add(
						Projection::group(
							Expression::add('id', 2)
						)
					)
			);
			
			$this->assertEquals(
				OQL::groupBy('id > 2')->
					toProjection(),
				Projection::chain()->
					add(
						Projection::group(
							Expression::gt('id', 2)
						)
					)
			);
			
			$this->assertEquals(
				OQL::groupBy('$1')->
					bindNext('id')->
					toProjection(),
				Projection::chain()->
					add(
						Projection::group('id')
					)
			);
			
			$this->assertEquals(
				OQL::groupBy('$1, $2 + 3')->
					bindNext('name')->
					bindNext('id')->
					toProjection(),
				Projection::chain()->
					add(
						Projection::group('name')
					)->
					add(
						Projection::group(
							Expression::add('id', 3)
						)
					)
			);
		}
		
		public function testOrderBy()
		{
			$query = OQL::select('from TestUser');
			$criteria = Criteria::create(TestUser::dao());
			
			$this->assertCriteria(
				$query->addOrderBy(
					OQL::orderBy('id')
				),
				$criteria->addOrder(
					OrderBy::create('id')
				)
			);
			
			$this->assertCriteria(
				$query->addOrderBy(
					OQL::orderBy('name asc, city.id desc')
				),
				$criteria->
					addOrder(
						OrderBy::create('name')->asc()
					)->
					addOrder(
						OrderBy::create('city.id')->desc()
					)
			);
			
			$this->assertEquals(
				OQL::orderBy('id + city.id desc, name')->
					toOrder(),
				OrderChain::create()->
					add(
						OrderBy::create(
							Expression::add('id', 'city.id')
						)->
						desc()
					)->
					add(
						OrderBy::create('name')
					)
			);
			
			$order = OQL::orderBy('name ilike $1')->
				bindNext('%ЙЦуК');
			$this->assertEquals(
				$order->toOrder(),
				OrderChain::create()->
					add(
						OrderBy::create(
							Expression::ilike('name', '%ЙЦуК')
						)
					)
			);
			
			$this->assertCriteria(
				OQL::select('from TestUser')->
					addOrderBy($order)->
					bindNext('test'),
				Criteria::create(TestUser::dao())->
					addOrder(
						OrderBy::create(
							Expression::ilike('name', 'test')
						)
					)
			);
		}
		
		public function testHaving()
		{
			$query = OQL::select('from TestUser');
			$criteria = Criteria::create(TestUser::dao());
			
			$this->assertCriteria(
				$query->addHaving(
					OQL::having('id > 0')
				),
				$criteria->addProjection(
					Projection::having(
						Expression::gt('id', 0)
					)
				)
			);
			
			$this->assertCriteria(
				$query->addHaving(
					OQL::having('name is not null and (id <> $1 or id != $2)')->
						bindNext(4)->
						bindNext(8)
				),
				$criteria->addProjection(
					Projection::having(
						Expression::expAnd(
							Expression::notNull('name'),
							Expression::expOr(
								Expression::notEq('id', 4),
								Expression::notEq('id', 8)
							)
						)
					)
				)
			);
			
			$this->assertEquals(
				OQL::having('id + $15')->
					bind(15, 16)->
					toProjection(),
				Projection::having(
					Expression::add('id', 16)
				)
			);
			
			$this->assertCriteria(
				OQL::select('from TestUser')->
					addHaving(
						OQL::having('id = $1')->
							bindNext(23)
					)->
					bindNext(42),
				Criteria::create(TestUser::dao())->
					addProjection(
						Projection::having(
							Expression::eq('id', 42)
						)
					)
			);
		}
		
		/**
		 * @return OqlSelectClauseTest
		**/
		private function assertCriteria(OqlQuery $query, Criteria $criteria)
		{
			$dialect = $this->getDbByType('PgSQL')->getDialect();
			
			$this->assertEquals(
				$query->toCriteria()->toDialectString($dialect),
				$criteria->toDialectString($dialect)
			);
			
			return $this;
		}
	}
?>