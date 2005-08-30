<?php
/***************************************************************************
 *   Copyright (C) 2005 by Sveta Smirnova                                  *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
 /* $Id$ **/

	/**
	 * Contains class for net query processing and UnsupportedMethodException class
	 * 
	 * @package		Unit
	 * @author		Sveta Smirnova <sveta@microbecal.com>
	 * @version		1.0
	 * @copyright	2005
	**/

	/**
	 * Processes query and returns result
	 * 
	 * @package		Unit
	 * @author		Sveta Smirnova <sveta@microbecal.com>
	 * @version		1.0
	 * @copyright	2005
	**/
	class NetQuery
	{
		/**
		 * Default protocol
		**/
		const DEFAULT_PROTOCOL = 'http://';

		/**
		 * Default port
		**/
		const DEFAULT_PORT = 80;

		/**
		 * @var		string
		 * @access	private
		**/
		private $protocol = NetQuery::DEFAULT_PROTOCOL;

		/**
		 * @var		string
		 * @access	private
		**/
		private $host;

		/**
		 * @var     integer
		 * @access  private
		**/
		private $port = NetQuery::DEFAULT_PORT;

		/**
		 * @var		string
		 * @access	private
		**/
		private $rn = "\r\n";

		/**
		 * Constructor: sets necessary values
		 * 
		 * @param	string  host
		 * @param	integer port
		 * @param	string  protocol
		 * @access	public
		**/
		public function __construct($host, $port = NetQuery::DEFAULT_PORT, $protocol = NetQuery::DEFAULT_PROTOCOL)
		{
			$this->protocol    = $protocol;
			$this->host        = $host;
			$this->port        = $port;
		}

		/**
		 * Processes query
		 * 
		 * @param	string	query string
		 * @param	string	relative url from site root
		 * @param	string	method
		 * @param	boolean	if true, cleans header
		 * @access	public
		 * @return	string	query result
		**/
		public function query($query, $url, $method = 'get', $noheader = true)
		{
			$method = strtolower($method);
			$cookie = '';

			if (method_exists($this, $method)) {
				$run = true;

				do {
					$result = call_user_func(array($this, $method), $query, $url, $cookie);
					$matches = array();

					if (preg_match(
							'/Location:\s+http:\/\/(\S+)\/(\S+)\s*\r\n/smi',
							$result,
							$matches)) {
						$url = $matches[2];
						$method = 'get';
						$query = '';

						if (preg_match(
								'/Set-Cookie: (PHPSESSID=([\w|\d]+));.*?\r\n/smi',
								$result,
								$matches)) {
							$cookie = "Cookie: {$matches[1]}$this->rn";
						}
					} else {
						$run = false;
					}
				} while ($run);

				if ($noheader) {
					return preg_replace('/^.*?\r\n\r\n/s', '', $result);
				} else {
					return $result;
				}
			} else {
				throw new UnsupportedMethodException($method);
			}
		}

		/**
		 * Processes GET query
		 * 
		 * @param	string	query string
		 * @param	string	url
		 * @param	string	cookie string
		 * @access	private
		 * @return	string	query result
		**/
		private function get($query, $url, $cookie = '')
		{
			$socket = $this->getSocket();
				
			if ($query) {
				$out = "GET /$url?&$query HTTP/1.0$this->rn";
			} else {
				$out = "GET /$url HTTP/1.0$this->rn";
			}
			$out .= 'Host: ' . $this->host . $this->rn;
			$out .= "User-Agent: NetQuery$this->rn";
			$out .= $cookie;
			$out .= $this->rn;
			
			return $this->getQueryResult($socket, $out);
		}

		/**
		 * Processes POST query
		 * 
		 * @param	string	query string
		 * @param	string	url
		 * @param	string	cookie string
		 * @access	private
		 * @return	string	query result
		**/
		private function post($query, $url, $cookie = '')
		{
			try {
				$socket	= $this->getSocket();
				$length	= strlen($query);
				$out	= "POST /$url HTTP/1.0$this->rn";
				$out 	.= 'Host: ' . $this->host . $this->rn;
				$out 	.= "User-Agent: NetQuery$this->rn";
				$out	.= $cookie;
				$out	.= "Content-Type: application/x-www-form-urlencoded$this->rn";
				$out	.= "Content-Length: $length$this->rn$this->rn";
				$out	.= "$query$this->rn";

				return $this->getQueryResult($socket, $out);
			} catch (Exception $e) {
				return null;
			}
		}

		/**
		 * Connects to socket specified by $this->host and $this->port
		 * 
		 * @param	resource	socket pointer
		 * @param	string		write data to socket
		 * @access	private
		 * @return	string
		**/
		private function getQueryResult($socket, $out)
		{
			$result = '';
			socket_write($socket, $out, strlen($out));
			while ($out = socket_read($socket, 2048)) {
				$result .= $out;
			}
			
			socket_close($socket);
			return $result;
		}

		/**
		 * Connects to socket specified by $this->host and $this->port
		 * 
		 * @access	private
		 * @return	resource    a file pointer
		 * @see		<http://php.net/fsockopen>
		**/
		private function getSocket()
		{
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			socket_connect($socket, $this->host, $this->port);
			return $socket;
		}
	}
?>