<?php

namespace Scrapers\Trackers\Exception;

/**
 * Class ScraperException
 * @package Scrapers\Trackers\Exception
 */
class ScraperException extends \Exception
{
    /**
     * @var bool
     */
    private $connection_error;

    /**
     * ScraperException constructor.
     * @param string $message
     * @param int $code
     * @param bool $connection_error
     */
    public function __construct($message, $code = 0, $connection_error = false)
    {
        $this->connection_error = $connection_error;
        parent::__construct($message, $code);
    }

    /**
     * @return bool
     */
    public function isConnectionError()
    {
        return $this->connection_error;
    }
}
