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

	namespace Onphp\Test;

	class AMQPTestCaseNoAckQueueConsumer extends \Onphp\AMQPPeclQueueConsumer
	{
		protected $checkString = '';

		public function handleCancelOk($consumerTag)
		{
			$this->checkString .= 'C';
		}

		public function handleConsumeOk($consumerTag)
		{
			$this->checkString .= 'A';

			AMQPPeclTest::checkMessageCount($this->getChannel());
		}

		public function handleDelivery(\Onphp\AMQPIncomingMessage $delivery)
		{
			AMQPPeclTest::messageTest($delivery, $this->count);

			//send acknowledge to RabbitMQ
			$this->getChannel()->basicAck(
				$delivery->getDeliveryTag(),
				true
			);

			return parent::handleDelivery($delivery);
		}

		public function getCheckString()
		{
			return $this->checkString;
		}

		public function handleChangeConsumerTag($fromTag, $toTag)
		{
			return;
		}
	}

	class AMQPTestCaseAutoAckQueueConsumer extends \Onphp\AMQPPeclQueueConsumer
	{
		protected $checkString = '';

		public function handleCancelOk($consumerTag)
		{
			$this->checkString .= 'C';
		}

		public function handleConsumeOk($consumerTag)
		{
			$this->checkString .= 'A';

			AMQPPeclTest::checkMessageCount($this->getChannel());
		}

		public function handleDelivery(\Onphp\AMQPIncomingMessage $delivery)
		{
			AMQPPeclTest::messageTest($delivery, $this->count);

			return parent::handleDelivery($delivery);
		}

		public function getCheckString()
		{
			return $this->checkString;
		}

		public function handleChangeConsumerTag($fromTag, $toTag)
		{
			return;
		}
	}

	class AMQPPeclTest extends TestCase
	{
		const COUNT_OF_PUBLISH = 5;
		const MESSAGE_COUNT_WAIT = 200000;

		/**
		 * cluster master-slave of 2 nodes on single machine
		 */
		const PORT_MIRRORED = 5673; // port of slave node
		
		protected static $queueList = array(
			// basic queue
			'basic' => array(
				'exchange' => 'AMQPPeclTestExchange',
				'exchangeType' => \Onphp\AMQPExchangeType::DIRECT,
				'name' => 'AMQPPeclTestQueue',
				'key' => 'routing.key',
				'args' => array()
			),
			// exchange2exchange binding
			'exchangeBinded' => array(
				'exchange' => 'AMQPPeclTestExchangeBinded',
				'exchangeType' => \Onphp\AMQPExchangeType::FANOUT,
				'name' => 'AMQPPeclTestQueueBinded',
				'key' => 'routing.key.binded',
				'args' => array()
			),
			// Highly Available Queues
			'mirrored' => array(
				'exchange' => 'AMQPPeclTestExchange',
				'exchangeType' => \Onphp\AMQPExchangeType::DIRECT,
				'name' => 'AMQPPeclTestQueueMirrored',
				'key' => 'routing.key.mirrored',
				'args' => array('x-ha-policy' => 'all')
			)
		);

		protected function setUp()
		{
			if (!extension_loaded('amqp')) {
				$this->markTestSkipped(
					'The amqp extension is not available.'
				);
			}
		}

		public static function messageTest(\Onphp\AMQPIncomingMessage $mess, $i)
		{
			self::messageAssertion($mess, $i);
		}


		/**
		 * @param \Onphp\AMQPPeclChannel $channel
		 * @param string $label
		 * @param int $value
		 */
		public static function checkMessageCount(\Onphp\AMQPChannelInterface $channel,
			$label = 'basic', $value = self::COUNT_OF_PUBLISH
		) {
			usleep(self::MESSAGE_COUNT_WAIT);

			self::assertTrue(isset(self::$queueList[$label]));

			$count =  $channel->queueDeclare(
				self::$queueList[$label]['name'],
				\Onphp\AMQPQueueConfig::create()->
					setDurable(true)->
					setArguments(
						self::$queueList[$label]['args']
					)
			);

			self::assertEquals($value, $count);
		}

		public function testDefaulConnection()
		{
			try {
				$c = new \Onphp\AMQPPecl(
					\Onphp\AMQPCredentials::createDefault()
				);
				
				$this->assertInstanceOf('\Onphp\AMQP', $c->connect());
				$this->assertTrue($c->isConnected());
				
			} catch (\Exception $e) {
				$this->fail($e->getMessage());
			}
		}
		
		public function testCustomConnection()
		{
			try {
				$c = new \Onphp\AMQPPecl(
					\Onphp\AMQPCredentials::create()->
						setHost('localhost')->
						setPort(5672)->
						setLogin('guest')->
						setPassword('guest')->
						setVirtualHost('/')
				);
				
				$this->assertInstanceOf('\Onphp\AMQP', $c->connect());
				$this->assertTrue($c->isConnected());
				
			} catch (\Exception $e) {
				$this->fail($e->getMessage());
			}
		}

		public function testChannel()
		{
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::create()->
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
				'\Onphp\AMQPPeclChannel',
				$c->getChannel(1)
			);

			try {
				$c->dropChannel(1);

				$this->assertSame(2, count($c->getChannelList()));

				$c->getChannel(1);
				$this->fail("Channel was't dropped");

			} catch (\Onphp\MissingElementException $e) {
				//ok
			}
		}

		public function testDeclareExchange()
		{
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			try {
				$this->exchangeDeclare($channel, 'basic');
			} catch (\Exception $e) {
				$this->fail($e->getMessage());
			}
		}

		public function testDeclareQueue()
		{
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			try {
				$int = $this->queueDeclare($channel, 'basic');

				$this->assertSame($int, 0);

			} catch (\Exception $e) {
				$this->fail($e->getMessage());
			}
		}

		public function testProducerLogic()
		{
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			$this->exchangeDeclare($channel, 'basic');
			$this->queueDeclare($channel, 'basic');
			$this->queueBind($channel, 'basic');
			$this->queuePurge($channel, 'basic');
			$this->publishMessages($channel);

		}

		/**
		 * @depends testProducerLogic
		**/
		public function testNoAckConsumerLogic()
		{
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			$this->checkMessageCount($channel);

			for ($j = 1; $j <= self::COUNT_OF_PUBLISH; $j++) {
				$mess = $channel->basicGet(
					self::$queueList['basic']['name'],
					false
				);
				self::messageTest($mess, $j);
			}
			
			$this->assertSame(self::COUNT_OF_PUBLISH, $j - 1);
			
			$this->checkMessageCount($channel, 'basic', 0);
			
			$c->disconnect();
		}

		/**
		 * @depends testNoAckConsumerLogic
		**/
		public function testConsumerLogic()
		{
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			$this->checkMessageCount($channel);

			$i = 0;
			try {
				while($mess = $channel->basicGet(self::$queueList['basic']['name']))
					self::messageTest($mess, ++$i);
			} catch (\Onphp\ObjectNotFoundException $e) {
				//it's ok, because queue is empty
				$this->assertSame(self::COUNT_OF_PUBLISH, $i);
			}
		}

		/**
		 * test connection on drop node
		 */
		public function testDeclareQueueCluster()
		{
			$c = \Onphp\AMQPSelective::me()->
				addLink(
					'slave',
					new \Onphp\AMQPPecl(
						\Onphp\AMQPCredentials::createDefault()->
						setPort(self::PORT_MIRRORED)
					)
				)->
				addLink(
					'master',
					new \Onphp\AMQPPecl(
						\Onphp\AMQPCredentials::createDefault()
					)
				)->
				setCurrent('slave');

			$c->dropLink('slave');

			$channel = $c->createChannel(1);

			AMQPPeclTest::assertEquals(
				\Onphp\AMQPCredentials::DEFAULT_PORT,
				$c->getCredentials()->getPort()
			);

			try {
				$int = $this->queueDeclare($channel, 'mirrored');
				$this->assertSame($int, 0);
			} catch (\Exception $e) {
				$this->fail($e->getMessage());
			}
		}

		public function testProducerLogicMirrored()
		{
			$c = \Onphp\AMQPSelective::me()->
				addLink(
					'slave',
					new \Onphp\AMQPPecl(
						\Onphp\AMQPCredentials::createDefault()->
						setPort(self::PORT_MIRRORED)
					)
				)->
				addLink(
					'master',
					new \Onphp\AMQPPecl(
						\Onphp\AMQPCredentials::createDefault()
					)
				)->
				setCurrent('slave');

			$channel = $c->createChannel(1);
			
			$this->exchangeDeclare($channel, 'mirrored');
			$this->queueDeclare($channel, 'mirrored');
			$this->queueBind($channel, 'mirrored');
			$this->queuePurge($channel, 'mirrored');

			AMQPPeclTest::assertEquals(
				self::PORT_MIRRORED,
				$c->getCredentials()->getPort()
			);

			$c->dropLink('slave');

			$this->publishMessages($channel, false, 'mirrored');

			AMQPPeclTest::assertEquals(
				\Onphp\AMQPCredentials::DEFAULT_PORT,
				$c->getCredentials()->getPort()
			);

			$this->checkMessageCount($channel, 'mirrored');

		}

		/**
		 * @depends testProducerLogicMirrored
		**/
		public function testConsumerLogicMirrored()
		{
			$c = \Onphp\AMQPSelective::me()->
				addLink(
					'slave',
					new \Onphp\AMQPPecl(
						\Onphp\AMQPCredentials::createDefault()->
						setPort(self::PORT_MIRRORED)
					)
				)->
				addLink(
					'master',
					new \Onphp\AMQPPecl(
						\Onphp\AMQPCredentials::createDefault()
					)
				)->
				setCurrent('slave');

			$c->dropLink('slave');
			
			$channel = $c->createChannel(1);
			
			$this->checkMessageCount($channel, 'mirrored');

			$i = 0;
			try {
				while($mess = $channel->basicGet(self::$queueList['mirrored']['name']))
					self::messageTest($mess, ++$i);
			} catch (\Onphp\ObjectNotFoundException $e) {/**/}
			$this->assertSame(self::COUNT_OF_PUBLISH, $i);

			AMQPPeclTest::assertEquals(
				\Onphp\AMQPCredentials::DEFAULT_PORT,
				$c->getCredentials()->getPort()
			);
				
		}

		public function testCleanup()
		{
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			foreach (array('basic', 'mirrored') as $label) {
				$this->exchangeDeclare($channel, $label);
				$this->queueDeclare($channel, $label);
				$this->queueBind($channel, $label);
				$this->queueUnbind($channel, $label);
				$this->queueDelete($channel, $label);
				$this->exchangeDelete($channel, $label);
			}
		}


		public function testQueueConsumerNoAck()
		{
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			$this->exchangeDeclare($channel, 'basic');
			$this->queueDeclare($channel, 'basic');
			$this->queueBind($channel, 'basic');
			$this->queuePurge($channel, 'basic');

			$this->publishMessages($channel, false);

			$consumer = new AMQPTestCaseNoAckQueueConsumer($channel);
			$consumer->setLimit(AMQPPeclTest::COUNT_OF_PUBLISH);

			$channel->basicConsume(
				self::$queueList['basic']['name'],
				false,
				$consumer
			);
			$this->assertSame('AC', $consumer->getCheckString());
			$this->assertEquals(self::COUNT_OF_PUBLISH, $consumer->getCount());

			/**
			 * check queue is empty
			 */
			//drop channels and close connection
			$c->disconnect();

			$c = new \Onphp\AMQPPecl(\Onphp\AMQPCredentials::createDefault());
			$channel = $c->createChannel(1);

			$this->exchangeDeclare($channel, 'basic');
			$inQueueCount = $this->queueDeclare($channel, 'basic');
			$this->assertSame(0, $inQueueCount);
		}

		public function testQueueConsumerAutoAck()
		{
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);
			$this->exchangeDeclare($channel, 'basic');
			$inQueueCount = $this->queueDeclare($channel, 'basic');
			$this->assertSame(0, $inQueueCount);
			$this->queueBind($channel, 'basic');
			$this->queuePurge($channel, 'basic');

			$this->publishMessages($channel, false);

			$consumer = new AMQPTestCaseAutoAckQueueConsumer($channel);
			$consumer->setLimit(AMQPPeclTest::COUNT_OF_PUBLISH);

			$channel->basicConsume(
				self::$queueList['basic']['name'],
				true,
				$consumer
			);

			//observer logic test
			$this->assertSame('AC', $consumer->getCheckString());
			$this->assertEquals(self::COUNT_OF_PUBLISH, $consumer->getCount());

			//drop channels and close connection
			$c->disconnect();

			$c = new \Onphp\AMQPPecl(\Onphp\AMQPCredentials::createDefault());
			$channel = $c->createChannel(1);

			$this->exchangeDeclare($channel, 'basic');
			$inQueueCount = $this->queueDeclare($channel, 'basic');
			$this->assertSame(0, $inQueueCount);
		}

		
		public function testExchangeToExchangeProducerLogic()
		{
			$this->exchangeToExchangeCleanup();
			
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			$this->exchangeDeclare($channel, 'basic');
			$this->queueDeclare($channel, 'basic');
			$this->exchangeDeclare($channel, 'exchangeBinded');
			$this->queueDeclare($channel, 'exchangeBinded');
			$this->queuePurge($channel, 'basic');
			$this->queuePurge($channel, 'exchangeBinded');
			$this->queueBind($channel, 'basic');
			$this->queueBind($channel, 'exchangeBinded');

			/**
			 * binding exchangeBinded-exchange to basic-exchange
			 * by basic-routing key
			 */
			$channelInterface = $channel->exchangeBind(
				self::$queueList['basic']['exchange'],
				self::$queueList['exchangeBinded']['exchange'],
				self::$queueList['basic']['key']
			);
			$this->assertInstanceOf('\Onphp\AMQPChannelInterface', $channelInterface);

			/**
			 * publish messages to 2 queues throw exchangeBinded-exchange
			 * with basic-key
			 */
			for($i = 1; $i <= self::COUNT_OF_PUBLISH; $i++) {
				$channelInterface = $channel->basicPublish(
					self::$queueList['exchangeBinded']['exchange'],
					self::$queueList['basic']['key'],
					\Onphp\AMQPOutgoingMessage::create()->
						setBody("message {$i}")->
						setTimestamp(\Onphp\Timestamp::makeNow())->
						setAppId(__CLASS__)->
						setMessageId($i)->
						setContentEncoding('utf-8')
				);

				$this->assertInstanceOf(
					'\Onphp\AMQPChannelInterface',
					$channelInterface
				);
			}

			 // message count in basic-queue
			$this->checkMessageCount($channel);

			 // message count in exchangeBinded-queue
			$this->checkMessageCount($channel, 'exchangeBinded');
		}

		/**
		 * @depends testExchangeToExchangeProducerLogic
		**/
		public function testExchangeToExchangeConsumerLogic()
		{
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			$names = array(
				self::$queueList['basic']['name'],
				self::$queueList['exchangeBinded']['name']
			);
			foreach ($names as $name) {
				$i = 0;
				try {
					while($mess = $channel->basicGet($name)) 
						self::messageTest($mess, ++$i);
				} catch (\Onphp\ObjectNotFoundException $e) {/**/}
				
				$this->assertSame(
					self::COUNT_OF_PUBLISH,
					$i,
					"message count={$i} in {$name}-queue, must be equal to "
						.self::COUNT_OF_PUBLISH
				);
			}
		}

		public function testExchangeToExchangeCleanup()
		{
			$this->exchangeToExchangeCleanup();
		}

		protected function exchangeToExchangeCleanup()
		{
			$c = new \Onphp\AMQPPecl(
				\Onphp\AMQPCredentials::createDefault()
			);

			$channel = $c->createChannel(1);

			$this->exchangeDeclare($channel, 'basic');
			$this->exchangeDeclare($channel, 'exchangeBinded');
			$this->queueDeclare($channel, 'exchangeBinded');
			$this->queueDeclare($channel, 'basic');
			$this->queueBind($channel, 'basic');
			$this->queueBind($channel, 'exchangeBinded');

			$channel->queueBind(
				self::$queueList['basic']['name'],
				self::$queueList['exchangeBinded']['exchange'],
				self::$queueList['basic']['key']
			);

			$this->queueUnbind($channel, 'basic');
			$this->queueUnbind($channel, 'exchangeBinded');

			$channelInterface = $channel->queueUnbind(
				self::$queueList['basic']['name'],
				self::$queueList['exchangeBinded']['exchange'],
				self::$queueList['basic']['key']
			);
			$this->assertInstanceOf(
				'\Onphp\AMQPChannelInterface',
				$channelInterface
			);
			$this->queueDelete($channel, 'exchangeBinded');
			$this->queueDelete($channel, 'basic');

			$this->exchangeDelete($channel, 'basic');
			$this->exchangeDelete($channel, 'exchangeBinded');
		}

				/**
		 * @param \Onphp\AMQPChannelInterface $channel
		 * @param bool $check
		 * @param string $key
		 * @param string $queueName
		 */
		protected function publishMessages(\Onphp\AMQPChannelInterface $channel, $check = true,
			$label = 'basic'
		) {
			for($i = 1; $i <= self::COUNT_OF_PUBLISH; $i++) {
				$channelInterface = $channel->basicPublish(
					self::$queueList[$label]['exchange'],
					self::$queueList[$label]['key'],
					\Onphp\AMQPOutgoingMessage::create()->
						setBody("message {$i}")->
						setTimestamp(\Onphp\Timestamp::makeNow())->
						setAppId(__CLASS__)->
						setMessageId($i)->
						setContentEncoding('utf-8')
				);

				if ($check)
					$this->assertInstanceOf(
						'\Onphp\AMQPChannelInterface',
						$channelInterface
					);
			}

			if ($check)
				$this->checkMessageCount($channel, $label);
		}

		protected static function messageAssertion(\Onphp\AMQPIncomingMessage $mess, $i)
		{
			self::assertInstanceOf('\Onphp\AMQPIncomingMessage', $mess);
			self::assertInstanceOf('\Onphp\Timestamp', $mess->getTimestamp());
			self::assertTrue(strlen(trim($mess->getDeliveryTag())) > 0);

			$properties = $mess->getProperties();

			//self::assertEquals('guest', $mess->getUserId());
			self::assertTrue(
				isset($properties[\Onphp\AMQPIncomingMessage::USER_ID])
				&& $properties[\Onphp\AMQPIncomingMessage::USER_ID] ==
				$mess->getUserId()
			);

			self::assertEquals(__CLASS__, $mess->getAppId());
			self::assertTrue(
				isset($properties[\Onphp\AMQPIncomingMessage::APP_ID])
				&& $properties[\Onphp\AMQPIncomingMessage::APP_ID] ==
				$mess->getAppId()
			);

			self::assertEquals($i, $mess->getMessageId());
			self::assertTrue(
				isset($properties[\Onphp\AMQPIncomingMessage::MESSAGE_ID])
				&& $properties[\Onphp\AMQPIncomingMessage::MESSAGE_ID] ==
				$mess->getMessageId()
			);


			self::assertEquals('text/plain', $mess->getContentType());
			self::assertTrue(
				isset($properties[\Onphp\AMQPIncomingMessage::CONTENT_TYPE])
				&& $properties[\Onphp\AMQPIncomingMessage::CONTENT_TYPE] ==
				$mess->getContentType()
			);

			self::assertEquals('utf-8', $mess->getContentEncoding());
			self::assertTrue(
				isset($properties[\Onphp\AMQPIncomingMessage::CONTENT_ENCODING])
				&& $properties[\Onphp\AMQPIncomingMessage::CONTENT_ENCODING] ==
				$mess->getContentEncoding()
			);

			self::assertEquals("message {$i}", $mess->getBody());
		}

		/**
		 * @param \Onphp\AMQPPeclChannel $channel
		 * @param \Onphp\AMQPPeclChannel $label
		 * @return \Onphp\AMQPPeclChannel
		 */
		protected function exchangeDeclare(\Onphp\AMQPChannelInterface $channel, $label)
		{
			$this->assertTrue(isset(self::$queueList[$label]));
			
			$interface = $channel->exchangeDeclare(
				self::$queueList[$label]['exchange'],
				\Onphp\AMQPExchangeConfig::create()->
					setType(
						new \Onphp\AMQPExchangeType(self::$queueList[$label]['exchangeType'])
					)->
					setDurable(true)
			);

			$this->assertInstanceOf('\Onphp\AMQPChannelInterface', $interface);

			return $interface;
		}

		/**
		 * @param \Onphp\AMQPChannelInterface $channel
		 * @param string $label
		 * @return \Onphp\AMQPChannelInterface
		 */
		protected function exchangeDelete(\Onphp\AMQPChannelInterface $channel, $label)
		{
			$this->assertTrue(isset(self::$queueList[$label]));

			$channelInterface = $channel->exchangeDelete(
				self::$queueList[$label]['exchange']
			);

			$this->assertInstanceOf(
				'\Onphp\AMQPChannelInterface',
				$channelInterface
			);

			return $channelInterface;
		}
		
		/**
		 * @param \Onphp\AMQPChannelInterface $channel
		 * @param string $label
		 * @return int
		 */
		protected function queueDeclare(\Onphp\AMQPChannelInterface $channel, $label)
		{
			$this->assertTrue(isset(self::$queueList[$label]));

			return $channel->queueDeclare(
				self::$queueList[$label]['name'],
				\Onphp\AMQPQueueConfig::create()->
					setDurable(true)->
					setArguments(
						self::$queueList[$label]['args']
					)
			);
		}

		/**
		 * @param \Onphp\AMQPChannelInterface $channel
		 * @param string $label
		 * @return \Onphp\AMQPChannelInterface
		 */
		protected function queueBind(\Onphp\AMQPChannelInterface $channel, $label)
		{
			$this->assertTrue(isset(self::$queueList[$label]));

			$channelInterface = $channel->queueBind(
				self::$queueList[$label]['name'],
				self::$queueList[$label]['exchange'],
				self::$queueList[$label]['key']
			);
			
			$this->assertInstanceOf('\Onphp\AMQPChannelInterface', $channelInterface);

			return $channelInterface;
		}

		/**
		 * @param \Onphp\AMQPChannelInterface $channel
		 * @param string $label
		 * @return \Onphp\AMQPChannelInterface
		 */
		protected function queuePurge(\Onphp\AMQPChannelInterface $channel, $label)
		{
			$this->assertTrue(isset(self::$queueList[$label]));

			$channelInterface = $channel->queuePurge(self::$queueList[$label]['name']);

			$this->assertInstanceOf('\Onphp\AMQPChannelInterface', $channelInterface);

			return $channelInterface;
		}

		/**
		 * @param \Onphp\AMQPChannelInterface $channel
		 * @param \Onphp\AMQPPeclChannel $label
		 * @return \Onphp\AMQPChannelInterface
		 */
		protected function queueUnbind(\Onphp\AMQPChannelInterface $channel, $label)
		{
			$this->assertTrue(isset(self::$queueList[$label]));

			$channelInterface = $channel->queueUnbind(
				self::$queueList[$label]['name'],
				self::$queueList[$label]['exchange'],
				self::$queueList[$label]['key']
			);

			$this->assertInstanceOf(
				'\Onphp\AMQPChannelInterface',
				$channelInterface
			);

			return $channelInterface;
		}

		/**
		 * @param \Onphp\AMQPChannelInterface $channel
		 * @param string $label
		 * @return \Onphp\AMQPChannelInterface
		 */
		protected function queueDelete(\Onphp\AMQPChannelInterface $channel, $label)
		{
			$this->assertTrue(isset(self::$queueList[$label]));

			$channelInterface = $channel->queueDelete(
				self::$queueList[$label]['name']
			);

			$this->assertInstanceOf(
				'\Onphp\AMQPChannelInterface',
				$channelInterface
			);

			return $channelInterface;
		}

	}
?>