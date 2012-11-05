<?php

/* * *************************************************************************
 *   Copyright (C) 2012 by Alexey Denisov                                  *
 *   alexeydsov@gmail.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 * ************************************************************************* */

namespace Onphp\NsConverter;

class ClassNameBuffer implements Buffer
{
	private $buffer = false;
	private $className = '';
	private $classNameStart = null;
	private $classNameEnd = null;
	private $subjects = [];

	private static $excludeNames = [
		'null',
		'true',
		'false',
		'parent',
		'self',
		'static',
	];
	
	public static function canStart($subject, $prevSubject = null)
	{
		$isOkSubject = is_array($subject)
			&& in_array($subject[0], [T_NS_SEPARATOR, T_STRING]);
		
		$isNokPrevSubject = false;
		if ($prevSubject) {
			$isNokPrevSubject = is_array($prevSubject)
				&& in_array($prevSubject[0], [T_OBJECT_OPERATOR, T_PAAMAYIM_NEKUDOTAYIM, T_CONST]);
		}
		$isExcludeNames = is_array($subject)
			&& $subject[0] == T_STRING
			&& in_array(mb_strtolower($subject[1]), self::$excludeNames);
		
		return $isOkSubject
			&& !$isNokPrevSubject
			&& !$isExcludeNames;
	}
	
	/**
	 * @return \Onphp\NsConverter\NamespaceBuffer
	 */
	public function init()
	{
		$this->buffer = false;
		$this->className = '';
		$this->classNameStart = null;
		$this->classNameEnd = null;
		$this->subjects = [];
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isBuffer()
	{
		return $this->buffer == true;
	}
	
	public function getClassName() 
	{
		return (!$this->buffer && $this->className)
			? $this->className
			: null;
	}
	
	public function getClassNameStart()
	{
		return (!$this->buffer && $this->className)
			? $this->classNameStart
			: null;
	}

	public function getClassNameEnd()
	{
		return (!$this->buffer && $this->className)
			? $this->classNameEnd
			: null;
	}

	
	public function process($subject, $i)
	{
		if (!$this->buffer && self::canStart($subject)) {
			$this->buffer = true;
			$this->className = $subject[1];
			$this->classNameStart = $i;
			$this->classNameEnd = null;
			$this->subjects = [$i => $subject];
		} elseif ($this->buffer) {
			$this->subjects[$i] = $subject;
			if (self::canStart($subject)) {
				$this->className .= $subject[1];
			} elseif (is_array($subject) && $subject[0] == T_WHITESPACE) {
				//we'll skip spaces
			} else {
				$this->buffer = false;
				$this->classNameEnd = $this->getEndSubject($i - 1);
				if (!preg_match('~^[\\\\A-Z]~u', $this->className)) {
					$this->className = '';
					$this->classNameEnd = null;
					$this->classNameStart = null;
				}
			}
		}
	}
	
	/**
	 * @param int $i
	 * @return int
	 */
	private function getEndSubject($i)
	{
		while (isset($this->subjects[$i])) {
			$subject = $this->subjects[$i];
			if (is_array($subject) && $subject[0] == T_WHITESPACE) {
				$i--;
				continue;
			} else {
				break;
			}
		}
		return $i;
	}
}
