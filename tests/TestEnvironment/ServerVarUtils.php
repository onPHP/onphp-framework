<?php
	/** $Id$ **/

	class ServerVarUtils extends StaticFactory
	{
		public static function build(&$serverVars, $url)
		{
			$urlParts = parse_url($url);

			if (stripos($url, 'https')) {
				$serverVars['HTTPS'] = 'on';
				$serverVars['SERVER_PORT'] = 443;
			} else {
				$serverVars['SERVER_PORT'] = 80;
			}
			
			if (!empty($urlParts['scheme']))
				$serverVars['SERVER_PROTOCOL'] = strtoupper($urlParts['scheme']).'/1.1';

			if (!empty($urlParts['host']))
				$serverVars['HTTP_HOST'] = $urlParts['host'];

			if (!empty($urlParts['port']))
				$serverVars['HTTP_HOST'] .= ':'.$urlParts['port'];

			if (!empty($urlParts['path']))
				$serverVars['REQUEST_URI'] = $urlParts['path'];

			if (!empty($urlParts['query']))
				$serverVars['REQUEST_URI'] .= '?'.$urlParts['query'];

			if (!empty($urlParts['fragment']))
				$serverVars['REQUEST_URI'] .= '#'.$urlParts['fragment'];
		}

		public static function unsetVars(&$serverVars)
		{
			unset($serverVars['HTTPS']);
			unset($serverVars['SERVER_PORT']);
			unset($serverVars['SERVER_PROTOCOL']);
			unset($serverVars['HTTP_HOST']);
			unset($serverVars['REQUEST_URI']);
		}
	}
?>