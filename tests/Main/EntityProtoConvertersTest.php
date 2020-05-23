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

namespace OnPHP\Tests\Main;

use OnPHP\Core\Base\Singleton;
use OnPHP\Main\EntityProto\Builder\DirectoryToObjectBinder;
use OnPHP\Main\EntityProto\Builder\ObjectToDirectoryBinder;
use OnPHP\Tests\TestEnvironment\DirectoryItem;
use OnPHP\Tests\TestEnvironment\EntityProtoDirectoryItem;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group core
 * @group proto
 */
final class EntityProtoConvertersTest extends TestCase
{
	public function testDirectoryBinder()
	{
		$converter = DirectoryToObjectBinder::create(
			Singleton::getInstance(EntityProtoDirectoryItem::class)
		);

		$directoryContext = \ONPHP_TEST_PATH.'Main/data/directory';

		$result = $converter->make($directoryContext);

		$this->assertEquals(
			"wow, it's working!",
			$result->getInner()->getTextField()
		);

		$rand = rand();

		$result->setTextField($rand);

		$newDirectoryContext = \ONPHP_TEST_PATH.'tests/Main/data/directory';

		$unconverter = ObjectToDirectoryBinder::create(
			Singleton::getInstance(EntityProtoDirectoryItem::class)
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

		$ringDir = \ONPHP_TEST_PATH.'tests/Main/data/ring';

		$actual = glob($ringDir.'/*');

		$expected = array(
			$ringDir.'/contents',
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
			$ringDir.'/items/424/contents',
			$ringDir.'/items/424/inner',
			$ringDir.'/items/424/textField'
		);

		$this->assertEquals($expected, $actual);

		$this->assertEquals(readlink($ringDir.'/items/424/inner'), $ringDir.'/items/421');
	}

	public function testReadSymlinks()
	{
		$this->createContainers();

		$ringDir = \ONPHP_TEST_PATH.'tests/Main/data/ring';

		$converter = DirectoryToObjectBinder::create(
			Singleton::getInstance(EntityProtoDirectoryItem::class)
		);

		$result = $converter->make($ringDir);

		$this->assertNotNull($result);

		$this->assertNotNull($result->getInner());

		$this->assertEquals(421, $result->getInner()->getId());
		$this->assertEquals(422, $result->getInner()->getInner()->getId());

		$this->assertNotNull($result->getInner()->getInner());

		$newHead = DirectoryItem::create()->setId('newHead')->
			setInner($result->getInner());

		$newItemTmpDir = \ONPHP_TEST_PATH.'tests/Main/data/ring.tmp';
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
		$ringDir = \ONPHP_TEST_PATH.'tests/Main/data/ring';

		$converter = ObjectToDirectoryBinder::create(
			Singleton::getInstance(EntityProtoDirectoryItem::class)
		)->
			setDirectory($ringDir);

		$itemsConverter = $converter->cloneInnerBuilder('items');

		$ringListHead = DirectoryItem::create()->
			setId('421');

		$result = $itemsConverter->makeList(array($ringListHead));

		$ringListHead->setInner(
			$items[2] = DirectoryItem::create()->
			setId('422')->
			setInner(
				$items[1] = DirectoryItem::create()->
				setId('423')->
				setInner(
					$items[0] = DirectoryItem::create()->
					setId('424')->
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
?>