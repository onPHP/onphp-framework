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
	}

	abstract class DirectoryItemBase
	{
		protected $textField;
		protected $fileName;
		protected $inner;

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
		
		public function getInner()
		{
			return $this->inner;
		} 
	}

	final class DirectoryItem extends DirectoryItemBase { /* nop */ }

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
					optional(),
			);
		}
	}
	
?>
