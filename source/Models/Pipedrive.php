<?php

namespace Source\Models;

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

    public function read($url, int $start = 0, int $limit = 500, bool $all = true)
    {
        return $this->request('GET', $url, null, $start, $limit, $all);
    }

    public function update($url, array $data)
    {
        return $this->request('PUT', $url, $data);
    }

    public function delete($url)
    {
        return $this->request('DELETE', $url);
    }

    private function request(
        string $method,
        string $url,
        array $data = null,
        int $start = 0,
        int $limit = 500,
        bool $all = true
    ) {
        $method = strtoupper($method);

        // Anexa o token se não estiver presente
        if (strpos($url, 'api_token=') === false) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'api_token=' . $this->token;
        }

        /*
         * Para requisições GET, aplica paginação quando o retorno for uma lista.
         *
         * Se $all = true:
         * - busca todas as páginas.
         *
         * Se $all = false:
         * - busca apenas uma página com start e limit informados.
         *
         * Se o endpoint retornar data como objeto:
         * - retorna a resposta original, sem sobrescrever data.
         */
        if ($method === 'GET') {
            $todosRegistros = [];
            $ultimoResponse = null;
            $paginaAtual = $start;

            do {
                /*
                 * Remove start e limit da URL original, caso já existam,
                 * para evitar duplicidade.
                 */
                $urlPaginada = preg_replace('/([&?])start=\d+&?/', '$1', $url);
                $urlPaginada = preg_replace('/([&?])limit=\d+&?/', '$1', $urlPaginada);
                $urlPaginada = rtrim($urlPaginada, '?&');

                $urlPaginada .= (strpos($urlPaginada, '?') === false ? '?' : '&') . "start={$paginaAtual}&limit={$limit}";

                $ch = curl_init($urlPaginada);

                if ($ch === false) {
                    throw new \RuntimeException('Erro ao inicializar cURL.');
                }

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                $response = curl_exec($ch);

                if ($response === false) {
                    $erro = curl_error($ch);
                    curl_close($ch);

                    throw new \RuntimeException("Erro na requisição cURL: {$erro}");
                }

                curl_close($ch);

                $response = json_decode($response, false);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException(
                        'Erro ao decodificar JSON: ' . json_last_error_msg()
                    );
                }

                $ultimoResponse = $response;

                /*
                 * Caso o endpoint retorne um único registro.
                 *
                 * Exemplo:
                 * /deals/54980
                 *
                 * Nesse caso, data é object, não array.
                 * Não deve aplicar merge nem sobrescrever data.
                 */
                if (isset($response->data) && is_object($response->data)) {
                    return $response;
                }

                /*
                 * Caso o endpoint retorne uma lista.
                 *
                 * Exemplo:
                 * /deals
                 * /persons
                 * /organizations
                 */
                $registrosPagina = [];

                if (isset($response->data) && is_array($response->data)) {
                    $registrosPagina = $response->data;
                    $todosRegistros = array_merge($todosRegistros, $registrosPagina);
                }

                $totalPagina = count($registrosPagina);

                $temMaisRegistros = $response->additional_data->pagination->more_items_in_collection
                    ?? ($totalPagina === $limit);

                $paginaAtual += $limit;

                /*
                 * Se $all for false, força a parada após a primeira requisição.
                 */
                if ($all === false) {
                    break;
                }
            } while ($temMaisRegistros && $totalPagina > 0);

            if ($ultimoResponse === null) {
                $ultimoResponse = new \stdClass();
            }

            /*
             * Só sobrescreve data quando estamos lidando com lista.
             */
            $ultimoResponse->data = $todosRegistros;

            return $ultimoResponse;
        }

        /*
         * Fluxo normal para POST, PUT e DELETE.
         */
        $ch = curl_init($url);

        if ($ch === false) {
            throw new \RuntimeException('Erro ao inicializar cURL.');
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        } elseif (in_array($method, ['PUT', 'DELETE'], true)) {
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

        if ($response === false) {
            $erro = curl_error($ch);
            curl_close($ch);

            throw new \RuntimeException("Erro na requisição cURL: {$erro}");
        }

        curl_close($ch);

        $response = json_decode($response, false);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                'Erro ao decodificar JSON: ' . json_last_error_msg()
            );
        }

        return $response;
    }
}
