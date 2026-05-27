# Classe Pipedrive para PHP

Classe PHP simples para consumo da API do Pipedrive utilizando `cURL`, com suporte a operações básicas de **criação**, **leitura**, **atualização** e **exclusão** de registros.

Esta versão também possui paginação automática para requisições `GET`, permitindo buscar todos os registros de um endpoint paginado ou limitar a consulta usando `start`, `limit` e `all`.

---

## Sumário

1. [Visão geral](#visão-geral)
2. [Requisitos](#requisitos)
3. [Instalação](#instalação)
4. [Autoload e namespace](#autoload-e-namespace)
5. [Como instanciar a classe](#como-instanciar-a-classe)
6. [Métodos disponíveis](#métodos-disponíveis)
7. [Paginação em requisições GET](#paginação-em-requisições-get)
8. [Exemplos de uso](#exemplos-de-uso)
9. [Estrutura de retorno](#estrutura-de-retorno)
10. [Tratamento de erros](#tratamento-de-erros)
11. [Observações importantes](#observações-importantes)
12. [Autor](#autor)
13. [Licença](#licença)

---

## Visão geral

A classe `Pipedrive` centraliza chamadas HTTP para a API do Pipedrive, adicionando automaticamente o parâmetro `api_token` à URL quando ele ainda não estiver presente.

Ela permite trabalhar com qualquer endpoint da API do Pipedrive por meio dos seguintes métodos:

- `create()` para requisições `POST`;
- `read()` para requisições `GET`;
- `update()` para requisições `PUT`;
- `delete()` para requisições `DELETE`.

O método `read()` possui tratamento especial para paginação. Por padrão, ele percorre todas as páginas de um endpoint paginado e retorna um único objeto com todos os registros consolidados em `data`.

---

## Requisitos

- PHP `>= 7.4`;
- Extensão `cURL` habilitada;
- Composer para autoload PSR-4;
- Token de API válido do Pipedrive.

---

## Instalação

Execute o Composer para gerar o autoload:

```bash
composer install
```

Caso altere namespaces, classes ou estrutura de pastas, atualize o autoload:

```bash
composer dump-autoload
```

---

## Autoload e namespace

O arquivo `composer.json` define o autoload PSR-4 da seguinte forma:

```json
{
  "autoload": {
    "psr-4": {
      "Source\\": "source/"
    }
  }
}
```

Uso esperado no projeto:

```php
use Source\Models\Pipedrive;
```

> Observação: mantenha o namespace da classe compatível com o autoload do Composer. Se o `composer.json` usa `Source\\`, a classe deve declarar `namespace Source\Models;`.

---

## Como instanciar a classe

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Source\Models\Pipedrive;

$token = 'SEU_API_TOKEN_AQUI';

$pipedrive = new Pipedrive($token);
```

---

## Métodos disponíveis

### `create($url, array $data)`

Executa uma requisição `POST` para criar registros.

```php
$response = $pipedrive->create('https://api.pipedrive.com/v1/deals', [
    'title' => 'Novo negócio de teste',
    'value' => 1500,
    'currency' => 'BRL'
]);
```

---

### `read($url, int $start = 0, int $limit = 500, bool $all = true)`

Executa uma requisição `GET`.

Assinatura atual:

```php
public function read($url, int $start = 0, int $limit = 500, bool $all = true)
```

Parâmetros:

| Parâmetro | Tipo | Padrão | Descrição |
|---|---:|---:|---|
| `$url` | `string` | obrigatório | URL completa do endpoint da API. |
| `$start` | `int` | `0` | Posição inicial da consulta. |
| `$limit` | `int` | `500` | Quantidade de registros por página. |
| `$all` | `bool` | `true` | Quando `true`, busca todas as páginas. Quando `false`, busca apenas uma página. |

---

### `update($url, array $data)`

Executa uma requisição `PUT` para atualizar registros.

```php
$response = $pipedrive->update('https://api.pipedrive.com/v1/deals/123', [
    'title' => 'Negócio atualizado'
]);
```

---

### `delete($url)`

Executa uma requisição `DELETE` para remover registros.

```php
$response = $pipedrive->delete('https://api.pipedrive.com/v1/deals/123');
```

---

## Paginação em requisições GET

A paginação foi implementada diretamente no método privado `request()`.

Quando o método usado é `GET`, a classe:

1. adiciona o `api_token`, caso ele ainda não esteja presente na URL;
2. remove `start` e `limit` antigos da URL, caso existam;
3. aplica os valores informados em `$start` e `$limit`;
4. executa a chamada com `cURL`;
5. verifica se `data` é uma lista ou um objeto único;
6. consolida todos os registros em `data` quando o endpoint retorna lista;
7. retorna a resposta original quando o endpoint retorna objeto único.

### Busca completa por padrão

```php
$response = $pipedrive->read('https://api.pipedrive.com/v1/deals');
```

Essa chamada usa:

```php
$start = 0;
$limit = 500;
$all = true;
```

Ou seja, busca todas as páginas disponíveis.

---

### Buscar apenas 1 registro

```php
$response = $pipedrive->read(
    'https://api.pipedrive.com/v1/deals',
    0,
    1,
    false
);
```

Essa chamada busca apenas uma página, começando em `0`, com `limit = 1`.

---

### Buscar apenas 10 registros

```php
$response = $pipedrive->read(
    'https://api.pipedrive.com/v1/deals',
    0,
    10,
    false
);
```

---

### Buscar registros a partir de uma posição específica

```php
$response = $pipedrive->read(
    'https://api.pipedrive.com/v1/deals',
    500,
    500,
    true
);
```

Essa chamada busca todos os registros a partir da posição `500`, usando páginas de `500` registros.

---

## Exemplos de uso

### Listar todos os negócios

```php
$response = $pipedrive->read('https://api.pipedrive.com/v1/deals');

foreach ($response->data as $deal) {
    echo $deal->id . ' - ' . $deal->title . PHP_EOL;
}
```

---

### Listar organizações com limite de 5 registros

```php
$response = $pipedrive->read(
    'https://api.pipedrive.com/v1/organizations',
    0,
    5,
    false
);

foreach ($response->data as $organization) {
    echo $organization->id . ' - ' . $organization->name . PHP_EOL;
}
```

---

### Buscar um negócio específico

```php
$response = $pipedrive->read('https://api.pipedrive.com/v1/deals/8393862');

echo $response->data->id;
echo $response->data->title;
```

Quando o endpoint retorna um registro único, `data` vem como objeto e não é convertido para array.

---

### Criar um negócio

```php
$response = $pipedrive->create('https://api.pipedrive.com/v1/deals', [
    'title' => '0000-leo-teste'
]);

if ($response->success) {
    echo 'Negócio criado com sucesso.';
}
```

---

### Atualizar um negócio

```php
$response = $pipedrive->update('https://api.pipedrive.com/v1/deals/54980', [
    'title' => 'Rio de Janeiro - Negócio atualizado'
]);
```

---

### Excluir um negócio

```php
$response = $pipedrive->delete('https://api.pipedrive.com/v1/deals/8393862');
```

---

## Estrutura de retorno

A classe retorna o objeto decodificado da própria API do Pipedrive por meio de:

```php
json_decode($response, false);
```

Portanto, o retorno normalmente segue o padrão da API:

```php
$response->success;
$response->data;
$response->additional_data;
$response->related_objects;
```

### Retorno de endpoint paginado

Em endpoints de lista, como `/deals`, `/organizations` ou `/persons`, o campo `data` será um array:

```php
$response = $pipedrive->read('https://api.pipedrive.com/v1/deals');

if ($response->success && is_array($response->data)) {
    foreach ($response->data as $deal) {
        echo $deal->title . PHP_EOL;
    }
}
```

### Retorno de endpoint único

Em endpoints específicos, como `/deals/{id}`, o campo `data` será um objeto:

```php
$response = $pipedrive->read('https://api.pipedrive.com/v1/deals/8393862');

if ($response->success && is_object($response->data)) {
    echo $response->data->title;
}
```

---

## Tratamento de erros

A classe lança exceções do tipo `RuntimeException` quando ocorre:

- falha ao inicializar o `cURL`;
- erro durante a execução da requisição;
- erro ao decodificar o JSON retornado pela API.

Exemplo de uso com `try/catch`:

```php
try {
    $response = $pipedrive->read('https://api.pipedrive.com/v1/deals', 0, 1, false);

    var_dump($response);
} catch (RuntimeException $exception) {
    echo 'Erro: ' . $exception->getMessage();
}
```

---

## Observações importantes

### Sobre o parâmetro `$all`

Para limitar a quantidade de registros, informe `$all = false`.

Correto para buscar apenas 1 registro:

```php
$response = $pipedrive->read($url, 0, 1, false);
```

Atenção:

```php
$response = $pipedrive->read($url, 0, 1);
```

Essa chamada mantém `$all = true`. Portanto, a classe buscará todos os registros, paginando de 1 em 1.

---

### Sobre o `var_dump()` exibindo `...`

Quando o `var_dump()` exibe `...`, isso normalmente não significa que a API retornou reticências. Em ambiente com Xdebug, o PHP pode limitar a profundidade de exibição de objetos grandes.

Para visualizar melhor:

```php
echo '<pre>';
print_r($response);
echo '</pre>';
```

Ou ajuste a configuração do Xdebug:

```ini
xdebug.var_display_max_depth = 10
xdebug.var_display_max_children = 256
xdebug.var_display_max_data = 1024
```

---

### Sobre `start` e `limit` na URL

A classe remove `start` e `limit` antigos da URL antes de aplicar os valores recebidos no método `read()`.

Exemplo:

```php
$response = $pipedrive->read(
    'https://api.pipedrive.com/v1/deals?filter_id=123&start=0&limit=500',
    0,
    10,
    false
);
```

A chamada final usará `start=0` e `limit=10`.

---

## Autor

**Léo Motta Rocha**  
Desenvolvedor Full Stack | Integrações | Automação de Dados

- LinkedIn: [linkedin.com/in/leomottarocha](https://www.linkedin.com/in/leomottarocha)
- GitHub: [github.com/leomottarocha](https://github.com/leomottarocha)

---

## Licença

Distribuído sob a licença **MIT**.
