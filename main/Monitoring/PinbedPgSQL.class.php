<?php
/****************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
	
	/**
	 *  @ingroup DB
	**/
	final class PinbedPgSQL extends PgSQL
	{
		public function connect()
		{
			if (PinbaClient::isEnabled())
				PinbaClient::me()->timerStart(
					'pg_sql_connect_'.$this->basename,
					array('pg_sql_connect' => $this->basename)
				);
			
			$result = parent::connect();
			
			if (PinbaClient::isEnabled())
				PinbaClient::me()->timerStop('pg_sql_connect_'.$this->basename);
			
			return $result;
		}
		
		public function queryRaw($queryString)
		{
			if (PinbaClient::isEnabled()) {
				$queryLabel = substr($queryString, 0, 5);
				
				PinbaClient::me()->timerStart(
					'pg_sql_query_'.$this->basename,
					array(
						'group'			=> 'sql',
						'pg_sql_query'	=> $queryLabel,
						'pg_sql_server'	=> $this->hostname,
						'pg_sql_base'	=> $this->basename
					)
				);
			}
			
			try {
				$result = parent::queryRaw($queryString);
				
				if (PinbaClient::isEnabled())
					PinbaClient::me()->timerStop('pg_sql_query_'.$this->basename);
				
				return $result;
				
			} catch (Exception $e) {
				if (PinbaClient::isEnabled())
					PinbaClient::me()->timerStop('pg_sql_query_'.$this->basename);
				
				throw $e;
			}
			
			Assert::isUnreachable();
		}
	}
?>