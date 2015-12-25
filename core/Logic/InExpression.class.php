<?php
/****************************************************************************
 *   Copyright (C) 2004-2009 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * Name says it all. :-)
 *
 * @ingroup Logic
 **/
class InExpression implements LogicalObject, MappableObject
{
    const
        IN = 'IN',
        NOT_IN = 'NOT IN';

    /** @var null  */
    private $left = null;
    /** @var Criteria|MappableObject|null|Query  */
    private $right = null;
    /** @var null  */
    private $logic = null;

    /**
     * InExpression constructor.
     * @param $left
     * @param $right
     * @param $logic
     */
    public function __construct($left, $right, $logic)
    {
        Assert::isTrue(
            ($right instanceof Query)
            || ($right instanceof Criteria)
            || ($right instanceof MappableObject)
            || is_array($right)
        );

        Assert::isTrue(
            ($logic == self::IN)
            || ($logic == self::NOT_IN)
        );

        $this->left = $left;
        $this->right = $right;
        $this->logic = $logic;
    }

    /**
     * @param ProtoDAO $dao
     * @param JoinCapableQuery $query
     * @return InExpression
     */
    public function toMapped(ProtoDAO $dao, JoinCapableQuery $query) : InExpression
    {
        if (is_array($this->right)) {
            $right = [];
            foreach ($this->right as $atom) {
                $right[] = $dao->guessAtom($atom, $query);
            }
        } elseif ($this->right instanceof MappableObject) {
            $right = $this->right->toMapped($dao, $query);
        } else {
            $right = $this->right;
        } // untransformable

        return new self(
            $dao->guessAtom($this->left, $query),
            $right,
            $this->logic
        );
    }

    /**
     * @param Dialect $dialect
     * @return string
     * @throws WrongArgumentException
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $string =
            '('
            . $dialect->toFieldString($this->left)
            . ' ' . $this->logic
            . ' ';

        $right = $this->right;

        if ($right instanceof DialectString) {

            $string .= '(' . $right->toDialectString($dialect) . ')';

        } elseif (is_array($right)) {

            $string .= (new SQLArray($right))
                ->toDialectString($dialect);

        } else {
            throw new WrongArgumentException(
                'sql select or array accepted by ' . $this->logic
            );
        }

        $string .= ')';

        return $string;
    }

    /**
     * @param Form $form
     * @return bool
     * @throws UnsupportedMethodException
     */
    public function toBoolean(Form $form) : bool
    {
        $left = $form->toFormValue($this->left);
        $right = $this->right;

        $both =
            (null !== $left)
            && (null !== $right);

        switch ($this->logic) {

            case self::IN:
                return $both && (in_array($left, $right));

            case self::NOT_IN:
                return $both && (!in_array($left, $right));

            default:

                throw new UnsupportedMethodException(
                    "'{$this->logic}' doesn't supported"
                );
        }
    }
}