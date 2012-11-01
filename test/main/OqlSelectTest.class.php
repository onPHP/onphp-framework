<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class OqlSelectTest extends TestCaseDB
	{
		public function testProperty()
		{
			$this->
				// no properties
				assertCriteria(
					'from TestUser',
					\Onphp\Criteria::create(TestUser::dao())
				)->
				// simple property
				assertCriteria(
					'id from TestUser',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(\Onphp\Projection::property('id'))
				)->
				// aggregate functions, distinct, aliases, properties
				assertCriteria(
					'avg(id), count(city.id) as count, '
					.'count(distinct city.id) as distinctCount, '
					.'sum(id), min(id), max(id), city.Name as cityName, '
					.'distinct id, city, min from TestUser',
					\Onphp\Criteria::create(TestUser::dao())->
						setDistinct(true)->
						setProjection(
							\Onphp\Projection::chain()->
								add(\Onphp\Projection::avg('id'))->
								add(\Onphp\Projection::count('city.id', 'count'))->
								add(\Onphp\Projection::distinctCount('city.id', 'distinctCount'))->
								add(\Onphp\Projection::sum('id'))->
								add(\Onphp\Projection::min('id'))->
								add(\Onphp\Projection::max('id'))->
								add(\Onphp\Projection::property('city.Name', 'cityName'))->
								add(\Onphp\Projection::property('id'))->
								add(\Onphp\Projection::property('city'))->
								add(\Onphp\Projection::property('min'))
						)
				)->
				// arithmetic expression in aggregate function
				assertCriteria(
					'avg(-id - -1 / -$1), avg((10-id) * -($1+-2.1)), avg(10) as ten '
					.'from TestUser',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::chain()->
								add(
									\Onphp\Projection::avg(
										\Onphp\Expression::sub(
											\Onphp\Expression::minus('id'),
											\Onphp\Expression::div(-1, -10)
										)
									)
								)->
								add(
									\Onphp\Projection::avg(
										\Onphp\Expression::mul(
											\Onphp\Expression::sub(10, 'id'),
											\Onphp\Expression::minus(
												\Onphp\Expression::add(10, -2.1)
											)
										)
									)
								)->
								add(\Onphp\Projection::avg(10, 'ten'))
						),
					array(1 => 10)
				)->
				// boolean and arithmetic expressions in count function
				assertCriteria(
					'count(distinct -id * 2 / -3 > 10), count(id in (1, -$1, -3) and Name like `test`)'
					.'from TestUser',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::chain()->
								add(
									\Onphp\Projection::distinctCount(
										\Onphp\Expression::gt(
											\Onphp\Expression::div(
												\Onphp\Expression::mul(
													\Onphp\Expression::minus('id'),
													2
												),
												-3
											),
											10
										)
									)
								)->
								add(
									\Onphp\Projection::count(
										\Onphp\Expression::expAnd(
											\Onphp\Expression::in('id', array(1, -2, -3)),
											\Onphp\Expression::like('Name', 'test')
										)
									)
								)
						),
					array(1 => 2)
				)->
				// boolean and arithmetic expressions as property
				assertCriteria(
					'(id > 10) and (20 > id) as inInterval, ((2*id + 1) / id), -id '
					.'from TestUser',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::chain()->
								add(
									\Onphp\Projection::property(
										\Onphp\Expression::expAnd(
											\Onphp\Expression::gt('id', 10),
											\Onphp\Expression::gt(20, 'id')
										),
										'inInterval'
									)
								)->
								add(
									\Onphp\Projection::property(
										\Onphp\Expression::div(
											\Onphp\Expression::add(
												\Onphp\Expression::mul(2, 'id'),
												1
											),
											'id'
										)
									)
								)->
								add(
									\Onphp\Projection::property(
										\Onphp\Expression::minus('id')
									)
								)
						)
				);
		}
		
		public function testWhere()
		{
			$userId = 1;
			$user = TestUser::create()->setId($userId);
			
			$this->
				// bindings, operator chain
				assertCriteria(
					'from TestUser where id = $1 or id = $2 or $2 = id or $1 = $2',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::expOr(
								\Onphp\Expression::expOr(
									\Onphp\Expression::expOr(
										\Onphp\Expression::eqId('id', $user),
										\Onphp\Expression::eq('id', $userId)
									),
									\Onphp\Expression::eq($userId, 'id')
								),
								\Onphp\Expression::eq($userId, $userId)
							)
						),
					array(
						1 => $user,
						2 => $userId
					)
				)->
				// comparison operators
				assertCriteria(
					'from TestUser where id = 1 or id >= 1 or id <= 1 or id <> 1 or id != 1',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::expOr(
								\Onphp\Expression::expOr(
									\Onphp\Expression::expOr(
										\Onphp\Expression::expOr(
											\Onphp\Expression::eq('id', 1),
											\Onphp\Expression::gtEq('id', 1)
										),
										\Onphp\Expression::ltEq('id', 1)
									),
									\Onphp\Expression::notEq('id', 1)
								),
								\Onphp\Expression::notEq('id', 1)
							)
						)
				)->
				// priority
				assertCriteria(
					'from TestUser where id = 1 and Name = "some" '
					.'or Name = "any" or id = 1 > 2 = id * 2 + 1',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::expOr(
								\Onphp\Expression::expOr(
									\Onphp\Expression::expAnd(
										\Onphp\Expression::eq('id', 1),
										\Onphp\Expression::eq('Name', 'some')
									),
									\Onphp\Expression::eq('Name', 'any')
								),
								\Onphp\Expression::gt(
									\Onphp\Expression::eq('id', 1),
									\Onphp\Expression::eq(
										2,
										\Onphp\Expression::add(
											\Onphp\Expression::mul('id', 2),
											1
										)
									)
								)
							)
						)
				)->
				// parentheses priority
				assertCriteria(
					'from TestUser where (id = 1 and (Name = "some" or Name = "any"))',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::expAnd(
								\Onphp\Expression::eq('id', 1),
								\Onphp\Expression::expOr(
									\Onphp\Expression::eq('Name', 'some'),
									\Onphp\Expression::eq('Name', 'any')
								)
							)
						)
				)->
				// parentheses priority
				assertCriteria(
					'from TestUser where ((Name = "some" or Name = "any")) and (id = 1)',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::expAnd(
								\Onphp\Expression::expOr(
									\Onphp\Expression::eq('Name', 'some'),
									\Onphp\Expression::eq('Name', 'any')
								),
								\Onphp\Expression::eq('id', 1)
							)
						)
				)->
				// complex boolean expressions
				assertCriteria(
					'from TestUser where (id = 1) != ((1 = id) = (id >= 2))',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::notEq(
								\Onphp\Expression::eq('id', 1),
								\Onphp\Expression::eq(
									\Onphp\Expression::eq(1, 'id'),
									\Onphp\Expression::gtEq('id', 2)
								)
							)
						)
				)->
				// unary not
				assertCriteria(
					'from TestUser where not (not not id = 1 and not id > 1)',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::not(
								\Onphp\Expression::expAnd(
									\Onphp\Expression::not(
										\Onphp\Expression::not(
											\Onphp\Expression::eq('id', 1)
										)
									),
									\Onphp\Expression::not(
										\Onphp\Expression::gt('id', 1)
									)
								)
							)
						)
				)->
				// is ([not] null|true|false)
				assertCriteria(
					'from TestUser where id is null or id is not null or id is true or id is false',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::expOr(
								\Onphp\Expression::expOr(
									\Onphp\Expression::expOr(
										\Onphp\Expression::isNull('id'),
										\Onphp\Expression::notNull('id')
									),
									\Onphp\Expression::isTrue('id')
								),
								\Onphp\Expression::isFalse('id')
							)
						)
				)->
				// [not] in
				assertCriteria(
					'from TestUser where id in (1) or id not in (1, "2", $1, true)',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::expOr(
								\Onphp\Expression::in('id', array(1)),
								\Onphp\Expression::notIn('id', array(1, '2', true, true))
							)
						),
					array(1 => true)
				)->
				// in subquery
				assertCriteria(
					'from TestUser where id in ($1)',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::in(
								'id',
								\Onphp\Criteria::create(TestUser::dao())->
									setProjection(\Onphp\Projection::property('id'))
							)
						),
					array(
						1 => OQL::select('id from TestUser')->toCriteria()
					)
				)->
				// in array
				assertCriteria(
					'from TestUser where id in ($1)',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::in(
								'id',
								array(1, 2)
							)
						),
					array(
						1 => array(1, 2)
					)
				)->
				// [not] (like|ilike|similar to)
				assertCriteria(
					'from TestUser where id like $1 or id not like "Ы%" '
					.'or id ilike $2 or id not ilike "ы%" '
					.'or Name similar to "s" or Name not similar to $3',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::expOr(
								\Onphp\Expression::expOr(
									\Onphp\Expression::expOr(
										\Onphp\Expression::expOr(
											\Onphp\Expression::expOr(
												\Onphp\Expression::like('id', 'ы'),
												\Onphp\Expression::notLike('id', 'Ы%')
											),
											\Onphp\Expression::ilike('id', 'Ы')
										),
										\Onphp\Expression::notIlike('id', 'ы%')
									),
									\Onphp\Expression::similar('Name', 's')
								),
								\Onphp\Expression::notSimilar('Name', 'S')
							)
						),
					array(
						1 => 'ы',
						2 => 'Ы',
						3 => 'S'
					)
				)->
				// between
				assertCriteria(
					'from TestUser where created between "2008-08-06 10:00" and $1 '
					.'or id between id and 10',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::expOr(
								\Onphp\Expression::between(
									'created',
									'2008-08-06 10:00',
									\Onphp\SQLFunction::create('now')
								),
								\Onphp\Expression::between('id', 'id', 10)
							)
						),
					array(1 => \Onphp\SQLFunction::create('now'))
				)->
				// arithmetic expression
				assertCriteria(
					'from TestUser where (2 + -id --1) / 2 = id',
					\Onphp\Criteria::create(TestUser::dao())->
						add(
							\Onphp\Expression::eq(
								\Onphp\Expression::div(
									\Onphp\Expression::sub(
										\Onphp\Expression::add(
											2,
											\Onphp\Expression::minus('id')
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
			$this->
				assertCriteria(
					'from TestUser group by id',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::group('id')
						)
				)->
				assertCriteria(
					'from TestUser group by id, nickname',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::chain()->
								add(\Onphp\Projection::group('id'))->
								add(\Onphp\Projection::group('nickname'))
						)
				)->
				assertCriteria(
					'from TestUser group by id + 1, id / 2',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::chain()->
								add(
									\Onphp\Projection::group(
										\Onphp\Expression::add('id', 1)
									)
								)->
								add(
									\Onphp\Projection::group(
										\Onphp\Expression::div('id', 2)
									)
								)
						)
				)->
				assertCriteria(
					'from TestUser group by id > (1 + id) / 2',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::group(
								\Onphp\Expression::gt(
									'id',
									\Onphp\Expression::div(
										\Onphp\Expression::add(1, 'id'),
										2
									)
								)
							)
						)
				)->
				assertCriteria(
					'from TestUser group by $1, $2 - $3',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::chain()->
								add(\Onphp\Projection::group('id'))->
								add(
									\Onphp\Projection::group(
										\Onphp\Expression::sub(
											\Onphp\SQLFunction::create('rand'),
											10
										)
									)
								)
						),
					array(
						1 => 'id',
						2 => \Onphp\SQLFunction::create('rand'),
						3 => 10 
					)
				);
		}
		
		public function testOrderBy()
		{
			$this->
				// property
				assertCriteria(
					'from TestUser order by id',
					\Onphp\Criteria::create(TestUser::dao())->
						addOrder(
							\Onphp\OrderBy::create('id')
						)
				)->
				// asc|desc
				assertCriteria(
					'from TestUser order by id asc, nickname desc',
					\Onphp\Criteria::create(TestUser::dao())->
						addOrder(
							\Onphp\OrderChain::create()->
								add(
									\Onphp\OrderBy::create('id')->asc()
								)->
								add(
									\Onphp\OrderBy::create('nickname')->desc()
								)
						)
				)->
				// substitution, boolean and arithmetic expressions
				assertCriteria(
					'from TestUser order by $1, nickname is null, -id/2 + 1 asc ',
					\Onphp\Criteria::create(TestUser::dao())->
						addOrder(
							\Onphp\OrderChain::create()->
								add(
									\Onphp\OrderBy::create(\Onphp\SQLFunction::create('rand'))
								)->
								add(
									\Onphp\OrderBy::create(
										\Onphp\Expression::isNull('nickname')
									)
								)->
								add(
									\Onphp\OrderBy::create(
										\Onphp\Expression::add(
											\Onphp\Expression::div(
												\Onphp\Expression::minus('id'),
												2
											),
											1
										)
									)->
									asc()
								)
						),
					array(1 => \Onphp\SQLFunction::create('rand'))
				);
		}
		
		public function testHaving()
		{
			$this->
				// arithmetic expression
				assertCriteria(
					'from TestUser having (2 + -id --1) / 2 = id',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::having(
								\Onphp\Expression::eq(
									\Onphp\Expression::div(
										\Onphp\Expression::sub(
											\Onphp\Expression::add(
												2,
												\Onphp\Expression::minus('id')
											),
											-1
										),
										2
									),
									'id'
								)
							)
						),
					array(1 => 'id')
				)->
				// complex boolean expressions
				assertCriteria(
					'from TestUser having (id = 1) != ((1 = id) = (id >= 2))',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::having(
								\Onphp\Expression::notEq(
									\Onphp\Expression::eq('id', 1),
									\Onphp\Expression::eq(
										\Onphp\Expression::eq(1, 'id'),
										\Onphp\Expression::gtEq('id', 2)
									)
								)
							)
						)
				)->
				// substitution
				assertCriteria(
					'from TestUser having $1 = 1',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::having(
								\Onphp\Expression::eq(
									\Onphp\SQLFunction::create('count', 'id'),
									1
								)
							)
						),
					array(1 => \Onphp\SQLFunction::create('count', 'id'))
				);
		}
		
		public function testLimitOffset()
		{
			$this->
				assertCriteria(
					'from TestUser limit 10',
					\Onphp\Criteria::create(TestUser::dao())->
						setLimit(10)
				)->
				assertCriteria(
					'from TestUser limit $1',
					\Onphp\Criteria::create(TestUser::dao())->
						setLimit(31),
					array(1 => 31)
				)->
				assertCriteria(
					'from TestUser limit 10 offset 0',
					\Onphp\Criteria::create(TestUser::dao())->
						setLimit(10)->
						setOffset(0)
				)->
				assertCriteria(
					'from TestUser limit $1 offset $2',
					\Onphp\Criteria::create(TestUser::dao())->
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
				array(1 => \Onphp\SQLFunction::create('rand'))
			);
			
			foreach ($bindingsList as $bindings) {
				$value = $bindings[1];
				if ($value instanceof \Onphp\Identifiable)
					$value = $value->getId();
				
				$this->
					// in property list
					assertCriteria(
						'$1 from TestUser',
						\Onphp\Criteria::create(TestUser::dao())->
							setProjection(
								\Onphp\Projection::property($value)
							),
						$bindings
					)->
					// in aggregate function
					assertCriteria(
						'count($1) from TestUser',
						\Onphp\Criteria::create(TestUser::dao())->
							setProjection(
								\Onphp\Projection::count($value)
							),
						$bindings
					)->
					// in where expression
					assertCriteria(
						'from TestUser where id = $1',
						\Onphp\Criteria::create(TestUser::dao())->
							add(
								\Onphp\Expression::eq('id', $value)
							),
						$bindings
					);
				
				// in 'in' expression
				if (is_scalar($value)) {
					$this->assertCriteria(
						'from TestUser where id in (1, $1)',
						\Onphp\Criteria::create(TestUser::dao())->
							add(
								\Onphp\Expression::in('id', array(1, $value))
							),
						$bindings
					);
				}
				
				$this->
					// in order by expression
					assertCriteria(
						'from TestUser order by $1',
						\Onphp\Criteria::create(TestUser::dao())->
							addOrder(
								\Onphp\OrderBy::create($value)
							),
						$bindings
					)->
					// in having expression
					assertCriteria(
						'from TestUser having id = $1',
						\Onphp\Criteria::create(TestUser::dao())->
							setProjection(
								\Onphp\Projection::having(
									\Onphp\Expression::eq('id', $value)
								)
							),
						$bindings
					)->
					// in group by expression
					assertCriteria(
						'from TestUser group by id = $1',
						\Onphp\Criteria::create(TestUser::dao())->
							setProjection(
								\Onphp\Projection::group(
									\Onphp\Expression::eq('id', $value)
								)
							),
						$bindings
					);
					
				if (is_integer($value) && $value >= 0)
					$this->
						// in limit expression
						assertCriteria(
							'from TestUser limit $1',
							\Onphp\Criteria::create(TestUser::dao())->
								setLimit($value),
							$bindings
						)->
						// in offset expression
						assertCriteria(
							'from TestUser offset $1',
							\Onphp\Criteria::create(TestUser::dao())->
								setOffset($value),
							$bindings
						);
			}
		}
		
		public function testBindNext()
		{
			$this->assertEquals(
				OQL::select('from TestCity where foo = $1 and $2 = $3')->
				bind(1, 'bar')->
				bind(2, 'foo')->
				bind(3, 'boo'),
				
				OQL::select('from TestCity where foo = $1 and $2 = $3')->
				bindNext('bar')->
				bindNext('foo')->
				bindNext('boo')
			);
		}
		
		public function testQuery()
		{
			$criteria = \Onphp\Criteria::create(TestUser::dao())->
				setProjection(
					\Onphp\Projection::property('id')
				)->
				add(
					\Onphp\Expression::isTrue('id')
				);
			
			$this->
				// property and where
				assertCriteria(
					'id from TestUser where id is true',
					$criteria
				)->
				// property, where and order by
				assertCriteria(
					'id from TestUser where id is true order by id asc',
					$criteria->
						addOrder(
							\Onphp\OrderBy::create('id')->asc()
						)
				)->
				// property, where, order by, limit, offset
				assertCriteria(
					'id from TestUser where id is true order by id asc limit 10 offset 1',
					$criteria->
						setLimit(10)->
						setOffset(1)
				)->
				// property, where, order by, limit, offset, group by
				assertCriteria(
					'id from TestUser where id is true group by id order by id asc limit 10 offset 1',
					$criteria->
						setProjection(
							\Onphp\Projection::chain()->
								add(\Onphp\Projection::property('id'))->
								add(\Onphp\Projection::group('id'))
						)
				)->
				// property, where, order by, limit, offset, group by, having
				assertCriteria(
					'id from TestUser where id is true group by id order by id asc having id = 1 limit 10 offset 1',
					$criteria->
						setProjection(
							\Onphp\Projection::chain()->
								add(\Onphp\Projection::property('id'))->
								add(\Onphp\Projection::group('id'))->
								add(
									\Onphp\Projection::having(
										\Onphp\Expression::eq('id', 1)
									)
								)
						)
				)->
				// property, group by, having
				assertCriteria(
					'count(id) as count from TestUser group by id having count = 2',
					\Onphp\Criteria::create(TestUser::dao())->
						setProjection(
							\Onphp\Projection::chain()->
								add(\Onphp\Projection::count('id', 'count'))->
								add(\Onphp\Projection::group('id'))->
								add(
									\Onphp\Projection::having(
										\Onphp\Expression::eq('count', 2)
									)
								)
						)
				);
		}
		
		public function testSyntaxError()
		{
			$this->
				assertSyntaxError(
					'',
					"expecting 'from' clause"
				)->
				assertSyntaxError(
					'count) from',
					"unexpected ')'"
				)->
				assertSyntaxError(
					'count( from',
					"expecting ')' in function call: count"
				)->
				assertSyntaxError(
					'count() from',
					"expecting first argument in expression: )"
				)->
				assertSyntaxError(
					'prop1 as 123',
					'expecting alias name: 123'
				)->
				assertSyntaxError(
					'from 123',
					'invalid class name: 123'
				)->
				assertSyntaxError(
					'from OQL',
					'class must implement DAOConnected interface: OQL'
				)->
				assertSyntaxError(
					'from TestUser order by having where',
					'unexpected: where'
				)->
				assertSyntaxError(
					'from TestUser where',
					'expecting first argument in expression: =|!='
				)->
				assertSyntaxError(
					'from TestUser where 1 + ',
					'expecting first argument in expression: *|/'
				)->
				assertSyntaxError(
					'from TestUser where and id = 1',
					"expecting 'where' expression"
				)->
				assertSyntaxError(
					'from TestUser where ((e=1)',
					"expecting ')' in expression"
				)->
				assertSyntaxError(
					'from TestUser where ((e=1)))',
					"unexpected ')'"
				)->
				assertSyntaxError(
					'from TestUser where a is',
					"expecting 'null', 'not null', 'true' or 'false'"
				)->
				assertSyntaxError(
					'from TestUser where a in (',
					'expecting constant or substitution in expression: in'
				)->
				assertSyntaxError(
					'from TestUser where a in (1',
					"expecting ')' in expression: in"
				)->
				assertSyntaxError(
					'from TestUser where a not',
					'expecting in, like, ilike or similar to'
				)->
				assertSyntaxError(
					'from TestUser where a like 123',
					'expecting string constant or substitution: like'
				)->
				assertSyntaxError(
					'from TestUser where a between',
					'expecting first argument in expression: between'
				)->
				assertSyntaxError(
					'from TestUser where a between 1',
					"expecting 'and' in expression: between"
				)->
				assertSyntaxError(
					'from TestUser where a between 1 and',
					'expecting second argument in expression: between'
				)->
				assertSyntaxError(
					'from TestUser limit',
					"expecting 'limit' expression"
				)->
				assertSyntaxError(
					'from TestUser limit prop',
					"expecting 'limit' expression"
				)->
				assertSyntaxError(
					'from TestUser limit offset',
					"expecting 'limit' expression"
				)->
				assertSyntaxError(
					'from TestUser offset',
					"expecting 'offset' expression"
				)->
				assertSyntaxError(
					'from TestUser offset prop',
					"expecting 'offset' expression"
				);
		}
		
		/**
		 * @return \Onphp\Test\OqlSelectTest
		**/
		private function assertCriteria($query, \Onphp\Criteria $criteria, $bindings = null)
		{
			$oqlQuery = OQL::select($query);
			
			if (is_array($bindings))
				$oqlQuery->bindAll($bindings);
			
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			$this->assertEquals(
				$oqlQuery->toCriteria()->toDialectString($dialect),
				$criteria->toDialectString($dialect)
			);
			
			return $this;
		}
		
		/**
		 * @return \Onphp\Test\OqlSelectTest
		**/
		private function assertSyntaxError($query, $message)
		{
			try {
				OQL::select($query);
				
			} catch (\Onphp\SyntaxErrorException $e) {
				$this->assertEquals($e->getMessage(), $message);
			}
			
			return $this;
		}
	}
?>