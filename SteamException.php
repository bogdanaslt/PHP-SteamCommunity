<?php

namespace bogdanaslt\SteamCommunity;


class SteamException extends \Exception
{
    
    private $information;
    
    public function getInformation()
    {
        return $this->information;
    }
    
    public function setInformation($information)
    {
        $this->information = $information;
    }

}
