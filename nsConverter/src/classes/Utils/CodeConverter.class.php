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

class CodeConverter
{
	private static $constants = [
		'UPLOAD_ERR_NO_FILE',
		'PREG_SPLIT_NO_EMPTY',
		'PREG_SPLIT_DELIM_CAPTURE',
		'PREG_SPLIT_OFFSET_CAPTURE',
		'MYSQLI_CLIENT_FOUND_ROWS',
		'SQLITE_NUM',
	];

	/**
	 * @var \Onphp\NsConverter\ClassStorage
	 */
	private $classStorage = null;
	/**
	 * @var \Onphp\NsConverter\CodeStorage
	 */
	private $codeStorage = null;
	/**
	 * @var \Onphp\NsConverter\NamespaceBuffer
	 */
	private $namespaceBuffer = null;
	/**
	 * @var \Onphp\NsConverter\ClassNameDetectBuffer
	 */
	private $classNameDetectBuffer = null;
	private $newNamespace = null;

	/**
	 * @param \Onphp\NsConverter\ClassStorage $classStorage
	 * @return \Onphp\NsConverter\CodeConverter
	 */
	public function setClassStorage(ClassStorage $classStorage)
	{
		$this->classStorage = $classStorage;
		return $this;
	}

	/**
	 * @param \Onphp\NsConverter\CodeStorage $codeStorage
	 * @return \Onphp\NsConverter\CodeConverter
	 */
	public function setCodeStorage(CodeStorage $codeStorage)
	{
		$this->codeStorage = $codeStorage;
		return $this;
	}

	/**
	 * @param \Onphp\NsConverter\NamespaceBuffer $namespaceBuffer
	 * @return \Onphp\NsConverter\CodeConverter
	 */
	public function setNamespaceBuffer(NamespaceBuffer $namespaceBuffer)
	{
		$this->namespaceBuffer = $namespaceBuffer;
		return $this;
	}

	/**
	 * @param string $newNamespace
	 * @return \Onphp\NsConverter\CodeConverter
	 */
	public function setNewNamespace($newNamespace)
	{
		$this->newNamespace = $newNamespace;
		return $this;
	}

	/**
	 * @param \Onphp\NsConverter\ClassNameDetectBuffer $classNameDetectBuffer
	 * @return \Onphp\NsConverter\CodeConverter
	 */
	public function setClassNameDetectBuffer(ClassNameDetectBuffer $classNameDetectBuffer)
	{
		$this->classNameDetectBuffer = $classNameDetectBuffer;
		return $this;
	}

	public function run()
	{
		$this->replaceNamespace();

		foreach ($this->classNameDetectBuffer->getClassNameList() as $row) {
			list($className, $from, $to) = $row;
			$this->processClassName($className, $from, $to);
		}

		$this->replaceCommentsStrings();
	}

	private function processClassName($className, $from, $to)
	{
		if ($constant = $this->classStorage->findConstant($className)) {
			/* ok, skip replacing for constants */
		} elseif ($class = $this->classStorage->findByClassName($className, $this->namespaceBuffer->getNamespace())) {
			if ($class instanceof NsClass) {
				$this->codeStorage->addReplace($class->getFullNewName($this->newNamespace), $from, $to);
			}
		} else {
			$msg = 'Could not find something about name "'.$className.'"';
			throw new \Onphp\MissingElementException($msg);
		}
	}

	private function replaceNamespace()
	{
		if ($this->namespaceBuffer->getBufferStart()) {
			$this->codeStorage->addReplace(
				'namespace '.trim($this->newNamespace, '\\'),
				$this->namespaceBuffer->getBufferStart(),
				$this->namespaceBuffer->getBufferEnd() - 1
			);
			return true;
		} else {
			$startSubject = $this->codeStorage->get(0);
			if (!(is_array($startSubject) && $startSubject[0] == T_OPEN_TAG)) {
				$startSubject = $this->codeStorage->get(0);
				$startText = is_array($startSubject) ? $startSubject[1] : $startSubject;
				$this->codeStorage->addReplace(
					'<?php namespace '.trim($this->newNamespace, '\\').";\n?>".$startText,
					0
				);
				return true;
			}

			$openPos = null;
			$codePos = null;
			for ($i = 0; $i < $this->codeStorage->count(); $i++) {
				$subject = $this->codeStorage->get($i);
				if (is_null($openPos) && is_array($subject) && $subject[0] == T_OPEN_TAG) {
					$openPos = $i;
				}
				if (is_null($openPos)) {
					continue;
				}
				if (!is_null($openPos) && is_array($subject) && $subject[0] == T_CLOSE_TAG) {
					break;
				}

				$continue = is_array($subject)
					&& in_array($subject[0], [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_COMMENT, T_DOC_COMMENT, T_WHITESPACE]);
				if (!$continue) {
					$codePos = $i;
					break;
				}
			}

			if (!is_null($codePos)) {
				$tabs = '';
				$tabSubject = $this->codeStorage->get($codePos - 1);
				if (is_array($tabSubject) && $tabSubject[0] = T_WHITESPACE) {
					if (preg_match("~(\t+)$~u", $tabSubject[1], $matches)) {
						$tabs = $matches[1];
					}
				}

				$this->codeStorage->addAppend(
					'namespace '.trim($this->newNamespace, '\\').";\n\n".$tabs,
					$codePos - 1
				);
				return true;
			} elseif (!is_null($openPos)) {
				$tabs = '';
				$nextSubject = $this->codeStorage->get($openPos + 1);
				if (is_array($nextSubject) && $nextSubject[0] == T_WHITESPACE) {
					if (preg_match("~(\t+)$~u", $nextSubject[1], $matches)) {
						$tabs = $matches[1];
					}
				}
				$this->codeStorage->addAppend(
					"\n{$tabs}".'namespace '.trim($this->newNamespace, '\\').";\n{$tabs}",
					$openPos
				);
				return true;
			} else {
				throw new \Onphp\WrongStateException('Php open tag not found');
			}

			return false;
		}
	}

	private function replaceCommentsStrings()
	{
		$count = $this->codeStorage->count();
		for ($i = 0; $i < $count; $i++) {
			$subject = $this->codeStorage->get($i);
			if (is_array($subject) && in_array($subject[0], [T_COMMENT, T_DOC_COMMENT])) {
				$newComment = $this->processComment($subject[1], $i);
				$this->codeStorage->addReplace($newComment, $i);
			} elseif (is_array($subject) && in_array($subject[0], [T_CONSTANT_ENCAPSED_STRING])) {
				$newString = $this->processString($subject[1], $i);
				$this->codeStorage->addReplace($newString, $i);
			}
		}
	}

	private function processString($string, $num)
	{
		$pattern = '~^([\'"])([\\\\A-Z][\\\\A-Za-z0-9]+)([\'"])~';
		if (preg_match($pattern, $string, $matches) && $matches[1] == $matches[3]) {
			$class = $this->classStorage->findByClassName($matches[2], $this->namespaceBuffer->getNamespace());
			if ($class) {
				return $matches[1]
					. $class->getFullNewName()
					.$matches[1];
			}
		}
		return $string;
	}

	private function processComment($comment, $num)
	{
		$parts = [];
		foreach ($this->getCommentPatterns() as $pattern) {
			if ($count = preg_match_all($pattern, $comment, $matches)) {
				for ($i = 0; $i < $count; $i++) {
					$parts[$matches[0][$i]] = $matches[1][$i];
				}
			}
		}

		foreach ($parts as $codeString => $className) {
			$class = $this->classStorage->findByClassName(
				$className,
				$this->namespaceBuffer->getNamespace()
			);
			if ($class) {
				$newClassName = $class->getFullNewName();
				$newCodeString = preg_replace(
					'~'.$this->regPatternEscape($className).'~u',
					$newClassName,
					$codeString
				);
				$newCodeString = preg_replace('~\$~', '\\\$', $newCodeString);
				$pattern = '~'.$this->regPatternEscape($codeString).'~u';

				$comment = preg_replace($pattern, $newCodeString, $comment);
			}
		}
		return $comment;
	}

	private function getCommentPatterns()
	{
		$classPattern = '[\\\\A-Z][\\\\A-Za-z0-9]+';

		return [
			'~@param\s+('.$classPattern.')\s+\$[\w]+~us',
			'~@var\s+('.$classPattern.')~us',
			'~@var\s+\$[\w]+\s+('.$classPattern.')~us',
			'~@return\s+('.$classPattern.')~us',
			'~@throws\s+('.$classPattern.')~us',
		];
	}

	private function regPatternEscape($string)
	{
		return preg_replace('~([$@()?\.+\\\\])~', '\\\\$1', $string);
	}
}
