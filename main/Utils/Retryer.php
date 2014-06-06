<?php
/**
 * Simple class to make N retries of given code
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2014.05.18
 */

class Retryer {

    protected $code;
    protected $retries = 3;

    /** @var int */
    protected $timeout = 1000000; //1 second (in microseconds)
    protected $exceptions = array();

    public static function create( $code ) {
        return new static($code);
    }

    public function __construct( $code ) {
        $this->code = $code;
    }

    /**
     * @return array
     */
    public function getExceptions() {
        return $this->exceptions;
    }

    /**
     * @param int $retries
     * @return static
     */
    public function setRetries($retries) {
        $this->retries = $retries;
        return $this;
    }

    /**
     * @return int
     */
    public function getRetries() {
        return $this->retries;
    }

    /**
     * @param int|callable $timeout
     * @return static
     */
    public function setTimeout($timeout) {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return int|callable
     */
    public function getTimeout() {
        return $this->timeout;
    }

    /** @return true|false */
    public function exec() {
        if ( !$this->retries ) {
            return false;
        }

        $exceptions = array();
        $code = $this->code;
        $result = null;
        $success = false;

        for( $try = 1; $try <= $this->retries; $try++ ) {
            $success = true;
            try {
                $result = $code();
            }
			catch (Exception $e) {
                $success = false;
                $exceptions[] = $e;
            }

            if ($success) {
                break;
            }

            if ( ($timeout = $this->timeout) && ($try < $this->retries) ) {
                if (is_callable($timeout)) {
                    $timeout = $timeout($try);
                }
                usleep($timeout);
            }
        }

        $this->exceptions = $exceptions;
        if ( !$success ) {
            throw RetryerException::create('Failed after retries limit reached!', $this->exceptions);
        }

        return $result;
    }
} 