<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Flow
 **/
class DropCommand implements EditorCommand
{
    /**
     * @deprecated
     *
     * @return DropCommand
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return ModelAndView
     **/
    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        if ($object = $form->getValue('id')) {

            if ($object instanceof Identifiable) {

                $object->dao()->drop($object);

                return
                    (new ModelAndView())->setView(BaseEditor::COMMAND_SUCCEEDED);

            } else {
                // already deleted
                $form->markMissing('id');
            }
        }

        return new ModelAndView();
    }
}

?>