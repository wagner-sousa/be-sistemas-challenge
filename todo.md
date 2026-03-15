
## TODO (cada checkbox pensado como branch independente; evitar misturar logica de outras camadas)

### Ambiente (preparacao)
- [x] Docker compose com app + MySQL, variaveis .env alinhadas e start via "docker compose up" documentado.

### Estrutura de Dados
- [x] Migrations: livros (titulo, autor, isbn, copias disponiveis) e emprestimos (user, livro, datas/status), relacionamentos e constraints basicas.
- [x] Seed inicial: inserir pelo menos 10 livros completos para teste.

### Backend
- [ ] Servico Livros (API): endpoints para listar todos os livros e cadastrar novo livro com validacoes; action pattern opcional; sem UI.
- [ ] Servico Emprestimos (API): emprestar 1..n livros se ha copias (>0), controle de estoque, limite de 3 emprestimos ativos por usuario, devolucao, marca atraso apos 7 dias, idempotencia/concorrencia; sem UI.
- [ ] Testes Backend: concorrencia de emprestimo do mesmo livro por usuarios diferentes e limite de 3 por usuario; sem UI.
- [ ] Autenticacao: instalar Laravel Breeze (login/registro) e aplicar protecao de rotas backend/frontend.

### Frontend
- [ ] Base SPA: React TS com fetch/SWR, rotas e guardas de autenticacao, tema MUI e responsividade; sem telas especificas.
- [ ] Tela Listagem de Livros: MUI Table responsiva consumindo API de livros, mostra disponibilidade/estoque, com cache se util; sem alterar servicos.
- [ ] Tela Meus Emprestimos: lista emprestimos do usuario (endpoint raw SQL), exibe status (ativo/atrasado) e datas; sem alterar servicos.
- [ ] Endpoint Raw SQL Meus Emprestimos: usar DB::select/DB::raw para join livros + emprestimos do usuario autenticado; sem UI.
- [ ] Teste e2e: validar listagem de livros no frontend carregando a tabela Material UI.

### Entrega
- [ ] README/Entrega: instrucoes de setup/execucao, comando docker compose up, como rodar testes e credenciais/URLs.
