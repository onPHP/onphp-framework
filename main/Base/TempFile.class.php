<?php

/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class TempFile
{
    private $path = null;

    public function __construct(
        $directory = 'temp-garbage/', $prefix = 'TmpFile'
    )
    {
        $this->path = FileUtils::makeTempFile($directory, $prefix);
    }

    public function __destruct()
    {
        try {
            unlink($this->path);
        } catch (BaseException $e) {
            // boo! deal with garbage yourself.
        }
    }

    public function getPath()
    {
        return $this->path;
    }
}