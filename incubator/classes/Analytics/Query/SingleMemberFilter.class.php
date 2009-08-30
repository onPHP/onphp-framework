<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	class SingleMemberFilter extends DimensionFilter
	{
		private $member = null;
		
		/**
		 * @return SingleMemberFilter
		**/
		public function setMember($member)
		{
			$this->member = $member;
			
			return $this;
		}
		
		public function getMember()
		{
			return $this->member;
		}
	}
?>