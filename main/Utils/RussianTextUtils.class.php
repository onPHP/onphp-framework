<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Sveta Smirnova                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Utils
	**/
	final class RussianTextUtils extends StaticFactory
	{
		const MALE		= 0;
		const FEMALE	= 1;
		const NEUTRAL	= 2;
	
		private static $secondDecade = array(11, 12, 13, 14, 15, 16, 17, 18, 19);
	
		private static $orderedSuffixes = array(
			self::MALE 		=> array('ый', 'ой', 'ий'),
			self::FEMALE 	=> array('ая', 'ья', ''),
			self::NEUTRAL 	=> array('ое', 'ье', '')
		);
	
		private static $orederedDigits = array(
			'перв',
			'втор',
			'трет',
			'четвёрт',
			'пят',
			'шест',
			'седьм',
			'восьм',
			'девят',
			'десят',
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
		 * @param	$number		integer variable
		 * @param	$suffixes	array of suffixes as array('ца', 'цы', '')
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
		 * doesn't duplicate strtolower('%B', ...)
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