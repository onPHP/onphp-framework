<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * URN is an absolute URI without authority part.
	 * 
	 * @ingroup Net
	**/
	namespace Onphp;

	final class Urn extends GenericUri
	{
		protected $schemeSpecificPart	= null;
		
		protected static $knownSubSchemes	= array(
			'urn'		=> '\Onphp\Urn',
			'mailto'	=> '\Onphp\Urn',
			'news'		=> '\Onphp\Urn',
			'isbn'		=> '\Onphp\Urn',
			'tel'		=> '\Onphp\Urn',
			'fax'		=> '\Onphp\Urn',
		);
		
		/**
		 * @ret\Onphp\Urn \Onphp\Urn
		**/
		public static function create()
		{
			return new self;
		}
		
		public static function getKnownSubSchemes()
		{
			return static::$knownSubSchemes;
		}
		
		public function isValid()
		{
			if (
				$this->scheme === null
				|| $this->getAuthority() !== null
			)
				return false;
			
			return parent::isValid();
		}
	}
?>