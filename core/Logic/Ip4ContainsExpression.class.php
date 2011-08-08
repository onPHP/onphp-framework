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
	 * @ingroup Logic
	**/
	final class Ip4ContainsExpression implements LogicalObject, MappableObject
	{
		private $range = null;
		private $ip = null;
		
		public function __construct($range, $ip)
		{
			$this->range = $range;
			$this->ip = $ip;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $dialect->quoteIpInRange($this->range, $this->ip);
		}
		
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			return new self(
				$dao->guessAtom($this->range, $query),
				$dao->guessAtom($this->ip, $query)
			);
		}
		
		public function toBoolean(Form $form)
		{
			throw new UnimplementedFeatureException('Author was too lazy to make it');
		}
	}
?>