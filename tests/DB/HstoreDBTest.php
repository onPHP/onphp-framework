<?php

namespace OnPHP\Tests\DB;

use OnPHP\Core\Base\Timestamp;
use OnPHP\Core\Cache\Cache;
use OnPHP\Core\DB\DBPool;
use OnPHP\Core\Form\FormUtils;
use OnPHP\Core\Form\Primitive;
use OnPHP\Main\Base\Hstore;
use OnPHP\Tests\Meta\Business\Credentials;
use OnPHP\Tests\Meta\Business\TestCity;
use OnPHP\Tests\Meta\Business\TestUser;
use OnPHP\Tests\TestEnvironment\DBTestPool;
use OnPHP\Tests\TestEnvironment\TestCaseDAO;

/**
 * @group core
 * @group db
 * @group dao
 */
class HstoreDBTest extends TestCaseDAO
{
	/**
	 * Install hstore
	 * /usr/share/postgresql/contrib # cat hstore.sql | psql -U pgsql -d onphp
	**/
	public function testHstore()
	{
		foreach (DBTestPool::me()->getPool() as $connector => $db) {
			DBPool::me()->setDefault($db);
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
					Timestamp::create(time())
				)->
				setRegistered(
					Timestamp::create(time())->modify('-1 day')
				)->
				setProperties(Hstore::make($properties));

			$moscow = TestCity::dao()->add($moscow);

			$user = TestUser::dao()->add($user);

			Cache::me()->clean();
			TestUser::dao()->dropIdentityMap();

			$user = TestUser::dao()->getById('1');

			$this->assertInstanceOf(Hstore::class, $user->getProperties());

			$this->assertEquals(
				$properties,
				$user->getProperties()->getList()
			);

			$form = TestUser::proto()->makeForm();

			$form->get('properties')->
				setFormMapping(
					array(
						Primitive::string('age'),
						Primitive::integer('weight'),
						Primitive::string('comment'),
					)
				);

			$form->import(
				array('id' => $user->getId())
			);

			$this->assertNotNull($form->getValue('id'));

			$object = $user;

			FormUtils::object2form($object, $form);

			$this->assertInstanceOf(Hstore::class, $form->getValue('properties'));

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

			FormUtils::form2object($form, $user, false);

			$this->assertEquals(
				$user->getProperties()->getList(),
				array_filter($properties)
			);
		}
	}
}
?>