<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Connected to concrete table DBField.
 *
 * @ingroup OSQL
 * @ingroup Module
 **/
class SelectField extends FieldTable implements Aliased
{
    /** @var null  */
    private $alias = null;

    /**
     * SelectField constructor.
     * @param DialectString $field
     * @param $alias
     */
    public function __construct(DialectString $field, $alias)
    {
        parent::__construct($field);
        $this->alias = $alias;
    }

    /**
     * @return null
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return null
     */
    public function getName()
    {
        if ($this->field instanceof DBField) {
            return $this->field->getField();
        }

        return $this->alias;
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect)
    {
        return
            parent::toDialectString($dialect)
            . (
            $this->alias
                ? ' AS ' . $dialect->quoteField($this->alias)
                : null
            );
    }
}
