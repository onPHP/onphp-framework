<?php
/***************************************************************************
 *   Copyright (C) 2012 by Aleksey S. Denisov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Uncachers
 **/
class UncacherCommonDaoWorker extends UncacherBaseDaoWorker
{
    function __construct($className, $idKey)
    {
        parent::__construct($className, $idKey);
    }

    protected function uncacheClassName($className, $idKeys)
    {
        ClassUtils::callStaticMethod("$className::dao")->uncacheLists();
        parent::uncacheClassName($className, $idKeys);
    }
}