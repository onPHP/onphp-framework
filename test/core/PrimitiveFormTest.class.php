<?php

	class PrimitiveFormTestEntityProto extends EntityProto
	{
		/**
		 * @return PrimitiveFormTestEntityProto
		 */
		public static function me()
		{
			return self::getInstance(__CLASS__);
		}
		
		public function getFormMapping()
		{
			return array(
				Primitive::string('name')->required(),
				Primitive::integer('age')->setMin(0)->required(),
				Primitive::boolean('capital'),
			);
		}
		
		public function checkPostConstraints($object, Form $form, $previousObject = null)
		{
			$name = $form->getValue('name');
			$capital = $form->getValue('capital');
			if ($name == 'Moscow' && !$capital) {
				$form->markWrong('capital');
			} elseif ($name != 'Moscow' && $capital) {
				$form->markWrong('name');
			}
			return $this;
		}
	}

	/**
	 * @group pf
	 */
	final class PrimitiveFormTest extends TestCase
	{
		/**
		 * @group pf1
		 */
		public function testWithEntityProto()
		{
			$prm = Primitive::form('city')->
				ofProto($this->getEntityProto())->
				setNeedValidate(true);
			
			$this->primitiveFormCheck($prm);
		}
		
		/**
		 * @group pf2
		 */
		public function testWithCompositeFormWithoutEntityProto()
		{
			$entityProto = $this->getEntityProto();
			$ruleCallback = CallbackLogicalObject::create(
				function (Form $form) use ($entityProto) {
					$entityProto->checkPostConstraints(null, $form);
					return true;
				}
			);
			$form = $this->getEntityProto()->makeForm()->
				addRule('rule', $ruleCallback);
			
			//primitive with composite custom form
			$prm = Primitive::form('city')->
				setNeedValidate(true)->
				setComposite(true)->
				setValue($form);
			
			$this->primitiveFormCheck($prm);
		}
		
		/**
		 * @group pf3
		 */
		public function testWithFormAndErrors()
		{
			$prm = Primitive::form('capital')->
				ofProto($this->getEntityProto())->
				setNeedValidate(true)->
				required();
			$prmList = Primitive::formsList('cities')->
				setNeedValidate(true)->
				ofProto($this->getEntityProto());
			
			$scope = $this->getMultiCityScope();
			
			$form = Form::create()->
				add($prm)->
				add($prmList);
			
			//success import
			$form->import($scope);
			$this->assertEquals(array(), $form->getErrors());
			$this->assertEquals(array(), $form->getInnerErrors());
			$form->checkRules();
			$this->assertEquals(array(), $form->getErrors());
			$this->assertEquals(array(), $form->getInnerErrors());
			$this->assertEquals($scope, $form->export());
			
			//error in capital
			$form->clean()->dropAllErrors();
			$scope['capital']['name'] = 'NewYork';
			$scope['cities'][0]['name'] = 'Moscow';
			$form->import($scope);
			$this->assertEquals(array(), $form->getErrors());
			$this->assertEquals(array(), $form->getInnerErrors());
			$form->checkRules();
			$this->assertEquals(array('capital' => Form::WRONG, 'cities' => Form::WRONG), $form->getErrors());
			$this->assertEquals(
				array(
					'capital' => array('name' => Form::WRONG),
					'cities' => array(0 => array('capital' => Form::WRONG)),
				),
				$form->getInnerErrors()
			);
			
			//missing capital and 
			$form->clean()->dropAllErrors();
			$scope = $this->getMultiCityScope();
			unset($scope['capital'], $scope['cities'][1]['age']);
			$form->import($scope);
			$this->assertEquals(array('capital' => Form::MISSING, 'cities' => Form::WRONG), $form->getErrors());
			$this->assertEquals(
				array(
					'capital' => Form::MISSING,
					'cities' => array(1 => array('age' => Form::MISSING))
				),
				$form->getInnerErrors()
			);
		}
		
		private function primitiveFormCheck(PrimitiveForm $prm)
		{
			$scope = $this->getOneCityScope();
			
			//success import
			$this->assertTrue($prm->import($scope));
			$this->assertTrue($prm->validate());
			$prm->setNeedValidate(true);
			$this->assertTrue($prm->validate());
			$this->assertEmpty($prm->getInnerErrors());
			$this->assertEquals($scope['city'], $prm->exportValue());

			//wrong city name
			$scope['city']['name'] = 'Novgorod';
			$this->assertTrue($prm->import($scope));
			$this->assertFalse($prm->validate());
			$this->assertEquals(array('name' => Form::WRONG), $prm->getInnerErrors());

			//cleaning
			$prm->clean();
			$this->assertEquals(array(), $prm->getInnerErrors());
			$this->assertEquals(null, $prm->exportValue());

			unset($scope['city']['capital'], $scope['city']['age']);
			$this->assertFalse($prm->import($scope));
			$this->assertFalse($prm->validate());
			$this->assertEquals(array('age' => Form::MISSING), $prm->getInnerErrors());
		}
		
		private function getMultiCityScope()
		{
			return array(
				'capital' => $this->getCityScope(),
				'cities' => array(
					array(
						'name' => 'Novgorod',
						'age' => (Date::create('now')->getYear() - 859),
					),
					array(
						'name' => 'Murmansk',
						'age' => (Date::create('now')->getYear() - 1916),
					),
				),
			);
		}
		
		private function getOneCityScope()
		{
			return array(
				'city' => $this->getCityScope(),
			);
		}
		
		private function getCityScope()
		{
			return array(
				'name' => 'Moscow',
				'age' => (Date::create('now')->getYear() - 1147),
				'capital' => true,
			);
		}
		
		/**
		 * @return EntityProto
		 */
		private function getEntityProto()
		{
			return PrimitiveFormTestEntityProto::me();
		}
	}
