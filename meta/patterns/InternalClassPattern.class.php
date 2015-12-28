<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Patterns
 **/
final class InternalClassPattern
    extends BasePattern
    implements GenerationPattern
{
    /**
     * @return InternalClassPattern
     **/
    public function build(MetaClass $class)
    {
        return $this;
    }

    public function tableExists()
    {
        return false;
    }

    public function daoExists()
    {
        return true;
    }
}

?>