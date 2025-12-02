<?php

class TursoConnection
{
    private $url;
    private $token;

    public function __construct()
    {
        // Obtenemos las variables que Vercel configuró automáticamente
        $this->url = getenv('TURSO_DATABASE_URL');
        $this->token = getenv('TURSO_AUTH_TOKEN');
        $this->url = str_replace('libsql://', 'https://', $this->url);
    }

    public function query($sql, $params = [])
    {
        $postData = [
            "statements" => [
                [
                    "q" => $sql,
                    "params" => $params
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . "/v2/pipeline");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->token,
            "Content-Type: application/json"
        ]);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            die('Error Curl: ' . curl_error($ch));
        }
        curl_close($ch);

        return json_decode($result, true);
    }
}
