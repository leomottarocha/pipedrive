<?php

namespace source\Models;

final class Pipedrive
{
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function create($url, array $data)
    {
        return $this->request('POST', $url, $data);
    }

    public function read($url)
    {
        return $this->request('GET', $url);
    }

    public function update($url, array $data)
    {
        return $this->request('PUT', $url, $data);
    }

    public function delete($url)
    {
        return $this->request('DELETE', $url);
    }

    private function request($method, $url, array $data = null)
    {
        // Anexa o token se não estiver presente
        if (strpos($url, 'api_token=') === false) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'api_token=' . $this->token;
        }

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        } elseif (in_array($method, ['PUT', 'DELETE'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'status_code' => 0,
                'data' => null,
                'error_message' => 'Erro cURL: ' . $error
            ];
        }

        $decoded = json_decode($response, false);

        if ($status >= 200 && $status < 300) {
            return (object) [
                'success' => true,
                'status_code' => $status,
                'data' => $decoded->data ?? $decoded,
                'error_message' => null
            ];
        }

        return (object) [
            'success' => false,
            'status_code' => $status,
            'data' => null,
            'error_message' => $decoded->error ?? 'Erro HTTP ' . $status
        ];
    }
}
