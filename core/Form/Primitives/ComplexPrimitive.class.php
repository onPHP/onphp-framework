<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Basis for primitives which can be scattered across import scope.
	 * 
	 * @ingroup Primitives
	**/
	abstract class ComplexPrimitive extends RangedPrimitive
	{
		private $single = null;	// single, not single or fsck it

		public function __construct($name)
		{
			$this->single = new Ternary(null);
			parent::__construct($name);
		}

		public function getState()
		{
			return $this->single;
		}

		public function setState(Ternary $ternary)
		{
			$this->single->setValue($ternary->getValue());

			return $this;
		}

		public function setSingle()
		{
			$this->single->setTrue();

			return $this;
		}

		public function setComplex()
		{
			$this->single->setFalse();

			return $this;
		}

		public function setAnyState()
		{
			$this->single->setNull();

			return $this;
		}

		// implement me, child :-)
		abstract protected function importSingle($scope);
		abstract protected function importMarried($scope);

		public function import($scope)
		{
			parent::import($scope);
			
			if ($this->single->isTrue())
				return $this->importSingle($scope);
			elseif ($this->single->isFalse())
				return $this->importMarried($scope);
			else {
				if (!$this->importMarried($scope))
					return $this->importSingle($scope);

				return true;
			}

			/* NOTREACHED */
		}
	}
?>