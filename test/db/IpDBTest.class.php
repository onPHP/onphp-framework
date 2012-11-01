<?php
	/**
	 * @group ipdb
	 */
	namespace Onphp\Test;

	class IpDBTest extends TestCaseDAO
	{
		public function testToDialect()
		{
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			$expression =
				\Onphp\Expression::containsIp(
					\Onphp\IpRange::create('127.0.0.1-127.0.0.5'),
					\Onphp\IpAddress::create('127.0.0.3')
				);
			
			$this->assertEquals(
				"'127.0.0.3' <<= '127.0.0.1-127.0.0.5'",
				$expression->toDialectString($dialect)
			);
			
			$expression =
				\Onphp\Expression::containsIp(
					\Onphp\DBField::create('range'),
					'192.168.1.1'
				);
			$this->assertEquals(
				'\'192.168.1.1\' <<= "range"',
				$expression->toDialectString($dialect)	
			);
		}
		
		public function testWithObjects()
		{
			$dialect = $this->getDbByType('\Onphp\PgSQL')->getDialect();
			
			$criteria =
				\Onphp\Criteria::create(TestUser::dao())->
				add(
					\Onphp\Expression::containsIp(
						\Onphp\IpRange::create('192.168.1.1-192.168.1.255'), 'ip')
				)->
				addProjection(\Onphp\Projection::property('id'));
			
			$this->assertEquals(
				$criteria->toDialectString($dialect),
				'SELECT "test_user"."id" FROM "test_user" WHERE "test_user"."ip" <<= \'192.168.1.1-192.168.1.255\''
			);
			
			$criteria =
				\Onphp\Criteria::create(TestInternetProvider::dao())->
				add(
					\Onphp\Expression::containsIp(
						'range',
						\Onphp\IpAddress::create('42.42.42.42')
					)
				)->addProjection(\Onphp\Projection::property('id'));
			
			$this->assertEquals(
				$criteria->toDialectString($dialect),
				'SELECT "test_internet_provider"."id" FROM "test_internet_provider" WHERE \'42.42.42.42\' <<= "test_internet_provider"."range"'
						
			);
		}
		
		public function testIpAddressProperty()
		{
			foreach (DBTestPool::me()->getPool() as $db) {
				\Onphp\DBPool::me()->setDefault($db);
				
				$city =
					TestCity::create()->
					setName('Khimki');

				TestCity::dao()->add($city);

				$userWithIp =
					TestUser::create()->
						setCredentials(
							Credentials::create()->
							setNickName('postgreser')->
							setPassword(sha1('postgreser'))
						)->
						setLastLogin(\Onphp\Timestamp::makeNow())->
						setRegistered(\Onphp\Timestamp::makeNow())->
						setCity($city)->
						setIp(\Onphp\IpAddress::create('127.0.0.1'));

				TestUser::dao()->add($userWithIp);

				$this->assertTrue($userWithIp->getId() >= 1);

				$this->assertTrue($userWithIp->getIp() instanceof \Onphp\IpAddress);

				$plainIp =
					\Onphp\DBPool::me()->getByDao(TestUser::dao())->
					queryColumn(
						\Onphp\OSQL::select()->get('ip')->
						from(TestUser::dao()->getTable())->
						where(\Onphp\Expression::eq('id', $userWithIp->getId()))
					);

				$this->assertEquals($plainIp[0], $userWithIp->getIp()->toString());

				$count =
					\Onphp\Criteria::create(TestUser::dao())->
					add(\Onphp\Expression::eq('ip', \Onphp\IpAddress::create('127.0.0.1')))->
					addProjection(\Onphp\Projection::count('*', 'count'))->
					getCustom('count');

				$this->assertEquals($count, 1);
			}
		}
		
		public function testIpRangeProperty()
		{
			foreach (DBTestPool::me()->getPool() as $db) {
				\Onphp\DBPool::me()->setDefault($db);
				
				$akado =
					TestInternetProvider::create()->
					setName('Akada')->
					setRange(
						\Onphp\IpRange::create(
							\Onphp\IpAddress::create('192.168.1.1'),
							\Onphp\IpAddress::create('192.168.1.42')
						)
					);

				TestInternetProvider::dao()->
					add($akado);

				$plainRange =
						\Onphp\Criteria::create(TestInternetProvider::dao())->
						addProjection(\Onphp\Projection::property('range'))->
						add(\Onphp\Expression::eq('name', 'Akada'))->
						getCustom();

				$this->assertEquals(
					$plainRange['range'],
					'192.168.1.1-192.168.1.42'
				);

				TestInternetProvider::dao()->
				add(
					TestInternetProvider::create()->
					setName('DomRu')->
					setRange(
						\Onphp\IpRange::create('192.168.2.0/24')
					)
				);

				$list =
					\Onphp\Criteria::create(TestInternetProvider::dao())->
					addOrder('id')->
					getList();

				$this->assertEquals(count($list), 2);
			}
		}
	}
?>