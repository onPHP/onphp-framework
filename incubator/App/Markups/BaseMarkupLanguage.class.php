<?
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	abstract class BaseMarkupLanguage
	{
		// redefine me
		protected $commonName	= null;
		protected $versions		= array();
		protected $version		= null;
		
		abstract public function create();

		/**
		 * @returns MarkupDocument
		**/
		abstract public function parse($data);

		/**
		 * @returns string
		**/
		abstract public function render(MarkupDocument $data);

		public function getCommonName()
		{
			return $this->getCommonName();
		}

		public function setVersion($version)
		{
			if (!isset($versions[$versions]))
				throw
					new WrongArgumentException(
						"dont know nothing about version == {{$version}}"
					);

			$this->version = $version;

			return $this;
		}

		public function getVersion()
		{
			return $this->version;
		}
	}
?>