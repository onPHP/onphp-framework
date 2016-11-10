<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 10.11.16
 * Time: 11:49
 */
class CreateSchemaQuery extends QueryIdentification
{
    private $schema;

    function __construct($schema)
    {
        $this->schema = $schema;
    }

    public function toDialectString(Dialect $dialect)
    {
        $out = 'CREATE SCHEMA IF NOT EXISTS ' . $dialect->quoteSchema($this->schema) . ';';

        return $out;
    }
}