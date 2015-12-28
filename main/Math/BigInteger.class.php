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
 * @ingroup Math
 **/
interface BigInteger extends Stringable
{
    /**
     * @return BigNumberFactory
     **/
    public static function getFactory();

    /**
     * @return BigInteger
     **/
    public function add(BigInteger $x);

    public function compareTo(BigInteger $x);

    /**
     * @return BigInteger
     **/
    public function mod(BigInteger $mod);

    /**
     * @return BigInteger
     **/
    public function pow(BigInteger $exp);

    /**
     * @return BigInteger
     **/
    public function modPow(BigInteger $exp, BigInteger $mod);

    /**
     * @return BigInteger
     **/
    public function subtract(BigInteger $x);

    /**
     * @return BigInteger
     **/
    public function mul(BigInteger $x);

    /**
     * @return BigInteger
     **/
    public function div(BigInteger $x);

    /**
     * convert to big-endian signed two's complement notation
     **/
    public function toBinary();

    public function intValue();

    public function floatValue();
}

