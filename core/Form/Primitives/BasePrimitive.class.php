<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov					 *
 *																		 *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as		*
 *   published by the Free Software Foundation; either version 3 of the	*
 *   License, or (at your option) any later version.					   *
 *																		 *
 ***************************************************************************/

	/**
	 * Parent of every Primitive.
	 * 
	 * @ingroup Primitives
	 * @ingroup Module
	**/
	abstract class BasePrimitive
	{
		const WRONG			= 0x0001;
		const MISSING		= 0x0002;

		protected $name		= null;
		protected $default	= null;
		protected $value	= null;

		protected $required	= false;
		protected $imported	= false;

		protected $raw		= null;

		protected $error	= null;

		/**
		 * Label for presentation
		 * @var string
		 */
		protected $label		= null;

		/**
		 * Description for presentation
		 * @var string
		 */
		protected $description	= null;

		/**
		 * Error labels for each error type
		 * @var array of ('errorType' => 'label')
		 */
		protected $errorLabels = array();

		/**
		 * Error description for each error type
		 * @var array of ('errorType' => 'description')
		 */
		protected $errorDescriptions = array();

		/**
		 * @deprecated by error
		 */
		protected $customError = null;

		public function __construct($name)
		{
			$this->name = $name;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}

		public function getDefault()
		{
			return $this->default;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function setDefault($default)
		{
			$this->default = $default;
			
			return $this;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		public function getRawValue()
		{
			return $this->raw;
		}

		public function getValueOrDefault()
		{
			if ($this->value !== null)
				return $this->value;

			return $this->default;
		}
		
		/**
		 * @deprecated since version 1.0
		 * @see getSafeValue, getValueOrDefault
		 */
		public function getActualValue()
		{
			if ($this->value !== null)
				return $this->value;
			elseif ($this->imported)
				return $this->raw;
			
			return $this->default;
		}

		public function getSafeValue()
		{
			if ($this->imported)
				return $this->value;

			return $this->default;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function setValue($value)
		{
			$this->value = $value;
			
			return $this;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function dropValue()
		{
			$this->value = null;
			
			return $this;
		}
		
		/**
		 * @return BasePrimitive
		 * 
		 * usually, you should not use this method
		**/
		public function setRawValue($raw)
		{
			$this->raw = $raw;
			
			return $this;
		}
		
		public function isRequired()
		{
			return $this->required;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function setRequired($really = false)
		{
			$this->required = (true === $really ? true : false);
			
			return $this;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function required()
		{
			$this->required = true;
			
			return $this;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function optional()
		{
			$this->required = false;
			
			return $this;
		}
		
		public function isImported()
		{
			return $this->imported;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function clean()
		{
			$this->raw = null;
			$this->value = null;
			$this->imported = false;
			$this->dropError();
			
			return $this;
		}
		
		public function importValue($value)
		{
			return $this->import(array($this->getName() => $value));
		}
		
		public function exportValue()
		{
			return $this->value;
		}

		public function getError()
		{
			return $this->error | $this->customError;
		}

		/**
		 * @return BasePrimitive
		**/
		public function setError($error)
		{
			Assert::isPositiveInteger($error);

			$this->error = $error;
			$this->customError = $error;

			return $this;
		}

		/**
		 * @return BasePrimitive
		**/
		public function dropError()
		{
			$this->error = null;
			$this->customError = null;

			return $this;
		}

		/**
		 * @alias dropError
		 * @return BasePrimitive
		 */
		public function markGood()
		{
			return $this->dropError();
		}

		/**
		 * @alias setError
		 * @return BasePrimitive
		 */
		public function markWrong()
		{
			return $this->setError(static::WRONG);
		}

		/**
		 * @alias setError
		 * @return BasePrimitive
		 */
		public function markMissing()
		{
			return $this->setError(static::MISSING);
		}

		/**
		 * @deprecated by getError
		 */
		public function getCustomError()
		{
			return $this->getError();
		}

		/**
		 * @param null $val
		 * @return BasePrimitive
		 */
		public function setLabel($val=null)
		{
			$this->label = $val;

			return $this;
		}

		/**
		 * @return null|string
		 */
		public function getLabel()
		{
			return $this->label;
		}

		public function setDescription($val)
		{
			$this->description = $val;

			return $this;
		}

		/**
		 * @return null|string
		 */
		public function getDescription()
		{
			return $this->description;
		}

		/**
		 * @param $type
		 * @param $val
		 * @return BasePrimitive
		 */
		public function setErrorLabel($type, $val)
		{
			$this->errorLabels[$type] = $val;

			return $this;
		}

		/**
		 * @param $val
		 * @return BasePrimitive
		 */
		public function setWrongLabel($val)
		{
			return $this->setErrorLabel(BasePrimitive::WRONG, $val);
		}

		public function setMissingLabel($val)
		{
			return $this->setErrorLabel(BasePrimitive::MISSING, $val);
		}

		/**
		 * @param integer $type
		 * @return null|string
		 */
		public function getErrorLabel($type)
		{
			return (isset($this->errorLabels[$type]))
					? $this->errorLabels[$type]
					: null;
		}

		/**
		 * @return null|string
		 */
		public function getActualErrorLabel()
		{
			if(
				($error = $this->getError())
				&& $error !==null
			) {
				return $this->getErrorLabel($error);
			}


			return null;
		}

		/**
		 * @return array
		 */
		public function getErrorLabels()
		{
			return $this->errorLabels;
		}

		/**
		 * @param $type
		 * @param $val
		 * @return BasePrimitive
		 */
		public function setErrorDescription($type, $val)
		{
			$this->errorDescriptions[$type] = $val;

			return $this;
		}

		/**
		 * @param integer $type
		 * @return null|string
		 */
		public function getErrorDescription($type)
		{
			return (isset($this->errorDescriptions[$type]))
					? $this->errorDescriptions[$type]
					: null;
		}

		/**
		 * @return null|string
		 */
		public function getActualErrorDescription()
		{
			if(
				($error = $this->getError())
				&& $error !==null
			) {
				return $this->getErrorDescription($error);
			}

			return null;
		}

		/**
		 * @return array
		 */
		public function getErrorDescriptions()
		{
			return $this->errorDescriptions;
		}

		
		public function import($scope)
		{
            if (isset($scope[$this->name]))
            {
                $this->setRawValue(
                    $scope[$this->name]
                );
                $this->imported = true;
            }

			return $this;
		}
	}
?>