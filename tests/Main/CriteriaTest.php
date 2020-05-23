<?php

namespace OnPHP\Tests\Main;

use OnPHP\Core\Base\Date;
use OnPHP\Core\DB\ImaginaryDialect;
use OnPHP\Core\Exception\WrongStateException;
use OnPHP\Core\Logic\BinaryExpression;
use OnPHP\Core\Logic\Expression;
use OnPHP\Core\OSQL\DBValue;
use OnPHP\Core\OSQL\OrderBy;
use OnPHP\Core\OSQL\OrderChain;
use OnPHP\Core\OSQL\SQLFunction;
use OnPHP\Main\Criteria\Criteria;
use OnPHP\Main\Criteria\Projection;
use OnPHP\Main\Criteria\Projection\ClassProjection;
use OnPHP\Tests\Meta\Business\TestCity;
use OnPHP\Tests\Meta\Business\TestUser;
use OnPHP\Tests\Meta\Business\TestUserWithContact;
use OnPHP\Tests\Meta\Business\TestUserWithContactExtended;
use OnPHP\Tests\TestEnvironment\TestCase;


/**
 * @group core
 * @group db
 * @group criteria
 */
final class CriteriaTest extends TestCase
{
	public function testClassProjection()
	{
		$criteria =
			Criteria::create(TestUser::dao())->
			setProjection(
				Projection::chain()->add(
					ClassProjection::create(TestUser::class)
				)->
				add(
					Projection::group('id')
				)
			);

		$this->assertEquals(
			$criteria->toSelectQuery()->getFieldsCount(),
			count(TestUser::dao()->getFields())
		);
	}

	public function testAddProjection()
	{
		$criteria = Criteria::create(TestUser::dao());

		$this->assertEquals(
			$criteria->getProjection(),
			Projection::chain()
		);

		$criteria = Criteria::create(TestUser::dao())->
			addProjection(
				Projection::chain()
			);

		$this->assertEquals(
			$criteria->getProjection(),
			Projection::chain()
		);

		$criteria = Criteria::create(TestUser::dao())->
			addProjection(
				Projection::property('id')
			);

		$this->assertEquals(
			$criteria->getProjection(),
			Projection::chain()->
				add(
					Projection::property('id')
				)
		);
	}

	public function testSetProjection()
	{
		$criteria = Criteria::create(TestUser::dao())->
			setProjection(
				Projection::chain()->
					add(
						Projection::property('id')
					)
			);

		$this->assertEquals(
			$criteria->getProjection(),
			Projection::chain()->
				add(
					Projection::property('id')
				)
		);

		$criteria = Criteria::create(TestUser::dao())->
			setProjection(
				Projection::property('id')
			);

		$this->assertEquals(
			$criteria->getProjection(),
			Projection::chain()->
				add(
					Projection::property('id')
				)
		);
	}

	/**
	 * @dataProvider orderDataProvider
	**/
	public function testOrder($order, $expectedString)
	{
		$criteria = Criteria::create(TestUser::dao())->
			setProjection(
				Projection::property('id')
			)->
			addOrder($order);

		$this->assertEquals(
			$criteria->toDialectString(ImaginaryDialect::me()),
			'SELECT test_user.id FROM test_user ORDER BY '.$expectedString
		);
	}

	public function testValueObject()
	{
		$criteria =
			Criteria::create(TestUserWithContact::dao())->
			setProjection(
				Projection::property('id')
			)->
			add(
				Expression::eq('contacts.city', 1)
			);			

		$this->assertEquals(
			$criteria->toDialectString(ImaginaryDialect::me()),
			'SELECT test_user_with_contact.id FROM test_user_with_contact WHERE (test_user_with_contact.city_id = 1)'
		);

		$criteria =
			Criteria::create(TestUserWithContact::dao())->
			setProjection(
				Projection::property('id')
			)->
			add(
				Expression::eq('contacts.city.name', 'Moscow')
			);		

		$this->assertEquals(
			$criteria->toDialectString(ImaginaryDialect::me()),
			'SELECT test_user_with_contact.id FROM test_user_with_contact JOIN custom_table AS 3524772f_city_id ON (test_user_with_contact.city_id = 3524772f_city_id.id) WHERE (3524772f_city_id.name = Moscow)'
		);

		//check extending ValueObject
		$criteria =
			Criteria::create(TestUserWithContactExtended::dao())->
			setProjection(
				Projection::property('id')
			)->
			add(
				Expression::eq('contactExt.skype', 'skype_nick_name')
			);

		$this->assertEquals(
			$criteria->toDialectString(ImaginaryDialect::me()),
			'SELECT test_user_with_contact_extended.id FROM test_user_with_contact_extended WHERE (test_user_with_contact_extended.skype = skype_nick_name)'
		);

		$criteria =
			Criteria::create(TestUserWithContactExtended::dao())->
			setProjection(
				Projection::property('id')
			)->
			add(
				Expression::eq('contactExt.city.name', 'Moscow')
			);

		$this->assertEquals(
			$criteria->toDialectString(ImaginaryDialect::me()),
			'SELECT test_user_with_contact_extended.id FROM test_user_with_contact_extended JOIN custom_table AS 3524772f_city_id ON (test_user_with_contact_extended.city_id = 3524772f_city_id.id) WHERE (3524772f_city_id.name = Moscow)'
		);
	}

	public function testDialectStringObjects()
	{
		$criteria =
			Criteria::create(TestUser::dao())->
			setProjection(
				Projection::property('id')
			)->
			add(
				Expression::gt('registered', Date::create('2011-01-01'))
			);

		$this->assertEquals(
			$criteria->toDialectString(ImaginaryDialect::me()),
			'SELECT test_user.id FROM test_user WHERE (test_user.registered > 2011-01-01)'
		);

		$criteria =
			Criteria::create(TestUserWithContactExtended::dao())->
			setProjection(
				Projection::property('contactExt.city.id', 'cityId')
			)->
			add(
				Expression::eq('contactExt.city', TestCity::create()->setId(22))
			);

		$this->assertEquals(
			$criteria->toDialectString(ImaginaryDialect::me()),
			'SELECT test_user_with_contact_extended.city_id AS cityId FROM test_user_with_contact_extended WHERE (test_user_with_contact_extended.city_id = 22)'
		);

		$cityList = array(
			TestCity::create()->setId(3),
			TestCity::create()->setId(44),
		);

		$criteria =
			Criteria::create(TestUser::dao())->
			setProjection(
				Projection::property('id')
			)->
			add(
				Expression::in('city', $cityList)
			);

		$this->assertEquals(
			$criteria->toDialectString(ImaginaryDialect::me()),
			'SELECT test_user.id FROM test_user WHERE (test_user.city_id IN (3, 44))'
		);
	}

	public function testSqlFunction()
	{
		$criteria = Criteria::create(TestCity::dao())->
			addProjection(
				Projection::property(
					SQLFunction::create(
						'count',
						SQLFunction::create(
							'substring',
							BinaryExpression::create(
								'name',
								BinaryExpression::create(
									DBValue::create('M....w'),
									DBValue::create('#'),
									'for'
								)->
								noBrackets(),
								'from'
							)->
							noBrackets()
						)
					)->
					setAggregateDistinct()->
					setAlias('my_alias')
				)
			);

		$this->assertEquals(
			$criteria->toDialectString(ImaginaryDialect::me()),
			'SELECT count(DISTINCT substring(custom_table.name from M....w for #)) AS my_alias FROM custom_table'
		);
	}

	public function testSleepWithEmptyDao()
	{
		$baseCriteria =
			Criteria::create()->
			setLimit(10);

		$newBaseCriteria =
			unserialize(serialize($baseCriteria));

		$this->assertEquals(
			$newBaseCriteria->getLimit(),
			$baseCriteria->getLimit()
		);

		$this->assertEquals(
			$newBaseCriteria->getDao(),
			$baseCriteria->getDao()
		);
	}

	public function testListWithForgottenDao()
	{
		$criteria =
			Criteria::create()->
				add(Expression::eq('id', 42));

		$listCriteria = clone $criteria;

		$this->expectException(WrongStateException::class);
		
		$listCriteria->getList();
	}
	
	public function testCustomListWithForgottenDao()
	{
		$criteria =
			Criteria::create()->
				add(Expression::eq('id', 42))->
				addProjection(Projection::property('id'));
		
		$this->expectException(WrongStateException::class);
		
		$criteria->getCustomList();
	}

	public static function orderDataProvider()
	{
		return array(
			array(OrderBy::create('id'), 'test_user.id'),
			array(
				OrderChain::create()->
					add(OrderBy::create('id')->asc())->
					add(OrderBy::create('id')),
				'test_user.id ASC, test_user.id'
			),
			array(OrderBy::create('id')->asc(), 'test_user.id ASC'),
			array(OrderBy::create('id')->desc(), 'test_user.id DESC'),
			array(OrderBy::create('id')->nullsFirst(), 'test_user.id NULLS FIRST'),
			array(OrderBy::create('id')->nullsLast(), 'test_user.id NULLS LAST'),
			array(OrderBy::create('id')->asc()->nullsLast(), 'test_user.id ASC NULLS LAST'),
			array(OrderBy::create('id')->desc()->nullsFirst(), 'test_user.id DESC NULLS FIRST'),
			array(
				OrderBy::create(Expression::isNull('id'))->
					asc()->
					nullsFirst(),
				'((test_user.id IS NULL)) ASC NULLS FIRST'
			)
		);
	}
}
?>