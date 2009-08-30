<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	final class RussianTextUtils extends StaticFactory
	{
		const MALE		= 0;
		const FEMALE	= 1;
		const NEUTRAL	= 2;
		
		private static $orderedSuffixes = array(
			self::MALE		=> array('ый', 'ой', 'ий'),
			self::FEMALE	=> array('ая', 'ья', null),
			self::NEUTRAL	=> array('ое', 'ье', null)
		);
		
		private static $orderedDigits = array(
			'перв',
			'втор',
			'трет',
			'четвёрт',
			'пят',
			'шест',
			'седьм',
			'восьм',
			'девят',
			'десят'
		);
		
		private static $bytePrefixes = array(
			null, 'К', 'М', 'Г', 'Т', 'П'
		);
		
		/**
		 * Returns text representation of digit
		**/
		public static function getAsText($number, $gender)
		{
			throw new UnsupportedMethodException();
		}
		
		/**
		 * Selects russian case for number.
		 * for example:
		 * 	1 результат
		 * 	2 результата
		 * 	5 результатов
		 * @param $number integer
		 * @param $cases words to select from array('результат', 'результата', 'результатов')
		**/
		public static function selectCaseForNumber($number, $cases)
		{
			if (($number % 10) == 1 && ($number % 100) != 11) {
				
				return $cases[0];
				
			} elseif (
				($number % 10) > 1
				&& ($number % 10) < 5
				&& ($number < 10 || $number > 20)
			) {
				
				return $cases[1];
				
			} else {
				return $cases[2];
			}
		}
		
		/**
		 * doesn't duplicate strftime('%B', ...)
		 * only when 'russian' locale set in windoze
		**/
		public static function getMonthInGenitiveCase($month)
		{
			static $months = array(
				'января', 'февраля', 'марта', 'апреля',
				'мая', 'июня', 'июля', 'августа', 'сентября',
				'октября', 'ноября', 'декабря'
			);
			
			return $months[$month - 1];
		}
		
		public static function getMonthInSubjectiveCase($month)
		{
			static $months = array(
				'январь', 'февраль', 'март', 'апрель',
				'май', 'июнь', 'июль', 'август', 'сентябрь',
				'октябрь', 'ноябрь', 'декабрь'
			);
			
			return $months[$month - 1];
		}
		
		public static function getDayOfWeek($day)
		{
			static $weekDays = array(
				'вс', 'пн', 'вт', 'ср',
				'чт', 'пт', 'сб', 'вс'
			);
			
			return $weekDays[$day];
		}
		
		public static function getDateAsText(Timestamp $date, $todayWordNeed = true)
		{
			$dayStart = Timestamp::create(Timestamp::today());
			$tomorrowDayStart = $dayStart->spawn('+1 day');
			
			if (
				(Timestamp::compare($date, $dayStart) == 1)
				&& (Timestamp::compare($date, $tomorrowDayStart) == -1)
			)
				return
					(
						$todayWordNeed === true
							? 'сегодня '
							: null
					)
					."в "
					.date('G:i', $date->toStamp());
			
			$yesterdayStart = $dayStart->spawn('-1 day');
			
			if (
				(Timestamp::compare($date, $yesterdayStart) == 1)
				&& (Timestamp::compare($date, $dayStart) == -1)
			)
				return 'вчера в '.date('G:i', $date->toStamp());
			
			return date('j.m.Y в G:i', $date->toStamp());
		}
		
		public static function friendlyFileSize($size, $precision = 2)
		{
			if ($size < 1024)
				return
					$size.' '.self::selectCaseForNumber(
						$size, array('байт', 'байта', 'байт')
					);
			else
				return TextUtils::friendlyFileSize(
					$size, $precision, self::$bytePrefixes, true
				).'Б';
		}
	}
?>