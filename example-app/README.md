# API PayPortal - Guideline

## Pré-requisitos

Antes de iniciar o desenvolvimento ou a execução do ambiente de desenvolvimento da API PayPortal, certifique-se de ter instalado em sua máquina as seguintes ferramentas:

- **Docker**: Necessário para criar e gerenciar os containers do ambiente de desenvolvimento. A instalação pode ser feita através do site oficial [Docker](https://www.docker.com/get-started).
- **Docker Compose**: Utilizado para definir e executar aplicações Docker multi-container. Normalmente, é instalado junto com o Docker Desktop para Windows e Mac. Para Linux, pode ser necessário instalar separadamente. Verifique a documentação oficial [Docker Compose](https://docs.docker.com/compose/install/) para instruções de instalação.

## Subir ambiente de Dev

Para subir o ambiente de desenvolvimento completo da API PayPortal, utilize a combinação dos arquivos de composição do Docker com o seguinte comando:

 ```
 docker-compose up -d
 ```

Este comando irá inicializar todos os containers necessários para o desenvolvimento, incluindo:

- **PHP**: Container do PHP configurado para executar a aplicação na versão 8.3.
- **MySQL**: Container do banco de dados MySQL para armazenamento de dados da aplicação.
- **phpMyAdmin**: Interface web para gerenciar o banco de dados MySQL, facilitando o acesso sem a necessidade de instalar software adicional para conexão.

O serviço da API estará disponível em `http://localhost:9086` por padrão, a menos que a URL da aplicação seja alterada no arquivo `.env`.

### Instalar dependências

Para instalar as dependências do projeto, acesse o container do PHP utilizando o comando:

 ```
 docker-compose exec php bash
 ```

Este comando deve ser executado na raiz do projeto, onde se encontra o arquivo `docker-compose.yml`. Ao acessar o shell (terminal) do container, execute o comando:

 ```
 composer install
 ```

Isso instalará todas as bibliotecas e dependências necessárias para o projeto, conforme definido no arquivo `composer.json`.

### Subir as Migrations

É essencial subir as migrations para configurar o banco de dados. Dentro do container do PHP, execute o comando:

 ```
 php artisan migrate
 ```

Este comando irá criar as tabelas necessárias no banco de dados MySQL, conforme definido nas migrations do projeto.

### Rodar os seeds

É essencial rodar os seeds para popular o banco de dados, incluindo um usuário inicial. Dentro do container do PHP, execute o comando:

 ```
 php artisan db:seed
 ```

Este comando irá popular o banco de dados com os dados necessários para realizar as operações iniciais na API.


### Acessar a Aplicação

Após a instalação das dependências, a inicialização dos containers e a configuração do banco de dados, a API PayPortal estará acessível através do endereço `http://localhost:9086`.

O projeto utiliza Swagger para documentação dos endpoints. Para acessar a documentação da API e testar os endpoints, navegue até `http://localhost:9086/api/documentation#/Pedidos%20de%20Reembolso/store`.

Para acessar o phpMyAdmin e gerenciar o banco de dados MySQL, navegue até o endereço configurado para o phpMyAdmin, que também é definido no Docker Compose, como `http://localhost:8080`.

 ---

Com esses passos, você terá um ambiente de desenvolvimento completo para a API PayPortal, incluindo a aplicação, o banco de dados e uma interface de gerenciamento de banco de dados, prontos para desenvolvimento e testes.

## Arquitetura do Sistema

### Visão Geral da Arquitetura

A API PayPortal é desenvolvida com foco na robustez, segurança e escalabilidade, utilizando tecnologias modernas e práticas de desenvolvimento ágeis. A seguir, apresentamos os componentes-chave da arquitetura do sistema:

#### 1. Linguagem de Programação e Framework
- **PHP 8.3 com Laravel 10**: A escolha do PHP 8.3 se dá por sua performance aprimorada e novos recursos de linguagem. O Laravel 10 é utilizado como framework devido à sua ampla comunidade, recursos avançados para o desenvolvimento web e sua arquitetura elegante para construção de APIs robustas.

#### 2. Banco de Dados
- **MySQL 8**: Optamos pelo MySQL 8 por sua ampla adoção, suporte a transações, segurança aprimorada e capacidade de lidar com grandes volumes de dados, atendendo às necessidades de armazenamento e recuperação de dados da aplicação de forma eficiente.

#### 3. Containerização
- **Docker e Docker Compose**: A utilização do Docker e Docker Compose permite a criação de um ambiente de desenvolvimento isolado e replicável, facilitando a configuração e reduzindo discrepâncias entre ambientes de desenvolvimento, teste e produção.

#### 4. Documentação da API
- **Swagger com L5 Swagger**: Para documentar a API e facilitar o teste dos endpoints, utilizamos o Swagger, integrado ao Laravel através do pacote L5 Swagger. Isso proporciona uma interface interativa para a visualização e teste da API, melhorando a experiência de desenvolvedores e usuários finais.

 ---

Esta arquitetura foi projetada para oferecer uma base sólida para o desenvolvimento e expansão da API PayPortal, garantindo que a aplicação seja segura, escalável e de fácil manutenção.

## Autenticação e Autorização

A autenticação e autorização na API PayPortal são gerenciadas utilizando o Laravel Sanctum, que fornece um sistema de autenticação leve para SPAs (single page applications), aplicativos móveis e APIs token simples. O modelo autenticador é a classe `User` dentro do namespace `App\Models`.

### Configuração Inicial

Antes de utilizar a autenticação, é necessário configurar o ambiente e criar um usuário no banco de dados:

- **Modelo de Usuário**: O modelo `User` é utilizado para gerenciar os usuários da aplicação.
- **Rodar os Seeds**: Para criar um usuário inicial no banco de dados, é necessário rodar os seeds do projeto. Caso ainda não tenha feito isso, consulte a seção [Subir ambiente de Dev](#subir-ambiente-de-dev) e a subsequente subseção [Rodar os Seeds](#rodar-os-seeds) para mais informações.

### Autenticação com Sanctum

A autenticação ocorre através do `LoginController` dentro do namespace `App\Http\Controllers\Api`. O método `login` recebe as credenciais do usuário e, em caso de sucesso, retorna um token de acesso:

```php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            return response()->json(['success' =$success], 200);
        } else {
            return response()->json(['error' ='Unauthorized'], 401);
        }
    }
}
```

Este método verifica as credenciais fornecidas e, se corretas, gera um token único que será utilizado para autenticar as requisições subsequentes do usuário. Em caso de falha na autenticação, retorna um erro `401 Unauthorized`.

### Utilizando o Token de Acesso

Após o login bem-sucedido, o token de acesso deve ser incluído no cabeçalho `Authorization` das requisições subsequentes, no formato `Bearer {token}`.

### Sanctum

Estamos utilizando o Laravel Sanctum para a autenticação, devido à sua simplicidade e eficácia para aplicações que necessitam de autenticação via token, incluindo APIs, SPAs e aplicativos móveis.

## Tratativas de Erros

A API PayPortal utiliza o sistema de validação padrão do Laravel para garantir que todos os dados de entrada sejam validados antes de serem processados pela aplicação. Isso ajuda a prevenir erros de execução e garante que os dados estejam corretos e completos. Abaixo, detalhamos como os erros são tratados e comunicados aos usuários da API.

### Validação de Entrada

Para a validação de entrada, utilizamos o `Validator` do Laravel, que oferece uma forma concisa e poderosa de garantir que os dados recebidos estejam de acordo com as regras definidas. Exemplo de validação:

 ```php
 $validator = Validator::make($request->all(), [
     'empregado_id' => 'required|exists:empregados,id',
     'dataDespesa' => 'required|date|before_or_equal:'.Carbon::now()->toDateString(),
     'descricao' => 'required|string',
     'valor' => 'required|numeric',
 ]);

 if ($validator->fails()) {
     return response()->json($validator->errors(), 400);
 }
 ```

Esta abordagem garante que todos os campos necessários estejam presentes e sejam válidos. Caso a validação falhe, a API retorna uma resposta com status HTTP 400 (Bad Request), juntamente com os detalhes dos erros de validação, facilitando para o cliente da API a identificação e correção dos dados incorretos.

### Tratamento de Erros Específicos

Além da validação de entrada, a API também trata erros específicos relacionados à lógica de negócio. Por exemplo, se uma regra de negócio determina que a data da despesa não pode exceder 30 dias da data atual, a API verifica essa condição e retorna um erro 400 caso a regra seja violada:

 ```php
 if (Carbon::now()->greaterThan($limiteData)) {
     return response()->json(['error' => 'A data da despesa excede o limite de 30 dias para reembolso.'], 400);
 }
 ```

## Gestão de Logs

A gestão de logs é um aspecto crucial no desenvolvimento e manutenção de aplicações robustas e confiáveis. Na API PayPortal, adotamos uma abordagem proativa na geração de logs, utilizando a classe `Illuminate\Support\Facades\Log` do Laravel para registrar informações essenciais, erros e processos de debug. A seguir, detalhamos nossa estratégia de gestão de logs.

### Estratégia de Logging

- **Informações Gerais e de Fluxo**: Utilizamos `Log::info` para registrar eventos significativos que ocorrem durante a execução da aplicação. Isso inclui o início e a conclusão de processos importantes, bem como quaisquer ações críticas realizadas pelo usuário. Exemplo:

 ```php
 Log::info("Iniciando processo de persistência de um pedido de reembolso", $request->all());
 ```

- **Erros e Exceções**: Para erros e validações falhas, utilizamos `Log::error`. Isso nos ajuda a identificar rapidamente problemas que precisam ser corrigidos, além de facilitar a análise de falhas ou comportamentos inesperados da aplicação. Exemplo:

 ```php
 if ($validator->fails()) {
     Log::error("Dados inválidos para persistência do pedido", $request->all());
     return response()->json($validator->errors(), 400);
 }
 ```

- **Debug e Desenvolvimento**: `Log::debug` é amplamente utilizado durante o desenvolvimento e em ambientes de teste para registrar detalhes de execução e dados que ajudam na depuração de problemas. Recomendamos seu uso para detalhar o fluxo de execução e estados intermediários da aplicação. Exemplo:

 ```php
 Log::debug("Buscando empregado de id #{$request->empregado_id}");
 ```

### Níveis de Log

O nível do log é controlado pela variável de ambiente `LOG_LEVEL` definida no arquivo `.env`. Por padrão, o Laravel vem configurado com o nível `DEBUG`, o que significa que todos os níveis de log serão registrados. Em ambientes de produção, pode ser prudente ajustar esse nível para `INFO` ou `ERROR`, a fim de reduzir a quantidade de logs gerados e focar nas informações mais críticas.

### Boas Práticas

- **Logs Relevantes**: Garanta que os logs gerados sejam relevantes e úteis. Logs excessivos podem dificultar a análise e aumentar o custo de armazenamento.
- **Segurança dos Dados**: Tenha cuidado ao logar informações sensíveis. Evite registrar dados pessoais ou informações que possam comprometer a segurança da aplicação.
- **Monitoramento e Análise**: Utilize ferramentas de monitoramento e análise de logs para identificar padrões, antecipar problemas e otimizar a performance da aplicação.

Implementando uma gestão de logs eficaz, a API PayPortal assegura uma operação estável e eficiente, facilitando a identificação e resolução de problemas, além de proporcionar insights valiosos sobre o comportamento da aplicação.

## Código

### Estrutura de Desenvolvimento

Neste projeto, seguimos uma abordagem simplificada, focando em Controllers e Services para a lógica de negócios, sem a utilização de Repositories. Isso mantém o projeto ágil e direto, facilitando a manutenção e expansão.

### Passo a Passo para Adicionar Novas Funcionalidades

#### 1. Definir a Rota

Primeiro, defina a rota para a nova funcionalidade no arquivo `routes/api.php`. Isso especifica o endpoint que será exposto pela API.

 ```php
 // routes/api.php

 use App\Http\Controllers\PedidoReembolsoController;

 Route::post('/pedidos-reembolso', [PedidoReembolsoController::class, 'store']);
 ```

#### 2. Criar o Controller

Crie um novo controller para lidar com as requisições para o endpoint definido. Utilize o comando Artisan para gerar o controller:

 ```bash
 php artisan make:controller PedidoReembolsoController
 ```

No controller, injete a service responsável pela lógica de negócios:

 ```php
 // app/Http/Controllers/PedidoReembolsoController.php

 namespace App\Http\Controllers;

 use Illuminate\Http\Request;
 use App\Services\PedidoReembolsoService;
 use Illuminate\Support\Facades\Log;

 class PedidoReembolsoController extends Controller
 {
     protected PedidoReembolsoService $service;

     public function __construct(PedidoReembolsoService $pedidoReembolsoService)
     {
         $this->service = $pedidoReembolsoService;
     }

     public function store(Request $request)
     {
         // Implementação do método store
     }
 }
 ```

#### 3. Criar a Service

A service contém a lógica de negócios da aplicação. Crie uma nova service manualmente no diretório `app/Services`.

 ```php
 // app/Services/PedidoReembolsoService.php

 namespace App\Services;

 use App\Models\Empregado;
 use App\Models\PedidoReembolso;
 use Illuminate\Support\Facades\Log;
 use Carbon\Carbon;

 class PedidoReembolsoService
 {
     public function create(Empregado $empregado, string $dataDespesa, float $valor, string $descricao): PedidoReembolso
     {
         // Implementação do método create
     }
 }
 ```

### Documentação com Swagger

Não esqueça de documentar cada novo endpoint com annotations do Swagger, garantindo que a documentação da API seja atualizada automaticamente. Isso é crucial para manter a documentação alinhada com o código e facilitar o uso da API por outros desenvolvedores e ferramentas.

 ```php
 /**
  * @OA\Post(
  *     path="/api/pedidos-reembolso",
  *     tags={"Pedidos de Reembolso"},
  *     summary="Cria um novo pedido de reembolso",
  *     description="Cria um novo pedido de reembolso com os dados fornecidos.",
  *     operationId="store",
  *     @OA\RequestBody(
  *         required=true,
  *         description="Dados do pedido de reembolso",
  *         @OA\JsonContent(
  *             required={"empregado_id","dataDespesa","descricao","valor"},
  *             @OA\Property(property="empregado_id", type="integer", format="id", example=1),
  *             @OA\Property(property="dataDespesa", type="string", format="date", example="YYYY-MM-DD"),
  *             @OA\Property(property="descricao", type="string", example="Despesas com viagem"),
  *             @OA\Property(property="valor", type="number", format="float", example=123.45),
  *         ),
  *     ),
  *     @OA\Response(
  *         response=200,
  *         description="Pedido de reembolso criado com sucesso."
  *     ),
  *     @OA\Response(
  *         response=400,
  *         description="Dados inválidos."
  *     )
  * )
  */
 ```

Este guia fornece uma visão clara do padrão de desenvolvimento adotado no projeto, facilitando a criação de novas funcionalidades de forma consistente e documentada.

### Atualizar Documentação da API com Swagger

Após realizar as alterações no código e adicionar as annotations do Swagger aos seus controllers, é essencial atualizar a documentação da API para refletir as mudanças. Para isso, execute o seguinte comando no terminal:

 ```bash
 php artisan l5-swagger:generate
 ```

Este comando irá processar as annotations do Swagger em seus controllers e serviços, gerando a documentação da API atualizada. O resultado será acessível através da URL definida para a documentação do Swagger, geralmente `http://localhost:9086/api/documentation`.

Certifique-se de executar este comando sempre que fizer alterações na API que necessitem de atualização na documentação. Isso garantirá que a documentação esteja sempre sincronizada com o estado atual da sua API, facilitando o teste, a integração e o uso por outros desenvolvedores.

### DBML dos Models

 ```dbml
 Table departamentos {
  id int [pk, increment] // auto-increment
  nome varchar
}

Table empregados {
  id int [pk, increment]
  nome varchar
  cargo varchar
  limiteReembolsoMensal float
  departamento_id int
}

Table pedidos_reembolso {
  id int [pk, increment]
  empregado_id int
  dataDespesa date
  descricao text
  valor float
  status varchar
}

Table recibos {
  id int [pk, increment]
  pedido_id int
  caminhoArquivo varchar
}

// Relacionamentos
Ref: empregados.departamento_id > departamentos.id
Ref: pedidos_reembolso.empregado_id > empregados.id
Ref: recibos.pedido_id > pedidos_reembolso.id
 ```

Este DBML ilustra a estrutura das tabelas e seus relacionamentos, facilitando a compreensão do esquema do banco de dados. Os relacionamentos são definidos utilizando chaves estrangeiras, indicando como as tabelas estão conectadas entre si.

![Diagrama do Banco de Dados](./der_dbdiagram.png
)
