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
	 * @ingroup Primitives
	**/
	final class PrimitiveDomDocument extends PrimitiveString
	{
		const ERROR_PARSING_FAILED		= 5104;
		const ERROR_VALIDATION_FAILED	= 5105; // yanetut, magic consts
		
		const CACHE_PATH = 'xsd-cache/';
		
		private $schemaPath		= null;
		private $schemaContent	= null;
		
		private $errorLabel	= null;
		
		/**
		 * @return PrimitiveDomDocument
		 * 
		 * NOTE: take care of setting proper xmlns and targetNamespace both in
		 * XSD and XML
		**/
		public function ofXsd($schemaPath)
		{
			if (is_file($schemaPath)) {
				$this->schemaPath = $schemaPath;
				$this->schemaContent = null;
				
				return $this;
			}
			
			$cache = self::getCache();
			
			$cacheKey = md5($schemaPath);
			
			$schemaContent = $cache->get($cacheKey);
			
			if (!$schemaContent) {
				try {
					$handle = fopen($schemaPath, 'rb');
					$schemaContent = stream_get_contents($handle);
					fclose($handle);
				} catch (BaseException $e) {
					throw new IOException($e->getMessage());
				}
				
				$cache->set(
					$cacheKey, $schemaContent,
					Cache::EXPIRES_FOREVER
				);
			}
			
			$this->schemaPath = $schemaPath;
			$this->schemaContent = $schemaContent;
			
			return $this;
		}
		
		public function import(array $scope)
		{
			if (!$result = parent::import($scope))
				return $result;
			
			$rawValue = $this->value;
			
			$this->value = new DomDocument;
			
			try {
				
				$this->value->loadXML($rawValue);
				
			} catch (BaseException $e) {
				$this->value = null;
				
				$this->error = self::ERROR_PARSING_FAILED;
				$this->errorLabel = $e->getMessage();
				
				return false;
			}
			
			if ($this->schemaPath) {
				try {
					
					if ($this->schemaContent)
						$this->value->schemaValidateSource(
							$this->schemaContent
						);
					else
						$this->value->schemaValidate(
							$this->schemaPath
						);
					
				} catch (BaseException $e) {
					$this->value = null;
					
					$this->error = self::ERROR_VALIDATION_FAILED;
					$this->errorLabel = $e->getMessage();
					
					return false;
				}
			}
			
			return true;
		}
		
		public function getErrorLabel()
		{
			return $this->errorLabel;
		}
		
		/**
		 * @return RubberFileSystem
		**/
		private static function getCache()
		{
			static $result = null;
			
			if (!$result)
				$result = RubberFileSystem::create(self::CACHE_PATH);
			
			return $result;
		}
	}
?>