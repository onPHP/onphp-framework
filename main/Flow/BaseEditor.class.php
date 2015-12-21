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
 * @ingroup Flow
 **/
abstract class BaseEditor implements Controller
{
    const COMMAND_SUCCEEDED = 'success';
    const COMMAND_FAILED = 'error';

    // to be redefined in __construct
    protected $commandMap = array();
    protected $defaultAction = 'edit';

    protected $map = null;
    protected $subject = null;

    protected $idFieldName = null;

    public function __construct(Prototyped $subject)
    {
        $this->subject = $subject;

        $form =
            $this->subject->proto()->makeForm()->add(
                Primitive::choice('action')->setList($this->commandMap)->
                setDefault($this->defaultAction)
            );

        if ($this->idFieldName)
            $form->add(
                Primitive::alias($this->idFieldName, $form->get('id'))
            );

        $this->map =
            MappedForm::create($form)->
            addSource('id', RequestType::get())->
            addSource('action', RequestType::get())->
            setDefaultType(RequestType::post());

        if ($this->idFieldName)
            $this->map->addSource($this->idFieldName, RequestType::get());
    }

    /**
     * @return ModelAndView
     **/
    public function postHandleRequest(ModelAndView $mav, HttpRequest $request)
    {
        $form = $this->getForm();

        if ($mav->getView() == self::COMMAND_SUCCEEDED) {

            $mav->setView(new RedirectToView(get_class($this)));

            $mav->getModel()->
            drop('id');

        } else {
            $mav->setView(get_class($this));

            if ($command = $form->getValue('action'))
                $mav->getModel()->set('action', $command);
            else
                $form->dropAllErrors();

            $mav->getModel()->set('form', $form);
        }

        return $mav;
    }

    /**
     * @return Form
     **/
    public function getForm()
    {
        return $this->map->getForm();
    }
}