<?php
/*
Torrent UDP Scraper
v1.2

2010 by Johannes Zinnau
johannes@johnimedia.de

Licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License
http://creativecommons.org/licenses/by-sa/3.0/

It would be very nice if you send me your changes on this class, so that i can include them if they are improve it.
Thanks!

Usage:
```
try{
	$timeout = 2;
	
	$scraper = new udptscraper($timeout);
	$ret = $scraper->scrape('udp://tracker.tld:port',array('0000000000000000000000000000000000000000'));
	
	print_r($ret);
}catch(ScraperException $e){
	echo('Error: ' . $e->getMessage() . "<br />\n");
	echo('Connection error: ' . ($e->isConnectionError() ? 'yes' : 'no') . "<br />\n");
}
```
*/

namespace Scrapers\Trackers;

/**
 * Class UdpScraper
 * @package Scrapers\Trackers
 */
class UdpScraper extends TScraper
{
    /* 	$url: Tracker url like: udp://tracker.tld:port or udp://tracker.tld:port/announce
        $infohash: Infohash string or array (max 74 items). 40 char long infohash.
        */
    /**
     * @param $url
     * @param $info_hash
     * @return array
     * @throws Exception\ScraperException
     */
    public function scrape($url, $info_hash)
    {
        if (!is_array($info_hash)) {
            $info_hash = array($info_hash);
        }
        foreach ($info_hash as $hash) {
            if (!preg_match('#^[a-f0-9]{40}$#i', $hash)) {
                throw new Exception\ScraperException('Invalid infohash: ' . $hash . '.');
            }
        }
        if (count($info_hash) > 74) {
            throw new Exception\ScraperException('Too many infohashes provided.');
        }
        if (!preg_match('%udp://([^:/]*)(?::([0-9]*))?(?:/)?%si', $url, $m)) {
            throw new Exception\ScraperException('Invalid tracker url.');
        }
        $tracker = 'udp://' . $m[1];
        $port = isset($m[2]) ? $m[2] : 80;

        $transaction_id = mt_rand(0, 65535);
        try {
            $fp = fsockopen($tracker, $port, $errno, $errstr);
        } catch (\Exception $e) {
            throw new Exception\ScraperException('Could not open UDP connection: ' . $errno . ' - ' . $errstr, 0, true);
        }
        if (!$fp) {
            throw new Exception\ScraperException('Could not open UDP connection: ' . $errno . ' - ' . $errstr, 0, true);
        }
        stream_set_timeout($fp, $this->timeout);

        $current_connid = "\x00\x00\x04\x17\x27\x10\x19\x80";

        //Connection request
        $packet = $current_connid . pack('N', 0) . pack('N', $transaction_id);
        fwrite($fp, $packet);

        //Connection response
        $ret = fread($fp, 16);
        if (strlen($ret) < 1) {
            throw new Exception\ScraperException('No connection response.', 0, true);
        }
        if (strlen($ret) < 16) {
            throw new Exception\ScraperException('Too short connection response.');
        }
        $retd = unpack('Naction/Ntransid', $ret);
        if ($retd['action'] != 0 || $retd['transid'] != $transaction_id) {
            throw new Exception\ScraperException('Invalid connection response.');
        }
        $current_connid = substr($ret, 8, 8);

        //Scrape request
        $hashes = '';
        foreach ($info_hash as $hash) {
            $hashes .= pack('H*', $hash);
        }
        $packet = $current_connid . pack('N', 2) . pack('N', $transaction_id) . $hashes;
        fwrite($fp, $packet);

        //Scrape response
        $readlength = 8 + (12 * count($info_hash));
        $ret = fread($fp, $readlength);
        if (strlen($ret) < 1) {
            throw new Exception\ScraperException('No scrape response.', 0, true);
        }
        if (strlen($ret) < 8) {
            throw new Exception\ScraperException('Too short scrape response.');
        }
        $retd = unpack('Naction/Ntransid', $ret);
        // Todo check for error string if response = 3
        if ($retd['action'] != 2 || $retd['transid'] != $transaction_id) {
            throw new Exception\ScraperException('Invalid scrape response.');
        }
        if (strlen($ret) < $readlength) {
            throw new Exception\ScraperException('Too short scrape response.');
        }
        $torrents = array();
        $index = 8;
        foreach ($info_hash as $hash) {
            $retd = unpack('Nseeders/Ncompleted/Nleechers', substr($ret, $index, 12));
            $retd['infohash'] = $hash;
            $torrents[$hash] = $retd;
            $index = $index + 12;
        }

        return $torrents;
    }
}
