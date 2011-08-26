<?php
/***************************************************************************
 *   Copyright (C) 2011 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	class AMQPPeclTest extends TestCase
	{
		const EXCHANGE_NAME = 'AMQPPeclTestExchange';
		const QUEUE_NAME = 'AMQPPeclTestQueue';
		const COUNT_OF_PUBLISH = 5;
		const ROUTING_KEY = 'routing.key';

		protected function setUp()
		{
			if (!extension_loaded('amqp')) {
				$this->markTestSkipped(
					'The amqp extension is not available.'
				);
			}
		}

		public function testDefaulConnection()
		{
			try {
				$c = new AMQPPecl(
					AMQPCredentials::createDefault()
				);
				
				$this->assertInstanceOf('AMQP', $c->connect());
				$this->assertTrue($c->isConnected());
				
			} catch (Exception $e) {
				$this->fail($e->getMessage());
			}
		}
		
		public function testCustomConnection()
		{
			try {
				$c = new AMQPPecl(
					AMQPCredentials::create()->
						setHost('localhost')->
						setPort(5672)->
						setLogin('guest')->
						setPassword('guest')->
						setVirtualHost('/')
				);
				
				$this->assertInstanceOf('AMQP', $c->connect());
				$this->assertTrue($c->isConnected());
				
			} catch (Exception $e) {
				$this->fail($e->getMessage());
			}
		}

		public function testChannel()
		{
			$c = new AMQPPecl(
				AMQPCredentials::create()->
					setHost('localhost')->
					setPort(5672)->
					setLogin('guest')->
					setPassword('guest')->
					setVirtualHost('/')
			);

			$c->createChannel(1);
			$c->createChannel(2);
			$c->createChannel(3);

			$this->assertSame(3, count($c->getChannelList()));

			$this->assertInstanceOf(
				'AMQPPeclChannel',
				$c->getChannel(1)
			);

			try {
				$c->dropChannel(1);

				$this->assertSame(2, count($c->getChannelList()));

				$c->getChannel(1);
				$this->fail("Channel was't dropped");

			} catch (MissingElementException $e) {
				//ok
			}
		}

		public function testDeclareExchange()
		{
			$c = new AMQPPecl(
				AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			try {
				$channel = $channel->exchangeDeclare(
					self::EXCHANGE_NAME,
					AMQPExchangeConfig::create()->
						setType(
							new AMQPExchangeType(AMQPExchangeType::DIRECT)
						)->
						setDurable(true)
				);

				$this->assertInstanceOf('AMQPChannelInterface', $channel);

			} catch (Exception $e) {
				$this->fail($e->getMessage());
			}
		}

		public function testDeclareQueue()
		{
			$c = new AMQPPecl(
				AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			try {
				$int = $channel->queueDeclare(
					self::QUEUE_NAME,
					AMQPQueueConfig::create()->
						setDurable(true)
				);

				$this->assertSame($int, 0);

			} catch (Exception $e) {
				$this->fail($e->getMessage());
			}
		}

		public function testProducerLogic()
		{
			$c = new AMQPPecl(
				AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			$channel->exchangeDeclare(
				self::EXCHANGE_NAME,
				AMQPExchangeConfig::create()->
					setType(
						new AMQPExchangeType(AMQPExchangeType::DIRECT)
					)->
					setDurable(true)
			);

			$channel->queueDeclare(
				self::QUEUE_NAME,
				AMQPQueueConfig::create()->
					setDurable(true)
			);

			$channelInterface = $channel->queueBind(
				self::QUEUE_NAME, self::EXCHANGE_NAME, self::ROUTING_KEY
			);
			$this->assertInstanceOf('AMQPChannelInterface', $channelInterface);

			//cleanup queue
			$channelInterface = $channel->queuePurge(self::QUEUE_NAME);
			$this->assertInstanceOf('AMQPChannelInterface', $channelInterface);

			for($i = 1; $i <= self::COUNT_OF_PUBLISH; $i++) {
				$channelInterface = $channel->basicPublish(
					self::EXCHANGE_NAME,
					self::ROUTING_KEY,
					AMQPOutgoingMessage::create()->
						setBody("message {$i}")->
						setTimestamp(Timestamp::makeNow())->
						setAppId(__CLASS__)->
						setMessageId($i)->
						setContentEncoding('utf-8')
				);

				$this->assertInstanceOf(
					'AMQPChannelInterface',
					$channelInterface
				);
			}
		}

		/**
		 * @depends testProducerLogic
		**/
		public function testConsumerLogic()
		{
			$c = new AMQPPecl(
				AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			//only required to verify the number of messages in the queue
			$int = $channel->queueDeclare(
				self::QUEUE_NAME,
				AMQPQueueConfig::create()->
					setDurable(true)
			);
			
			$this->assertSame(self::COUNT_OF_PUBLISH, $int);

			$i = 0;
			try {
				while($mess = $channel->basicGet(self::QUEUE_NAME)) {
					$i++;

					$this->assertInstanceOf('AMQPIncomingMessage', $mess);
					$this->assertInstanceOf('Timestamp', $mess->getTimestamp());

					$properties = $mess->getProperties();

					$this->assertEquals(__CLASS__, $mess->getAppId());
					$this->assertTrue(
						isset($properties[AMQPIncomingMessage::APP_ID])
						&& $properties[AMQPIncomingMessage::APP_ID] ==
						$mess->getAppId()
					);

					$this->assertEquals($i, $mess->getMessageId());
					$this->assertTrue(
						isset($properties[AMQPIncomingMessage::MESSAGE_ID])
						&& $properties[AMQPIncomingMessage::MESSAGE_ID] ==
						$mess->getMessageId()
					);


					$this->assertEquals('text/plain', $mess->getContentType());
					$this->assertTrue(
						isset($properties[AMQPIncomingMessage::CONTENT_TYPE])
						&& $properties[AMQPIncomingMessage::CONTENT_TYPE] ==
						$mess->getContentType()
					);

					$this->assertEquals('utf-8', $mess->getContentEncoding());
					$this->assertTrue(
						isset($properties[AMQPIncomingMessage::CONTENT_ENCODING])
						&& $properties[AMQPIncomingMessage::CONTENT_ENCODING] ==
						$mess->getContentEncoding()
					);

					$this->assertEquals("message {$i}", $mess->getBody());
				}
			} catch (ObjectNotFoundException $e) {
				//it's ok, because queue is empty
				$this->assertSame(self::COUNT_OF_PUBLISH, $i);
			}
		}

		/**
		 * @depends testProducerLogic
		**/
		public function testCleanup()
		{
			$c = new AMQPPecl(
				AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			$channelInterface = $channel->queueUnbind(
				self::QUEUE_NAME,
				self::EXCHANGE_NAME,
				self::ROUTING_KEY
			);

			$this->assertInstanceOf(
				'AMQPChannelInterface',
				$channelInterface
			);

			$channelInterface = $channel->queueDelete(self::QUEUE_NAME);
			$this->assertInstanceOf(
				'AMQPChannelInterface',
				$channelInterface
			);

			$channelInterface = $channel->exchangeDelete(self::EXCHANGE_NAME);
			$this->assertInstanceOf(
				'AMQPChannelInterface',
				$channelInterface
			);
		}
	}
?>