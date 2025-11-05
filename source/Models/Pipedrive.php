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

    private function request(string $method, string $url, array $data = null)
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
            // Detecta se é upload de arquivo
            $isFileUpload = false;
            foreach ($data as $value) {
                if ($value instanceof \CURLFile) {
                    $isFileUpload = true;
                    break;
                }
            }

            if ($isFileUpload) {
                // Upload de arquivo -> multipart/form-data
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                // Dados normais -> JSON
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            return (object)[
                'success' => false,
                'error' => $error,
                'status' => $status
            ];
        }

        return json_decode($response, false);
    }
}
