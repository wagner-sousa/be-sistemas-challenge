
## TODO (cada checkbox pensado como branch independente; evitar misturar logica de outras camadas)

### Ambiente (preparação)
- [x] Docker compose com app + MySQL, variáveis .env alinhadas e start via "docker compose up" documentado.

### Estrutura de Dados
- [x] Migrations: livros (título, autor, ISBN, copias disponíveis) e empréstimos (user, livro, datas/status), relacionamentos e constraints básicas.
- [x] Seed inicial: inserir pelo menos 10 livros completos para teste.

### Backend
- [ ] Serviço Livros (API): endpoints para listar todos os livros e cadastrar novo livro com validações; action pattern opcional; sem UI.
- [ ] Serviço Empréstimos (API): emprestar 1..n livros se ha copias (>0), controle de estoque, limite de 3 empréstimos ativos por usuário, devolução, marca atraso apos 7 dias, idempotência/concorrência; sem UI.
- [ ] Testes Backend: concorrência de empréstimo do mesmo livro por usuários diferentes e limite de 3 por usuário; sem UI.
- [x] Autenticação: instalar Laravel Breeze (login/registro) e aplicar proteção de rotas backend/frontend.

### Frontend
- [ ] Base SPA: React TS com fetch/SWR, rotas e guardas de autenticação, tema MUI e responsividade; sem telas especificas.
- [ ] Tela Listagem de Livros: MUI Table responsiva consumindo API de livros, mostra disponibilidade/estoque, com cache se util; sem alterar serviços.
- [ ] Tela Meus Empréstimos: lista empréstimos do usuário (endpoint raw SQL), exibe status (ativo/atrasado) e datas; sem alterar serviços.
- [ ] Endpoint Raw SQL Meus Empréstimos: usar DB::select/DB::raw para join livros + empréstimos do usuário autenticado; sem UI.
- [ ] Teste e2e: validar listagem de livros no frontend carregando a tabela Material UI.

### Entrega
- [ ] README/Entrega: instruções de setup/execução, comando docker compose up, como rodar testes e credenciais/URLs.
