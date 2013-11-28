<?php
/**
 * Created by PhpStorm.
 * User: byorty
 * Date: 22.11.13
 * Time: 20:51
 */

class DBHstoreField extends DBField {

    private $key = null;

    /**
     * @return DBHstoreField
     **/
    public static function create($field, $table = null, $key = null)
    {
        $self = new self($field, $table);

        if ($key)
            $self->setKey($key);

        return $self;
    }

    public function toDialectString(Dialect $dialect)
    {
        $field =
            (
            $this->getTable()
                ? $this->getTable()->toDialectString($dialect).'.'
                : null
            )
            .$dialect->quoteField($this->getField());

        if ($this->key) {
            $field .= '->\'' . $this->key . '\'';
        }

        return
            $this->cast
                ? $dialect->toCasted($field, $this->cast)
                : $field;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key) {
        $this->key = $key;
        return $this;
    }
} 