<?php

/***************************************************************************
 *   Copyright (C) 2013 by Evgeny V. Kokovikhin                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
final class MetaClassPUMLGenerator extends StaticFactory
{
    public static function generate(MetaClass $class)
    {
        if (!$class->doBuild()) {
            return null;
        }

        $out = '';

        $out .= "class " . $class->getName();


        $out .= " {\n";


        foreach ($class->getProperties() as $property) {
            $out .= "+get" . ucfirst($property->getName()) . "()\n";
        }

        $out .= "}\n";

        if ($class->getParent()) {
            $out .= $class->getParent()->getName() . " <|-- " . $class->getName() . "\n";
        }

        $out .= "\n";

        return $out;
    }

    public static function generateLinks(array $classes)
    {
        $links = [];

        foreach ($classes as $class) {
            Assert::isInstance($class, 'MetaClass');

            foreach ($class->getProperties() as $property) {
                if (
                    $property->getType() instanceof ObjectType
                    && $property->getType()->getClassName()
                    && $property->getRelation()
                ) {
                    switch ($property->getRelation()->getId()) {
                        case MetaRelation::ONE_TO_ONE:
                            $rel = ' -- ';

                            break;

                        case MetaRelation::ONE_TO_MANY:
                            $rel = ' *-- ';

                            break;

                        case MetaRelation::MANY_TO_MANY:
                            $rel = ' *--* ';

                            break;
                        default:
                            throw new WrongStateException();
                            break;
                    }

                    $links[] = $class->getName() . $rel . $property->getType()->getClassName() . "\n";

                }
            }

            $links = array_unique($links);
        }

        return implode("", $links) . "\n";
    }
}