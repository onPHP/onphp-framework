<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @see UnifiedContainer
	 * 
	 * @ingroup Containers
	**/
	abstract class UnifiedContainerWorker
	{
		protected $oq			= null;
		protected $container	= null;
		
		abstract public function makeFetchQuery();
		abstract public function sync(&$insert, &$update = array(), &$delete);
		
		public function __construct(UnifiedContainer $uc)
		{
			$this->container = $uc;
		}
		
		public function setObjectQuery(ObjectQuery $oq)
		{
			$this->oq = $oq;
			
			return $this;
		}
	}
?>