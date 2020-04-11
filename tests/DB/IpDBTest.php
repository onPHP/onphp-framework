<?php

namespace OnPHP\Tests\DB;

use OnPHP\Core\Base\Timestamp;
use OnPHP\Core\DB\DBPool;
use OnPHP\Core\DB\PgSQL;
use OnPHP\Core\Logic\Expression;
use OnPHP\Core\OSQL\DBField;
use OnPHP\Core\OSQL\OSQL;
use OnPHP\Main\Criteria\Criteria;
use OnPHP\Main\Criteria\Projection;
use OnPHP\Main\Net\Ip\IpAddress;
use OnPHP\Main\Net\Ip\IpRange;
use OnPHP\Tests\Meta\Business\Credentials;
use OnPHP\Tests\Meta\Business\TestCity;
use OnPHP\Tests\Meta\Business\TestInternetProvider;
use OnPHP\Tests\Meta\Business\TestUser;
use OnPHP\Tests\TestEnvironment\DBTestPool;
use OnPHP\Tests\TestEnvironment\TestCaseDAO;

/**
 * @group ipdb
 */
class IpDBTest extends TestCaseDAO
{
	public function testToDialect()
	{
		$dialect = $this->getDbByType(PgSQL::class)->getDialect();
		
		$expression =
			Expression::containsIp(
				IpRange::create('127.0.0.1-127.0.0.5'),
				IpAddress::create('127.0.0.3')
			);
		
		$this->assertEquals(
			"'127.0.0.3' <<= '127.0.0.1-127.0.0.5'",
			$expression->toDialectString($dialect)
		);
		
		$expression =
			Expression::containsIp(
				DBField::create('range'),
				'192.168.1.1'
			);
		$this->assertEquals(
			'\'192.168.1.1\' <<= "range"',
			$expression->toDialectString($dialect)	
		);
	}
	
	public function testWithObjects()
	{
		$dialect = $this->getDbByType(PgSQL::class)->getDialect();
		
		$criteria =
			Criteria::create(TestUser::dao())->
			add(
				Expression::containsIp(
					IpRange::create('192.168.1.1-192.168.1.255'), 'ip')
			)->
			addProjection(Projection::property('id'));
		
		$this->assertEquals(
			$criteria->toDialectString($dialect),
			'SELECT "test_user"."id" FROM "test_user" WHERE "test_user"."ip" <<= \'192.168.1.1-192.168.1.255\''
		);
		
		$criteria =
			Criteria::create(TestInternetProvider::dao())->
			add(
				Expression::containsIp(
					'range',
					IpAddress::create('42.42.42.42')
				)
			)->addProjection(Projection::property('id'));
		
		$this->assertEquals(
			$criteria->toDialectString($dialect),
			'SELECT "test_internet_provider"."id" FROM "test_internet_provider" WHERE \'42.42.42.42\' <<= "test_internet_provider"."range"'
					
		);
	}
	
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