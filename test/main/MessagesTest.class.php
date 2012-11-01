<?php
	/* $Id$ */

	namespace Onphp\Test;

	final class MessagesTest extends TestCase
	{
		public function testFileQueue()
		{
			$dir = ONPHP_TEMP_PATH.'tests/messages';

			$uri = $dir.'/fileQueueItems';

			if (!is_dir($dir))
				mkdir($dir, 0700, true);

			if (file_exists($uri))
				unlink($uri);


			$queue = \Onphp\TextFileQueue::create()->
				setFileName($uri);

			$sender = \Onphp\TextFileSender::create()->
				setQueue($queue);

			$receiver = \Onphp\TextFileReceiver::create()->
				setQueue($queue);


			$sender->send(
				\Onphp\TextMessage::create()->
				setText('first ape')
			);

			$sender->send(
				\Onphp\TextMessage::create()->
				setText('second ape')
			);


			$message = $receiver->receive();

			$this->assertNotNull($message);

			$this->assertEquals('first ape', $message->getText());


			$message = $receiver->receive();

			$this->assertNotNull($message);

			$this->assertEquals('second ape', $message->getText());


			$sender->send(
				\Onphp\TextMessage::create()->
				setText('third ape')
			);


			$message = $receiver->receive();

			$this->assertNotNull($message);

			$this->assertEquals('third ape', $message->getText());
		}
	}
?>