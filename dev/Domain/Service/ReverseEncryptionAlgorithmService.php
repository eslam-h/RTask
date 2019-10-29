<?php

namespace Dev\Domain\Service;

/**
 * Class ReverseEncryptionAlgorithmService
 * @package Dev\Domain\Service
 */
class ReverseEncryptionAlgorithmService
{
    /**
     * Reverse encryption
     * @param $string
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function encrypt($string)
    {
        $url = "http://backendtask.robustastudio.com";
        $client = new \GuzzleHttp\Client(['base_uri' => $url]);
        $res = $client->request('POST','/encode', [
            'json' => ['string' => $string]
        ]);
        $responseJSON = json_decode($res->getBody(), true);
        return $responseJSON['string'];
    }

    /**
     * Reverse decryption
     * @param $string
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function decrypt($string)
    {
        $url = "http://backendtask.robustastudio.com";
        $client = new \GuzzleHttp\Client(['base_uri' => $url]);
        $res = $client->request('POST','/decode', [
            'json' => ['string' => $string]
        ]);
        $responseJSON = json_decode($res->getBody(), true);
        return $responseJSON;
    }
}