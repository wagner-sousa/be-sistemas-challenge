### ✨ Funcionalidades

- 🔐 **Autenticação JWT**: Login seguro com tokens de 24 horas (configurável)
- 🏙️ **Gerenciamento de Cidades**: Sincronização automática com API do IBGE
- 🏢 **CRUD de Empresas**: Cadastro completo com validações
- 📊 **Gerenciamento de Segmentos**: Categorias de empreendimentos
- 📡 **API REST Segura**: Endpoints protegidos com documentação Swagger
- 🧪 **Testes Automatizados**: Cobertura completa com JUnit 5
- 🗄️ **Banco PostgreSQL**: Migrações automatizadas com Flyway

## 💻 Tecnologias

### 📋 Pré-requisitos

![Java](https://img.shields.io/badge/Java-25-DD0700?style=for-the-badge&logo=java&logoColor=white)
![Spring Boot](https://img.shields.io/badge/Spring%20Boot-4.0.3-6DB33F?style=for-the-badge&logo=spring&logoColor=white)
![Spring Security](https://img.shields.io/badge/Spring%20Security-6DB33F?style=for-the-badge&logo=spring-security&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-latest-316192?style=for-the-badge&logo=postgresql&logoColor=white)
![Apache Maven](https://img.shields.io/badge/Apache%20Maven-3.9.12-C71A36?style=for-the-badge&logo=apachemaven&logoColor=white)
![JUnit5](https://img.shields.io/badge/JUnit5-5.10.1-25A162?style=for-the-badge&logo=junit5&logoColor=white)
![Swagger](https://img.shields.io/badge/Swagger-3.0.2-85EA2D?style=for-the-badge&logo=swagger&logoColor=black)

### Ferramentas de Desenvolvimento
![Visual Studio Code](https://img.shields.io/badge/Visual_Studio_Code-0078D4?style=for-the-badge&logo=visual%20studio%20code&logoColor=white)
![GitHub Copilot](https://img.shields.io/badge/github%20copilot-000000?style=for-the-badge&logo=githubcopilot&logoColor=white)
![SonarQube](https://img.shields.io/badge/Sonarqube-5190cf?style=for-the-badge&logo=sonarqube&logoColor=white)
![Prettier](https://img.shields.io/badge/prettier-1A2C34?style=for-the-badge&logo=prettier&logoColor=F7BA3E)
![ESLint](https://img.shields.io/badge/eslint-3A33D1?style=for-the-badge&logo=eslint&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2CA5E0?style=for-the-badge&logo=docker&logoColor=white)
![Postman](https://img.shields.io/badge/Postman-FF6C37?style=for-the-badge&logo=postman&logoColor=white)
![Git](https://img.shields.io/badge/Git-E34F26?style=for-the-badge&logo=git&logoColor=white)
![Linux](https://img.shields.io/badge/Linux-FCC624?style=for-the-badge&logo=linux&logoColor=black)
![JWT](https://img.shields.io/badge/JWT-000000?style=for-the-badge&logo=JSON%20web%20tokens&logoColor=white)
![Flyway](https://img.shields.io/badge/Flyway-CC0200?style=for-the-badge&logo=flyway)
![Spring Data JPA](https://img.shields.io/badge/Spring%20Data%20JPA-6DB33F?style=for-the-badge&logo=spring&logoColor=white)

### Tecnologias Principais
- **Spring Boot 4.0.3**: Framework principal
- **Spring Data JPA**: Persistência de dados
- **Spring Security**: Autenticação e autorização com JWT
- **JWT (JSON Web Tokens)**: Autenticação stateless com HS256
- **Flyway**: Migrações de banco de dados
- **PostgreSQL**: Banco de dados
- **RestTemplate**: Cliente HTTP para integração com APIs externas
- **SpringDoc OpenAPI**: Documentação Swagger


## 🚀 Instalando e Executando

### Desenvolvimento com Dev Container (Recomendado)

Este projeto utiliza Dev Containers para fornecer um ambiente de desenvolvimento consistente e isolado.

1. **Pré-requisitos**
   - Visual Studio Code
   - Extensão "Dev Containers" instalada
   - Docker instalado e em execução

2. **Abrir no Dev Container**
   - Abra o projeto no VS Code
   - Quando solicitado, clique em "Reopen in Container" ou use o comando `Dev Containers: Reopen in Container`
   - O ambiente será configurado automaticamente com Java 17, Maven e PostgreSQL

3. **Executar a aplicação**
   ```bash
   ./mvnw spring-boot:run
   ```

4. **Acesse o sistema**
   - 🌐 API: http://localhost:8080
   - 📚 Swagger UI: http://localhost:8080/swagger-ui/index.html
   - 🔐 Login: Use as credenciais admin/admin123

✅ **Vantagens**: Ambiente consistente, dependências isoladas, PostgreSQL integrado

A aplicação estará disponível em `http://localhost:8080`

## 📡 API Endpoints

### Autenticação
A aplicação usa Spring Security com autenticação JWT. Para acessar endpoints protegidos, é necessário obter um token via login.

#### Login
- **POST /login**
  - Descrição: Autentica o usuário e retorna um token JWT
  - Corpo da requisição:
    ```json
    {
      "username": "admin",
      "password": "admin123"
    }
    ```
  - Resposta de sucesso:
    ```json
    {
      "token": "eyJhbGciOiJIUzI1NiJ9..."
    }
    ```
  - Resposta de erro (401):
    ```json
    {
      "error": "Invalid credentials"
    }
    ```
  - Autenticação: Não requerida

Para usar o token, inclua no header: `Authorization: Bearer <token>`

### Como Usar a API

#### Via curl

```bash
# 1. Obter token
TOKEN=$(curl -s -X POST http://localhost:8080/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}' | jq -r '.token')

# 2. Listar cidades
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8080/cities

# 3. Criar segmento
curl -X POST http://localhost:8080/segments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "Tecnologia"}'
```

Para testar via Postman ou Swagger UI, consulte as seções dedicadas abaixo.

### Teste de Serviço
- **GET /test**
  - Descrição: Endpoint de teste da aplicação
  - Resposta: `"OK"`
  - Autenticação: Não requerida

### Cidades

#### Listar Cidades
- **GET /cities**
  - Descrição: Lista todas as cidades cadastradas
  - Resposta: Lista de objetos City
    ```json
    [
      {
        "id": 1,
        "code": 4200051,
        "name": "Abdon Batista"
      }
    ]
    ```

### Segmentos

#### Listar Segmentos
- **GET /segments**
  - Descrição: Lista todos os segmentos cadastrados
  - Resposta: Lista de objetos Segment
    ```json
    [
      {
        "id": 1,
        "name": "Tecnologia"
      }
    ]
    ```
    
#### Buscar Segmento por ID
- **GET /segments/{id}**
  - Descrição: Busca um segmento específico por ID
  - Parâmetros: `id` (Long)
  - Resposta: Objeto Segment ou 404 se não encontrado

#### Criar Segmento
- **POST /segments**
  - Descrição: Cria um novo segmento
  - Corpo da requisição:
    ```json
    {
      "name": "Tecnologia"
    }
    ```
  - Resposta: Objeto Segment criado

#### Atualizar Segmento
- **PUT /segments/{id}**
  - Descrição: Atualiza um segmento existente
  - Parâmetros: `id` (Long)
  - Corpo da requisição: Mesmo formato do POST
  - Resposta: Objeto Segment atualizado ou 404 se não encontrado

#### Deletar Segmento
- **DELETE /segments/{id}**
  - Descrição: Remove um segmento
  - Parâmetros: `id` (Long)
  - Resposta: 204 No Content

### Empresas

#### Listar Empresas
- **GET /companies**
  - Descrição: Lista todas as empresas cadastradas
  - Resposta: Lista de objetos Company
    ```json
    [
      {
        "id": 1,
        "name": "Empresa Exemplo",
        "responsibleName": "João Silva",
        "cityId": 1,
        "email": "contato@empresa.com",
        "segmentId": 1,
        "status": true
      }
    ]
    ```

#### Buscar Empresa por ID
- **GET /companies/{id}**
  - Descrição: Busca uma empresa específica por ID
  - Parâmetros: `id` (Long)
  - Resposta: Objeto Company ou 404 se não encontrado

#### Criar Empresa
- **POST /companies**
  - Descrição: Cria uma nova empresa
  - Corpo da requisição:
    ```json
    {
      "name": "Empresa Exemplo",
      "responsibleName": "João Silva",
      "cityId": 1,
      "email": "contato@empresa.com",
      "segmentId": 1,
      "status": true
    }
    ```
  - Resposta: Objeto Company criado
  - Validações: cityId e segmentId devem existir

#### Atualizar Empresa
- **PUT /companies/{id}**
  - Descrição: Atualiza uma empresa existente
  - Parâmetros: `id` (Long)
  - Corpo da requisição: Mesmo formato do POST
  - Resposta: Objeto Company atualizado ou 404 se não encontrado

#### Deletar Empresa
- **DELETE /companies/{id}**
  - Descrição: Remove uma empresa
  - Parâmetros: `id` (Long)
  - Resposta: 204 No Content

## 📚 Documentação da API

A documentação completa da API está disponível via Swagger UI.

### Acesso ao Swagger UI
- **URL:** `http://localhost:8080/swagger-ui/index.html`
- **Descrição:** Interface interativa para explorar e testar todos os endpoints da API
- **Autenticação:** Não requerida para acessar a documentação

### Recursos da Documentação
- Lista completa de todos os endpoints
- Descrições detalhadas de cada operação
- Exemplos de requests e responses
- Possibilidade de testar endpoints diretamente na interface
- Esquemas dos objetos de dados (DTOs)

## 🧪 Testando com Postman

Uma collection completa do Postman está disponível na raiz do projeto: [postman_collection.json](postman_collection.json).

### Como usar:
1. **Importe a collection no Postman:**
   - Abra o Postman
   - Clique em "Import" > "File"
   - Selecione o arquivo [postman_collection.json](postman_collection.json)

2. **Configure as variáveis:**
   - Na collection, defina `baseUrl` como `http://localhost:8080` (ou o URL do seu ambiente)
   - O token JWT será automaticamente salvo na variável `token` após o login

3. **Execute os requests:**
   - Comece com o request "Login" para obter o token
   - Use o token nos headers dos requests protegidos (`Authorization: Bearer {{token}}`)
   - Ajuste IDs nos requests (ex.: `cityId` e `segmentId`) conforme dados do banco

A collection inclui todos os endpoints documentados acima, com exemplos de bodies e autenticação configurada.

## 🏗️ Arquitetura

### Diagrama de Arquitetura

```mermaid
graph TB
    Client[Cliente<br/>Postman/Swagger] --> API[API REST<br/>Spring Boot]
    API --> Auth[Autenticação JWT<br/>Spring Security]
    API --> Controllers[Controllers<br/>City/Company/Segment]
    Controllers --> Services[Services<br/>Lógica de Negócio]
    Services --> Repositories[Repositories<br/>Spring Data JPA]
    Repositories --> DB[(PostgreSQL<br/>Banco de Dados)]
    Services --> IBGE[API IBGE<br/>RestTemplate]
    IBGE --> External[servicodados.ibge.gov.br]
```

### Modelo de Dados

```mermaid
erDiagram
    CITIES {
        BIGINT id PK
        INTEGER code UK
        VARCHAR name
    }
    
    SEGMENTS {
        BIGINT id PK
        VARCHAR name UK
    }
    
    COMPANIES {
        BIGINT id PK
        VARCHAR name
        VARCHAR responsible_name
        BIGINT city_id FK
        VARCHAR email
        BIGINT segment_id FK
        BOOLEAN status
    }
    
    CITIES ||--o{ COMPANIES : "city_id"
    SEGMENTS ||--o{ COMPANIES : "segment_id"
```

**Relacionamentos**: 
- Companies → Cities (Many-to-One): Uma empresa pertence a uma cidade
- Companies → Segments (Many-to-One): Uma empresa pertence a um segmento

### Estrutura do Projeto
```
src/main/java/com/api/demo/
├── controller/          # Controllers REST (CityController, CompanyController, etc.)
├── dto/                 # Data Transfer Objects (CityDTO, CompanyDTO, etc.)
├── entity/              # Entidades JPA (City, Company, Segment)
├── repository/          # Repositórios de dados (CityRepository, etc.)
├── service/             # Lógica de negócio (CityService, CompanyService, etc.)
├── security/            # Configurações de segurança JWT (JwtUtil, JwtAuthenticationFilter, etc.)
├── AppConfig.java       # Configurações da aplicação (RestTemplate, OpenAPI)
├── DemoApplication.java # Classe principal Spring Boot
├── HomeController.java  # Controller de teste
└── SecurityConfig.java  # Configurações de segurança
```

### Funcionalidades Chave
- **Sincronização Automática**: Na inicialização da aplicação, cidades de Santa Catarina são sincronizadas automaticamente da API do IBGE.
- **CRUD Completo**: Para cidades (somente leitura e sincronização), segmentos e empresas.
- **Autenticação JWT**: Login com usuário hardcoded (admin/admin123) para endpoints protegidos.
- **Documentação Interativa**: Swagger UI disponível em `/swagger-ui/index.html`.

## 🔐 Segurança e Autenticação

### Arquitetura de Segurança

```mermaid
flowchart LR
    Client[Cliente<br/>Postman/Swagger] --> API[API REST<br/>Spring Boot]
    API --> Auth[Autenticação JWT<br/>Spring Security]
    Auth --> Filter[JwtAuthenticationFilter<br/>Validação de Token]
    Filter --> Controller[Controllers<br/>Protegidos]
    Controller --> Service[Services<br/>Lógica de Negócio]
```

### Características

- ✅ **JWT (JSON Web Tokens)** para autenticação stateless
- ✅ **HS256** (HMAC-SHA256) para assinatura de tokens
- ✅ **Expiração Configurável** - Tokens válidos por 24 horas (configurável)
- ✅ **Chave Secreta de 256 bits** - Gerada com OpenSSL
- ✅ **Endpoints Protegidos** - Todos os CRUDs requerem token válido
- ✅ **CORS Habilitado** - Permite integrações externas

### Credenciais Padrão

Para acessar a API, use as seguintes credenciais:

```yaml
Username: admin
Password: admin123
```

> ⚠️ **Produção**: Altere essas credenciais antes de fazer deploy em produção!

### Geração de Chave Secreta JWT

Para gerar uma nova chave secreta segura:

```bash
# Gerar chave de 256 bits em Base64
openssl rand -base64 32
```

Atualize o valor em `application.properties`:

```properties
jwt.secret= SUA_CHAVE_GERADA_AQUI
jwt.expiration=86400000
```

### Fluxo de Autenticação

1. **Login**: Cliente envia `POST /login` com credenciais
2. **Token**: Servidor valida e retorna token JWT
3. **Armazenamento**: Cliente guarda token para futuras requisições
4. **Requisições**: Cliente envia `Authorization: Bearer <token>` em cada chamada
5. **Validação**: Servidor valida token e permite acesso ao recurso
6. **Expiração**: Após 24h, token expira e novo login é necessário

## 🧪 Testes

O projeto conta com uma suíte completa de testes automatizados.

### Executar Todos os Testes

```bash
./mvnw test
```

### Executar Testes e Gerar Relatório de Cobertura

```bash
./mvnw test jacoco:report
```

O relatório de cobertura estará disponível em `target/site/jacoco/index.html` (abra no navegador para visualizar).

### Cobertura de Testes

| Componente | Status |
|------------|--------|
| **Controllers** | ✅ Testes unitários e integração |
| **Services** | ✅ Lógica de negócio |
| **Security** | ✅ JWT e autenticação |
| **Repositories** | ✅ Operações de banco |
| **DTOs** | ✅ Validações e mapeamento |

**Total**: Cobertura superior a 80% do código

## 📦 Deploy

### Gerar JAR Executável

O JAR contém um servidor Tomcat embutido e pode ser executado standalone:

```bash
# Compilar o projeto
./mvnw clean package

# Executar o JAR gerado
java -jar target/challenge-software-backend-0.0.1-SNAPSHOT.jar
```

📦 **Arquivo**: `target/challenge-software-backend-0.0.1-SNAPSHOT.jar` (~70MB)

### Gerar WAR para Servidor Externo

O projeto está configurado para gerar arquivos WAR compatíveis com Tomcat 9+:

```bash
# Gerar WAR sem executar testes (mais rápido)
./mvnw clean package -DskipTests
```

📦 **Arquivo**: `target/challenge-software-backend-0.0.1-SNAPSHOT.war` (~67MB)

### Deploy no Tomcat

1. Copie o WAR para a pasta `webapps` do Tomcat:
   ```bash
   cp target/challenge-software-backend-0.0.1-SNAPSHOT.war $TOMCAT_HOME/webapps/challenge.war
   ```

2. Inicie o Tomcat e acesse: http://localhost:8080/challenge/

### Deploy com Docker

```bash
# Build da imagem
docker build -t challenge-backend .

# Executar container
docker run -d -p 8080:8080 \
  -e SPRING_DATASOURCE_URL=jdbc:postgresql://host.docker.internal:5432/demo \
  --name challenge-app challenge-backend
```

### Configurações

#### Configuração JWT (application.properties)
- `jwt.secret`: Chave secreta de 256 bits para assinatura (gerada com OpenSSL)
- `jwt.expiration`: Tempo de expiração do token em milissegundos (padrão: 86400000 = 24 horas)
- `app.user.username`: Nome de usuário para login (padrão: admin)
- `app.user.password`: Senha para login (padrão: admin123)

## Vídeo do Projeto

https://youtu.be/QFPW7g09MDA
