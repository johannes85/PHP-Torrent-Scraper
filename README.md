# PHP-Torrent-Scraper

Classes to scrape a torrent status by using the HTTP or UDP protocol.

## Example Usage:

```php
<?php

include 'vendor/autoload.php';

use Scrapers\Trackers as ST;

$scraper = new ST\UdpScraper();

$result = $scraper->scrape(
    "udp://tracker.ilibr.org:6969/announce",
    "7c18267426e81e849f282d1f9a10cf5a6a292c8c"
);

print_r($result);
```

-------

2010 by Johannes Zinnau
johannes@johnimedia.de

Licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License
http://creativecommons.org/licenses/by-sa/3.0/

It would be very nice if you send me your changes on this class, so that i can include them if they are improve it.
Thanks!