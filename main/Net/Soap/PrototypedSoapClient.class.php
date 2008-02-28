<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Quick reference:
	 * 
	 * 1. extend this class
	 * 
	 * 2. redefine wsdlUrl and classMap ('complexType' => 'DtoClass')
	 * 
	 * 3. make DtoProtos, Dtos and Business classes for your Xsd objects
	 *    and exception classes for your faults
	 * 
	 * 4. implement your methods, corresponding to operations in wsdl, in such
	 *    manner:
	 *
	 *	public function login(LoginRequest $request)
	 *	{
	 *		// preparations...
	 *		
	 *		$result = $this->call(
	 *			'login', $request, 'LoginResponse'
	 *		);
	 *		
	 *		// additional asserts...
	 *		
	 *		return $result;
	 *	}
	 *
	 *	5. implement logCall(), if you need debugging output
	 * 
	**/
	abstract class PrototypedSoapClient
	{
		protected $wsdlUrl		= null;
		protected $classMap		= array();
		
		protected $soapClient	= null;
		
		final public static function convertSoapFault(SoapFault $e)
		{
			$r = new ReflectionObject($e);
			
			if (!$r->hasProperty('detail') || !($e->detail instanceof stdClass))
				return $e;
			
			$r = new ReflectionObject($e->detail);
			
			if (
				$r->hasProperty('exception')
				&& $e->detail->exception instanceof SoapVar
			) {
				$exception = $e->detail->exception->enc_value;
				
				Assert::isInstance($exception, 'BaseException');
				
				return $exception;
			}
			
			return $e;
		}
		
		public function __construct()
		{
			$wsdlUrl = $this->getWsdlUrl();
			
			Assert::isNotNull($wsdlUrl);
			
			$this->soapClient = new SoapClient(
				$wsdlUrl,
				array(
					'soap_version'	=> SOAP_1_1,
					'classmap'		=> $this->classMap(),
					
					// TODO:?
					/*
					'compression'	=> SOAP_COMPRESSION_ACCEPT
						| SOAP_COMPRESSION_GZIP
					*/
					
					'trace'			=> true,
					'exceptions'	=> true
				)
			);
		}
		
		public function getWsdlUrl()
		{
			return $this->wsdlUrl;
		}
		
		public function classMap()
		{
			return $this->classMap;
		}
		
		protected function call($method, DTOMessage $request, $resultClass)
		{
			$requestDto = $request->makeDto();
			
			Assert::isInstance($requestDto, 'DTOClass');
			
			if (defined('__LOCAL_DEBUG__') && !defined('SIMPLE_TEST') ) {
				// self-validation
				
				$form = ObjectToFormConverter::create($request->dtoProto())->
					make($request);
				
				Assert::isTrue(
					!$form->getErrors()
					&& $request->dtoProto()->
						validate($request, $form),
					
					Assert::dumpArgument($request)
					."\n"
					.Assert::dumpArgument($form->getInnerErrors())
				);
			}
			
			try {
				try {
					
					$resultDto = $this->getSoapClient()->$method($requestDto);
					
				} catch (BaseException $e) {
					
					if (get_class($e) == 'BaseException') {
						throw new SoapFault(
							'Server',
							get_class($e).': '.$e->getMessage()
						);
						
					} else {
						$this->logCall();
						throw $e;
					}
				}
				
			} catch (SoapFault $e) {
				
				$this->logCall();
				throw self::convertSoapFault($e);
			}
			
			$this->logCall();
			
			if (!$resultClass) {
				Assert::isNull($resultDto);
				$result = null;
				
			} else {
				Assert::isInstance($resultDto, 'DTOClass');
				
				Assert::isEqual(
					$resultDto->dtoProto()->className(),
					$resultClass
				);
				
				$form = $resultDto->toForm();
				
				Assert::isTrue(
					!$form->getErrors(),
					
					Assert::dumpArgument($resultDto)
					."\n"
					.Assert::dumpArgument($form->getInnerErrors())
				);
				
				$result = $resultDto->makeObject($form);
				
				Assert::isInstance($result, 'DTOMessage');
				
				Assert::isEqual(get_class($result), $resultClass);
				
				Assert::isTrue(
					$result->dtoProto()->
						validate($result, $form),
						
					Assert::dumpArgument($result)
					."\n"
					.Assert::dumpArgument($form->getInnerErrors())
				);
			}
			
			return $result;
		}
		
		protected function getLastRequestCdata()
		{
			return $this->getXmlCdata(
				$this->getSoapClient()->__getLastRequest()
			);
		}
		
		protected function getLastResponseCdata()
		{
			return $this->getXmlCdata(
				$this->getSoapClient()->__getLastResponse()
			);
		}
		
		/**
		 * 
		 * The place for calling getLastRequestCdata() and
		 * getLastResponseCdata().
		 * 
		**/
		protected function logCall()
		{
			return $this;
		}
		
		private function getXmlCdata($xml)
		{
			return "<![CDATA[\n".$xml.']]>';
		}
		
		/**
		 * ONLY FOR TESTING:
		 * {{{
		**/
		
		/**
		 * @return SoapClient
		**/
		public function getSoapClient()
		{
			return $this->soapClient;
		}
		
		public function __getLastRequestHeaders()
		{
			return $this->getSoapClient()->__getLastRequestHeaders();
		}
		
		public function __getLastResponseHeaders()
		{
			return $this->getSoapClient()->__getLastResponseHeaders();
		}
		
		public function __getLastResponse()
		{
			return $this->getSoapClient()->__getLastResponse();
		}
		
		public function __getLastRequest()
		{
			return $this->getSoapClient()->__getLastRequest();
		}
		
		/**
		 * }}}
		**/
	}
?>