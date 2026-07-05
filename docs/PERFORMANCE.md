# Otimizações de Performance

## Índices do Banco de Dados

### Tabela `books`
- `author_id`: Acelera consultas por autor
- `active`: Acelera filtro de livros ativos
- `['active', 'author_id']`: Índice composto para consultas combinadas

### Tabela `borrowed_books`
- `user_id`: Acelera consultas por usuário
- `book_id`: Acelera consultas por livro
- `identifier`: Acelera busca por identificador de empréstimo
- `started_at`: Acelera ordenação por data
- `ended_at`: Acelera filtro de empréstimos ativos
- `['user_id', 'ended_at']`: Índice composto para consultas de empréstimos ativos por usuário

## Eager Loading

Todos os controllers usam eager loading para evitar N+1 queries:
- `BookController`: `with('author')`
- `BorrowedBookController`: `with(['book.author', 'user'])`

## Consultas SQL

A consulta SQL Raw para "Meus Empréstimos" usa JOIN otimizado com:
- Filtro por `user_id` (indexado)
- Ordenação por `created_at`
- Cálculo de `is_overdue` no banco (evita processamento em PHP)

## Cache

O frontend implementa cache no `LibraryContext`:
- Livros são cacheados até refresh explícito
- Empréstimos são cacheados até refresh explícito
- Reduz chamadas desnecessárias à API

## Paginação

- API retorna 15 itens por página (padrão Laravel)
- Frontend implementa paginação visual
- Reduz carga de dados transferidos

## Recomendações Futuras

1. **Cache Redis**: Implementar cache de consultas frequentes
2. **Query Logging**: Monitorar queries lentas
3. **Database Indexes**: Adicionar índices conforme crescimento de dados
4. **API Rate Limiting**: Prevenir abuso da API
5. **Lazy Loading**: Considerar infinite scroll para grandes volumes
