<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * This class represents an encoded Internet email address using the syntax
 * of RFC822 / RFC 2047. Typical address syntax is of the form
 * "user@example.com" or "Personal Name <user@example.com>".
 *
 * @see javax.mail.internet.InternetAddress
 **/
final class MailAddress
{
    const RFC_MAX_ENCODED_WORD_LENGTH = 75;

    private $address = null;
    private $person = null;
    private $charset = 'UTF-8';

    public static function create()
    {
        return new self;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    public function toString()
    {
        $specials = preg_quote('()<>@,;:".[]\\', '/');
        $cr = '\015';
        $space = '\ ';

        $ctls = '\000-\037\177';
        $char = '\000-\177';

        $atom = "[^{$specials}{$space}{$ctls}]+";
        $asciiAtom = "[$char]+";

        if (
            !preg_match($this->getAddressRegExp($asciiAtom), $this->address)
            || !preg_match($this->getAddressRegExp($atom), $this->address)
        ) {
            throw new WrongArgumentException(
                'wrongly formatted address is encountered'
                . ' or local-part contains quoted-string'
                . ' or domain contains domain-literal (unimplemented yet)'
            );
        }

        if (!$this->person) {
            return $this->address;
        }


        $person = $this->person;

        if (
            !preg_match($this->getUnquotedPhraseRegexp($asciiAtom), $person)
            || !preg_match($this->getUnquotedPhraseRegexp($atom), $person)
        ) {
            // TODO: linear-white-space instead of simple space may be here
            $qtextWithNoExceptions = "[$char\ ]";
            $qtextExceptions = "[$cr\"\\\\]";

            // TODO: quoted-pair inside quoted-string is allowed too
            if (
                preg_match("/^{$qtextWithNoExceptions}*$/u", $person)
                && !preg_match("/{$qtextExceptions}/", $person)
            ) {
                $person = '"' . $person . '"';

            } else {
                $person = $this->getEncodedPerson();
            }
        }

        return "$person <{$this->address}>";
    }

    private function getAddressRegExp($atom)
    {
        // TODO: word may be a quoted-string also
        $word = $atom;

        $localPart = "{$word}(\.{$word})*";

        $domainRef = $atom;

        // TODO: sub-domain may be a domain-literal also
        $subDomain = $domainRef;

        $domain = "{$subDomain}(\.{$subDomain})*";

        $addrSpec = "{$localPart}@{$domain}";

        return "/^{$addrSpec}$/";
    }

    private function getUnquotedPhraseRegexp($atom)
    {
        return "/^({$atom})(\ {$atom})*$/u";
    }

    private function getEncodedPerson()
    {
        $result = null;

        $personChunk = null;

        for ($i = 0; $i < mb_strlen($this->person); $i++) {
            $symbol = mb_substr($this->person, $i, 1);

            $newLength = strlen(
                $this->getEncodedWord($personChunk . $symbol)
            );

            if ($newLength >= self::RFC_MAX_ENCODED_WORD_LENGTH) {
                $result = $this->appendChunk($result, $personChunk);

                $personChunk = null;
            }

            $personChunk .= $symbol;
        }

        if ($personChunk) {
            $result = $this->appendChunk($result, $personChunk);
        }

        return $result;
    }

    private function getEncodedWord($word)
    {
        return "=?{$this->charset}?B?" . base64_encode($word) . "?=";
    }

    private function appendChunk($encodedPerson, $personChunk)
    {
        $crlfSpace = "\015\012 ";

        return
            $encodedPerson
            . ($encodedPerson ? $crlfSpace : null)
            . $this->getEncodedWord($personChunk);
    }
}

?>
