<?php

namespace bogdanaslt\SteamCommunity\MobileAuth;

use bogdanaslt\SteamCommunity\MobileAuth\Confirmations\Confirmation;
use bogdanaslt\SteamCommunity\SteamException;

class Confirmations
{
    private $mobileAuth;

    public function __construct(MobileAuth $mobileAuth)
    {
        $this->mobileAuth = $mobileAuth;
    }

    /**
     * Fetch list of confirmations. May need to retry more than once because of Steam occasionally not showing any confirmations.
     * @return Confirmation[]
     * @throws WgTokenInvalidException Thrown when session is invalid.
     */
    public function fetchConfirmations()
    {
        $url = $this->generateConfirmationUrl();
        $confirmations = [];
        $response = '';
        try {
            $response = $this->mobileAuth->steamCommunity()->cURL($url);
        } catch(SteamException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $confirmations;
        }

        if (strpos($response, '<div>Nothing to confirm</div>') === false) {
            $confIdRegex = '/data-confid="(\d+)"/';
            $confKeyRegex = '/data-key="(\d+)"/';
            $confOfferRegex = '/data-creator="(\d+)"/';
            $confDescRegex = '/<div>((Confirm|Trade with|Sell -) .+)<\/div>/';

            preg_match_all($confIdRegex, $response, $confIdMatches);
            preg_match_all($confKeyRegex, $response, $confKeyMatches);
            preg_match_all($confOfferRegex, $response, $confOfferMatches);
            preg_match_all($confDescRegex, $response, $confDescMatches);

            if (count($confIdMatches[1]) > 0 && count($confKeyMatches[1]) > 0 && count($confDescMatches) > 0 && count($confOfferMatches) > 0) {
                $checkedConfIds = [];

                for ($i = 0; $i < count($confIdMatches[1]); $i++) {
                    $confId = $confIdMatches[1][$i];

                    if (in_array($confId, $checkedConfIds)) {
                        continue;
                    }

                    $confKey = $confKeyMatches[1][$i];
                    $confOfferId = $confOfferMatches[1][$i];
                    $confDesc = isset($confDescMatches[1][$i]) ? $confDescMatches[1][$i] : '';
                    $confirmations[] = new Confirmation($confId, $confKey, $confOfferId, $confDesc);

                    $checkedConfIds[] = $confId;
                }
            } else {
                throw new WgTokenInvalidException();
            }
        }
        return $confirmations;
    }

    public function generateConfirmationUrl($tag = 'conf')
    {
        return 'https://steamcommunity.com/mobileconf/conf?' . $this->generateConfirmationQueryParams($tag);
    }

    public function generateConfirmationQueryParams($tag)
    {
        $time = TimeAligner::GetSteamTime();
        
        $params = [
            'p' => $this->mobileAuth->getDeviceId(),
            'a' => $this->mobileAuth->steamCommunity()->getSteamId(),
            'k' => $this->_generateConfirmationHashForTime($time, $tag),
            't' => $time,
            'm' => 'android',
            'tag' => $tag
        ];
        
        return http_build_query($params);
    }

    private function _generateConfirmationHashForTime($time, $tag)
    {
        $identitySecret = base64_decode($this->mobileAuth->getIdentitySecret());
        $array = $tag ? substr($tag, 0, 32) : '';
        for ($i = 8; $i > 0; $i--) {
            $array = chr($time & 0xFF) . $array;
            $time >>= 8;
        }
        $code = hash_hmac("sha1", $array, $identitySecret, true);
        return base64_encode($code);
    }

    /**
     * Get the trade offer ID of a confirmation. May need to retry more than once due to Steam occasionally failing to load the trade page.
     * @param Confirmation $confirmation
     * @return string
     */
    public function getConfirmationTradeOfferId(Confirmation $confirmation)
    {
        $url = 'https://steamcommunity.com/mobileconf/details/' . $confirmation->getConfirmationId() . '?' . $this->generateConfirmationQueryParams('details');
        $response = '';
        try {
            $response = $this->mobileAuth->steamCommunity()->cURL($url);
        } catch (SteamException $e){
            throw $e;
        } catch (\Exception $ex) {
            return '0';
        }

        if (!empty($response)) {
            $json = json_decode($response, true);
            if (isset($json['success']) && $json['success']) {
                $html = $json['html'];
                if (preg_match('/<div class="tradeoffer" id="tradeofferid_(\d+)" >/', $html, $matches)) {
                    return $matches[1];
                }
            }
        }
        return '0';
    }

    /**
     * Accept a confirmation.
     * @param Confirmation $confirmation
     * @return bool
     */
    public function acceptConfirmation(Confirmation $confirmation)
    {
        return $this->_sendConfirmationAjax($confirmation, 'allow');
    }

    /**
     * Cancel a confirmation.
     * @param Confirmation $confirmation
     * @return bool
     */
    public function cancelConfirmation(Confirmation $confirmation)
    {
        return $this->_sendConfirmationAjax($confirmation, 'cancel');
    }

    private function _sendConfirmationAjax(Confirmation $confirmation, $op)
    {
        $params = [
            'op' => $op,
            'cid' => $confirmation->getConfirmationId(),
            'ck' => $confirmation->getConfirmationKey()
        ];
        
        $query = http_build_query($params) . '&' . $this->generateConfirmationQueryParams($op);
        
        $url = 'https://steamcommunity.com/mobileconf/ajaxop?' . $query;
        $response = '';
        try {
            $response = $this->mobileAuth->steamCommunity()->cURL($url);
        } catch (SteamException $e) {
            throw $e;
        } catch (\Exception $ex) {
            return false;
        }
        if (!empty($response)) {
            $json = json_decode($response, true);
            return isset($json['success']) && $json['success'];
        }
        return false;
    }
}
