<?php
/***************************************************************************
 *   Copyright (C) 2015 Anton Gurov and Sheyn Davyd                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Types
 * @see http://www.postgresql.org/docs/9.4/static/datatype-json.html
 **/
class JsonType extends ObjectType
{
    /**
     * @return string
     */
    public function getPrimitiveName()
    {
        return 'json';
    }

    /**
     * @return bool
     */
    public function isGeneric()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isMeasurable()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getDeclaration()
    {
        return 'json';
    }

    /**
     * @param MetaClass $class
     * @param MetaClassProperty $property
     * @param MetaClassProperty|null $holder
     * @return string
     */
    public function toGetter(
        MetaClass $class,
        MetaClassProperty $property,
        MetaClassProperty $holder = null
    )
    {
        if ($holder)
            $name = $holder->getName() . '->get' . ucfirst($property->getName()) . '()';
        else
            $name = $property->getName();
        $methodName = 'get' . ucfirst($property->getName());
        return <<<EOT
/**
 * @return array
**/
public function {$methodName}()
{
	return \$this->{$name};
}
EOT;
    }

    /**
     * @param MetaClass $class
     * @param MetaClassProperty $property
     * @param MetaClassProperty|null $holder
     * @return string
     * @throws WrongArgumentException
     */
    public function toSetter(
        MetaClass $class,
        MetaClassProperty $property,
        MetaClassProperty $holder = null
    )
    {
        $name = $property->getName();
        $methodName = 'set' . ucfirst($name);
        $default = $property->isRequired() ? '' : ' = null';
        if ($holder) {
            Assert::isUnreachable();
        } else {
            return <<<EOT
/**
 * @return {$class->getName()}
**/
public function {$methodName}(array \${$name}{$default})
{
	\$this->{$name} = \${$name};
	return \$this;
}
EOT;
        }
        Assert::isUnreachable();
    }

    public function toColumnType()
    {
        return '(new DataType(DataType::JSON))';
    }
}

?>