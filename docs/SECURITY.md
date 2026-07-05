# Segurança

## Rate Limiting Implementado

### API Geral
- **Limite:** 60 requisições por minuto
- **Escopo:** Por usuário autenticado ou IP
- **Aplicação:** Todas as rotas da API

### Operações de Empréstimo
- **Limite:** 10 requisições por minuto
- **Escopo:** Por usuário autenticado ou IP
- **Aplicação:** Rotas de empréstimo e devolução

### Headers de Resposta
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
Retry-After: 60 (quando limite excedido)
```

## Autenticação

### Laravel Sanctum
- Autenticação stateful para SPA
- Tokens API para acesso programático
- CSRF protection habilitada

### Proteção de Rotas
```php
Route::middleware(['auth:sanctum'])->group(function () {
    // Rotas protegidas
});
```

## Validação de Dados

### Validação em Request Classes
- Todas as entradas são validadas
- Regras explícitas para cada campo
- Mensagens de erro customizadas

### Exemplo
```php
public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'author_name' => ['required', 'string', 'max:255'],
        'isbn_code' => ['required', 'string', 'size:13'],
        'total_quantity' => ['required', 'integer', 'min:1'],
    ];
}
```

## Proteção contra SQL Injection

### Consultas Parameterizadas
- Uso de Eloquent ORM
- Prepared statements automáticos
- Validação de tipos de dados

### SQL Raw Seguro
```php
DB::select($sql, [
    'duration' => $duration,
    'user_id' => $userId,
]);
```

## Proteção contra XSS

### Escape Automático
- Blade escapa output por padrão
- React escapa conteúdo por padrão
- Validação de entrada no backend

## Proteção contra CSRF

### Token CSRF
- Gerado automaticamente pelo Laravel
- Validado em todas as requisições POST/PUT/DELETE
- Header `X-CSRF-TOKEN` para requisições AJAX

### Exemplo
```typescript
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
    },
});
```

## Idempotência

### Prevenção de Duplicatas
- Campo `idempotency_key` em operações de empréstimo
- Verificação antes de processar
- Retorno do mesmo resultado para requisições duplicadas

### Exemplo
```json
POST /api/borrowed-books
{
    "books": [1, 2],
    "idempotency_key": "unique-key-123"
}
```

## Concorrência e Locking

### Pessimistic Locking
```php
$book = Book::query()
    ->whereKey($bookId)
    ->lockForUpdate()
    ->firstOrFail();
```

### Transações
```php
DB::transaction(function () {
    // Operações atômicas
});
```

### Benefícios
- Previne race conditions
- Garante consistência de dados
- Evita empréstimos duplicados

## Headers de Segurança

### Recomendados para Produção
```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
Content-Security-Policy: default-src 'self'
```

### Configuração no Nginx
```nginx
add_header X-Content-Type-Options nosniff;
add_header X-Frame-Options DENY;
add_header X-XSS-Protection "1; mode=block";
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

## Validação de Entrada

### Regras de Validação
- **Título:** string, máximo 255 caracteres
- **ISBN:** string, exatamente 13 caracteres
- **Quantidade:** inteiro, mínimo 1
- **Email:** formato válido de email
- **Senha:** mínimo 8 caracteres

### Sanitização
- Remoção de espaços em branco
- Conversão de tipos
- Validação de formato

## Autorização

### Policies (Futuro)
```php
class BookPolicy
{
    public function update(User $user, Book $book)
    {
        return $user->id === $book->user_id;
    }
}
```

### Gate (Futuro)
```php
Gate::define('borrow-book', function (User $user) {
    return $user->current_borrowed_books < 3;
});
```

## Monitoramento de Segurança

### Logs de Segurança
- Tentativas de acesso não autorizado
- Erros de validação
- Rate limit exceeded
- Operações suspeitas

### Alertas Recomendados
- Múltiplas tentativas de login falhas
- Rate limit excedido repetidamente
- Tentativas de acesso a recursos de outros usuários
- Operações em horários incomuns

## Checklist de Segurança

### Desenvolvimento
- [ ] Validação de todas as entradas
- [ ] Uso de prepared statements
- [ ] Escape de output
- [ ] CSRF protection
- [ ] Rate limiting
- [ ] Logs de operações críticas

### Produção
- [ ] HTTPS habilitado
- [ ] Headers de segurança configurados
- [ ] Variáveis de ambiente seguras
- [ ] Backups regulares
- [ ] Monitoramento de logs
- [ ] Atualizações de segurança

## Dependências Seguras

### Atualizações
```bash
composer audit
npm audit
```

### Verificação
- Dependências sem vulnerabilidades conhecidas
- Versões estáveis e mantidas
- Sem pacotes abandonados

## Boas Práticas

### Senhas
- Hash com bcrypt (Laravel padrão)
- Mínimo 8 caracteres
- Não armazenar em texto plano

### Tokens
- Tokens com expiração
- Revogação de tokens comprometidos
- Armazenamento seguro (HttpOnly cookies)

### Dados Sensíveis
- Não logar dados sensíveis
- Criptografar dados em repouso
- Máscara de dados em logs

## Recursos Adicionais

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security](https://laravel.com/docs/security)
- [Sanctum Documentation](https://laravel.com/docs/sanctum)
