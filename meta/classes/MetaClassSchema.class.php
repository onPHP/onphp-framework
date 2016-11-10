<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10.11.16
 * Time: 11:23
 */

class MetaClassSchema {

    private $schema;

    function __construct($schema)
    {
        $this->schema = $schema;
    }

    public function buildSchema()
    {
        $out = <<<EOT
setSchema('$this->schema')
EOT;

        return $out;
    }
}