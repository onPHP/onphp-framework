<?php
/**
 * The RiakLink object represents a link from one Riak object to
 * another.
 * @package RiakLink
 */
class RiakLink {
  /**
   * Construct a RiakLink object.
   * @param string $bucket - The bucket name.
   * @param string $key - The key.
   * @param string $tag - The tag.
   */
  function RiakLink($bucket, $key, $tag=NULL) {
    $this->bucket = $bucket;
    $this->key = $key;
    $this->tag = $tag;
    $this->client = NULL;
  }

  /**
   * Retrieve the RiakObject to which this link points.
   * @param integer $r - The R-value to use.
   * @return RiakObject
   */
  function get($r=NULL) {
    return $this->client->bucket($this->bucket)->get($this->key, $r);
  }

  /**
   * Retrieve the RiakObject to which this link points, as a binary.
   * @param integer $r - The R-value to use.
   * @return RiakObject
   */
  function getBinary($r=NULL) {
    return $this->client->bucket($this->bucket)->getBinary($this->key, $r);
  }

  /**
   * Get the bucket name of this link.
   * @return string
   */
  function getBucket() {
    return $this->bucket;
  }

  /**
   * Set the bucket name of this link.
   * @param string $name - The bucket name.
   * @return $this
   */
  function setBucket($name) {
    $this->bucket = $bucket;
    return $this;
  }

  /**
   * Get the key of this link.
   * @return string
   */
  function getKey() {
    return $this->key;
  }

  /**
   * Set the key of this link.
   * @param string $key - The key.
   * @return $this
   */
  function setKey($key) {
    $this->key = $key;
    return $this;
  }

  /**
   * Get the tag of this link.
   * @return string
   */
  function getTag() {
    if ($this->tag == null)
      return $this->bucket;
    else
      return $this->tag;
  }

  /**
   * Set the tag of this link.
   * @param string $tag - The tag.
   * @return $this
   */
  function setTag($tag) {
    $this->tag = $tag;
    return $this;
  }

  /**
   * Convert this RiakLink object to a link header string. Used internally.
   */
  function toLinkHeader($client) {
    $link = "</" .
      $client->prefix . "/" .
      urlencode($this->bucket) . "/" .
      urlencode($this->key) . ">; riaktag=\"" .
      urlencode($this->getTag()) . "\"";
    return $link;
  }

  /**
   * Return true if the links are equal.
   * @param RiakLink $link - A RiakLink object.
   * @return boolean
   */
  function isEqual($link) {
    $is_equal =
      ($this->bucket == $link->bucket) &&
      ($this->key == $link->key) &&
      ($this->getTag() == $link->getTag());
    return $is_equal;
  }
}
