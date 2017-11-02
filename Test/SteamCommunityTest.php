<?php

class SteamCommunityTest extends PHPUnit_Framework_TestCase
{
    public function test_setSessionWhenNotLoggedIn()
    {
        $steam = new \bogdanaslt\SteamCommunity\SteamCommunity([], dirname(__FILE__));
        $this->assertSame(0, $steam->getSteamId());
        $this->assertNotNull($steam->getSessionId());
    }
}
