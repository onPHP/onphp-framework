<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Containers
 **/
abstract class ManyToManyLinked extends UnifiedContainer
{
    public function __construct(
        Identifiable $parent,
        GenericDAO $dao,
        $lazy = true
    ) {
        parent::__construct($parent, $dao, $lazy);

        $worker =
            $lazy
                ? 'ManyToManyLinkedLazy'
                : 'ManyToManyLinkedFull';

        $this->worker = new $worker($this);
    }

    abstract public function getHelperTable();

    public function getParentTableIdField()
    {
        return 'id';
    }
}

?>