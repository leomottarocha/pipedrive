<?php

namespace source\Models;

class Pipedrive
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
            return [
                'success' => true,
                'status_code' => $status,
                'data' => $decoded->data ?? $decoded,
                'error_message' => null
            ];
        }

        return [
            'success' => false,
            'status_code' => $status,
            'data' => null,
            'error_message' => $decoded->error ?? 'Erro HTTP ' . $status
        ];
    }


    /**
     * @deprecated
     */
    public function retornarInformacao(string $url)
    {
        //Corrige problemas de url
        //$url = urlencode($url);

        // Inicializa o cURL
        $ch = curl_init($url);

        // Configurações básicas
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna o resultado como string
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Segue redirecionamentos

        // Adiciona cabeçalhos, caso necessário (Exemplo: se precisar de autenticação)
        // curl_setopt($ch, CURLOPT_HTTPHEADER, [
        //     'Authorization: Bearer SEU_TOKEN_AQUI',
        // ]);

        // Executa a requisição
        $data = curl_exec($ch);

        // Verifica se ocorreu erro durante a execução do cURL
        if (curl_errno($ch)) {
            // Retorna um erro caso haja falha
            curl_close($ch);
            return ['error' => 'Erro cURL: ' . curl_error($ch)];
        }

        // Obtém o código HTTP de resposta
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Verifica se a resposta HTTP foi bem-sucedida (código 200)
        if ($httpCode !== 200) {
            return ['error' => 'Erro na resposta HTTP: Código ' . $httpCode];
        }

        // Decodifica o JSON
        $resposta = json_decode($data, true); // Decodifica como array associativo

        // Retorna a resposta decodificada
        return $resposta;
    }
    /**
     * @deprecated
     */
    public function cadastrarInformacao(string $url, array $data, string $token)
    {
        $url = $url . "api_token=" . $token;
        $ch3 = curl_init();
        curl_setopt($ch3, CURLOPT_URL, $url);
        curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch3, CURLOPT_CUSTOMREQUEST, "POST"); //Aqui acontece a mágica do insert
        curl_setopt($ch3, CURLOPT_POSTFIELDS, http_build_query($data));
        $cadastrar = curl_exec($ch3);
        curl_close($ch3);

        if ($cadastrar) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * @deprecated
     */
    public function atualizarInformacao(string $url, array $data, string $token)
    {
        $url = $url . "api_token=" . $token;

        $chUpdate = curl_init();
        curl_setopt($chUpdate, CURLOPT_URL, $url);
        curl_setopt($chUpdate, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chUpdate, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($chUpdate, CURLOPT_POSTFIELDS, http_build_query($data));
        $dataUpdate = curl_exec($chUpdate);
        curl_close($chUpdate);

        if ($dataUpdate) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * @deprecated
     */
    public function alterarFiltro(string $url, array $data, string $token)
    {
        $ch = curl_init($url . "api_token=" . $token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json;', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST,           1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response) {
            return true;
        } else {
            return false;
        }
    }
}
