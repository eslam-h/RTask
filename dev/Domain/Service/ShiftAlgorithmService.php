<?php

namespace Dev\Domain\Service;

/**
 * ShiftAlgorithmService Class service responsible for shift encrypt and decrypt algorithm
 * @package Dev\Domain\Service
 */
class ShiftAlgorithmService
{
    /**
     * Shift encrypt algorithm
     * @param $str
     * @return \Illuminate\Http\JsonResponse
     */
    static function encrypt($str)
    {
        $encryptedText = "";
        $shiftOffset = 3;

        for ($i = 0;$i < strlen($str); $i++) {
            $c = $str[$i];
            if (ctype_alpha($c)) {
                if (ctype_upper($c)) {
                    $encryptedChar = chr((26 + (ord($c) + $shiftOffset - 65)) % 26 + 65);
                    $encryptedText .= $encryptedChar;
                } else {
                    $encryptedChar = chr((26 + (ord($c) + $shiftOffset - 97)) % 26 + 97);
                    $encryptedText .= $encryptedChar;
                }
            } else {
                $encryptedText .= " ";
            }
        }

        return $encryptedText;
    }

    /**
     * Shift decrypt algorithm
     * @param $str
     * @return \Illuminate\Http\JsonResponse
     */
    static function decrypt($str)
    {
        $encryptedText = "";
        $shiftOffset = 3;

        for ($i = 0;$i < strlen($str); $i++) {
            $c = $str[$i];
            if (ctype_alpha($c)) {
                if (ctype_upper($c)) {
                    $encryptedChar = chr((26 + (ord($c) - $shiftOffset - 65)) % 26 + 65);
                    $encryptedText .= $encryptedChar;
                } else {
                    $encryptedChar = chr((26 + (ord($c) - $shiftOffset - 97)) % 26 + 97);
                    $encryptedText .= $encryptedChar;
                }
            } else {
                $encryptedText .= " ";
            }
        }

        return $encryptedText;
    }
}