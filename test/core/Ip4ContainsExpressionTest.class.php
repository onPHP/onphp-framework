<?php
	
	final class Ip4ContainsExpressionTest extends TestCase
	{
		public function testToDialect()
		{
			$expression =
				Expression::containsIp(
					IpRange::create('127.0.0.1-127.0.0.5'),
					IpAddress::create('127.0.0.3')
				);
			
			$this->assertEquals(
				"'127.0.0.3' <<= '127.0.0.1-127.0.0.5'",
				$expression->toDialectString(PostgresDialect::me())
			);
			
			$expression =
				Expression::containsIp(
					DBField::create('range'),
					'192.168.1.1'
				);
			$this->assertEquals(
				'\'192.168.1.1\' <<= "range"',
				$expression->toDialectString(PostgresDialect::me())	
			);
			
		}
		
		public function testWithObjects()
		{
			$criteria =
				Criteria::create(TestUser::dao())->
				add(
					Expression::containsIp(
						IpRange::create('192.168.1.1-192.168.1.255'), 'ip')
				)->
				addProjection(Projection::property('id'));
			
			$this->assertEquals(
				$criteria->toDialectString(PostgresDialect::me()),
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
				$criteria->toDialectString(PostgresDialect::me()),
				'SELECT "test_internet_provider"."id" FROM "test_internet_provider" WHERE \'42.42.42.42\' <<= "test_internet_provider"."range"'
						
			);
		}
	}
?>