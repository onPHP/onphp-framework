<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Dmitry E. Pismenny                         *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Base;

use OnPHP\Core\Base\Enumeration;

final class IsoCurrency extends Enumeration
{
	const RUB	= 643;
	const USD	= 840;
	const EUR	= 978;
	const UAH	= 980;
	const ILS	= 376;
	const LVL	= 428;
	const LTL	= 440;
	const BYR	= 974;
	const EEK	= 233;
	const GBP	= 826;
	const KZT	= 398;
	const DKK	= 208;
	const SEK	= 752;
	const NOK	= 578;
	const KGS	= 417;

	protected $names = array(
		self::RUB	=> 'Russian Ruble',
		self::USD	=> 'US Dollar',
		self::EUR	=> 'Euro',
		self::UAH 	=> 'Hryvnia',
		self::ILS 	=> 'New Israeli Sheqel',
		self::LVL 	=> 'Latvian Lats',
		self::LTL 	=> 'Lithuanian Litas',
		self::BYR 	=> 'Belarussian Ruble',
		self::EEK 	=> 'Kroon',
		self::GBP 	=> 'Pound Sterling',
		self::KZT 	=> 'Tenge',
		self::DKK 	=> 'Danish Krone',
		self::SEK 	=> 'Swedish Krona',
		self::NOK 	=> 'Norwegian Krone',
		self::KGS 	=> 'Som'
	);

	protected $signs = array(
		self::RUB	=> 'руб.',
		self::USD	=> '$',
		self::EUR	=> '€',
		self::UAH 	=> 'грн.',
		self::ILS 	=> 'ILS',
		self::LVL 	=> 'LVL',
		self::LTL 	=> 'LTL',
		self::BYR 	=> 'BYR',
		self::EEK 	=> 'EEK',
		self::GBP 	=> 'GBP',
		self::KZT 	=> 'KZT',
		self::DKK 	=> 'DKK',
		self::SEK 	=> 'SEK',
		self::NOK 	=> 'NOK',
		self::KGS 	=> 'KGS'
	);

	/**
	 * @return IsoCurrency
	**/
	public static function create($id)
	{
		return new self($id);
	}

	public static function getAnyId()
	{
		return self::RUB;
	}

	public function getSign()
	{
		return $this->signs[$this->id];
	}
}
?>