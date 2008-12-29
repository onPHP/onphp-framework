<?php
/***************************************************************************
 *   Copyright (C) 2008 by Tsyrulnik Y. Viatcheslav                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class GoogleGeoPoint
	{
		protected $lat;
		protected $lng;
		protected $z;

		public function __construct($lat, $lng, $z = 0)
		{
			$this->setLatitude($lat);
			$this->setLongitude($lng);
			$this->setZ($z);
		}

		public function getZ()
		{
			return $this->z;
		}

		public function getLatitude()
		{
			return $this->lat;
		}

		public function getLongitude()
		{
			return $this->lng;
		}

		public function setLatitude($lat)
		{
			$this->lat = is_float($lat) ? $lat : null;
			return $this;
		}

		public function setLongitude($lng)
		{
			$this->lng = is_float($lng) ? $lng : null;
			return $this;
		}

		public function setZ($z = 0)
		{
			$this->z = $z;
			return $this;
		}
	}
?>