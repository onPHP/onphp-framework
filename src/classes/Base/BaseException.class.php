<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
 
	class BaseException extends Exception
	{
		protected $message 	= 'Base exception';

		/**
		 * @var		integer		user defined exception code
		 * @access	protected
		 */
		protected $code 	= 0;
		
		/**
		 * @var		string		source filename of exception
		 * @access	protected
		 */
		protected $file		= null;
		
		/**
		 * @var		integer		source line of exception
		 * @access	protected
		 */
		protected $line		= 0;

		private $trace		= null;
		private $string		= null;
		
		public function __construct($message = null, $code = 0, $file = null, $line = null)
		{
			parent::__construct($message, $code);
			
			if ($message)
				$this->message = $message;

			$this->code 	= $code;
			$this->trace	= debug_backtrace();
			
			if ($file)
				$this->file		= $file;
			else
				$this->file		= $this->trace[0]['file'];

			if ($line)
				$this->line		= $line;
			else
				$this->line		= $this->trace[0]['line'];

			$this->string	= $this->__toString();
		}
		
		function __toString()
		{
			return
				"[$this->message] in: \n".
				var_export($this->trace, true);
		}
	}
	
	class WrongArgumentException		extends BaseException {}
	class DatabaseException				extends BaseException {}
	class WrongStateException			extends BaseException {}
	class UnimplementedFeatureException	extends BaseException {}
	
	class ObjectNotFoundException		extends DatabaseException {}
	class DuplicateObjectException		extends DatabaseException {}

	class BusinessLogicException		extends Exception {}
	class NetworkException				extends Exception {}
?>