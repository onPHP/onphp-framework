<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class EntityProtoConvertersTest extends PHPUnit_Framework_TestCase
	{
		public function testDirectoryBinder()
		{
			$converter = DirectoryToObjectBinder::create(
				Singleton::getInstance('EntityProtoDirectoryItem')
			);

			$directoryContext = ONPHP_TEST_PATH.'main/data/directory';

			$result = $converter->make($directoryContext);


			$this->assertEquals("wow, it's working!", $result->getInner()->getTextField());


			$rand = rand();

			$result->setTextField($rand);

			
			$newDirectoryContext = ONPHP_TEMP_PATH.'tests/main/data/directory';

			$unconverter = ObjectToDirectoryBinder::create(
				Singleton::getInstance('EntityProtoDirectoryItem')
			)->
				setDirectory($newDirectoryContext);

			$unconverter->make($result);

			$this->assertEquals(
				file_get_contents($directoryContext.'/contents'),
				file_get_contents($newDirectoryContext.'/contents')
			);
		}

		public function testSymlinks()
		{
			$this->createContainers();

			$ringDir = ONPHP_TEMP_PATH.'tests/main/data/ring';

			$actual = glob($ringDir.'/*');

			$expected = array(
				$ringDir.'/inner',
				$ringDir.'/items',
				$ringDir.'/textField'
			);

			$this->assertEquals($expected, $actual);

			$actual = glob($ringDir.'/items/*');

			$expected = array(
				$ringDir.'/items/421',
				$ringDir.'/items/422',
				$ringDir.'/items/423',
				$ringDir.'/items/424'
			);

			$this->assertEquals($expected, $actual);

			$actual = glob($ringDir.'/items/424/*');

			$expected = array(
				$ringDir.'/items/424/inner',
				$ringDir.'/items/424/textField'
			);

			$this->assertEquals($expected, $actual);

			$this->assertEquals(readlink($ringDir.'/items/424/inner'), $ringDir.'/items/421');
		}


		public function testReadSymlinks()
		{
			$this->createContainers();

			$ringDir = ONPHP_TEMP_PATH.'tests/main/data/ring';

			$converter = DirectoryToObjectBinder::create(
				Singleton::getInstance('EntityProtoDirectoryItem')
			);

			$result = $converter->make($ringDir);

			$this->assertNotNull($result);

			$this->assertNotNull($result->getInner());

			$this->assertEquals(421, $result->getInner()->getId());

			$this->assertNotNull($result->getInner()->getInner());


			$newHead = DirectoryItem::create()->setId('newHead')->
				setInner($result->getInner());

			$newItemTmpDir = ONPHP_TEMP_PATH.'tests/main/data/ring.tmp';
			$saver = $converter->makeReverseBuilder()->
				setDirectory($newItemTmpDir);

			$saver->makeList(array($newHead));


			$result->setInner($newHead);

			$saver->
				setDirectory($ringDir)->
				make($result);

			$this->assertEquals(
				readlink($ringDir.'/inner'), $newItemTmpDir.'/newHead'
			);
		}

		private function createContainers()
		{
			$ringDir = ONPHP_TEMP_PATH.'tests/main/data/ring';

			$converter = ObjectToDirectoryBinder::create(
				Singleton::getInstance('EntityProtoDirectoryItem')
			)->
				setDirectory($ringDir);

			$itemsConverter = $converter->cloneBuilder(Singleton::getInstance('EntityProtoDirectoryItem'))->
				setDirectory($ringDir.'/items');

			$ringListHead = DirectoryItem::create()->
				setTextField('421');

			$result = $itemsConverter->makeList(array($ringListHead));

			$ringListHead->setInner(
				$items[2] = DirectoryItem::create()->
				setTextField('422')->
				setInner(
					$items[1] = DirectoryItem::create()->
					setTextField('423')->
					setInner(
						$items[0] = DirectoryItem::create()->
						setTextField('424')->
						setInner($ringListHead)
					)
				)
			);

			// storing head again to update inner link
			$items[3] = $ringListHead;

			$result = $itemsConverter->makeList($items);

			$mainContainer = DirectoryItem::create()->
				setTextField('main container');

			$mainContainer->setInner($ringListHead);

			// storing the container with its link to ring list head
			$result = $converter->make($mainContainer);
		}
	}

	abstract class DirectoryItemBase implements Identifiable
	{
		protected $textField;
		protected $fileName;
		protected $inner;

		public function getId()
		{
			return $this->getTextField();
		}

		public function setId($id)
		{
			return $this->setTextField($id);
		}

		public function setTextField($textField)
		{
			$this->textField = $textField;
			
			return $this;
		}

		public function getTextField()
		{
			return $this->textField;
		} 

		public function setFileName($fileName)
		{
			$this->fileName = $fileName;
			
			return $this;
		}

		public function getFileName()
		{
			return $this->fileName;
		}

		public function setInner(DirectoryItem $inner)
		{
			$this->inner = $inner;

			return $this;
		}

		public function dropInner()
		{
			$this->inner = null;

			return $this;
		}

		public function getInner()
		{
			return $this->inner;
		} 
	}

	final class DirectoryItem extends DirectoryItemBase
	{
		public static function create()
		{
			return new self;
		}
	}

	final class EntityProtoDirectoryItem extends EntityProto
	{
		public function className()
		{
			return 'DirectoryItem';
		}
		
		public function getFormMapping()
		{
			return array(
				'textField' => Primitive::string('textField')->
					setMax(256)->
					optional(),
				
				'fileName' => Primitive::file('contents')->
					required(),

				'inner' => Primitive::form('inner')->
					of('DirectoryItem')->
					required(),
			);
		}
	}
	
?>
