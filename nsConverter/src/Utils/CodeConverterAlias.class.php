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

use \Onphp\NsConverter\Buffers\AliasBuffer;
use \Onphp\WrongStateException;
use \Onphp\NsConverter\Buffers\CodeStorage;

class CodeConverterAlias
{
	/**
	 * @var AliasBuffer
	 */
	private $aliasBuffer = null;
	private $aliases = [];
	private $rAliases = [];
	private $hidden = [];
	private $skipUses = false;
//	private $currentNs = null;
//	private $currentClassName = null;

	/**
	 * @return AliasBuffer
	 */
	public function getAliasBuffer()
	{
		return $this->aliasBuffer;
	}

	public function setAliasBuffer(AliasBuffer $aliasBuffer)
	{
		$this->aliasBuffer = $aliasBuffer;
		return $this;
	}

	public function setSkipUses($skipUses)
	{
		$this->skipUses = $skipUses == true;
		return $this;
	}
	
	public function setCurrentClassName($currentName)
	{
		list($newNs) = NamespaceUtils::explodeFullName($currentName);
		$this->getClassNameAlias($currentName, $newNs);
		
		return $this;
	}
	
	public function getClassNameAlias($fullClassName, $newNs)
	{
		$newNs = NamespaceUtils::fixNamespace($newNs);
		list($ns, $className) = NamespaceUtils::explodeFullName($fullClassName);

		if ($this->skipUses) {
			return $newNs == $ns ? $className : $fullClassName;
		}

		if (isset($this->rAliases[$fullClassName])) {
			return $this->rAliases[$fullClassName];
		}

		$alias = $className;
		if (isset($this->aliases[$alias])) {
			$i = 2;
			do {
				$alias = $className.$i++;
			} while (isset($this->aliases[$alias]) && $i < 100);
			if (isset($this->aliases[$alias])) {
				throw new WrongStateException();
			}
		}
		$this->aliases[$alias] = $fullClassName;
		$this->rAliases[$fullClassName] = $alias;
		if ($ns == $newNs && $className == $alias) {
			$this->hidden[$alias] = true;
		}

		return $alias;
	}

	/**
	 * @param CodeStorage $codeStorage
	 */
	public function clearOldAliases(CodeStorage $codeStorage)
	{
		$isFirst = true;
		foreach ($this->aliasBuffer->getBuffers() as $fromTo) {
			list($from, $to) = $fromTo;
			$codeStorage->addReplace('', $from, $to);
			if (!$isFirst) {
				$whiteSubj = $codeStorage->get($to + 1);
				if (is_array($whiteSubj) && $whiteSubj[0] = T_WHITESPACE) {
					$codeStorage->addReplace('', $to + 1);
				}
				$whiteSubj = $codeStorage->get($from - 1);
				if (is_array($whiteSubj) && $whiteSubj[0] = T_WHITESPACE) {
					$codeStorage->addReplace('', $from - 1);
				}
			}
			$isFirst = false;
		}
	}

	public function getNewAliases($currentNs)
	{
		$currentNs = NamespaceUtils::fixNamespace($currentNs);

		$aliases = [];
		foreach ($this->aliases as $to => $from) {
			if (!isset($this->hidden[$to])) {
				if (NamespaceUtils::explodeFullName($from)[1] != $to) {
					$aliases[] = "use {$from} as {$to};";
				} else {
					$aliases[] = "use {$from};";
				}
			}
		}
		return $aliases;
	}
}
