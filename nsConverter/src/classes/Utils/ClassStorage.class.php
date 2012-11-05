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

class ClassStorage
{
	private $classStorage = [];
	private $oldNamesMap = [];

	/**
	 * @param \Onphp\NsConverter\NsClass $class
	 * @return \Onphp\NsConverter\ClassStorage
	 * @throws \Onphp\WrongStateException
	 */
	public function addClass(NsClass $class)
	{
		$fullName = $class->getFullName();
		$fullNewName = $class->getFullNewName();
		if (isset($this->classStorage[$fullNewName])) {
			$addedClass = $this->classStorage[$fullNewName];
			/* @var $addedClass \Onphp\NsConverter\NsClass */
			if (
				$addedClass->getName() == $class->getName()
				&& $addedClass->getNamespace() == $class->getNamespace()
				&& $addedClass->getNewNamespace() == $class->getNewNamespace()
			) {
				return $this;
			}
			throw new \Onphp\WrongStateException('Class name '.$fullNewName.' already added');
		}
		if (isset($this->oldNamesMap[$fullName])) {
//			var_dump($this->oldNamesMap[$fullName]); exit;
			throw new \Onphp\WrongStateException('Old Class name '.$fullName.' already added');
		}
		$this->oldNamesMap[$fullName] = $fullNewName;
		$this->classStorage[$fullNewName] = $class;
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @param string $namespace
	 * @return \Onphp\NsConverter\NsClass
	 */
	public function findByClassNs($name, $namespace = null)
	{
		$fullName = NamespaceUtils::fixNamespace($namespace).$name;
		if (isset($this->oldNamesMap[$fullName])) {
			$fullName = $this->oldNamesMap[$fullName];
		}
		
		return isset($this->classStorage[$fullName])
			? $this->classStorage[$fullName]
			: null;
	}
	
	/**
	 * @param string $fullName
	 * @return \Onphp\NsConverter\NsClass
	 */
	public function findByFullName($fullName)
	{
		list($namespace, $name) = NamespaceUtils::explodeFullName($fullName);
		return $this->findByClassNs($name, $namespace);
	}

	/**
	 * @param string $fullName
	 * @return \Onphp\NsConverter\NsClass
	 */
	public function findByClassName($className, $currentNs)
	{
		if (mb_strpos($className, '\\') !== 0) {
			if ($class = $this->findByClassNs($className, $currentNs)) {
				return $class;
			}
		}
		return $this->findByFullName($className);
	}
	
	public function export()
	{
		$config = [];
		foreach ($this->classStorage as $class) {
			/* @var $class \Onphp\NsConverter\NsClass */
			$config[] = "{$class->getName()}:{$class->getNamespace()}:{$class->getNewNamespace()}";
		}
		return implode("\n", $config);
	}
	
	public function import($data)
	{
		foreach (explode("\n", $data) as $row) {
			if (!trim($row))
				continue;
			if (mb_strpos($row, ';') === 0)
				continue;
			
			list($classname, $oldNamespace, $newNamespace) = explode(':', $row);
			
			$class = NsClass::create()
				->setName($classname)
				->setNamespace($oldNamespace)
				->setNewNamespace($newNamespace);
			$this->addClass($class);
		}
	}
}

?>