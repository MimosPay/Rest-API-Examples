<?php

require '../vendor/autoload.php';

// Define the http client
$api_base_url = 'https://api.dev.mimos.io:443/crypto-checkout/v1/';
$client = new \GuzzleHttp\Client(['base_uri' => $api_base_url]);

// inputs for request headers
$app_id = '!!!<your_api_id>';
$app_secrect = '!!!<your_api_secrect>';
$timestamp = round(microtime(true) * 1000);
$nonce = Utils::getNonce();

// Define array of request headers
$headers = array(
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
    'X-MM-APP-ID' => $app_id,
    'X-MM-TIMESTAMP' => $timestamp,
    'X-MM-SIGNATURE' => '',
    'X-MM-NONCE' => $nonce,
);

// Define array of request body
$request_body = array(
  'external_order_id' => '3jqx2a6vd96x67c91cplq',
  'price' => '599',
  'name' => 'iphone 11',
  'currency' => 'USD',
  'metadata' => '{"image_url": "https://images-na.ssl-images-amazon.com/images/I/61wjAvw5B2L._AC_SX425_.jpg","customer_id":"123456","customer_name":"my-user-name"}'
);

$headers['X-MM-SIGNATURE'] = Utils::calcSignature($headers, $request_body, $app_secrect);

try {
    $response = $client->request('POST','charges', array(
        'headers' => $headers,
        'debug' => true,
        'http_errors' => false,
        'json' => $request_body,
       )
    );
    print_r($response->getBody()->getContents() . PHP_EOL);
 }
 catch (\GuzzleHttp\Exception\BadResponseException $e) {
    // handle exception or api errors.
    print_r($e->getMessage() . PHP_EOL);
 }
 
Class Utils {
    public static function implodeKeyValue($glue, $array, $symbol = '=') {
        return implode($glue, array_map(
                function($k, $v) use($symbol) {
                    return $k . $symbol . $v;
                },
                array_keys($array),
                array_values($array)
                )
            );
    }
    public static function filterHeaders($headers) {
        $available_headers = ['X-MM-APP-ID', 'X-MM-TIMESTAMP', 'X-MM-NONCE'];
        return array_filter($headers, function ($key) use ($available_headers) {
            return in_array($key, $available_headers);
        }, ARRAY_FILTER_USE_KEY);
    }
    public static function filterEmptyValues($requestArray) {
        return array_filter($requestArray, fn($value) => !is_null($value) && $value !== '');
    }
    public static function getNonce() {
        return uniqid();   
    }
    public static function calcSignature($headers, $payload, $secrect) {
        // merge and filter request headers and request payload
        $array = self::filterEmptyValues(array_merge(self::filterHeaders($headers), $payload));
        
        // sort by key in alphabetic ascending order
        ksort($array);
        print_r($array);
        
        // Concatenate into the URL key-value format
        $stringA = self::implodeKeyValue('&', $array);
        print_r('stringA = ' . $stringA . PHP_EOL);
        
        // Concatenate with secrect
        $stringB = $stringA . '&key=' . $secrect;
        print_r('stringB = ' . $stringB . PHP_EOL);
        
        // MD5
        $signatureVal = strtoupper(md5($stringB));
        print_r('signatureVal = ' . $signatureVal . PHP_EOL);

        return $signatureVal;   
    }
}
