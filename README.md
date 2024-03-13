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

1. Clone este repositório em sua máquina local e abra a pasta do projeto.
```
git clone https://github.com/emersonsl/api-tickets.git
```
```
cd api-tickets
```
2. Crie o arquivo de configuração baseado no exemplo do repositório
```
cp .env.example .env
```
3. Configure as variáveis de ambiente necessárias no arquivo .env
 - credenciais do servidor de e-mail (os testes foram realizados utilizando o Amazon Simple Email Service)
 - credenciais do servidor de arquivos (os testes foram realizados com o Amazon Simple Storage Service - S3)
 - credenciais do banco de dados (as configurações do arquivo de exemplo já atenderão para o uso em ambiente local)
 - credenciais da api de pagamentos
5. Na pasta raiz do projeto, que contém o arquivo `docker-compose.yml`, execute os contêineres Docker com o comando abaixo.
```
docker compose up --build
``` 
6. Acesse a coleção do postman e comece a explorar os recursos disponíveis.

[COLECTION POSTMAN] https://www.postman.com/material-operator-40567745/workspace/shared/request/20894322-88030241-6419-4ab2-8f45-3fccdd2a9524

7. Usuários padrões que podem ser usados para testes
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

## Testes automatizados

Após iniciar o docker, execute o comando abaixo

```
docker exec -it api-tickets php artisan test --coverage-html tests/Reports/coverage/
``` 

O relatório de testes ficará disponível na pasta 

```
tests/Reports/coverage/
```

# Aplicação em nuvem

Para acessar a aplicação em nuvem utilize o endereço abaixo:

[Api Tickets] http://52.200.145.207/

## Autor

Este projeto foi desenvolvido por Emerson S. Lima.