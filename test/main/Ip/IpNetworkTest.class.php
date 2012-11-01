<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class IpNetworktest extends TestCase
	{
		public function testContains()
		{
			$IpNetwork =
				\Onphp\IpNetwork::create(
					\Onphp\IpAddress::create('83.149.5.0'),
					24
				);

			$this->assertTrue(
				$IpNetwork->contains(
					\Onphp\IpAddress::create('83.149.5.0')
				)
			);
			
			$this->assertTrue(
				$IpNetwork->contains(
					\Onphp\IpAddress::create('83.149.5.1')
				)
			);
			
			$this->assertTrue(
				$IpNetwork->contains(
					\Onphp\IpAddress::create('83.149.5.4')
				)
			);
			
			$this->assertTrue(
				$IpNetwork->contains(
					\Onphp\IpAddress::create('83.149.5.255')
				)
			);
			
			$this->assertFalse(
				$IpNetwork->contains(
					\Onphp\IpAddress::create('83.149.4.255')
				)
			);
			
			$this->assertFalse(
				$IpNetwork->contains(
					\Onphp\IpAddress::create('83.149.6.1')
				)
			);
			
			$IpNetwork =
				\Onphp\IpNetwork::create(
					\Onphp\IpAddress::create('83.149.24.64'),
					26
				);
				
			$this->assertTrue(
				$IpNetwork->contains(
					\Onphp\IpAddress::create('83.149.24.64')
				)
			);
			
			$this->assertTrue(
				$IpNetwork->contains(
					\Onphp\IpAddress::create('83.149.24.66')
				)
			);
			
			$this->assertTrue(
				$IpNetwork->contains(
					\Onphp\IpAddress::create('83.149.24.127')
				)
			);
			
			$this->assertFalse(
				$IpNetwork->contains(
					\Onphp\IpAddress::create('83.149.24.63')
				)
			);
			
			$this->assertFalse(
				$IpNetwork->contains(
					\Onphp\IpAddress::create('83.149.25.64')
				)
			);
		}
	}
?>