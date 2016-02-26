<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
class ForbiddenCommand implements EditorCommand
{
    /**
     * @return ModelAndView
     **/
    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        return (new ModelAndView())->setView(EditorController::COMMAND_FAILED);
    }
}