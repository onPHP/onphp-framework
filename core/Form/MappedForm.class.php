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
 * @ingroup Form
 **/
final class MappedForm
{
    /** @var Form|null  */
    private $form = null;
    /** @var null  */
    private $type = null;

    /** @var array  */
    private $map = [];

    /**
     * MappedForm constructor.
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
     * @deprecated
     * @return MappedForm
     **/
    public static function create(Form $form)
    {
        return new self($form);
    }

    /**
     * @return Form
     **/
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param RequestType $type
     * @return MappedForm
     */
    public function setDefaultType(RequestType $type) : MappedForm
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param $primitiveName
     * @param RequestType $type
     * @return MappedForm
     * @throws MissingElementException
     */
    public function addSource($primitiveName, RequestType $type) : MappedForm
    {
        $this->checkExistence($primitiveName);

        $this->map[$primitiveName][] = $type;

        return $this;
    }

    /**
     * @param $name
     * @return $this
     * @throws MissingElementException
     */
    private function checkExistence($name)
    {
        if (!$this->form->exists($name)) {
            throw new MissingElementException(
                "there is no '{$name}' primitive"
            );
        }

        return $this;
    }

    /**
     * @param HttpRequest $request
     * @return MappedForm
     */
    public function import(HttpRequest $request) : MappedForm
    {
        foreach ($this->form->getPrimitiveNames() as $name) {
            $this->importOne($name, $request);
        }

        $this->form->checkRules();

        return $this;
    }

    /**
     * @param $name
     * @param HttpRequest $request
     * @return MappedForm
     * @throws MissingElementException
     */
    public function importOne($name, HttpRequest $request) : MappedForm
    {
        $this->checkExistence($name);

        $scopes = [];

        if (isset($this->map[$name])) {
            foreach ($this->map[$name] as $type) {
                $scopes[] = $request->getByType($type);
            }
        } elseif ($this->type) {
            $scopes[] = $request->getByType($this->type);
        }

        $first = true;
        foreach ($scopes as $scope) {
            if ($first) {
                $this->form->importOne($name, $scope);
                $first = false;
            } else {
                $this->form->importOneMore($name, $scope);
            }
        }

        return $this;
    }

    /**
     * @param RequestType $type
     * @return array
     */
    public function export(RequestType $type) : array
    {
        $result = [];

        $default = ($this->type == $type);

        foreach ($this->form->getPrimitiveList() as $name => $prm) {
            if (
                (
                    isset($this->map[$name])
                    && in_array($type, $this->map[$name])
                )
                || (
                    !isset($this->map[$name])
                    && $default
                )
            ) {
                if ($prm->getValue()) {
                    $result[$name] = $prm->exportValue();
                }
            }
        }

        return $result;
    }
}
