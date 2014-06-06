<?php
/**
 * Exception thrown by Retryer class
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2014.05.18
 */

class RetryerException extends Exception{

    /** @var Exception[] */
    protected $exceptions = array();

    /** @return array */
    public function getExceptions() {
        return $this->exceptions;
    }

    public static function create($message, $exceptions) {
        $e = new static($message);
        $e->exceptions = $exceptions;
        return $e;
    }

    public function getMessageReadable() {
        $result = array();
        $try = 1;
        foreach ($this->exceptions as $exception) {
            $result[] = ($try++) . '. (' .get_class($exception) .') ' . $exception->getMessage() . ' @' . $exception->getFile() . ':' . $exception->getLine();
        }

        return implode("\n", $result);
    }

} 