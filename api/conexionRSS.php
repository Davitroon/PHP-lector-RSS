<?php

function download($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // sigue redirecciones
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP RSS Reader'); // evita bloqueos
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // timeout de 10 segundos

    $data = curl_exec($ch);

    if ($data === false) {
        error_log("cURL Error: " . curl_error($ch));
        $data = null;
    }

    curl_close($ch);
    return $data;
}
