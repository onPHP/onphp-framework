<?php
	/** $Id$ **/

	if (!defined('TSEARCH_INCUBATOR'))
		define('TSEARCH_INCUBATOR', ONPHP_TEST_PATH.'incubator'.DIRECTORY_SEPARATOR.'TSearch'.DIRECTORY_SEPARATOR);
	
	include_once(TSEARCH_INCUBATOR.'TSearchBusinessStub.class.php');
	include_once(TSEARCH_INCUBATOR.'TSearchBusinessStubDAO.class.php');
	include_once(TSEARCH_INCUBATOR.'TSearchBusinessStubContainer.class.php');

	class TSearchVectorBuilderTest extends TestCase
	{
		public function testTsearchDataBuild()
		{
			$object = $this->getBusinessObject();
			
			$this->assertTrue($object instanceof TSearchConfigurator);
			
			$this->assertEquals(
				$object->getTSearchData()->toString(),
				'First Second Third array of words name of object description of object stub'
			);
			
			/**
			 * ...save object
			**/
			$object->setId(123);
			
			$this->assertEquals(
				"UPDATE \"tsearch_stub\" SET \"fti\" = ("
					."setweight(to_tsvector('utf8_russian', 'First Second Third'), 'A')"
					." || setweight(to_tsvector('utf8_russian', 'array of words'), 'B')"
					." || setweight(to_tsvector('utf8_russian', 'name of object'), 'C')"
					." || setweight(to_tsvector('utf8_russian', 'description of object stub'), 'D')"
				.") WHERE (\"id\" = '123')",
				TSearchVectorBuilder::create($object)->
					toDialectString(PostgresDialect::me())
			);
			
			$object->setName(null); //@see TSearchBusinessStub::getTSearchData()
			
			$this->assertEquals(
				"UPDATE \"tsearch_stub\" SET \"fti\" = ("
					."setweight(to_tsvector('utf8_russian', 'First Second Third'), 'A')"
					." || setweight(to_tsvector('utf8_russian', 'array of words'), 'B')"
					." || setweight(to_tsvector('utf8_russian', 'description of object stub'), 'D')"
				.") WHERE (\"id\" = '123')",
				TSearchVectorBuilder::create($object)->
					toDialectString(PostgresDialect::me())
			);
		}
		
		public function testBuildByEmptyObject()
		{
			$object = TSearchBusinessStub::create();
			
			/**
			 * ...save object
			**/
			$object->setId(123);
			
			$this->assertEquals(
				"UPDATE \"tsearch_stub\" SET \"fti\" = NULL WHERE (\"id\" = '123')",
				TSearchVectorBuilder::create($object)->
					toDialectString(PostgresDialect::me())
			);
		}
		
		public function testSetQueryFields()
		{
			$object = $this->getBusinessObject();
			
			$insert =
				OSQL::insert()->
				set('myfield', 'value')->
				into(TSearchBusinessStub::dao()->getTable());
			
			TSearchVectorBuilder::create($object)->
				toUpdateOrInsertQuery($insert);
			
			$this->assertEquals(
				$insert->toDialectString(PostgresDialect::me()),
				"INSERT INTO \"tsearch_stub\" (\"myfield\", \"fti\") "
				."VALUES ('value', setweight(to_tsvector('utf8_russian', 'First Second Third'), 'A')"
				." || setweight(to_tsvector('utf8_russian', 'array of words'), 'B')"
				." || setweight(to_tsvector('utf8_russian', 'name of object'), 'C')"
				." || setweight(to_tsvector('utf8_russian', 'description of object stub'), 'D'))"
			);
		}
		
		protected function getBusinessObject()
		{
			return
				TSearchBusinessStub::create()->
					setName('name of object')->
					setDescription('description of object stub')->
					setSubNames(
						array(
							'array',
							'of',
							'words',
						)
					)->
					setObjectList(
						array(
							TSearchBusinessStubContainer::create()->setName('First'),
							TSearchBusinessStubContainer::create()->setName('<b>Second</b>'),
							TSearchBusinessStubContainer::create()->setName('Third'),
						)
					);
		}
	}
?>