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

	class SessionNotStartedException extends BaseException 
	{
		function __construct()
		{
			return
				parent::__construct(
					'start session before assign or access session variables'
				);
		}
	}

	final class Session
	{
		private static $isStarted = false;
		
		private function __construct() {/* bah! */}
		
		public static function start()
		{
			Session::$isStarted = true;
			session_start();
		}
		
		public static function destroy()
		{
			if (Session::$isStarted) {
				Session::$isStarted = false;
				try {
					session_destroy();
				} catch (BaseException $e) {
					// stfu
				}
				setcookie(session_name(), "", 0, "/");
			} else
				throw new SessionNotStartedException();
		}
		
		public static function flush()
		{
			return session_unset();
		}
		
		public static function assign($var, $val)
		{
			if (Session::isStarted())
				$_SESSION[$var] = $val;
			else 
				throw new SessionNotStartedException();
		}
		
		public static function exist()
		{
			if (Session::isStarted())
				if (func_num_args()) {
					foreach (func_get_args() as $arg) {
						if (!isset($_SESSION[$arg]))
							return false;
					}
					return true;
				} else
					throw new WrongArgumentException('argument(s) missing');

			throw new SessionNotStartedException();
		}
		
		public static function get($var)
		{
			if (Session::isStarted())
				return
					isset($_SESSION[$var]) ? $_SESSION[$var] : null;
			else
				throw new SessionNotStartedException();
		}
		
		public static function &getAll()
		{
			return $_SESSION;
		}
		
		public static function drop()
		{
			if (Session::isStarted())
				if (func_num_args())
					foreach (func_get_args() as $arg)
						unset($_SESSION[$arg]);
				else
					throw new WrongArgumentException('argument(s) missing');
			else 
				throw new SessionNotStartedException();
		}
		
		public static function isStarted()
		{
			return Session::$isStarted;
		}
		
		/**
		 * Assigns to $_SESSION scope variables defined in vars
		 * 
		 * @param	array	source scope
		 * @param	array	list of key names in scope which should be imported
		 * @access	public
		 * @return	void
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