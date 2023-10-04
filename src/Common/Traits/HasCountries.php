<?php

namespace LaravelPay\MyFatoorah\Common\Traits;

trait HasCountries
{
    /**
     * Get a list of MyFatoorah countries and their API URLs and names
     *
     * @return array of MyFatoorah data
     */
    public static function getMyFatoorahCountries(): array
    {
        $cachedFile = dirname(__FILE__).'/../../../resources/mf-config.json';

        if (file_exists($cachedFile)) {
            if ((time() - filemtime($cachedFile) > 3600)) {
                $countries = self::createNewMFConfigFile($cachedFile);
            }

            if (! empty($countries)) {
                return $countries;
            }

            $cache = file_get_contents($cachedFile);

            return ($cache) ? json_decode($cache, true) : [];
        } else {
            return self::createNewMFConfigFile($cachedFile);
        }
    }

    protected static function createNewMFConfigFile(string $cachedFile): array
    {
        $curl = curl_init('https://portal.myfatoorah.com/Files/API/mf-config.json');

        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code == 200) {
            file_put_contents($cachedFile, $response);

            return json_decode($response, true);
        } elseif ($http_code == 403) {
            touch($cachedFile);
            $fileContent = file_get_contents($cachedFile);
            if (! empty($fileContent)) {
                return json_decode($fileContent, true);
            }
        }

        return [];
    }
}
