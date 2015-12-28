<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Http
 **/
interface HttpResponse
{
    /**
     * @return HttpStatus
     **/
    public function getStatus();

    public function getReasonPhrase();

    /**
     * @return array of headers
     **/
    public function getHeaders();

    public function hasHeader($name);

    public function getHeader($name);

    public function getBody();
}

?>