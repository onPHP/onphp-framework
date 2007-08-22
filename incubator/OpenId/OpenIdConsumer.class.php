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
/* $Id$ */

	/**
	 * openId consumer library entry point
	 * 
	 * @see http://openid.net/specs/openid-authentication-1_1.html
	**/
	final class OpenIdConsumer
	{
		const DIFFIE_HELLMAN_P = '155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638443';
		const DIFFIE_HELLMAN_G = 2;
		
		private $randomSource = null;
		private $numberFactory = null;
		private $httpClient = null;
		
		public function __construct(
			RandomSource $randomSource,
			BigNumberFactory $numberFactory,
			HttpClient $httpClient
		) {
			$this->randomSource = $randomSource;
			$this->numberFactory = $numberFactory;
			$this->httpClient = $httpClient;
		}
		
		/**
		 * @return OpenIdConsumer
		**/
		public static function create(
			RandomSource $randomSource,
			BigNumberFactory $numberFactory,
			HttpClient $httpClient
		) {
			return new self($randomSource, $numberFactory, $httpClient);
		}
		
		/**
		 * "associate" mode request
		 * 
		 * @param $server to make association with (usually obtained from OpenIdCredentials)
		 * @param $manager - dao-like association manager
		 * @return OpenIdConsumerAssociation
		**/
		public function associate(HttpUrl $server, OpenIdConsumerAssociationManager $manager)
		{
			Assert::isTrue($server->isValid());
			
			if ($association = $manager->findByServer($server))
				return $association;
			
			$dhParameters = new DiffieHellmanParameters(
				$this->numberFactory->makeNumber(self::DIFFIE_HELLMAN_G),
				$this->numberFactory->makeNumber(self::DIFFIE_HELLMAN_P)
			);
			$keyPair = DiffieHellmanKeyPair::generate(
				$dhParameters,
				$this->randomSource
			);
			
			$request = HttpRequest::create()->
				setMethod(HttpMethod::post())->
				setUrl($server)->
				setPostVar('openid.mode', 'associate')->
				setPostVar('openid.assoc_type', 'HMAC-SHA1')->
				setPostVar('openid.session_type', 'DH-SHA1')->
				setPostVar(
					'openid.dh_modulus',
					base64_encode($dhParameters->getModulus()->toBinary())
				)->
				setPostVar(
					'openid.dh_gen',
					base64_encode($dhParameters->getGen()->toBinary())
				)->
				setPostVar(
					'openid.dh_consumer_public',
					base64_encode($keyPair->getPublic()->toBinary())
				);
			
			$response = $this->httpClient->send($request);
			if ($response->getStatus()->getId() != HttpStatus::CODE_200)
				throw new OpenIdException('bad response code from server');
			
			$result = $this->parseKeyValueFormat($response->getBody());
			
			if (empty($result['assoc_handle']))
				throw new OpenIdException('can\t live without handle');
			
			if (!isset($result['assoc_type']) || $result['assoc_type'] !== 'HMAC-SHA1')
				throw new OpenIdException('bad association type');
			
			if (!is_numeric($result['expires_in']))
				throw new OpenIdException('bad expires');
				
			if (
				isset($result['session_type']) 
				&& $result['session_type'] == 'DH-SHA1'
				&& isset($result['dh_server_public'])
			) {
				$secret = 
					sha1(
						$keyPair->
							makeSharedKey(
								$this->numberFactory->makeNumber(
									base64_decode($result['dh_server_public'])
								)
							)->
							toBinary(),
						true
					)
					^ base64_decode($result['enc_mac_key']);
			} elseif (
				(
					!isset($result['session_type']) 
					|| empty($result['session_type'])
				)
				&& isset($result['mac_key'])
			) {
				$secret = base64_decode($result['mac_key']);
			} else {
				throw new OpenIdException('no secret in answer');
			}
			
			return $manager->makeAndSave(
				$result['assoc_handle'],
				$result['assoc_type'],
				$secret,
				Timestamp::makeNow()->
					modify('+ '.$result['expires_in'].' seconds'),
				$server
			);
		}
		
		/**
		 * "checkid_immediate" mode request
		 * 
		 * @param $credentials - id and server urls
		 * @param $returnTo - URL where the provider should return the User-Agent back to
		 * @param $trustRoot - URL the Provider shall ask the End User to trust
		 * @param $association - result of associate call in smart mode
		 * @return ModelAndView
		**/
		public function checkIdImmediate(
			OpenIdCredentials $credentials,
			HttpUrl $returnTo, 
			$trustRoot = null,
			$association = null
		) {
			Assert::isTrue($returnTo->isValid());
			
			$view = RedirectView::create(
				$credentials->getServer()->toString()
			);
			$model = Model::create()->
				set('openid.mode', 'checkid_immediate')->
				set(
					'openid.identity',
					$credentials->getRealId()->toString()
				)->
				set(
					'openid.return_to',
					$returnTo->toString()
				);
			
			if ($association) {
				Assert::isTrue(
					$association instanceof OpenIdConsumerAssociation
					&& $association->getServer()->toString() 
						== $credentials->getServer()->toString()
				);
				$model->set(
					'openid.assoc_handle',
					$association->getHandle()
				);
			}
			
			if ($trustRoot) {
				Assert::isTrue(
					$trustRoot instanceof HttpUrl 
					&& $trustRoot->isValid()
				);
				$model->set(
					'openid.trust_root', 
					$trustRoot->toString()
				);
			}
			
			return ModelAndView::create()->setModel($model)->setView($view);
		}
		
		/**
		 * proceed results of checkid_immediate and checkid_setup
		 * 
		 * @param $request incoming request
		 * @param 
		**/
		public function doContinue(HttpRequest $request, $manager = null)
		{
			if ($manager)
				Assert::isTrue($manager instanceof OpenIdConsumerAssociationManager);
				
			$parameters = $this->parseGetParameters($request->getGet());
			if (!isset($parameters['openid.mode']))
				throw new WrongArgumentException('not an openid request');
			
			if ($parameters['openid.mode'] == 'id_res') {
				if (isset($parameters['openid.user_setup_url'])) {
					$setupUrl = HttpUrl::create()->parse(
						$parameters['openid.user_setup_url']
					);
					Assert::isTrue($setupUrl->isValid());
					return new OpenIdConsumerSetupRequired($setupUrl);
				}
			} elseif ($parameters['openid.mode'] = 'cancel') {
				return new OpenIdConsumerCancel();
			}
			
			throw new UnimplementedFeatureException('handle positive result');
		}
		
		private function parseKeyValueFormat($raw)
		{
			$result = array();
			$lines = explode("\n", $raw);
			foreach ($lines as $line) {
				if (!empty($line) && strpos($line, ':') !== false) {
					list($key, $value) = explode(':', $line, 2);
					$result[trim($key)] = trim($value);
				}
			}
			return $result;
		}
		
		private function parseGetParameters(/* array */ $get)
		{
			$result = array();
			foreach ($get as $key => $value) {
				if (strpos($key, 'openid') === 0) {
					$key = preg_replace('/^openid_/', 'openid.', $key);
					$result[$key] = $value;
				}
			}
			return $result;
		}
	}
?>