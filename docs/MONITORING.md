# Logging e Monitoramento

## Logs Implementados

### Operações de Empréstimo

#### Início do Processo
```php
Log::info('Iniciando processo de empréstimo', [
    'user_id' => $userId,
    'books_count' => $count,
    'book_ids' => $ids,
]);
```

#### Sucesso
```php
Log::info('Empréstimo realizado com sucesso', [
    'user_id' => $userId,
    'identifier' => $identifier,
    'books_count' => $count,
]);
```

### Operações de Devolução

#### Início
```php
Log::info('Iniciando devolução de livro', [
    'borrowed_book_id' => $id,
    'book_id' => $bookId,
    'user_id' => $userId,
]);
```

#### Sucesso
```php
Log::info('Livro devolvido com sucesso', [
    'borrowed_book_id' => $id,
    'book_id' => $bookId,
]);
```

## Visualizar Logs

### Ambiente de Desenvolvimento
```bash
tail -f storage/logs/laravel.log
```

### Filtrar por Operação
```bash
grep "Empréstimo realizado" storage/logs/laravel.log
grep "Livro devolvido" storage/logs/laravel.log
```

## Métricas Recomendadas

### Para Monitoramento em Produção

1. **Taxa de Empréstimos**
   - Empréstimos por hora/dia
   - Livros mais emprestados
   - Usuários mais ativos

2. **Taxa de Devoluções**
   - Devoluções no prazo vs atrasadas
   - Tempo médio de empréstimo

3. **Erros e Exceções**
   - Tentativas de empréstimo com estoque insuficiente
   - Limite de empréstimos atingido
   - Erros de validação

4. **Performance**
   - Tempo de resposta da API
   - Queries lentas
   - Uso de memória

## Ferramentas Recomendadas

### Log Management
- **Laravel Telescope**: Debug e monitoramento local
- **Spatie Laravel Log Cleanup**: Rotação de logs
- **Papertrail/Loggly**: Logs centralizados em produção

### Application Performance Monitoring (APM)
- **Laravel Horizon**: Monitoramento de queues
- **New Relic**: APM completo
- **Sentry**: Error tracking

### Database Monitoring
- **Query Logging**: Habilitar em desenvolvimento
- **Slow Query Log**: Identificar queries lentas
- **EXPLAIN ANALYZE**: Analisar performance de queries

## Alertas Recomendados

### Críticos
- Erro 500 em endpoints de empréstimo/devolução
- Falha de conexão com banco de dados
- Deadlocks em transações

### Avisos
- Estoque baixo (livros com 0 disponíveis)
- Usuários próximos do limite de empréstimos
- Empréstimos atrasados há mais de 14 dias

### Informativos
- Pico de empréstimos (mais de 10/hora)
- Novos usuários registrados
- Livros mais emprestados da semana

## Configuração de Logs

### Níveis de Log
```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
        'ignore_exceptions' => false,
    ],
    
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
    
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
],
```

### Contexto Adicional
```php
Log::withContext([
    'request_id' => Str::uuid(),
    'user_id' => Auth::id(),
]);
```

## Dashboard de Monitoramento

### Métricas Essenciais
- Total de empréstimos ativos
- Total de devoluções hoje
- Livros disponíveis por título
- Usuários com limite atingido

### Gráficos Recomendados
- Empréstimos por dia (últimos 30 dias)
- Tempo médio de empréstimo
- Taxa de devoluções atrasadas
- Top 10 livros mais emprestados

## Queries para Análise

### Empréstimos por Dia
```sql
SELECT DATE(started_at) as date, COUNT(*) as total
FROM borrowed_books
WHERE started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(started_at)
ORDER BY date DESC;
```

### Livros Mais Emprestados
```sql
SELECT b.title, COUNT(bb.id) as borrow_count
FROM borrowed_books bb
JOIN books b ON bb.book_id = b.id
GROUP BY b.id, b.title
ORDER BY borrow_count DESC
LIMIT 10;
```

### Taxa de Atraso
```sql
SELECT 
    COUNT(*) as total_loans,
    SUM(CASE WHEN ended_at IS NULL AND DATE_ADD(started_at, INTERVAL 7 DAY) < NOW() THEN 1 ELSE 0 END) as overdue
FROM borrowed_books
WHERE started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```
