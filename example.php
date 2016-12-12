<?php

require __DIR__ . '/vendor/autoload.php';

$requestParams = array(
    'CountryName' => 'Ukraine'
);

$client = new \AndreySerdjuk\SoapClientExtended\SoapClient(
    'http://www.webservicex.net/globalweather.asmx?WSDL',
    array(
        'cache_wsdl' => WSDL_CACHE_NONE,
        'curl_options' => array(
            CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
            CURLOPT_PROXY => "localhost",
            CURLOPT_PROXYPORT => 9051,
        )
    )
);

$response = $client->GetCitiesByCountry($requestParams);

print_r($response);