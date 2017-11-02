<?php

namespace bogdanaslt\SteamCommunity\TradeOffers\Trade;

use bogdanaslt\SteamCommunity\TradeOffers\Trade;

class TradeUser implements \JsonSerializable
{
    /** @var TradeAsset[] $assets */
    private $assets = [];
    private $currency = [];
    private $ready = false;
    private $trade;

    public function __construct(Trade $trade)
    {
        $this->trade = $trade;
    }

    public function addItem(TradeAsset $asset)
    {
        $exists = false;
        foreach ($this->assets as $tradeAsset) {
            if ($tradeAsset->getAppId() == $asset->getAppId() &&
                $tradeAsset->getContextId() == $asset->getContextId() &&
                $tradeAsset->getAssetId() == $asset->getAssetId() &&
                $tradeAsset->getAmount() == $asset->getAmount()) {
                $exists = true;
            }
        }
        if ($exists) {
            return false;
        } else {
            $this->trade->setVersion($this->trade->getVersion() + 1);
            $this->assets[] = $asset;
            return true;
        }
    }

    public function jsonSerialize()
    {
        return [
            'assets' => $this->assets,
            'currency' => $this->currency,
            'ready' => $this->ready
        ];
    }
}
