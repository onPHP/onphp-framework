<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OpenId
	**/
	final class OpenIdCredentials
	{
		private $claimedId	= null;
		private $realId		= null;
		private $server		= null;
		private $httpClient	= null;
		
		public function __construct(
			HttpUrl $claimedId,
			HttpClient $httpClient
		)
		{
			$this->claimedId = $claimedId->makeComparable();
			
			if (!$claimedId->isValid())
				throw new OpenIdException('invalid claimed id');
			
			$this->httpClient = $httpClient;
			
			$response = $httpClient->send(
				HttpRequest::create()->
				setMethod(HttpMethod::get())->
				setUrl($claimedId)
			);
			
			if ($response->getStatus()->getId() != 200) {
				throw new OpenIdException('can\'t fetch document');
			}
			
			$tokenizer = HtmlTokenizer::create(
					StringInputStream::create($response->getBody())
				)->
				lowercaseTags(true)->
				lowercaseAttributes(true);
			
			$insideHead = false;
			while ($token = $tokenizer->nextToken()) {
				if (!$insideHead) {
					if ($token instanceof SgmlOpenTag
						&& $token->getId() == 'head'
					) {
						$insideHead = true;
						continue;
					}
				}
				
				if ($insideHead) {
					if ($token instanceof SgmlEndTag && $token->getId() == 'head')
						break;
					
					if (
						$token instanceof SgmlOpenTag
						&& $token->getId() == 'link'
						&& $token->hasAttribute('rel')
						&& $token->hasAttribute('href')
					) {
						if ($token->getAttribute('rel') == 'openid.server')
							$this->server = HttpUrl::create()->parse(
								$token->getAttribute('href')
							);
						
						if ($token->getAttribute('rel') == 'openid.delegate')
							$this->realId = HttpUrl::create()->parse(
								$token->getAttribute('href')
							);
					}
				}
			}
			
			if (!$this->server || !$this->server->isValid())
				throw new OpenIdException('bad server');
			else
				$this->server->makeComparable();
			
			if (!$this->realId)
				$this->realId = $claimedId;
			elseif (!$this->realId->isValid())
				throw new OpenIdException('bad delegate');
			else
				$this->realId->makeComparable();
		}
		
		/**
		 * @return OpenIdCredentials
		**/
		public static function create(
			HttpUrl $claimedId,
			HttpClient $httpClient
		)
		{
			return new self($claimedId, $httpClient);
		}
		
		/**
		 * @return HttpUrl
		**/
		public function getRealId()
		{
			return $this->realId;
		}
		
		/**
		 * @return HttpUrl
		**/
		public function getServer()
		{
			return $this->server;
		}
	}
?>