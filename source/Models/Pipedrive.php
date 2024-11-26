<?php

namespace source\Models;

class Pipedrive
{
    public function retornarInformacao(string $url, string $token)
    {

        $url = $url . "api_token=" . $token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $data = curl_exec($ch);
        $resposta = json_decode($data);
        curl_close($ch);

        return $resposta;
    }
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
