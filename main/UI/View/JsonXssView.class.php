<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Flow
 **/
class JsonXssView extends JsonPView
{
    /**
     * Javascript valid function name pattern
     */
    const CALLBACK_PATTERN = '/^[\$A-Z_][0-9A-Z_\$\.]*$/i';

    /**
     * Default prefix
     * @var string
     */
    protected $prefix = 'window.';

    /**
     * Default callback
     * @var string
     */
    protected $callback = 'name';

    /**
     * @param $value
     * @return JsonXssView
     * @throws WrongArgumentException
     */
    public function setPrefix($value)
    {
        if (!preg_match(static::CALLBACK_PATTERN, $value)) {
            throw new WrongArgumentException('invalid prefix name, you should set valid javascript function name! gived "' . $value . '"');
        }

        $this->prefix = $value;

        return $this;
    }

    /**
     * @param Model $model
     * @return string
     */
    public function toString($model = null) : string
    {
        /*
         * Escaping warning datas
         */
        $this->setHexAmp(true);
        $this->setHexApos(true);
        $this->setHexQuot(true);
        $this->setHexTag(true);

        $json = JsonView::toString($model);

        $json = str_ireplace(
            ['u0022', 'u0027'],
            ['\u0022', '\u0027'],
            $json
        );

        $result = '<script type="text/javascript">' . "\n";
        $result .= "\t" . $this->prefix . $this->callback . '=\'' . $json . '\';' . "\n";
        $result .= '</script>' . "\n";

        return $result;
    }

}
