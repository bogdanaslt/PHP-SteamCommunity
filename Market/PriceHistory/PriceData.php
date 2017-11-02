<?php

namespace bogdanaslt\SteamCommunity\Market\PriceHistory;

class PriceData
{
    private $dateAndTime;
    private $medianPrice;
    private $volume;

    public function __construct($priceArray)
    {
        if (count($priceArray) == 3) {
            $this->dateAndTime = $priceArray[0];
            $this->medianPrice = $priceArray[1];
            $this->volume = (int)$priceArray[2];
        }
    }

    /**
     * @return string
     */
    public function getDateAndTime()
    {
        return $this->dateAndTime;
    }

    /**
     * @return float
     */
    public function getMedianPrice()
    {
        return $this->medianPrice;
    }

    /**
     * @return int
     */
    public function getVolume()
    {
        return $this->volume;
    }
}
