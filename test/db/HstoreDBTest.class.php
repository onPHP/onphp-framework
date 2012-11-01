<?php
	namespace Onphp\Test;

	class HstoreDBTest extends TestCaseDAO
	{
		/**
		 * Install hstore
		 * /usr/share/postgresql/contrib # cat hstore.sql | psql -U pgsql -d onphp
		**/
		public function testHstore()
		{
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				\Onphp\DBPool::me()->setDefault($db);
				$properties = array(
					'age' => '23',
					'weight' => 80,
					'comment' => null,
				);

				$user =
					TestUser::create()->
					setCity(
						$moscow = TestCity::create()->
						setName('Moscow')
					)->
					setCredentials(
						Credentials::create()->
						setNickname('fake')->
						setPassword(sha1('passwd'))
					)->
					setLastLogin(
						\Onphp\Timestamp::create(time())
					)->
					setRegistered(
						\Onphp\Timestamp::create(time())->modify('-1 day')
					)->
					setProperties(\Onphp\Hstore::make($properties));

				$moscow = TestCity::dao()->add($moscow);

				$user = TestUser::dao()->add($user);

				\Onphp\Cache::me()->clean();
				TestUser::dao()->dropIdentityMap();

				$user = TestUser::dao()->getById('1');

				$this->assertInstanceOf('\Onphp\Hstore', $user->getProperties());

				$this->assertEquals(
					$properties,
					$user->getProperties()->getList()
				);

				$form = TestUser::proto()->makeForm();

				$form->get('properties')->
					setFormMapping(
						array(
							\Onphp\Primitive::string('age'),
							\Onphp\Primitive::integer('weight'),
							\Onphp\Primitive::string('comment'),
						)
					);

				$form->import(
					array('id' => $user->getId())
				);

				$this->assertNotNull($form->getValue('id'));

				$object = $user;

				\Onphp\FormUtils::object2form($object, $form);

				$this->assertInstanceOf('\Onphp\Hstore', $form->getValue('properties'));

				$this->assertEquals(
					array_filter($properties),
					$form->getValue('properties')->getList()
				);

				$subform = $form->get('properties')->getInnerForm();

				$this->assertEquals(
					$subform->getValue('age'),
					'23'
				);

				$this->assertEquals(
					$subform->getValue('weight'),
					80
				);

				$this->assertNull(
					$subform->getValue('comment')
				);

				$user = new TestUser();

				\Onphp\FormUtils::form2object($form, $user, false);

				$this->assertEquals(
					$user->getProperties()->getList(),
					array_filter($properties)
				);
			}
		}
	}
?>