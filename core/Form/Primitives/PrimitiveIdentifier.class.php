<?php
/***************************************************************************
 *   Copyright (C) 2006-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Primitives
 **/
class PrimitiveIdentifier extends IdentifiablePrimitive
{
    private $methodName = 'getById';

    /**
     * @throws WrongArgumentException
     * @return PrimitiveIdentifier
     **/
    public function of($class)
    {
        $className = $this->guessClassName($class);

        Assert::classExists($className);

        Assert::isInstance(
            $className,
            'DAOConnected',
            "class '{$className}' must implement DAOConnected interface"
        );

        $this->className = $className;

        return $this;
    }

    /**
     * @return GenericDAO
     **/
    public function dao()
    {
        Assert::isNotNull(
            $this->className,
            'specify class name first of all'
        );

        return call_user_func([$this->className, 'dao']);
    }

    /**
     * @return PrimitiveIdentifier
     **/
    public function setMethodName($methodName)
    {
        if (is_callable($methodName)) {
            /* all ok, call what you want */
        } elseif (strpos($methodName, '::') === false) {
            $dao = $this->dao();

            Assert::isTrue(
                method_exists($dao, $methodName),
                "knows nothing about '" . get_class($dao)
                . "::{$methodName}' method"
            );
        } else {
            ClassUtils::checkStaticMethod($methodName);
        }

        $this->methodName = $methodName;

        return $this;
    }

    public function importValue($value)
    {
        if ($value instanceof Identifiable) {
            try {
                Assert::isInstance($value, $this->className);

                return
                    $this->import(
                        [$this->getName() => $this->actualExportValue($value)]
                    );

            } catch (WrongArgumentException $e) {
                return false;
            }
        }

        return parent::importValue($value);
    }

    public function import($scope)
    {
        if (!$this->className)
            throw new WrongStateException(
                "no class defined for PrimitiveIdentifier '{$this->name}'"
            );

        $className = $this->className;

        if (
            isset($scope[$this->name])
            && $scope[$this->name] instanceof $className
        ) {
            $value = $scope[$this->name];

            $this->raw = $this->actualExportValue($value);
            $this->setValue($value);

            return $this->imported = true;
        }

        $result = parent::import($scope);

        if ($result === true) {
            try {
                $result = $this->actualImportValue($this->value);

                Assert::isInstance($result, $className);

                $this->value = $result;

                return true;

            } catch (WrongArgumentException $e) {
                // not imported
            } catch (ObjectNotFoundException $e) {
                // not imported
            }

            $this->value = null;

            return false;
        }

        return $result;
    }

    protected function actualImportValue($value)
    {
        if (is_callable($this->methodName)) {
            return call_user_func($this->methodName, $value);
        } elseif (strpos($this->methodName, '::') === false) {
            return $this->dao()->{$this->methodName}($value);
        } else {
            return ClassUtils::callStaticMethod($this->methodName, $value);
        }
    }
}