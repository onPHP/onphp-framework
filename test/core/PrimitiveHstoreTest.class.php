<?php
	namespace Onphp\Test;

	final class PrimitiveHstoreTest extends TestCase
	{
		protected static $scope =
			array(
				'properties' => array(
					 'age' => '23',
					 'weight' => 80,
					 'comment' => 'test user case',
				)
			);
			
		protected static $invalidScope =
			array(
				'properties' => array(
					 'weight' => 'test error',
				)
			);
		
		public function testImport()
		{
			$prm = $this->create();
			
			$this->assertTrue(
				$prm->import(
					self::$scope
				)
			);
			
			$subform = $prm->getInnerForm();
			
			$this->assertEquals($subform->getValue('age'), '23');
			$this->assertEquals($subform->getValue('weight'), 80);
			$this->assertEquals($subform->getValue('comment'), 'test user case');
			
			$this->assertInstanceOf('\Onphp\Hstore', $prm->getValue());
			
			$hstore = $prm->getValue();
			
			$this->assertEquals($hstore->get('age'), '23');
			$this->assertEquals($hstore->get('weight'), 80);
			$this->assertEquals($hstore->get('comment'), 'test user case');
			
			$this->assertEquals(
				$hstore->getList(),
				self::$scope['properties']
			);
			$this->assertEquals(
				$prm->exportValue(),
				self::$scope['properties']
			);
			
			try {
				$hstore->get('NotFound');
				$this->fail('NotFound');
			} catch (\Onphp\ObjectNotFoundException $e) {
				/** ok **/
			}
			
			$prm->clean();
		}
		
		public function testInvalidImport()
		{
			$prm = $this->create();
			
			$this->assertFalse(
				$prm->import(
					self::$invalidScope
				)
			);
			
			$subform = $prm->getInnerForm();
			
			$this->assertNull(
				$subform->getValue('weight')
			);
			
			$this->assertEquals(
				$prm->getInnerErrors(),
				array(
					'weight' => \Onphp\Form::WRONG
				)
			);
			
			$this->assertNull($prm->exportValue());
			
			$prm->clean();
		}
		
		/**
		 * @return \Onphp\PrimitiveHstore
		**/
		protected function create()
		{
			return
				\Onphp\Primitive::hstore('properties')->
				setFormMapping(
					array(
						\Onphp\Primitive::string('age'),
						\Onphp\Primitive::integer('weight'),
						\Onphp\Primitive::string('comment'),
					)
				);
		}
	}
?>