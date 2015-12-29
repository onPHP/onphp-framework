<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Builders
 **/
class AutoProtoClassBuilder extends BaseBuilder
{
    /**
     * @param MetaClass $class
     * @return string
     */
    public static function build(MetaClass $class)
    {
        $out = self::getHead();

        $parent = $class->getParent();

        if ($class->hasBuildableParent()) {
            $parentName = 'Proto' . $parent->getName();
        } else {
            $parentName = 'AbstractProtoClass';
        }

        $out .= <<<EOT
abstract class AutoProto{$class->getName()} extends {$parentName}
{
EOT;
        $classDump = self::dumpMetaClass($class);

        $out .= <<<EOT

{$classDump}
}

EOT;

        return $out . self::getHeel();
    }

    /**
     * @param MetaClass $class
     * @return string
     */
    private static function dumpMetaClass(MetaClass $class) : string
    {
        $propertyList = $class->getWithInternalProperties();

        $out = <<<EOT
protected function makePropertyList()
{

EOT;

        if ($class->hasBuildableParent()) {
            $out .= <<<EOT
    return array_merge(
        parent::makePropertyList(),
        [

EOT;
            if ($class->getIdentifier()) {
                $propertyList[$class->getIdentifier()->getName()] =
                    $class->getIdentifier();
            }
        } else {
            $out .= <<<EOT
    return
        [

EOT;
        }

        $list = [];

        foreach ($propertyList as $property) {
            $list[] =
                "            '{$property->getName()}' => "
                . $property->toLightProperty($class)->toString();
        }

        $out .= implode(",\n", $list);

        if ($class->hasBuildableParent()) {
            $out .= <<<EOT
\n        ]
\n    );
\n}
EOT;

        } else {

            $out .= <<<EOT

        ];
\n}
EOT;
        }
        return $out;
    }
}
