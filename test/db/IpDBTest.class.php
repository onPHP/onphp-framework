<?php
	/**
	 * @group ipdb
	 */
	class IpDBTest extends TestCaseDAO
	{
		
		public function testIpAddressProperty()
		{
			foreach (DBTestPool::me()->getPool() as $db) {
				DBPool::me()->setDefault($db);
				
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
						setLastLogin(Timestamp::makeNow())->
						setRegistered(Timestamp::makeNow())->
						setCity($city)->
						setIp(IpAddress::create('127.0.0.1'));

				TestUser::dao()->add($userWithIp);

				$this->assertTrue($userWithIp->getId() >= 1);

				$this->assertTrue($userWithIp->getIp() instanceof IpAddress);

				$plainIp =
					DBPool::me()->getByDao(TestUser::dao())->
					queryColumn(
						OSQL::select()->get('ip')->
						from(TestUser::dao()->getTable())->
						where(Expression::eq('id', $userWithIp->getId()))
					);

				$this->assertEquals($plainIp[0], $userWithIp->getIp()->toString());

				$count =
					Criteria::create(TestUser::dao())->
					add(Expression::eq('ip', IpAddress::create('127.0.0.1')))->
					addProjection(Projection::count('*', 'count'))->
					getCustom('count');

				$this->assertEquals($count, 1);
			}
		}
		
		public function testIpRangeProperty()
		{
			foreach (DBTestPool::me()->getPool() as $db) {
				DBPool::me()->setDefault($db);
				
				$akado =
					TestInternetProvider::create()->
					setName('Akada')->
					setRange(
						IpRange::create(
							IpAddress::create('192.168.1.1'),
							IpAddress::create('192.168.1.42')
						)
					);

				TestInternetProvider::dao()->
					add($akado);

				$plainRange =
						Criteria::create(TestInternetProvider::dao())->
						addProjection(Projection::property('range'))->
						add(Expression::eq('name', 'Akada'))->
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
						IpRange::create('192.168.2.0/24')
					)
				);

				$list =
					Criteria::create(TestInternetProvider::dao())->
					addOrder('id')->
					getList();

				$this->assertEquals(count($list), 2);
			}
		}
	}
?>