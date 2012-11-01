<?php
/***************************************************************************
 *   Copyright (C) by Georgiy T. Kutsurua                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 ***************************************************************************/

	/**
	 * Timestamp with time zone
	 */
	namespace Onphp;

	class TimestampTZ extends Timestamp
	{
		/**
		 * @static
		 * @return string
		 */
		protected static function getFormat()
		{
			return 'Y-m-d H:i:sO';
		}

		/**
		 * @return \Onphp\Timestamp
		**/
		public function toTimestamp($zone=null)
		{
			if($zone) {

				if(
					!($zone instanceof \DateTimeZone)
					&& is_scalar($zone)
				) {
					$zone = new \DateTimeZone($zone);
				}

				return new static($this->toStamp(), $zone);
			}

			return  parent::toTimestamp();
		}

		public static function compare(Date $left, Date $right)
		{
			Assert::isTrue(
				(
					$left instanceof TimestampTZ
					&& $right instanceof TimestampTZ
				)
			);

			return parent::compare($left, $right);
		}
	}
?>