# 🚀 Classe Pipedrive (PHP SDK)

Uma classe PHP simples e robusta para **integração direta com a API do Pipedrive**, permitindo executar operações CRUD (Create, Read, Update, Delete) de forma padronizada e segura.  

Ideal para projetos que precisam se comunicar com o Pipedrive sem depender de bibliotecas externas.

---

## 📦 Visão Geral

A classe `Pipedrive` encapsula a lógica de autenticação via token e o envio de requisições HTTP usando `cURL`.  
Ela facilita a criação, leitura, atualização e exclusão de registros em qualquer endpoint da API do Pipedrive.

- ✅ Simples de usar  
- 🔒 Autenticação via `api_token`  
- 📡 Métodos genéricos para qualquer endpoint  
- 🧩 Retorno padronizado e fácil de interpretar  

---

## ⚙️ Estrutura da Classe

```php
namespace Source\Models;

class Pipedrive
{
    private $token;

    public function __construct($token);
    public function create($url, array $data);
    public function read($url);
    public function update($url, array $data);
    public function delete($url);
    private function request($method, $url, array $data = null);
}
```

---

## 🧠 Como Funciona

A autenticação é feita via `api_token`, automaticamente anexado à URL de cada requisição.  
O método interno `request()` executa as chamadas HTTP com `cURL`, gerencia erros e retorna um objeto padronizado com os seguintes campos:

| Campo | Tipo | Descrição |
|--------|------|------------|
| `success` | `bool` | Indica se a requisição foi bem-sucedida |
| `status_code` | `int` | Código HTTP retornado pela API |
| `data` | `mixed` | Dados retornados pela API (`stdClass` ou `null`) |
| `error_message` | `string|null` | Mensagem de erro em caso de falha |

---

## 🚀 Exemplo de Uso

```php
require 'vendor/autoload.php';

use Source\Models\Pipedrive;

// Seu token pessoal do Pipedrive
$token = 'SEU_API_TOKEN_AQUI';

// Cria a instância
$pipedrive = new Pipedrive($token);

// Exemplo: Criar um novo negócio (deal)
$response = $pipedrive->create(
    'https://api.pipedrive.com/v1/deals',
    [
        'title' => 'Negócio Exemplo',
        'value' => 5000,
        'currency' => 'BRL',
        'stage_id' => 3
    ]
);

// Verifica o resultado
if ($response->success) {
    echo "✅ Deal criado com sucesso! ID: " . $response->data->id;
} else {
    echo "❌ Erro: " . $response->error_message;
}
```

---

## 📚 Exemplos de Métodos

### 🔹 `create($url, array $data)`
Cria um novo registro (POST).

```php
$pipedrive->create('https://api.pipedrive.com/v1/persons', [
    'name' => 'João da Silva',
    'email' => 'joao@empresa.com'
]);
```

---

### 🔹 `read($url)`
Lê dados de um endpoint (GET).

```php
$pipedrive->read('https://api.pipedrive.com/v1/deals/123');
```

---

### 🔹 `update($url, array $data)`
Atualiza registros existentes (PUT).

```php
$pipedrive->update('https://api.pipedrive.com/v1/deals/123', [
    'title' => 'Negócio Atualizado'
]);
```

---

### 🔹 `delete($url)`
Exclui um registro (DELETE).

```php
$pipedrive->delete('https://api.pipedrive.com/v1/deals/123');
```

---

## 📤 Estrutura de Retorno

Exemplo de retorno de sucesso:

```json
{
  "success": true,
  "status_code": 200,
  "data": {
    "id": 123,
    "title": "Negócio Exemplo"
  },
  "error_message": null
}
```

Exemplo de erro:

```json
{
  "success": false,
  "status_code": 404,
  "data": null,
  "error_message": "Erro HTTP 404"
}
```

---

## 🧩 Boas Práticas

- Sempre use **URLs completas** da API do Pipedrive (ex: `https://api.pipedrive.com/v1/deals`).
- O método já adiciona automaticamente `?api_token=SEU_TOKEN` quando necessário.
- Em caso de erro `cURL`, verifique a chave `error_message`.
- Para tratar arrays, use `json_decode($response, true)` manualmente, se desejar converter para array associativo.

---

## 🧾 Licença

Distribuído sob a licença **MIT**.  
Sinta-se à vontade para usar, modificar e compartilhar.  

---

## 👨‍💻 Autor

**Léo M. Rocha**  
Desenvolvedor PHP | Integrações | Automação de Dados  
[LinkedIn](https://www.linkedin.com/in/leomottarocha) · [GitHub](https://github.com/leomottarocha)

---

> 💡 *Simples, elegante e direto ao ponto — exatamente o que uma integração Pipedrive em PHP precisa ser.*
