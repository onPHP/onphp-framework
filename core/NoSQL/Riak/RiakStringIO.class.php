<?php
/**
 * Private class used to accumulate a CURL response.
 * @package RiakStringIO
 */
class RiakStringIO {
  function RiakStringIO() {
    $this->contents = '';
  }

  function write($ch, $data) {
    $this->contents .= $data;
    return strlen($data);
  }

  function contents() {
    return $this->contents;
  }
}
