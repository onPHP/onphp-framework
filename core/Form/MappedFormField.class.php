<?php
/***************************************************************************
 *   Copyright (C) 2010 by Kutcurua Georgy Tamazievich                     *
 *   email: g.kutcurua@gmail.com, icq: 723737, jabber: soloweb@jabber.ru   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class MappedFormField extends FormField
	{
		/**
		 * @var string
		 */
		protected $map				=null;

		/**
		 * @var bool
		 */
		protected $isSilent = false;

		/**
		 * @return MappedFormField
		**/
		public static function create($name)
		{
			return new self($name);
		}

		/**
		 * @param string $value
		 * @return MappedFormField
		 */
		public function setMap($value)
		{
			$this->map = $value;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getMap()
		{
			return $this->map;
		}

		/**
		 * @param bool $isSilent
		 * @return MappedFormField
		 */
		public function setSilent($isSilent)
		{
			$this->isSilent = $isSilent;
			return $this;
		}

		/**
		 * @return bool
		 */
		public function getIsSilent()
		{
			return $this->isSilent;
		}

		/**
		 * @return array
		 */
		protected function makeMapChain()
		{
			Assert::isNotNull(
				$this->getMap(),
				__METHOD__.': '.
				_('you must be set "method"!')
			);

			$chain = array();
			$map = $this->getMap();
			$delimiter = '.';

			if( mb_strstr($map, $delimiter) !== FALSE )
			{
				$chain = explode($delimiter, $map);
			} else {
				$chain[] = $map;
			}

			return $chain;
		}

		public function toValue(Form $form)
		{
			$object = parent::toValue($form);

			if( $object === null )
				return null;


			Assert::isInstance(
				$object,
				'Prototyped',
				__METHOD__.': '.
				_('value must be instance of Prototyped!')
			);

			$result = $object;
			$mapChain = $this->makeMapChain();
			foreach ( $mapChain as $propertyName )
			{
				if(
					!is_object( $result )
				) {
					if( $this->isSilent ) {
						return NULL;
					} else {
						throw new WrongArgumentException(
							__METHOD__.': '.
							_('previous property not is a object type!')
						);
					}
				}

				if(
					is_object($result) &&
					!($result instanceof Prototyped)
				) {
					if( $this->isSilent ) {
						return NULL;
					} else {
						throw new WrongArgumentException(
							__METHOD__.': '.
							_('result must be instance of Prototyped!')
						);
					}
				}


				$property = $result->proto()->getPropertyByName( $propertyName );
				$result = call_user_func(
					array(
						$result,
						$property->getGetter()
					)
				);

			}

			return $result;
		}

	}
