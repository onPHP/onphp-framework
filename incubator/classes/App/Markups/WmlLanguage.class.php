<?
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
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
		 * @returns WmlMarkupDocument
		**/
		public function parse($data)
		{
			return new UnimplementedFeatureException();
		}

		/**
		 * @returns string
		**/
		public function render(WmlMarkupDocument $data)
		{
			return new UnimplementedFeatureException();
		}
	}
?>