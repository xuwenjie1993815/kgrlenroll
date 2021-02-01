<?php


// 应用公共文件
error_reporting(E_ERROR | E_PARSE );

// HTTP请求（支持HTTP/HTTPS，支持GET/POST）
function http_request($url, $data = null,$headers){
    $curl = curl_init();
    if ($headers) {
        if ($headers == '1') {
            $headers = array("Content-Type:application/json;charset='utf-8'","Accept:application/json");
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (! empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}




