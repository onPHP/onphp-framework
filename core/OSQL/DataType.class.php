<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Generic SQL data types.
 *
 * @ingroup OSQL
 **/
class DataType extends Enumeration implements DialectString
{
    const
        SMALLINT = 0x001001,
        INTEGER = 0x001002,
        BIGINT = 0x001003,
        NUMERIC = 0x001704,
        REAL = 0x001105,
        DOUBLE = 0x001106,
        BOOLEAN = 0x000007,
        CHAR = 0x000108,
        VARCHAR = 0x000109,
        TEXT = 0x00000A,
        DATE = 0x00000B,
        TIME = 0x000A0C,
        TIMESTAMP = 0x000A0D,
        TIMESTAMPTZ = 0x000A0E,
        INTERVAL = 0x00000F,
        BINARY = 0x00000E,
        IP = 0x000010,
        IP_RANGE = 0x000011,
        HAVE_SIZE = 0x000100,
        HAVE_PRECISION = 0x000200,
        HAVE_SCALE = 0x000400,
        HAVE_TIMEZONE = 0x000800,
        CAN_BE_UNSIGNED = 0x001000;

    /** @var array  */
    protected $names = [
        self::SMALLINT => 'SMALLINT',
        self::INTEGER => 'INTEGER',
        self::BIGINT => 'BIGINT',
        self::NUMERIC => 'NUMERIC',
        self::REAL => 'FLOAT',
        self::DOUBLE => 'DOUBLE PRECISION',
        self::BOOLEAN => 'BOOLEAN',
        self::CHAR => 'CHARACTER',
        self::VARCHAR => 'CHARACTER VARYING',
        self::TEXT => 'TEXT',
        self::DATE => 'DATE',
        self::TIME => 'TIME',
        self::TIMESTAMP => 'TIMESTAMP',
        self::TIMESTAMPTZ => 'TIMESTAMP',
        self::INTERVAL => 'INTERVAL',
        self::BINARY => 'BINARY',
        self::IP => 'IP',
        self::IP_RANGE => 'IP_RANGE'
    ];

    /** @var null  */
    private $size = null;
    /** @var null  */
    private $precision = null;
    /** @var null  */
    private $scale = null;
    /** @var bool  */
    private $null = true;
    /** @var bool  */
    private $timezone = false;
    /** @var bool  */
    private $unsigned = false;

    /**
     * @deprecated
     * @return DataType
     **/
    public static function create($id)
    {
        return new self($id);
    }

    /**
     * @return int
     */
    public static function getAnyId()
    {
        return self::BOOLEAN;
    }

    /**
     * @return null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @throws WrongArgumentException
     * @return DataType
     **/
    public function setSize($size)
    {
        Assert::isInteger($size);
        Assert::isTrue($this->hasSize());

        $this->size = $size;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSize() : bool
    {
        return (bool) ($this->id & self::HAVE_SIZE);
    }

    /**
     * @return null
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @throws WrongArgumentException
     * @return DataType
     **/
    public function setPrecision($precision)
    {
        Assert::isInteger($precision);
        Assert::isTrue(($this->id & self::HAVE_PRECISION) > 0);

        $this->precision = $precision;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPrecision()
    {
        return (bool) ($this->id & self::HAVE_PRECISION);
    }

    /**
     * @return null
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * @throws WrongArgumentException
     * @return DataType
     **/
    /**
     * @param $scale
     * @return DataType
     * @throws WrongArgumentException
     */
    public function setScale($scale) : DataType
    {
        Assert::isInteger($scale);
        Assert::isTrue(($this->id & self::HAVE_SCALE) > 0);

        $this->scale = $scale;

        return $this;
    }

    /**
     * @throws WrongArgumentException
     * @return DateType
     **/
    public function setTimezoned($zoned = false) : DateType
    {
        Assert::isTrue(($this->id & self::HAVE_TIMEZONE) > 0);

        $this->timezone = (true === $zoned);

        return $this;
    }

    /**
     * @return bool
     */
    public function isTimezoned() : bool
    {
        return $this->timezone;
    }

    /**
     * @return bool
     */
    public function isNull() : bool
    {
        return $this->null;
    }

    /**
     * @param bool $isNull
     * @return DataType
     */
    public function setNull(bool $isNull = false) : DataType
    {
        $this->null = ($isNull === true);

        return $this;
    }

    public function isUnsigned()
    {
        return $this->unsigned;
    }

    /**
     * @param bool $unsigned
     * @return DataType
     * @throws WrongArgumentException
     */
    public function setUnsigned(bool $unsigned = false) : DataType
    {
        Assert::isTrue(($this->id && self::CAN_BE_UNSIGNED) > 0);

        $this->unsigned = ($unsigned === true);

        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return null|string
     * @throws WrongStateException
     */
    public function toDialectString(Dialect $dialect)
    {
        $out = $dialect->typeToString($this);

        if ($this->unsigned) {
            $out .= ' UNSIGNED';
        }

        if ($this->id & self::HAVE_PRECISION) {
            if ($this->precision) {

                switch ($this->id) {

                    case self::TIME:
                    case self::TIMESTAMP:

                        $out .= "({$this->precision})";
                        break;

                    case self::NUMERIC:

                        $out .=
                            $this->precision
                                ? "({$this->size}, {$this->precision})"
                                : "({$this->size})";
                        break;

                    default:

                        throw new WrongStateException();
                }
            }
        } elseif ($this->hasSize()) {
            if (!$this->size) {
                throw new WrongStateException(
                    "type '{$this->name}' must have size"
                );
            }

            $out .= "({$this->size})";
        }

        if ($this->id & self::HAVE_TIMEZONE) {
            $out .= $dialect->timeZone($this->timezone);
        }

        $out .=
            $this->null
                ? ' NULL'
                : ' NOT NULL';

        return $out;
    }
}
