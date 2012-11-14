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

namespace Onphp\NsConverter\Utils;

use \Onphp\NsConverter\Buffers\CodeStorage;
use \Onphp\NsConverter\Buffers\NamespaceBuffer;
use \Onphp\NsConverter\Buffers\ClassNameDetectBuffer;
use \Onphp\NsConverter\Buffers\AliasBuffer;
use \Onphp\NsConverter\Business\NsClass;
use \Onphp\MissingElementException;
use \Onphp\WrongStateException;

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
	 * @var ClassStorage
	 */
	private $classStorage = null;
	/**
	 * @var CodeStorage
	 */
	private $codeStorage = null;
	/**
	 * @var NamespaceBuffer
	 */
	private $namespaceBuffer = null;
	/**
	 * @var ClassNameDetectBuffer
	 */
	private $classNameDetectBuffer = null;
	/**
	 * @var AliasBuffer
	 */
	private $aliasBuffer = null;
	private $newNamespace = null;
	private $skipUses = false;
	private $currentClassName = null;

	/**
	 * @param ClassStorage $classStorage
	 * @return CodeConverter
	 */
	public function setClassStorage(ClassStorage $classStorage)
	{
		$this->classStorage = $classStorage;
		return $this;
	}

	/**
	 * @param CodeStorage $codeStorage
	 * @return CodeConverter
	 */
	public function setCodeStorage(CodeStorage $codeStorage)
	{
		$this->codeStorage = $codeStorage;
		return $this;
	}

	/**
	 * @param NamespaceBuffer $namespaceBuffer
	 * @return CodeConverter
	 */
	public function setNamespaceBuffer(NamespaceBuffer $namespaceBuffer)
	{
		$this->namespaceBuffer = $namespaceBuffer;
		return $this;
	}

	/**
	 * @param string $newNamespace
	 * @return CodeConverter
	 */
	public function setNewNamespace($newNamespace)
	{
		$this->newNamespace = $newNamespace;
		return $this;
	}

	/**
	 * @param ClassNameDetectBuffer $classNameDetectBuffer
	 * @return CodeConverter
	 */
	public function setClassNameDetectBuffer(ClassNameDetectBuffer $classNameDetectBuffer)
	{
		$this->classNameDetectBuffer = $classNameDetectBuffer;
		return $this;
	}

	/**
	 * @param AliasBuffer $aliasBuffer
	 * @return CodeConverter
	 */
	public function setAliasBuffer(AliasBuffer $aliasBuffer)
	{
		$this->aliasBuffer = $aliasBuffer;
		return $this;
	}

	/**
	 * @param type $skipUses
	 * @return CodeConverter
	 */
	public function setSkipUses($skipUses = false)
	{
		$this->skipUses = $skipUses == true;
		return $this;
	}
	
	/**
	 * @param type $currentClassName
	 * @return CodeConverter
	 */
	public function setCurrentClassName($currentClassName)
	{
		$this->currentClassName = $currentClassName;
		return $this;
	}
	
	public function run()
	{
		$aliasConverter = new CodeConverterAlias();
		$aliasConverter
			->setAliasBuffer($this->aliasBuffer)
			->setSkipUses($this->skipUses)
			->setCurrentClassName($this->currentClassName);

		$this->classStorage->setAliasConverter($aliasConverter);

		$aliasConverter->clearOldAliases($this->codeStorage);
		//replacing classnames
		foreach ($this->classNameDetectBuffer->getClassNameList() as $row) {
			list($className, $from, $to) = $row;
			$this->processClassName($className, $from, $to);
		}

		$this->replaceCommentsStrings();

		$aliases = $aliasConverter->getNewAliases($this->newNamespace);
		$this->replaceNamespace($aliases);

	}

	private function processClassName($className, $from, $to)
	{
		if ($constant = $this->classStorage->findConstant($className)) {
			/* ok, skip replacing for constants */
		} elseif ($class = $this->classStorage->findByRawClassName($className, $this->namespaceBuffer->getNamespace())) {
			if ($class instanceof NsClass) {
				$alias = $this->classStorage->getAliasClassName($class, $this->newNamespace);
				$this->codeStorage->addReplace($alias, $from, $to);
			}
		} else {
			$msg = 'Could not find something about name "'.$className.'"';
			throw new MissingElementException($msg);
		}
	}

	private function replaceNamespace(array $aliases = [])
	{
		if ($this->namespaceBuffer->getBufferStart()) {
			$this->codeStorage->addReplace(
				'namespace '.trim($this->newNamespace, '\\'),
				$this->namespaceBuffer->getBufferStart(),
				$this->namespaceBuffer->getBufferEnd() - 1
			);

			//adding uses after namespace
			if (!empty($aliases)) {
				$tabs = '';
				$endWhite = $this->codeStorage->get($this->namespaceBuffer->getBufferEnd() + 1);
				$startWhite = $this->codeStorage->get($this->namespaceBuffer->getBufferStart() - 1);
				if (
					is_array($startWhite)
					&& $startWhite[0] == T_WHITESPACE
					&& preg_match('~\n(\t*)$~u', $startWhite[1], $matches)
				) {
					$tabs = $matches[1];
				}
				$postFix = is_array($endWhite) ? $endWhite[1] : $endWhite;
				$aliasString = "\n\n{$tabs}".implode("\n{$tabs}", $aliases).$postFix;

				$this->codeStorage->addReplace($aliasString, $this->namespaceBuffer->getBufferEnd() + 1);
			}

			return true;
		} else {
			$startSubject = $this->codeStorage->get(0);
			if (!(is_array($startSubject) && $startSubject[0] == T_OPEN_TAG)) {
				$startSubject = $this->codeStorage->get(0);
				$startText = is_array($startSubject) ? $startSubject[1] : $startSubject;

				$ns = 'namespace '.trim($this->newNamespace, '\\').';';
				if ($aliases) {
					$ns .= "\n".implode("\n", $aliases);
				}
				$this->codeStorage->addReplace(
					"<?php\n".$ns."\n?>".$startText,
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

				$nsString = 'namespace '.trim($this->newNamespace, '\\').";\n\n".$tabs;
				if (!empty($aliases)) {
					$nsString .= implode("\n{$tabs}", $aliases)."\n\n{$tabs}";
				}

				$this->codeStorage->addAppend($nsString, $codePos - 1);
				return true;
			} elseif (!is_null($openPos)) {
				$tabs = '';
				$nextSubject = $this->codeStorage->get($openPos + 1);
				if (is_array($nextSubject) && $nextSubject[0] == T_WHITESPACE) {
					if (preg_match("~(\t+)$~u", $nextSubject[1], $matches)) {
						$tabs = $matches[1];
					}
				}

				$nsString = 'namespace '.trim($this->newNamespace, '\\').";\n".$tabs;
				if (!empty($aliases)) {
					$nsString .= implode("\n{$tabs}", $aliases)."\n{$tabs}";
				}
				$this->codeStorage->addAppend(
					"\n{$tabs}".$nsString,
					$openPos
				);
				return true;
			} else {
				throw new WrongStateException('Php open tag not found');
			}

			return null;
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
			$class = $this->classStorage->findByRawClassName($matches[2], $this->namespaceBuffer->getNamespace(), false);
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
			$class = $this->classStorage->findByRawClassName(
				$className,
				$this->namespaceBuffer->getNamespace()
			);
			if ($class) {
				$alias = $this->classStorage->getAliasClassName($class, $this->newNamespace);
				$newCodeString = preg_replace(
					'~'.$this->regPatternEscape($className).'~u',
					$alias,
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
