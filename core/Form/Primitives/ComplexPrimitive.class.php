<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Basis for primitives which can be scattered across import scope.
	 * 
	 * @ingroup Primitives
	 * @ingroup Module
	**/
	namespace Onphp;

	abstract class ComplexPrimitive extends RangedPrimitive
	{
		private $single = null;	// single, not single or fsck it

		public function __construct($name)
		{
			$this->single = new Ternary(null);
			parent::__construct($name);
		}

		/**
		 * @return \Onphp\Ternary
		**/
		public function getState()
		{
			return $this->single;
		}

		/**
		 * @return \Onphp\ComplexPrimitive
		**/
		public function setState(Ternary $ternary)
		{
			$this->single->setValue($ternary->getValue());

			return $this;
		}

		/**
		 * @return \Onphp\ComplexPrimitive
		**/
		public function setSingle()
		{
			$this->single->setTrue();

			return $this;
		}

		/**
		 * @return \Onphp\ComplexPrimitive
		**/
		public function setComplex()
		{
			$this->single->setFalse();

			return $this;
		}

		/**
		 * @return \Onphp\ComplexPrimitive
		**/
		public function setAnyState()
		{
			$this->single->setNull();

			return $this;
		}

		// implement me, child :-)
		abstract public function importSingle($scope);
		abstract public function importMarried($scope);

		public function import($scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			if ($this->single->isTrue())
				return $this->importSingle($scope);
			elseif ($this->single->isFalse())
				return $this->importMarried($scope);
			else {
				if (!$this->importMarried($scope))
					return $this->importSingle($scope);

				return true;
			}

			Assert::isUnreachable();
		}
		
		public function exportValue()
		{
			throw new UnimplementedFeatureException();
		}
	}
?>