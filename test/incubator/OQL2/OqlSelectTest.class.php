<?php
	/* $Id$ */
	
	final class OqlSelectTest extends TestCase
	{
		public function testProperty()
		{
			/*
			// no properties
			$this->assertCriteria(
				'from TestUser',
				Criteria::create(TestUser::dao())
			);
			
			// simple property
			$this->assertCriteria(
				'id from TestUser',
				Criteria::create(TestUser::dao())->
					setProjection(Projection::property('id'))
			);
			
			*/
			
			// aggregate functions, distinct, aliases, properties
			$this->assertCriteria(
				'avg(id), count(city.id) as count, '
				.'count(distinct city.id) as distinctCount, '
				.'sum(id), min(id), max(id), city.Name as cityName, '
				.'distinct id, city, min from TestUser',
				Criteria::create(TestUser::dao())->
					setDistinct(true)->
					setProjection(
						Projection::chain()->
							add(Projection::avg('id'))->
							add(Projection::count('city.id', 'count'))->
							add(Projection::distinctCount('city.id', 'distinctCount'))->
							add(Projection::sum('id'))->
							add(Projection::min('id'))->
							add(Projection::max('id'))->
							add(Projection::property('city.Name', 'cityName'))->
							add(Projection::property('id'))->
							add(Projection::property('city'))->
							add(Projection::property('min'))
					)
			);
			
			/*
			// arithmetic expression in aggregate function
			$this->assertCriteria(
				'avg(-id - -1 / -$1), avg((10-id) * -($1+-2.1)), avg(10) as ten '
				.'from TestUser',
				Criteria::create(TestUser::dao())->
					setProjection(
						Projection::chain()->
							add(
								Projection::avg(
									Expression::sub(
										Expression::minus('id'),
										Expression::div(-1, -10)
									)
								)
							)->
							add(
								Projection::avg(
									Expression::mul(
										Expression::sub(10, 'id'),
										Expression::minus(
											Expression::add(10, -2.1)
										)
									)
								)
							)->
							add(Projection::avg(10, 'ten'))
					),
				array(1 => 10)
			);
			
			// boolean and arithmetic expressions in count function
			$this->assertCriteria(
				'count(distinct -id * 2 / -3 > 10), count(id in (1, -$1, -3) and Name like `test`)'
				.'from TestUser',
				Criteria::create(TestUser::dao())->
					setProjection(
						Projection::chain()->
							add(
								Projection::distinctCount(
									Expression::gt(
										Expression::div(
											Expression::mul(
												Expression::minus('id'),
												2
											),
											-3
										),
										10
									)
								)
							)->
							add(
								Projection::count(
									Expression::expAnd(
										Expression::in('id', array(1, -2, -3)),
										Expression::like('Name', 'test')
									)
								)
							)
					),
				array(1 => 2)
			);
			
			// boolean and arithmetic expressions as property
			$this->assertCriteria(
				'(id > 10) and (20 > id) as inInterval, ((2*id + 1) / id), -id '
				.'from TestUser',
				Criteria::create(TestUser::dao())->
					setProjection(
						Projection::chain()->
							add(
								Projection::property(
									Expression::expAnd(
										Expression::gt('id', 10),
										Expression::gt(20, 'id')
									),
									'inInterval'
								)
							)->
							add(
								Projection::property(
									Expression::div(
										Expression::add(
											Expression::mul(2, 'id'),
											1
										),
										'id'
									)
								)
							)->
							add(
								Projection::property(
									Expression::minus('id')
								)
							)
					)
			);
			*/
		}
		
		public function testWhere()
		{
			$userId = 1;
			$user = TestUser::create()->setId($userId);
			
			// bindings, operator chain
			$this->assertCriteria(
				'from TestUser where id = $1 or id = $2 or $2 = id or $1 = $2',
				Criteria::create(TestUser::dao())->
					add(
						Expression::expOr(
							Expression::eqId('id', $user),
							Expression::expOr(
								Expression::eq('id', $userId),
								Expression::expOr(
									Expression::eq($userId, 'id'),
									Expression::eq($userId, $userId)
								)
							)
						)
					),
				array(
					1 => $user,
					2 => $userId
				)
			);
			
			// comparison operators
			$this->assertCriteria(
				'from TestUser where id = 1 or id >= 1 or id <= 1 or id <> 1 or id != 1',
				Criteria::create(TestUser::dao())->
					add(
						Expression::expOr(
							Expression::eq('id', 1),
							Expression::expOr(
								Expression::gtEq('id', 1),
								Expression::expOr(
									Expression::ltEq('id', 1),
									Expression::expOr(
										Expression::notEq('id', 1),
										Expression::notEq('id', 1)
									)
								)
							)
						)
					)
			);
			
			// priority
			$this->assertCriteria(
				'from TestUser where id = 1 and Name = "some" '
				.'or Name = "any" or not id > 1 and 2 = id * 2 + 1',
				Criteria::create(TestUser::dao())->
					add(
						Expression::expOr(
							Expression::expAnd(
								Expression::eq('id', 1),
								Expression::eq('Name', 'some')
							),
							Expression::expOr(
								Expression::eq('Name', 'any'),
								Expression::expAnd(
									Expression::not(
										Expression::gt('id', 1)
									),
									Expression::eq(
										2,
										Expression::add(
											Expression::mul('id', 2),
											1
										)
									)
								)
							)
						)
					)
			);
			
			// parentheses priority
			$this->assertCriteria(
				'from TestUser where (id = 1 and (Name = "some" or Name = "any"))',
				Criteria::create(TestUser::dao())->
					add(
						Expression::expAnd(
							Expression::eq('id', 1),
							Expression::expOr(
								Expression::eq('Name', 'some'),
								Expression::eq('Name', 'any')
							)
						)
					)
			);
			
			// parentheses priority
			$this->assertCriteria(
				'from TestUser where ((Name = "some" or Name = "any")) and (id = 1)',
				Criteria::create(TestUser::dao())->
					add(
						Expression::expAnd(
							Expression::expOr(
								Expression::eq('Name', 'some'),
								Expression::eq('Name', 'any')
							),
							Expression::eq('id', 1)
						)
					)
			);
			
			// unary not
			$this->assertCriteria(
				'from TestUser where not (not not id = 1 and not id > 1)',
				Criteria::create(TestUser::dao())->
					add(
						Expression::not(
							Expression::expAnd(
								Expression::not(
									Expression::not(
										Expression::eq('id', 1)
									)
								),
								Expression::not(
									Expression::gt('id', 1)
								)
							)
						)
					)
			);
			
			// is ([not] null|true|false)
			$this->assertCriteria(
				'from TestUser where id is null or id is not null or id is true or id is false',
				Criteria::create(TestUser::dao())->
					add(
						Expression::expOr(
							Expression::isNull('id'),
							Expression::expOr(
								Expression::notNull('id'),
								Expression::expOr(
									Expression::isTrue('id'),
									Expression::isFalse('id')
								)
							)
						)
					)
			);
			
			// [not] in
			$this->assertCriteria(
				'from TestUser where id in (1) or id not in (1, "2", $1, true)',
				Criteria::create(TestUser::dao())->
					add(
						Expression::expOr(
							Expression::in('id', array(1)),
							Expression::notIn('id', array(1, '2', true, true))
						)
					),
				array(1 => true)
			);
			
			// in subquery
			try {
				$this->assertCriteria(
					'from TestUser where id in ($1)',
					Criteria::create(TestUser::dao())->
						add(
							Expression::in(
								'id',
								Criteria::create(TestUser::dao())->
									setProjection(Projection::property('id'))
							)
						),
					array(
						1 => OQL::select('id from TestUser')->toCriteria()
					)
				);
			} catch (MissingElementException $e) {
				// no db link
			}
			
			// in array
			$this->assertCriteria(
				'from TestUser where id in ($1)',
				Criteria::create(TestUser::dao())->
					add(
						Expression::in(
							'id',
							array(1, 2)
						)
					),
				array(
					1 => array(1, 2)
				)
			);
			
			// [not] (like|ilike|similar to)
			$this->assertCriteria(
				'from TestUser where id like $1 or id not like "Ы%" '
				.'or id ilike $2 or id not ilike "ы%" '
				.'or Name similar to "s" or Name not similar to $3',
				Criteria::create(TestUser::dao())->
					add(
						Expression::expOr(
							Expression::expOr(
								Expression::expOr(
									Expression::expOr(
										Expression::expOr(
											Expression::like('id', 'ы'),
											Expression::notLike('id', 'Ы%')
										),
										Expression::ilike('id', 'Ы')
									),
									Expression::notIlike('id', 'ы%')
								),
								Expression::similar('Name', 's')
							),
							Expression::notSimilar('Name', 'S')
						)
					),
				array(
					1 => 'ы',
					2 => 'Ы',
					3 => 'S'
				)
			);
			
			// between
			$this->assertCriteria(
				'from TestUser where created between "2008-08-06 10:00" and $1 '
				.'or id between id and 10',
				Criteria::create(TestUser::dao())->
					add(
						Expression::expOr(
							Expression::between(
								'created',
								'2008-08-06 10:00',
								SQLFunction::create('now')
							),
							Expression::between('id', 'id', 10)
						)
					),
				array(1 => SQLFunction::create('now'))
			);
			
			// arithmetic expression
			$this->assertCriteria(
				'from TestUser where (2 + -id --1) / 2 = id',
				Criteria::create(TestUser::dao())->
					add(
						Expression::eq(
							Expression::div(
								Expression::sub(
									Expression::add(
										2,
										Expression::minus('id')
									),
									-1
								),
								2
							),
							'id'
						)
					),
				array(1 => 'id')
			);
		}
		
		public function testGroupBy()
		{
			$this->assertCriteria(
				'from TestUser group by id',
				Criteria::create(TestUser::dao())->
					setProjection(
						Projection::group('id')
					)
			);
			
			$this->assertCriteria(
				'from TestUser group by id, nickname',
				Criteria::create(TestUser::dao())->
					setProjection(
						Projection::chain()->
							add(Projection::group('id'))->
							add(Projection::group('nickname'))
					)
			);
		}
		
		public function testOrderBy()
		{
			// property
			$this->assertCriteria(
				'from TestUser order by id',
				Criteria::create(TestUser::dao())->
					addOrder(
						OrderBy::create('id')
					)
			);
			
			// asc|desc
			$this->assertCriteria(
				'from TestUser order by id asc, nickname desc',
				Criteria::create(TestUser::dao())->
					addOrder(
						OrderChain::create()->
							add(
								OrderBy::create('id')->asc()
							)->
							add(
								OrderBy::create('nickname')->desc()
							)
					)
			);
			
			// placeholder, boolean and arithmetic expressions
			$this->assertCriteria(
				'from TestUser order by $1, nickname is null, -id/2 + 1 asc ',
				Criteria::create(TestUser::dao())->
					addOrder(
						OrderChain::create()->
							add(
								OrderBy::create(SQLFunction::create('rand'))
							)->
							add(
								OrderBy::create(
									Expression::isNull('nickname')
								)
							)->
							add(
								OrderBy::create(
									Expression::add(
										Expression::div(
											Expression::minus('id'),
											2
										),
										1
									)
								)->
								asc()
							)
					),
				array(1 => SQLFunction::create('rand'))
			);
		}
		
		public function testHaving()
		{
			// arithmetic expression
			$this->assertCriteria(
				'from TestUser having (2 + -id --1) / 2 = id',
				Criteria::create(TestUser::dao())->
					setProjection(
						Projection::having(
							Expression::eq(
								Expression::div(
									Expression::add(
										2,
										Expression::sub(
											Expression::minus('id'),
											-1
										)
									),
									2
								),
								'id'
							)
						)
					)
			);
			
			// placeholder
			$this->assertCriteria(
				'from TestUser having $1 = 1',
				Criteria::create(TestUser::dao())->
					setProjection(
						Projection::having(
							Expression::eq(
								SQLFunction::create('count', 'id'),
								1
							)
						)
					),
				array(1 => SQLFunction::create('count', 'id'))
			);
		}
		
		public function testLimitOffset()
		{
			$this->assertCriteria(
				'from TestUser limit 10',
				Criteria::create(TestUser::dao())->
					setLimit(10)
			);
			
			$this->assertCriteria(
				'from TestUser limit $1',
				Criteria::create(TestUser::dao())->
					setLimit(31),
				array(1 => 31)
			);
			
			$this->assertCriteria(
				'from TestUser limit 10 offset 0',
				Criteria::create(TestUser::dao())->
					setLimit(10)->
					setOffset(0)
			);
			
			$this->assertCriteria(
				'from TestUser limit $1 offset $2',
				Criteria::create(TestUser::dao())->
					setLimit(10)->
					setOffset(31),
				array(1 => 10, 2 => 31)
			);
		}
		
		public function testBind()
		{
			$user = TestUser::create()->setId(1);
			
			$bindingsList = array(
				// number
				array(1 => 1.123),
				// signed number
				array(1 => -1),
				// string
				array(1 => 'test'),
				// Identifiable object
				array(1 => $user),
				// DialectString object
				array(1 => SQLFunction::create('rand'))
			);
			
			foreach ($bindingsList as $bindings) {
				$value = $bindings[1];
				if ($value instanceof Identifiable)
					$value = $value->getId();
				
				// in property list
				$this->assertCriteria(
					'$1 from TestUser',
					Criteria::create(TestUser::dao())->
						setProjection(
							Projection::property($value)
						),
					$bindings
				);
				
				// in aggregate function
				$this->assertCriteria(
					'count($1) from TestUser',
					Criteria::create(TestUser::dao())->
						setProjection(
							Projection::count($value)
						),
					$bindings
				);
				
				// in where expression
				$this->assertCriteria(
					'from TestUser where id = $1',
					Criteria::create(TestUser::dao())->
						add(
							Expression::eq('id', $value)
						),
					$bindings
				);
				
				// in 'in' expression
				if (is_scalar($value)) {
					$this->assertCriteria(
						'from TestUser where id in (1, $1)',
						Criteria::create(TestUser::dao())->
							add(
								Expression::in('id', array(1, $value))
							),
						$bindings
					);
				}
				
				// in order by expression
				$this->assertCriteria(
					'from TestUser order by $1',
					Criteria::create(TestUser::dao())->
						addOrder(
							OrderBy::create($value)
						),
					$bindings
				);
				
				// in having expression
				$this->assertCriteria(
					'from TestUser having id = $1',
					Criteria::create(TestUser::dao())->
						setProjection(
							Projection::having(
								Expression::eq('id', $value)
							)
						),
					$bindings
				);
					
				if (is_integer($bindings[1]) && $bindings[1] >= 0) {
					// in limit expression
					$this->assertCriteria(
						'from TestUser limit $1',
						Criteria::create(TestUser::dao())->
							setLimit($value),
						$bindings
					);
					
					// in offset expression
					$this->assertCriteria(
						'from TestUser offset $1',
						Criteria::create(TestUser::dao())->
							setOffset($value),
						$bindings
					);
				}
			}
		}
		
		public function testBindNext()
		{
			$query = OQL::select('from TestCity where foo = $1 and $2 = $3');
			$this->assertEquals(
				$query->
					bind(1, 'bar')->
					bind(2, 'foo')->
					bind(3, 'boo'),
				
				$query->
					bindNext('bar')->
					bindNext('foo')->
					bindNext('boo')
			);
		}
		
		public function testQuery()
		{
			$criteria = Criteria::create(TestUser::dao())->
				setProjection(
					Projection::property('id')
				)->
				add(
					Expression::isTrue('id')
				);
			
			// property and where
			$this->assertCriteria(
				'id from TestUser where id is true',
				$criteria
			);
			
			// property, where and order by
			$this->assertCriteria(
				'id from TestUser where id is true order by id asc',
				$criteria->
					addOrder(
						OrderBy::create('id')->asc()
					)
			);
			
			// property, where, order by, limit, offset
			$this->assertCriteria(
				'id from TestUser where id is true order by id asc limit 10 offset 1',
				$criteria->
					setLimit(10)->
					setOffset(1)
			);
			
			// property, where, order by, limit, offset, group by
			$this->assertCriteria(
				'id from TestUser where id is true group by id order by id asc limit 10 offset 1',
				$criteria->
					setProjection(
						Projection::chain()->
							add(Projection::property('id'))->
							add(Projection::group('id'))
					)
			);
			
			// property, where, order by, limit, offset, group by, having
			$this->assertCriteria(
				'id from TestUser where id is true group by id order by id asc having id = 1 limit 10 offset 1',
				$criteria->
					setProjection(
						Projection::chain()->
							add(Projection::property('id'))->
							add(Projection::group('id'))->
							add(
								Projection::having(
									Expression::eq('id', 1)
								)
							)
					)
			);
			
			// property, group by, having
			$this->assertCriteria(
				'count(id) as count from TestUser group by id having count = 2',
				Criteria::create(TestUser::dao())->
					setProjection(
						Projection::chain()->
							add(Projection::count('id', 'count'))->
							add(Projection::group('id'))->
							add(
								Projection::having(
									Expression::eq('count', 2)
								)
							)
					)
			);
		}
		
		public function testSyntaxError()
		{
			// FIXME
			/*
			$this->assertSyntaxError(
				'',
				"expecting 'from' clause"
			);
			
			$this->assertSyntaxError(
				'count) from',
				"unexpected ')'"
			);
			
			$this->assertSyntaxError(
				'count( from',
				"expecting ')' in function call: count"
			);
			
			$this->assertSyntaxError(
				'count() from',
				"expecting first argument in expression: )"
			);
			
			$this->assertSyntaxError(
				'prop1 as 123',
				'expecting alias name: 123'
			);
			
			$this->assertSyntaxError(
				'from 123',
				'invalid class name: 123'
			);
			
			$this->assertSyntaxError(
				'from OQL',
				'class must implement DAOConnected interface: OQL'
			);
			
			$this->assertSyntaxError(
				'from TestUser order by having where',
				'unexpected: where'
			);
			
			$this->assertSyntaxError(
				'from TestUser where',
				'expecting first argument in expression: =|!='
			);
			
			$this->assertSyntaxError(
				'from TestUser where 1 + ',
				'expecting first argument in expression: *|/'
			);
			
			$this->assertSyntaxError(
				'from TestUser where and id = 1',
				"expecting 'where' expression"
			);
			
			$this->assertSyntaxError(
				'from TestUser where ((e=1)',
				"expecting ')' in expression"
			);
			
			$this->assertSyntaxError(
				'from TestUser where ((e=1)))',
				"unexpected ')'"
			);
			
			$this->assertSyntaxError(
				'from TestUser where a is',
				"expecting 'null', 'not null', 'true' or 'false'"
			);
			
			$this->assertSyntaxError(
				'from TestUser where a in (',
				'expecting constant or placeholder in expression: in'
			);
			
			$this->assertSyntaxError(
				'from TestUser where a in (1',
				"expecting ')' in expression: in"
			);
			
			$this->assertSyntaxError(
				'from TestUser where a not',
				'expecting in, like, ilike or similar to'
			);
			
			$this->assertSyntaxError(
				'from TestUser where a like 123',
				'expecting string constant or placeholder: like'
			);
			
			$this->assertSyntaxError(
				'from TestUser where a between',
				'expecting first argument in expression: between'
			);
			
			$this->assertSyntaxError(
				'from TestUser where a between 1',
				"expecting 'and' in expression: between"
			);
			
			$this->assertSyntaxError(
				'from TestUser where a between 1 and',
				'expecting second argument in expression: between'
			);
			
			$this->assertSyntaxError(
				'from TestUser limit',
				"expecting 'limit' expression"
			);
			
			$this->assertSyntaxError(
				'from TestUser limit prop',
				"expecting 'limit' expression"
			);
			
			$this->assertSyntaxError(
				'from TestUser limit offset',
				"expecting 'limit' expression"
			);
			
			$this->assertSyntaxError(
				'from TestUser offset',
				"expecting 'offset' expression"
			);
			
			$this->assertSyntaxError(
				'from TestUser offset prop',
				"expecting 'offset' expression"
			);
			*/
		}
		
		/**
		 * @return OqlSelectTest
		**/
		private function assertCriteria($query, Criteria $criteria, $bindings = null)
		{
			$query = OQL::select($query);
			
			if (is_array($bindings))
				$query->bindAll($bindings);
			
			$dialect = PostgresDialect::me();
			
			$this->assertEquals(
				$query->toCriteria()->toDialectString($dialect),
				$criteria->toDialectString($dialect)
			);
			
			return $this;
		}
		
		/**
		 * @return OqlSelectTest
		**/
		private function assertSyntaxError($query, $message)
		{
			try {
				OQL::select($query);
				
			} catch (SyntaxErrorException $e) {
				$this->assertEquals($e->getMessage(), $message);
			}
			
			return $this;
		}
	}
?>