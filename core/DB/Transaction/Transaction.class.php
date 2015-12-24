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
 * Transaction's factory.
 *
 * @ingroup Transaction
 **/
class Transaction extends StaticFactory
{
    /**
     * @param DB $db
     * @return DBTransaction
     */
    public static function immediate(DB $db) : DBTransaction
    {
        return new DBTransaction($db);
    }

    /**
     * @param DB $db
     * @return TransactionQueue
     */
    public static function deferred(DB $db) : TransactionQueue
    {
        return new TransactionQueue($db);
    }

    /**
     * @param DB $db
     * @return FakeTransaction
     */
    public static function fake(DB $db) : FakeTransaction
    {
        return new FakeTransaction($db);
    }
}
