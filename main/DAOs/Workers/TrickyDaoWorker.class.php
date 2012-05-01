<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 19.01.2012                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Tricky caching worker for SQL-NoSQL projects
	 *
	 * @ingroup DAOs
	**/
	class TrickyDaoWorker extends CommonDaoWorker {

		public function uncacheLists() {
			if( $this->isNoSqlDao() ) {
				return true;
			}

			return parent::uncacheLists();
		}

		/**
		 * Проверяем является ли текущий DAO реализацией NoSqlDAO
		 * @return bool
		 */
		protected function isNoSqlDao() {
			return ($this->dao instanceof NoSqlDAO);
		}


	}
