<?php

/***************************************************************************
 *   Copyright (C) 2007-2008 by Ivan Y. Khvostishkov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
abstract class FormBuilder extends PrototypedBuilder
{
    /**
     * @return Form
     **/
    public function fillOwn($object, &$result)
    {
        Assert::isInstance($result, 'Form');
        /** @var  Form $result */
        foreach ($this->getFormMapping() as $primitive) {
            if (
                $primitive instanceof PrimitiveForm
                && $result->exists($primitive->getName())
                && $primitive->isComposite()
            ) {

                Assert::isEqual(
                    $primitive->getProto(),
                    $result->get($primitive->getName())->getProto()
                );

                continue;
            }

            $result->add($primitive);
        }

        $result = parent::fillOwn($object, $result);

        $result->setProto($this->proto);

        return $result;
    }

    /**
     * @return Form
     **/
    protected function createEmpty()
    {
        return Form::create();
    }
}
