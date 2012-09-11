<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class UuidUtilsTest extends TestCase
	{
		public function testMain()
		{
			if(!UuidUtils::isExtensionLoaded() )
				$this->markTestSkipped('uuid module is not supported, skipped!');
			else {

				/*
				 * time based uuid
				 */
				$uuid = UuidUtils::make();

				try{
					Assert::isUuid($uuid);
				} catch(WrongArgumentException $e) {
					$this->fail('UuidUtils::make generate uncorrectly id "'.$uuid.'"');
				}

			}

		}
	}
?>