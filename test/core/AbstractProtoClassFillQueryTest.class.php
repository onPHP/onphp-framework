<?php
	namespace Onphp\Test;

	final class AbstractProtoClassFillQueryTest extends TestCase
	{
		public function testFullInsertQueryByCity()
		{
			$city = $this->spawnCity();
			
			$insertCity = $city->proto()->fillQuery(\Onphp\OSQL::insert(), $city);
			$this->assertEquals(
				'INSERT INTO  (id, name, capital, large) VALUES (20, Saint-Peterburg, TRUE, TRUE)',
				$insertCity->toDialectString(\Onphp\ImaginaryDialect::me())
			);
		}
		
		public function testFullUpdateQueryByUser()
		{
			$city = $this->spawnCity();
			$user = $this->spawnUser(array('city' => $city));
			$updateUser = $user->proto()->fillQuery(\Onphp\OSQL::update(), $user);
			$this->assertEquals(
				'UPDATE  SET id = 77, nickname = NULL, password = NULL, '
					.'very_custom_field_name = 2011-12-31 00:00:00, '
					.'registered = 2011-12-30 00:00:00, strange_time = 01:23:45, '
					.'city_id = 20, first_optional_id = NULL, second_optional_id = NULL, '
					.'url = https://www.github.com, '
					.'properties = "a"=>"apple","b"=>"bananas",, ip = 127.0.0.1',
				$updateUser->toDialectString(\Onphp\ImaginaryDialect::me())
			);
		}
		
		public function testFullUpdateQueryByUserWithContactExt()
		{
			$contactValue = $this->spawnContactValueExt();
			$user = $this->spawnUserWithContactExt(array('contactExt' => $contactValue));
			
			$updateUser = $user->proto()->fillQuery(\Onphp\OSQL::update(), $user);
			$this->assertEquals(
				'UPDATE  SET id = 77, name = Aleksey, surname = Alekseev, email = foo@bar.com, '
					.'icq = 12345678, phone = 89012345678, city_id = NULL, '
					.'web = https://www.github.com/, skype = github',
				$updateUser->toDialectString(\Onphp\ImaginaryDialect::me())
			);
		}
		
		public function testUpdateQueryByCityOneBoolean()
		{
			$cityOld = $this->spawnCity(array('capital' => false));
			$city = $this->spawnCity(array('capital' => true)); //1918
			
			$updateCity = $city->proto()->fillQuery(\Onphp\OSQL::update(), $city, $cityOld);
			$this->assertEquals(
				'UPDATE  SET capital = TRUE',
				$updateCity->toDialectString(\Onphp\ImaginaryDialect::me())
			);
		}
		
		public function testUpdateQueryByCityOneString()
		{
			$cityOld = $this->spawnCity(array('name' => 'Leningrad'));
			$city = $this->spawnCity(array('name' => 'Saint-Peterburg'));
			
			$updateCity = $city->proto()->fillQuery(\Onphp\OSQL::update(), $city, $cityOld);
			$this->assertEquals(
				'UPDATE  SET name = Saint-Peterburg',
				$updateCity->toDialectString(\Onphp\ImaginaryDialect::me())
			);
		}
		
		public function testUpdateQueryByUserTimesAndUrl()
		{
			$oldUser = $this->spawnUser(array(
				'strangeTime' => \Onphp\Time::create('12:12:12'),
				'url' => \Onphp\HttpUrl::create()->parse('http://code.google.com/'),
			));
			$user = $this->spawnUser(array('lastLogin' => \Onphp\Timestamp::create('2012-01-01')));
			
			$updateUser = $user->proto()->fillQuery(\Onphp\OSQL::update(), $user, $oldUser);
			$this->assertEquals(
				'UPDATE  SET very_custom_field_name = 2012-01-01 00:00:00, '
					.'strange_time = 01:23:45, url = https://www.github.com',
				$updateUser->toDialectString(\Onphp\ImaginaryDialect::me())
			);
		}
		
		public function testUpdateQueryByUserCitiesAndHstore()
		{
			$moscow = $this->spawnCity(array('id' => 1, 'name' => 'Moscow'));
			$piter = $this->spawnCity(array('id' => 2, 'name' => 'Saint-Peterburg'));
			$omsk = $this->spawnCity(array('id' => 3, 'name' => 'Omsk'));
			
			$userParams = array(
				'city' => $moscow,
				'firstOptional' => $piter,
				'secondOptional' => null,
				'properties' => \Onphp\Hstore::make(array()),
			);
			$oldUser = $this->spawnUser($userParams);
			$userParams = array(
				'city' => $piter,
				'firstOptional' => null,
				'secondOptional' => $omsk,
				'properties' => \Onphp\Hstore::make(array('param' => 'value')),
			);
			$user = $this->spawnUser($userParams);
			
			$updateUser = $user->proto()->fillQuery(\Onphp\OSQL::update(), $user, $oldUser);
			$this->assertEquals(
				'UPDATE  SET city_id = 2, first_optional_id = NULL, '
					.'second_optional_id = 3, properties = "param"=>"value",',
				$updateUser->toDialectString(\Onphp\ImaginaryDialect::me())
			);
		}
		
		public function testUpdateQueryByUserWithValueObject()
		{
			$moscow = $this->spawnCity();
			$oldContactExt = $this->spawnContactValueExt(array('email' => 'bar@foo.com'));
			$oldUser = $this->spawnUserWithContactExt(array('contactExt' => $oldContactExt));
			$contactExt = $this->spawnContactValueExt(array('city' => $moscow));
			$user = $this->spawnUserWithContactExt(array('contactExt' => $contactExt));
			
			$updateUser = $user->proto()->fillQuery(\Onphp\OSQL::update(), $user, $oldUser);
			$this->assertEquals(
				'UPDATE  SET email = foo@bar.com, city_id = 20',
				$updateUser->toDialectString(\Onphp\ImaginaryDialect::me())
			);
		}
		
		public function testUpdateQueryByUserWithSameValueObject()
		{
			//if value object same for both main objects - we'll update all fields from value object
			$contactExt = $this->spawnContactValueExt();
			$oldUser = $this->spawnUserWithContactExt(array('contactExt' => $contactExt));
			$user = $this->spawnUserWithContactExt(array('contactExt' => $contactExt));
			
			$updateUser = $user->proto()->fillQuery(\Onphp\OSQL::update(), $user, $oldUser);
			$this->assertEquals(
				'UPDATE  SET email = foo@bar.com, icq = 12345678, '
					.'phone = 89012345678, city_id = NULL, '
					.'web = https://www.github.com/, skype = github',
				$updateUser->toDialectString(\Onphp\ImaginaryDialect::me())
			);
		}
		
		/**
		 * @return \Onphp\Test\TestCity
		 */
		private function spawnCity($options = array())
		{
			$options += array(
				'capital' => true,
				'large' => true,
				'name' => 'Saint-Peterburg',
				'id' => 20,
			);
			
			return $this->spawnObject(TestCity::create(), $options);
		}
		
		/**
		 * @return \Onphp\Test\TestUser
		 */
		private function spawnUser($options = array())
		{
			$options += array(
				'id' => '77',
				'credentials' => Credentials::create(),
				'lastLogin' => \Onphp\Timestamp::create('2011-12-31'),
				'registered' => \Onphp\Timestamp::create('2011-12-30'),
				'strangeTime' => \Onphp\Time::create('01:23:45'),
				'city' => null,
				'firstOptional' => null,
				'secondOptional' => null,
				'url' => \Onphp\HttpUrl::create()->parse('https://www.github.com'),
				'properties' => \Onphp\Hstore::make(array('a' => 'apple', 'b' => 'bananas')),
				'ip' => \Onphp\IpAddress::create('127.0.0.1'),
			);
			
			return $this->spawnObject(TestUser::create(), $options);
					
		}
		
		/**
		 * @return \Onphp\Test\TestContactValueExtended
		 */
		private function spawnContactValueExt($options = array())
		{
			$options += array(
				'web' => 'https://www.github.com/',
				'skype' => 'github',
				'email' => 'foo@bar.com',
				'icq' => 12345678,
				'phone' => '89012345678',
				'city' => null,
			);
			
			return $this->spawnObject(TestContactValueExtended::create(), $options);
		}
		
		/**
		 * @return \Onphp\Test\TestUserWithContactExtended
		 */
		private function spawnUserWithContactExt($options = array())
		{
			$options += array(
				'id' => '77',
				'name' => 'Aleksey',
				'surname' => 'Alekseev',
				'contactExt' => null,
			);
			
			return $this->spawnObject(TestUserWithContactExtended::create(), $options);
		}
		
		private function spawnObject(\Onphp\Prototyped $object, array $options)
		{
			foreach ($object->proto()->getPropertyList() as $propName => $property) {
				/* @var $property \Onphp\LightMetaProperty */
				if (isset($options[$propName])) {
					$setter = $property->getSetter();
					$object->{$setter}($options[$propName]);
				}
			}
			return $object;
		}
	}
?>