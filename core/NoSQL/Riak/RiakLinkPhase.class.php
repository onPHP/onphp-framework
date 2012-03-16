<?php
/**
 * The RiakLinkPhase object holds information about a Link phase in a
 * map/reduce operation.
 * @package RiakLinkPhase
 */
class RiakLinkPhase {
  /**
   * Construct a RiakLinkPhase object.
   * @param string $bucket - The bucket name.
   * @param string $tag - The tag.
   * @param boolean $keep - True to return results of this phase.
   */
  function RiakLinkPhase($bucket, $tag, $keep) {
    $this->bucket = $bucket;
    $this->tag = $tag;
    $this->keep = $keep;
  }

  /**
   * Convert the RiakLinkPhase to an associative array. Used
   * internally.
   */
  function to_array() {
    $stepdef = array("bucket"=>$this->bucket,
                     "tag"=>$this->tag,
                     "keep"=>$this->keep);
    return array("link"=>$stepdef);
  }
}
