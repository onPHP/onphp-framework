<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @see Session
	 * 
	 * @ingroup Base
	**/
	namespace Onphp;

	final class SessionNotStartedException extends BaseException
	{
		public function __construct()
		{
			return
				parent::__construct(
					'start session before assign or access session variables'
				);
		}
	}
	
	/**
	 * Simple static wrapper around session_*() functions.
	 * 
	 * @ingroup Base
	**/
	final class Session extends StaticFactory
	{
		private static $isStarted = false;
		
		public static function start()
		{
			session_start();
			self::$isStarted = true;
		}
		
		/**
		 * @throws \Onphp\SessionNotStartedException
		**/
		/* void */ public static function destroy()
		{
			if (!self::$isStarted)
				throw new SessionNotStartedException();
			
			self::$isStarted = false;
			
			try {
				session_destroy();
			} catch (BaseException $e) {
				// stfu
			}
			
			setcookie(session_name(), null, 0, '/');
		}
		
		public static function flush()
		{
			return session_unset();
		}
		
		/**
		 * @throws \Onphp\SessionNotStartedException
		**/
		/* void */ public static function assign($var, $val)
		{
			if (!self::isStarted())
				throw new SessionNotStartedException();
			
			$_SESSION[$var] = $val;
		}
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @throws \Onphp\SessionNotStartedException
		**/
		public static function exist(/* ... */)
		{
			if (!self::isStarted())
				throw new SessionNotStartedException();
			
			if (!func_num_args())
				throw new WrongArgumentException('missing argument(s)');
			
			foreach (func_get_args() as $arg) {
				if (!isset($_SESSION[$arg]))
					return false;
			}
			
			return true;
		}
		
		/**
		 * @throws \Onphp\SessionNotStartedException
		**/
		public static function get($var)
		{
			if (!self::isStarted())
				throw new SessionNotStartedException();
			
			return isset($_SESSION[$var]) ? $_SESSION[$var] : null;
		}
		
		public static function &getAll()
		{
			return $_SESSION;
		}
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @throws \Onphp\SessionNotStartedException
		**/
		/* void */ public static function drop(/* ... */)
		{
			if (!self::isStarted())
				throw new SessionNotStartedException();
			
			if (!func_num_args())
				throw new WrongArgumentException('missing argument(s)');
			
			foreach (func_get_args() as $arg)
				unset($_SESSION[$arg]);
		}
		
		/**
		 * @throws \Onphp\SessionNotStartedException
		**/
		/* void */ public static function dropAll()
		{
			if (!self::isStarted())
				throw new SessionNotStartedException();
			
			if ($_SESSION) {
				foreach (array_keys($_SESSION) as $key) {
					self::drop($key);
				}
			}
		}
		
		public static function isStarted()
		{
			return self::$isStarted;
		}
		
		/**
		 * assigns to $_SESSION scope variables defined in given array
		**/
		/* void */ public static function arrayAssign($scope, $array)
		{
			Assert::isArray($array);
			
			foreach ($array as $var) {
				if (isset($scope[$var])) {
					$_SESSION[$var] = $scope[$var];
				}
			}
		}
		
		/**
		 * @throws \Onphp\SessionNotStartedException
		**/
		public static function getName()
		{
			if (!self::isStarted())
				throw new SessionNotStartedException();
			
			return session_name();
		}
		
		/**
		 * @throws \Onphp\SessionNotStartedException
		**/
		public static function getId()
		{
			if (!self::isStarted())
				throw new SessionNotStartedException();
			
			return session_id();
		}
	}
?>