<?php
/*

Torrent Scraper Base Class
v1.0

2010 by Johannes Zinnau
johannes@johnimedia.de

Licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License
http://creativecommons.org/licenses/by-sa/3.0/

It would be very nice if you send me your changes on this class, so that i can include them if they are improve it.
Thanks!

Usage:
See udptscraper.php or httptscraper.php
*/

namespace Scrapers\Trackers;

/**
 * Class TScraper
 * @package Scrapers\Trackers
 */
abstract class TScraper
{
    /**
     * @var int
     */
    protected $timeout;

    /**
     * TScraper constructor.
     * @param int $timeout
     */
    public function __construct($timeout = 2)
    {
        $this->timeout = $timeout;
    }
}
