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
 * SQLite dialect.
 *
 * @see http://www.sqlite.org/
 *
 * @ingroup DB
 **/
class LitePDODialect extends LiteDialect
{
    public function quoteValue($value)
    {
        /// @see Sequenceless for this convention

        if ($value instanceof Identifier && !$value->isFinalized())
            return 'null';

        if (Assert::checkInteger($value))
            return $value;

        return $this->getLink()->quote($value);
    }

    public function quoteBinary($data)
    {
        //here must be PDO::PARAM_LOB, but i couldn't get success result, so used base64_encode/decode
        return $this->getLink()->quote(base64_encode($data), PDO::PARAM_STR);
    }

    public function unquoteBinary($data)
    {
        try {
            return base64_decode($data);
        } catch (Exception $e) {
            throw new UnimplementedFeatureException('Wrong encoding, if you get it, throw correct exception');
        }
    }

    /**
     * @return PDO
     */
    protected function getLink()
    {
        return parent::getLink();
    }
}

?>