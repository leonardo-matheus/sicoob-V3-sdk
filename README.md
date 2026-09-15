# SDK PHP para API Sicoob V3

Biblioteca PHP para integrar aplicações à API de Cobrança Bancária V3 do Sicoob. Encapsula autenticação OAuth2, seleção entre sandbox e produção, requisições HTTP e conversão de payloads e respostas em objetos tipados.

Este projeto não é um SDK oficial do Sicoob.

## Escopo implementado

- autenticação `client_credentials` e renovação de token;
- parâmetros de certificado e chave no autenticador, com configuração de mTLS ainda a revisar;
- inclusão, consulta e segunda via de boletos;
- alterações de vencimento, desconto, abatimento, multa, juros e dados do pagador;
- baixa, protesto, negativação, PIX e rateio de crédito;
- consulta de faixas de nosso número e movimentação de carteira;
- DTOs, enums e payloads específicos do domínio de cobrança.

## Organização

```text
src/
├── Authenticator.php       token OAuth2 e credenciais
├── ClientRequest.php       transporte HTTP com Guzzle
├── CobrancaBancaria.php    operações da API V3
├── Config/                 URLs de sandbox e produção
├── DTO/                    objetos de entrada e resposta
├── Enum/                   códigos do domínio bancário
├── Model/                  validação e mapeamento
├── Payload/                comandos de alteração e baixa
└── Exception/              erros do SDK e da API
```

A classe `CobrancaBancaria` funciona como fachada do módulo. Ela valida parâmetros específicos, monta os endpoints e delega HTTP a `ClientRequest`. DTOs e enums deixam códigos bancários explícitos e concentram a conversão de arrays recebidos da API.

## Instalação

Requisitos: PHP 8.1 ou superior, Composer, Guzzle 7 e credenciais válidas no Portal do Desenvolvedor Sicoob.

```bash
composer install
```

O pacote declara o namespace PSR-4 `Logics\SicoobSdk\` apontando para `src/`.

## Uso básico

```php
<?php

use Logics\SicoobSdk\Authenticator;
use Logics\SicoobSdk\CobrancaBancaria;

$auth = new Authenticator(
    clientId: getenv('SICOOB_CLIENT_ID'),
    certificate: getenv('SICOOB_CERT_PATH'),
    certificateKey: getenv('SICOOB_KEY_PATH'),
    isSandbox: true,
);

$cobranca = new CobrancaBancaria(
    authenticator: $auth,
    numeroContrato: (int) getenv('SICOOB_NUMERO_CONTRATO'),
);

$boleto = $cobranca->consultarBoleto(nossoNumero: '123456');
```

Para criar ou alterar cobranças, use os DTOs e payloads disponíveis em `src/DTO` e `src/Payload/CobrancaBancaria`. A assinatura de cada operação e os links para a documentação correspondente estão em `src/CobrancaBancaria.php`.

## Decisões e implicações

- Guzzle centraliza as chamadas HTTP; a aplicação consumidora continua responsável pelo armazenamento seguro de certificados e segredos.
- o token é mantido em memória no objeto `Authenticator` e renovado conforme o prazo informado pelo servidor; não há cache compartilhado entre processos.
- sandbox e produção usam URLs distintas selecionadas no construtor.
- as operações são síncronas e propagam falhas por exceções.
- o SDK acopla seus modelos ao contrato V3; mudanças do provedor podem exigir atualização de DTOs, enums e payloads.

## Testes

```bash
composer install
./vendor/bin/phpunit src/tests
```

A suíte configura o sandbox e realiza chamadas HTTP reais, incluindo operações que alteram cobranças. Ela depende do serviço externo e não usa mocks para isolar o transporte. Execute com dados e credenciais de teste.

## Limites atuais

- em `Authenticator::generateToken`, `cert` e `ssl_key` são enviados dentro de `form_params`, em vez das opções TLS do Guzzle; o transporte precisa ser revisado antes de depender de autenticação mTLS em produção;
- a cobertura automatizada está concentrada em `src/tests/CobrancaBancariaTest.php`;
- o projeto não implementa persistência, filas, retentativas com backoff ou observabilidade;
- limites de lote, formatos e regras operacionais continuam sujeitos ao contrato vigente da API do Sicoob;
- o uso em produção exige validação própria de certificados, escopos, tratamento de erros e idempotência.

## Referências

- [Portal do Desenvolvedor Sicoob](https://developers.sicoob.com.br/portal/documentacao)
- [Documentação da API de Cobrança Bancária V3](https://documenter.getpostman.com/view/20565799/Uzs6yNhe#intro)

## Licença

MIT. Consulte [LICENSE](LICENSE). O `composer.json` preserva a autoria declarada de Logics Softwares e o namespace `Logics\SicoobSdk\`.
