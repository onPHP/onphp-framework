<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Atom for ternary-based logic.
 *
 * @ingroup Base
 * @ingroup Module
 **/
class Ternary implements Stringable
{
    /**
     * @var null|boolean
     */
    private $trinity = null;    // ;-)

    /**
     * Ternary constructor.
     * @param null $boolean
     */
    public function __construct($boolean = null)
    {
        return $this->setValue($boolean);
    }

    /**
     * @param null $boolean
     * @return Ternary
     * @throws WrongArgumentException
     */
    public function setValue($boolean = null) : Ternary
    {
        Assert::isTernaryBase($boolean);

        $this->trinity = $boolean;

        return $this;
    }

    /**
     * @param null $boolean
     * @return Ternary
     */
    public static function create($boolean = null) : Ternary
    {
        return new self($boolean);
    }

    /**
     * @param $value
     * @param $true
     * @param $false
     * @param null $null
     * @return Ternary
     * @throws WrongArgumentException
     */
    public static function spawn($value, $true, $false, $null = null)
    {
        if ($value === $true) {
            return new Ternary(true);
        } elseif ($value === $false) {
            return new Ternary(false);
        } elseif (($value === $null) || ($null === null)) {
            return new Ternary(null);
        } else /* if ($value !== $null && $null !== null) or anything else */ {
            throw new WrongArgumentException(
                "failed to spawn Ternary from '{$value}' switching on " .
                "'{$true}', '{$false}' and '{$null}'"
            );
        }
    }

    /**
     * @return boolean
     */
    public function isNull() : bool
    {
        return (null === $this->trinity);
    }

    /**
     * @return boolean
     */
    public function isTrue() : bool
    {
        return (true === $this->trinity);
    }

    /**
     * @return boolean
     */
    public function isFalse() : bool
    {
        return (false === $this->trinity);
    }

    /**
     * @return Ternary
     */
    public function setNull() : Ternary
    {
        $this->trinity = null;

        return $this;
    }

    /**
     * @return Ternary
     **/
    public function setTrue() : Ternary
    {
        $this->trinity = true;

        return $this;
    }

    /**
     * @return Ternary
     **/
    public function setFalse() : Ternary
    {
        $this->trinity = false;

        return $this;
    }

    /**
     * @return boolean|null
     */
    public function getValue()
    {
        return $this->trinity;
    }

    /**
     * to string
     *
     * @return string
     * @throws WrongStateException
     */
    public function toString() : string
    {
        return $this->decide('true', 'false', 'null');
    }

    /**
     * @param $true
     * @param $false
     * @param null $null
     * @return null
     * @throws WrongStateException
     */
    public function decide($true, $false, $null = null)
    {
        if ($this->trinity === true) {
            return $true;
        } elseif ($this->trinity === false) {
            return $false;
        } elseif ($this->trinity === null) {
            return $null;
        }

        throw new WrongStateException(
            'mama, weer all crazee now!' // (c) Slade
        );
    }
}
