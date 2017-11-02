<?php

namespace bogdanaslt\SteamCommunity\Market;

use bogdanaslt\SteamCommunity\SteamException;

class Listings
{
    private $totalCount;

    public function __construct($json)
    {
        if (!is_array($json)) {
            throw new SteamException("Json must be in array format.");
        }
        if (isset($json['success']) && $json['success']) {
            $this->totalCount = $json['total_count'];
        } else {
            throw new SteamException("Market PriceOverview json must have valid data.");
        }
    }

    /**
     * @return mixed
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }
}
