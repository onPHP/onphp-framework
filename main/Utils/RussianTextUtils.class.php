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
		
		// TODO: deprecated by selectCaseForNumber
		private static $secondDecade = array(11, 12, 13, 14, 15, 16, 17, 18, 19);
		
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
		
		/**
		 * Returns text representation of digit
		**/
		public static function getAsText($number, $gender)
		{
			throw new UnsupportedMethodException();
		}
		
		/**
		 * Returns suffix for word
		 * 
		 * @deprecated by selectCaseForNumber
		 * @param	$number		integer variable
		 * @param	$suffixes	array of suffixes as array('ца', 'цы', null)
		**/
		public static function getSuffix($number, $suffixes)
		{
			if (
				in_array(
					intval(substr($number, strlen($number) - 2, 2)),
					self::$secondDecade, true
				)
			) {
				return $suffixes[2];
			}
			
			$lastDigit = substr($number, strlen($number) - 1, 1);
			
			switch ($lastDigit) {
				case '1':
					return $suffixes[0];
				
				case '2':
				case '3':
				case '4':
					return $suffixes[1];
				
				default:
					return $suffixes[2];
			}
		}
		
		/**
		 * Select russian case for number
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
	}
?>