# API Documentation

## Autenticação

A API usa Laravel Sanctum para autenticação. Todas as rotas requerem token Bearer.

```
Authorization: Bearer {token}
```

### Obter Token

Faça login via interface web e gere um token em `/profile`.

---

## Endpoints

### Livros (Books)

#### Listar Livros
```
GET /api/books
```

**Query Parameters:**
- `page` (int): Número da página (padrão: 1)
- `per_page` (int): Itens por página (padrão: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Livro Exemplo",
      "author": "Autor Exemplo",
      "isbn_code": "9781234567890",
      "total_quantity": 5,
      "borrowed_quantity": 2,
      "available_quantity": 3,
      "active": true
    }
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 15,
  "total": 1
}
```

#### Criar Livro
```
POST /api/books
```

**Body:**
```json
{
  "title": "Novo Livro",
  "author_name": "Nome do Autor",
  "isbn_code": "9781234567890",
  "total_quantity": 5,
  "active": true
}
```

**Response:** `201 Created`

#### Obter Livro
```
GET /api/books/{id}
```

#### Atualizar Livro
```
PUT /api/books/{id}
```

**Body:** (mesmo formato de criação, campos opcionais)

#### Deletar Livro
```
DELETE /api/books/{id}
```

**Erros:**
- `422`: Livro possui empréstimos ativos

---

### Empréstimos (Borrowed Books)

#### Listar Empréstimos do Usuário
```
GET /api/borrowed-books
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "identifier": "uuid-string",
      "book_id": 1,
      "title": "Livro Exemplo",
      "author": "Autor Exemplo",
      "started_at": "2026-01-01T00:00:00Z",
      "predicted_end_at": "2026-01-08T00:00:00Z",
      "ended_at": null,
      "is_overdue": false
    }
  ]
}
```

#### Criar Empréstimo
```
POST /api/borrowed-books
```

**Body:**
```json
{
  "books": [1, 2, 3],
  "idempotency_key": "unique-key-123"
}
```

**Parâmetros:**
- `books` (array): IDs dos livros para emprestar
- `idempotency_key` (string, opcional): Chave para prevenir duplicatas

**Response:** `201 Created`
```json
{
  "identifier": "uuid-string",
  "idempotency_key": "unique-key-123"
}
```

**Erros:**
- `422`: Livro indisponível
- `422`: Limite de 3 empréstimos ativos atingido

#### Devolver Livro Individual
```
PATCH /api/borrowed-books/return/book/{id}
```

**Response:** `200 OK`

**Erros:**
- `422`: Empréstimo já finalizado

#### Devolver por Identificador
```
POST /api/borrowed-books/return/{identifier}
```

**Response:** `200 OK`
```json
{
  "message": "Livros devolvidos com sucesso."
}
```

**Erros:**
- `422`: Identificador não encontrado

---

### Autores (Authors)

#### Listar Autores
```
GET /api/authors
```

#### Criar Autor
```
POST /api/authors
```

**Body:**
```json
{
  "name": "Nome do Autor"
}
```

#### Obter Autor
```
GET /api/authors/{id}
```

#### Atualizar Autor
```
PUT /api/authors/{id}
```

#### Deletar Autor
```
DELETE /api/authors/{id}
```

---

## Códigos de Status

| Código | Descrição |
|--------|-----------|
| 200 | Sucesso |
| 201 | Criado com sucesso |
| 204 | Sem conteúdo (deleção) |
| 401 | Não autenticado |
| 403 | Não autorizado |
| 404 | Não encontrado |
| 422 | Erro de validação |
| 500 | Erro interno do servidor |

---

## Idempotência

Para prevenir operações duplicadas, use o campo `idempotency_key` ao criar empréstimos:

1. Gere uma chave única (UUID, timestamp, etc)
2. Envie junto com a requisição
3. Se a mesma chave for enviada novamente, a API retornará o mesmo resultado sem processar novamente

**Exemplo:**
```bash
# Primeira requisição
POST /api/borrowed-books
{
  "books": [1],
  "idempotency_key": "req-123-abc"
}

# Segunda requisição (mesma chave)
POST /api/borrowed-books
{
  "books": [1],
  "idempotency_key": "req-123-abc"
}
# Retorna o mesmo identifier da primeira requisição
```

---

## Limites

- **Empréstimos ativos por usuário:** 3
- **Duração do empréstimo:** 7 dias
- **Itens por página:** 15 (padrão)

---

## Erros Comuns

### Livro Indisponível
```json
{
  "message": "O livro 'Nome do Livro' não possui cópias disponíveis no momento."
}
```

### Limite Atingido
```json
{
  "message": "Você já possui 3 livros emprestados. O limite é de 3 empréstimos ativos."
}
```

### Empréstimo Já Devolvido
```json
{
  "message": "Este empréstimo já foi finalizado."
}
```
