<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class WmlLanguage extends BaseMarkupLanguage
	{
		const VER_1_1	= 11;
		const VER_1_3	= 13;

		protected $commonName	= 'wml';

		protected $versions		= array(
			self::VER_1_1	=> true,
			self::VER_1_3	=> true
		);

		protected $version		= self::VER_1_3;

		private $imgExtensions	= array(
			self::VER_1_1	=> 'wbmp',
			self::VER_1_3	=> 'gif'
		);

		private $imgExtension	= 'gif';
		
		/**
		 * @return WmlLanguage
		**/
		public function create()
		{
			return new self;
		}

		public function setVersion($version)
		{
			parent::setVersion($version);

			$this->imgExtension = $this->imgExtensions[$version];

			return $this;
		}

		/**
		 * @return WmlMarkupDocument
		**/
		public function parse($data)
		{
			return new UnimplementedFeatureException();
		}

		public function render(WmlMarkupDocument $data)
		{
			return new UnimplementedFeatureException();
		}
	}
?>