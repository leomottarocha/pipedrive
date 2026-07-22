# Cliente Pipedrive para PHP

Cliente PHP simples para consumir a API v1 do Pipedrive com cURL. A classe oferece operações de criação, consulta, atualização e exclusão, paginação automática em requisições `GET` e envio de arquivos com `CURLFile`.

## Recursos

- requisições `GET`, `POST`, `PUT` e `DELETE`;
- inclusão automática do `api_token` na URL;
- paginação automática de endpoints que retornam listas;
- consulta de apenas uma página com `start` e `limit`;
- consolidação das páginas no campo `data`;
- envio de dados em JSON;
- upload de arquivos como `multipart/form-data`;
- retorno da resposta da API como objeto PHP.

## Requisitos

- PHP 7.4 ou superior;
- extensão PHP cURL habilitada;
- Composer;
- token de API do Pipedrive.

Para verificar se a extensão cURL está disponível:

```bash
php -m | grep curl
```

No Windows:

```powershell
php -m | Select-String curl
```

## Instalação

Clone ou copie o projeto e instale as dependências:

```bash
composer install
```

O autoload PSR-4 está configurado para mapear o namespace `Source\` para a pasta `source/`. Após criar ou mover classes, regenere-o com:

```bash
composer dump-autoload
```

## Configuração

Carregue o autoload, obtenha o token de uma variável de ambiente e instancie a classe:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Source\Models\Pipedrive;

$token = getenv('PIPEDRIVE_API_TOKEN');

if (!$token) {
    throw new RuntimeException('Defina a variável PIPEDRIVE_API_TOKEN.');
}

$pipedrive = new Pipedrive($token);
```

Não versione tokens reais no código-fonte. Em desenvolvimento, configure `PIPEDRIVE_API_TOKEN` no ambiente ou use uma solução de variáveis de ambiente que mantenha o segredo fora do Git.

## Uso

Os métodos recebem a URL completa do endpoint. A classe acrescenta `api_token` quando esse parâmetro ainda não está presente.

### Criar um registro

```php
$response = $pipedrive->create(
    'https://api.pipedrive.com/v1/deals',
    [
        'title' => 'Novo negócio',
        'value' => 1500,
        'currency' => 'BRL',
    ]
);

if ($response->success) {
    echo "Negócio criado: {$response->data->id}";
}
```

Dados comuns são codificados como JSON e enviados com o cabeçalho `Content-Type: application/json`.

### Consultar todos os registros

```php
$response = $pipedrive->read(
    'https://api.pipedrive.com/v1/deals'
);

foreach ($response->data as $deal) {
    echo "{$deal->id} - {$deal->title}" . PHP_EOL;
}
```

Por padrão, `read()` percorre todas as páginas, em lotes de 500 registros, e reúne os resultados em `$response->data`.

### Consultar apenas uma página

```php
$response = $pipedrive->read(
    'https://api.pipedrive.com/v1/organizations',
    0,     // start
    10,    // limit
    false  // não buscar as páginas seguintes
);
```

Para limitar o retorno, o quarto argumento deve ser `false`. A chamada `read($url, 0, 10)` mantém `$all = true` e, portanto, percorre todas as páginas em lotes de 10.

### Consultar a partir de uma posição

```php
$response = $pipedrive->read(
    'https://api.pipedrive.com/v1/deals',
    500,
    500,
    true
);
```

Nesse exemplo, a consulta começa na posição 500 e continua até o fim da coleção.

### Consultar um registro específico

```php
$response = $pipedrive->read(
    'https://api.pipedrive.com/v1/deals/123'
);

if ($response->success) {
    echo $response->data->title;
}
```

Quando o endpoint retorna um único registro, `data` permanece como objeto e a paginação é encerrada imediatamente.

### Atualizar um registro

```php
$response = $pipedrive->update(
    'https://api.pipedrive.com/v1/deals/123',
    ['title' => 'Negócio atualizado']
);
```

### Excluir um registro

```php
$response = $pipedrive->delete(
    'https://api.pipedrive.com/v1/deals/123'
);
```

### Enviar um arquivo

Quando algum valor do array é uma instância de `CURLFile`, a classe envia todos os dados como `multipart/form-data`:

```php
$file = new CURLFile(
    __DIR__ . '/documento.pdf',
    'application/pdf',
    'documento.pdf'
);

$response = $pipedrive->create(
    'https://api.pipedrive.com/v1/files',
    [
        'file' => $file,
        'deal_id' => 123,
    ]
);
```

## Referência dos métodos

| Método | Requisição | Descrição |
| --- | --- | --- |
| `create(string $url, array $data)` | `POST` | Cria um registro ou envia um arquivo. |
| `read(string $url, int $start = 0, int $limit = 500, bool $all = true)` | `GET` | Consulta um registro ou uma coleção. |
| `update(string $url, array $data)` | `PUT` | Atualiza um registro. |
| `delete(string $url)` | `DELETE` | Exclui um registro. |

## Como funciona a paginação

Em uma requisição `GET`, a classe:

1. adiciona o token à URL, se necessário;
2. remove parâmetros `start` e `limit` numéricos já existentes;
3. aplica os valores informados em `read()`;
4. executa a requisição;
5. retorna imediatamente se `data` for um objeto;
6. acumula os itens quando `data` for um array;
7. continua enquanto `additional_data.pagination.more_items_in_collection` indicar que há mais itens.

Se a API não fornecer o indicador de paginação, a classe considera que pode haver outra página quando a quantidade recebida for igual ao limite solicitado.

Outros parâmetros da URL são preservados:

```php
$response = $pipedrive->read(
    'https://api.pipedrive.com/v1/deals?filter_id=123&start=20&limit=20',
    0,
    10,
    false
);
```

A requisição usará `filter_id=123`, `start=0` e `limit=10`.

## Retorno

A resposta JSON é decodificada com `json_decode($response, false)`. Assim, o resultado normalmente é um `stdClass` com a estrutura fornecida pelo Pipedrive:

```php
$response->success;
$response->data;
$response->additional_data;
$response->related_objects;
```

Em endpoints de coleção, `data` é um array. Em endpoints de item único, `data` é um objeto.

## Tratamento de erros

Erros de inicialização ou execução do cURL e respostas que não contenham JSON válido geram uma `RuntimeException`:

```php
try {
    $response = $pipedrive->read(
        'https://api.pipedrive.com/v1/deals',
        0,
        1,
        false
    );

    if (!$response->success) {
        echo $response->error ?? 'A API recusou a requisição.';
    }
} catch (RuntimeException $exception) {
    echo 'Falha de comunicação: ' . $exception->getMessage();
}
```

Atualmente, a classe não lança exceção com base no status HTTP. Respostas de erro da API que contenham JSON válido são retornadas normalmente; verifique `success` e os campos de erro da resposta.

## Limitações e cuidados

- O token é enviado no parâmetro `api_token` da URL.
- Não há configuração pública de timeout, cabeçalhos adicionais ou tentativas automáticas.
- Não há validação explícita do status HTTP.
- A paginação automática mantém todos os itens em memória; para coleções grandes, prefira consultar uma página por vez com `$all = false`.
- O tratamento de paginação pressupõe o formato da API v1 (`additional_data.pagination`).

## Estrutura do projeto

```text
.
├── composer.json
├── index.php
└── source/
    └── Models/
        └── Pipedrive.php
```

A classe principal está em `Source\Models\Pipedrive`.

## Autor

Léo Motta Rocha — Desenvolvedor Full Stack, integrações e automação de dados.

- [LinkedIn](https://www.linkedin.com/in/leomottarocha)
- [GitHub](https://github.com/leomottarocha)

## Licença

O pacote declara a licença MIT em `composer.json`.
