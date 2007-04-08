<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @see Session
	 * 
	 * @ingroup Base
	**/
	final class SessionNotStartedException extends BaseException
	{
		function __construct()
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
		 * @throws SessionNotStartedException
		**/
		public static function destroy()
		{
			if (self::$isStarted) {
				self::$isStarted = false;
				try {
					session_destroy();
				} catch (BaseException $e) {
					// stfu
				}
				setcookie(session_name(), null, 0, '/');
			} else
				throw new SessionNotStartedException();
		}
		
		public static function flush()
		{
			return session_unset();
		}
		
		/**
		 * @throws SessionNotStartedException
		**/
		public static function assign($var, $val)
		{
			if (self::isStarted())
				$_SESSION[$var] = $val;
			else 
				throw new SessionNotStartedException();
		}
		
		/**
		 * @throws WrongArgumentException
		 * @throws SessionNotStartedException
		**/
		public static function exist(/* ... */)
		{
			if (self::isStarted())
				if (func_num_args()) {
					foreach (func_get_args() as $arg) {
						if (!isset($_SESSION[$arg]))
							return false;
					}
					return true;
				} else
					throw new WrongArgumentException('missing argument(s)');

			throw new SessionNotStartedException();
		}
		
		/**
		 * @throws SessionNotStartedException
		**/
		public static function get($var)
		{
			if (self::isStarted())
				return isset($_SESSION[$var]) ? $_SESSION[$var] : null;
			else
				throw new SessionNotStartedException();
		}
		
		public static function &getAll()
		{
			return $_SESSION;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @throws SessionNotStartedException
		**/
		public static function drop(/* ... */)
		{
			if (self::isStarted()) {
				if (func_num_args())
					foreach (func_get_args() as $arg)
						unset($_SESSION[$arg]);
				else
					throw new WrongArgumentException('missing argument(s)');
			} else
				throw new SessionNotStartedException();
		}
		
		/**
		 * @throws SessionNotStartedException
		**/
		public static function dropAll()
		{
			if (self::isStarted()) {
				if ($_SESSION) {
					foreach (array_keys($_SESSION) as $key) {
						self::drop($key);
					}
				}
			} else
				throw new SessionNotStartedException();
		}
		
		public static function isStarted()
		{
			return self::$isStarted;
		}
		
		/**
		 * assigns to $_SESSION scope variables defined in given array
		**/
		public static function arrayAssign(&$scope, $array)
		{
			Assert::isArray($array);
			
			foreach ($array as &$var) {
				if (isset($scope[$var])) {
					$_SESSION[$var] = $scope[$var];
				}
			}
		}
	}
?>