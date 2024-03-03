# Sistema de Gestão de Eventos

Este é um sistema de gestão de eventos desenvolvido para atender às necessidades do mercado em constante crescimento de organização e venda de ingressos para eventos. O sistema oferece uma plataforma abrangente que permite aos produtores de eventos criar, gerenciar e vender ingressos online, integrando-se ao Pix Web da Paggue para processamento de pagamentos e notificações automáticas.

## Recursos Principais

- **Signup**: Os usuários podem se cadastrar fornecendo informações como telefone, CPF/CNPJ, senha e nome.
- **Autenticação e Permissões**: O sistema oferece autenticação segura e gerencia permissões de acesso, garantindo que os usuários só possam acessar recursos específicos de acordo com seu perfil de acesso.
- **CRUDs**: Funcionalidades para criar, visualizar, atualizar e excluir produtores, eventos, setores, lotes, ingressos e cupons de desconto.
<!-- - **Armazenamento de Banners em Nuvem**: Os eventos podem ser associados a banners, que são armazenados em nuvem (AWS S3 na produção) para garantir disponibilidade e escalabilidade. -->
- **Venda de Ingressos Online**: Integração com o Pix Web da Paggue para facilitar o pagamento e processamento de pedidos de ingressos online.
<!-- - **Notificações Automáticas**: Após o processamento bem-sucedido do pagamento, são enviadas notificações automáticas por e-mail ou SMS para o administrador e o cliente. -->
- **Integração com a Paggue**: Utilização da API da Paggue para geração de Pix e recebimento de notificações de pagamento.
- **Ambiente de Desenvolvimento Facilitado**: Configuração do ambiente de desenvolvimento com Docker e Xdebug para facilitar a depuração e o desenvolvimento de novos recursos.

## Tecnologias Utilizadas

- **PHP/Laravel 10**: Framework PHP moderno e poderoso para o desenvolvimento rápido e eficiente de aplicativos web.
- **Docker**: Utilizado para facilitar a configuração e implantação do ambiente de desenvolvimento e produção.
<!-- - **AWS**: Utilização dos serviços da Amazon Web Services, incluindo S3, EC2, ELB e Lambda para armazenamento, hospedagem, balanceamento de carga e processamento de eventos assíncronos. -->
- **PostgreSQL**: Banco de dados relacional utilizado para armazenar dados relacionados a eventos, ingressos e usuários.
- **Postman**: Coleção completa com exemplos salvos para facilitar a integração e teste da API.

## Pré-requisitos

- Docker instalado na máquina local para configuração do ambiente de desenvolvimento.
<!-- - Conta na AWS para implantação do sistema em produção. -->

## Como Configurar e Executar

1. Clone este repositório em sua máquina local.
2. Configure as variáveis de ambiente necessárias, incluindo credenciais de banco de dados e da api de pagamentos.
3. Na pasta raiz do projeto, que contém o arquivo `docker-compose.yml`, execute os contêineres Docker com o comando abaixo.
```
docker compose up --build
``` 
4. Acesse a coleção do postman e comece a explorar os recursos disponíveis.

- https://www.postman.com/material-operator-40567745/workspace/shared/request/20894322-88030241-6419-4ab2-8f45-3fccdd2a9524
5. Usuário padrões que podem ser usados para testes
- Administrador
```
email: admin@example.com
password: admin123
```
- Produtor
```
email: promoter@example.com
password: promoter123
```
- Cliente
```
email: customer@example.com
password: customer123
```
## Autor

Este projeto foi desenvolvido por Emerson S. Lima.