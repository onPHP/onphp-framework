<?php
/****************************************************************************
 *   Copyright (C) 2008-2009 by Vladlen Y. Koshelev                         *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup OQL
	**/
	namespace Onphp;

	abstract class OqlQuery extends OqlQueryClause
	{
		protected $dao = null;
		
		/**
		 * @return \Onphp\ProtoDAO
		**/
		public function getDao()
		{
			return $this->dao;
		}
		
		/**
		 * @return \Onphp\OqlQuery
		**/
		public function setDao(ProtoDAO $dao)
		{
			$this->dao = $dao;
			
			return $this;
		}
	}
?>