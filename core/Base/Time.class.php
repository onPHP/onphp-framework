<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 ***************************************************************************/
/* $Id$ */

	class Time
	{
		private $hour	= 0;
		private $minute	= 0;
		private $second	= 0;
		
		public static function create($input)
		{
			return new Time($input);
		}
		
		// currently supports '01:23:45', '012345', '1234', '12'
		public function __construct($input)
		{
			$input = (string) $input;
			
			$count = substr_count($input, ':');
			
			$lenght = strlen($input);
			
			if (($count === 1) || ($count === 2)) {
				list($hour, $minute, $second) = explode(':', $input, ($count + 1));
				
				$this->
					setHour($hour)->
					setMinute($minute)->
					setSecond($second);
			} else {
				switch ($lenght) {
					case 1:
					case 2:
						
						$this->setHour(substr($input, 0, 2));
						break;
					
					case 3:

						$assumedHour = substr($input, 0, 2);
						
						if ($assumedHour > 12)
							$this->
								setHour($input{0})->
								setMinute(substr($input, 1, 3));
						else
							$this->
								setHour($assumedHour)->
								setMinute($input{2});

						break;

					case 4:
					case 5:
					case 6:

						$this->
							setHour(substr($input, 0, 2))->
							setMinute(substr($input, 2, 4))->
							setSecond(substr($input, 4, 6));
						
						break;
					
					default:
						throw new WrongArgumentException('unknown format');
				}
			}
			
			/* NOTREACHED */
		}
		
		public function getHour()
		{
			return $this->hour;
		}
		
		public function setHour($hour)
		{
			$hour = (int) $hour;
			
			Assert::isTrue(
				$hour >= 0 &&
				$hour <= 24,
				"wrong hour specified"
			);
			
			$this->hour = $hour;
			
			return $this;
		}
		
		public function getMinute()
		{
			return $this->minute;
		}
		
		public function setMinute($minute)
		{
			$minute = (int) $minute;
			
			Assert::isTrue(
				$minute >= 0  &&
				$minute <= 60,
				"wrong minute specified"
			);
			
			$this->minute = $minute;
			
			return $this;
		}
		
		public function getSecond()
		{
			return $this->second;
		}
		
		public function setSecond($second)
		{
			$second = (int) $second;
			
			Assert::isTrue(
				$second >= 0 &&
				$second <= 60
			);
			
			$this->second = $second;
			
			return $this;
		}
		
		public function toString()
		{
			return
				$this->doublize($this->hour).':'.
				$this->doublize($this->minute);
		}

		public function toFullString()
		{
			return
				$this->toString().
				(
					$this->second
						? ':'.$this->doublize($this->second)
						: ''
				);
		}

		private function doublize($int)
		{
			return
				$int <= 9
					? "0{$int}"
					: $int;
		}

		public function isAfter(Timestamp $base)
		{
			return 
				self::compare(
					$this, 
					new Time(date('H:i:s', $base->toStamp()))
				) === 1;
		}

		public static function compare(Time $left, Time $right)
		{
			if (
				($left->hour > 6) && ($right->hour > 6) 
				|| ($left->hour <= 6) && ($right->hour <= 6) 
			) {
				$leftStamp = mktime($left->hour, $left->minute, $left->second);
				$rightStamp = mktime(
					$right->hour, 
					$right->minute, 
					$right->second
				);

				if ($leftStamp == $rightStamp)
					return 0;
				else 
					return $leftStamp > $rightStamp ? 1 : -1;

			} elseif ($left->hour <= 6 && $right->hour > 6 )
				return 1;
			elseif ($left->hour > 6 && $right->hour <= 6)
				return -1;
		}
	}
?>