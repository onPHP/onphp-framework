<?php
/***************************************************************************
 *   Copyright (C) 2008 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp;

	interface Router
	{
		/**
		 * Processes a request and sets its controller and action.
		 * If no route was possible, an exception is thrown.
		 * 
		 * @param  HttpRequest
		 * @throws \Onphp\RouterException
		 * @return \Onphp\HttpRequest | boolean
		**/
		public function route(HttpRequest $request);
		
		/**
		 * Generates an URL path that can be used in
		 * URL creation, redirection, etc.
		 * 
		 * May be passed user params to override ones from URI,
		 * Request or even defaults.
		 * If passed parameter has a value of null, it's URL variable
		 * will be reset to default.
		 * 
		 * If null is passed as a route name assemble will use
		 * the current Route or 'default' if current is not yet set.
		 * 
		 * Reset is used to signal that all parameters
		 * must be reset to it's defaults.
		 * 
		 * Ignoring all URL specified values.
		 * User specified params still get precedence.
		 * 
		 * Encode tells to url encode resulting path parts.
		 * 
		 * @param  array $userParams Options passed by an user
		 * used to override parameters
		 * 
		 * @param  mixed $name The name of a Route to use
		 * @param  bool $reset Whether to reset to the route defaults
		 * ignoring URL params
		 * 
		 * @param  bool $encode Tells to encode URL parts on output
		 * @throws \Onphp\RouterException
		 * @return string Resulting URL path
		**/
		public function assembly(
			array $userParams = array(),
			$name = null,
			$reset = false,
			$encode = true
		);
	}
?>