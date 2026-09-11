# Sicoob PHP SDK

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

SDK PHP para integração com a **API de Cobrança Bancária V3** do Sicoob. Autenticação via OAuth2 com mTLS (certificado digital ICP Brasil).

## Requisitos

- **PHP** >= 8.1
- **Extensões:** `curl`, `openssl`, `mbstring`
- **Certificado digital** ICP Brasil (`.pem` + `.key`) para autenticação mTLS
- **Credenciais OAuth2** (`client_id`) obtidas no [Portal Developers Sicoob](https://developers.sicoob.com.br/)
- **Guzzle HTTP** >= 7.0 (instalado via Composer)

## Instalação

```bash
composer require leonardo-matheus/sicoob-v3-sdk
```

## Configuração

```php
use Logics\SicoobSdk\Authenticator;
use Logics\SicoobSdk\CobrancaBancaria;

$authenticator = new Authenticator(
    clientId: 'SEU_CLIENT_ID',
    certificate: '/caminho/para/certificado.pem',
    certificateKey: '/caminho/para/chave.key',
    isSandbox: false
);

$cobranca = new CobrancaBancaria(
    authenticator: $authenticator,
    numeroContrato: 25546454
);
```

### Sandbox

```php
$authenticator = new Authenticator(
    clientId: 'SEU_CLIENT_ID_SANDBOX',
    certificate: '/caminho/certificado.pem',
    certificateKey: '/caminho/chave.key',
    isSandbox: true
);
```

| Ambiente | Base URL |
|----------|----------|
| **Produção** | `https://api.sicoob.com.br/cobranca-bancaria/v3` |
| **Sandbox** | `https://sandbox.sicoob.com.br/sicoob/sandbox/cobranca-bancaria/v3` |

## Autenticação

O SDK usa **OAuth2 Client Credentials** com mTLS. O certificado ICP Brasil é enviado no header `x-certificate` da requisição de token. O token expira em 300s e é renovado automaticamente.

### Escopos OAuth2

| Escopo | Finalidade |
|--------|-----------|
| `cobranca_boletos_consultar` | Consulta de boletos |
| `cobranca_boletos_incluir` | Inclusão de boletos |
| `cobranca_boletos_pagador` | Operações com pagadores |
| `cobranca_boletos_segunda_via` | Emissão de segunda via |
| `cobranca_boletos_descontos` | Alteração de descontos |
| `cobranca_boletos_abatimentos` | Alteração de abatimentos |
| `cobranca_boletos_valor_nominal` | Alteração de valor nominal |
| `cobranca_boletos_seu_numero` | Alteração de seu número |
| `cobranca_boletos_especie_documento` | Alteração de espécie |
| `cobranca_boletos_baixa` | Baixa de boletos |
| `cobranca_boletos_rateio_credito` | Rateio de crédito |
| `cobranca_boletos_negativacoes_incluir` | Incluir negativação |
| `cobranca_boletos_negativacoes_alterar` | Alterar negativação |
| `cobranca_boletos_negativacoes_baixar` | Baixar negativação |
| `cobranca_boletos_protestos_incluir` | Incluir protesto |
| `cobranca_boletos_protestos_alterar` | Alterar protesto |
| `cobranca_boletos_protestos_desistir` | Desistir de protesto |
| `cobranca_boletos_solicitacao_movimentacao_incluir` | Solicitar movimentação |
| `cobranca_boletos_solicitacao_movimentacao_consultar` | Consultar movimentação |
| `cobranca_boletos_solicitacao_movimentacao_download` | Download movimentação |
| `cobranca_boletos_prorrogacoes_data_vencimento` | Prorrogar vencimento |
| `cobranca_boletos_prorrogacoes_data_limite_pagamento` | Prorrogar limite pagamento |
| `cobranca_boletos_encargos_multas` | Alterar multas |
| `cobranca_boletos_encargos_juros_mora` | Alterar juros mora |
| `cobranca_boletos_pix` | Operações PIX |

## Operações

### Incluir boleto

```php
use Logics\SicoobSdk\DTO\Boleto;
use Logics\SicoobSdk\DTO\Pagador;
use Logics\SicoobSdk\Enum\Modalidade;
use Logics\SicoobSdk\Enum\EspecieDocumento;
use Logics\SicoobSdk\Enum\IdentificacaoEmissaoBoleto;
use Logics\SicoobSdk\Enum\IdentificacaoDistribuicaoBoleto;
use Logics\SicoobSdk\Enum\CodigoCadastrarPIX;
use Logics\SicoobSdk\Enum\TipoMulta;

$pagador = (new Pagador())
    ->setNumeroCpfCnpj('98765432185')
    ->setNome('João da Silva')
    ->setEndereco('Rua 87 Quadra 1 Lote 1')
    ->setBairro('Santa Rosa')
    ->setCidade('Luziânia')
    ->setCep('72320000')
    ->setUf('DF')
    ->setEmail(['joao@email.com']);

$boleto = (new Boleto())
    ->setModalidade(Modalidade::SIMPLES_COM_REGISTRO)
    ->setNumeroContaCorrente(0)
    ->setEspecieDocumento(EspecieDocumento::FATURA)
    ->setDataEmissao('2024-01-15T00:00:00-03:00')
    ->setSeuNumero('123457')
    ->setIdentificacaoEmissaoBoleto(IdentificacaoEmissaoBoleto::BANCO_EMITE)
    ->setIdentificacaoDistribuicaoBoleto(IdentificacaoDistribuicaoBoleto::BANCO_DISTRIBUI)
    ->setValor(150.00)
    ->setDataVencimento('2024-02-15T00:00:00-03:00')
    ->setTipoMulta(TipoMulta::PERCENTUAL)
    ->setDataMulta('2024-02-16')
    ->setValorMulta(2.0)
    ->setPagador($pagador)
    ->setCodigoCadastrarPIX(CodigoCadastrarPIX::COM_PIX);

$boletoRegistrado = $cobranca->incluirBoleto($boleto);

echo $boletoRegistrado->getNossoNumero();
echo $boletoRegistrado->getCodigoBarras();
echo $boletoRegistrado->getLinhaDigitavel();
echo $boletoRegistrado->getQrCode();
```

> **Nota:** O endpoint retorna HTTP 207 (Multi-Status). O campo `mensagensInstrucao` na response é um array de strings.

### Consultar boleto

```php
// Por nosso número
$boleto = $cobranca->consultarBoleto(nossoNumero: '2588658');

// Por linha digitável
$boleto = $cobranca->consultarBoleto(linhaDigital: '1234567890123456789012345678901234567890123');

// Por código de barras
$boleto = $cobranca->consultarBoleto(codigoBarras: '75697861800000100001306601007613900022830001');
```

### Listar boletos por pagador

```php
use Logics\SicoobSdk\Enum\CodigoSituacaoParam;

$boletos = $cobranca->listarBoletosPorPagador(
    codigoSituacao: CodigoSituacaoParam::EM_ABERTO,
    dataInicio: '2024-01-01',
    dataFim: '2024-01-31',
    numeroCpfCnpj: '98765432185'
);
```

### Emitir segunda via

```php
$segundaVia = $cobranca->emitirSegundaViaBoleto(
    gerarPdf: true,
    nossoNumero: '2588658'
);

echo $segundaVia->getPdfBoleto(); // Base64 do PDF
echo $segundaVia->getQrCode();
```

### Prorrogar data de vencimento

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\ProrrogarDataVencimento;

$payload = new ProrrogarDataVencimento(
    nossoNumero: 2588658,
    dataVencimento: '2024-03-15'
);

$resultado = $cobranca->prorrogarDataVencimentoBoletos([$payload]);

foreach ($resultado as $item) {
    echo $item['result']->getCodigo();   // '0' = sucesso
    echo $item['result']->getMensagem();
}
```

### Prorrogar data limite de pagamento

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\ProrrogarDataLimitePagamento;

$payload = new ProrrogarDataLimitePagamento(
    nossoNumero: 2588658,
    dataLimitePagamento: '2024-03-20'
);

$resultado = $cobranca->prorrogarDataLimitePagamentoBoletos([$payload]);
```

### Alterar valor de multa

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\AlterarValorMulta;
use Logics\SicoobSdk\Enum\TipoMulta;

$payload = new AlterarValorMulta(
    nossoNumero: 2588658,
    tipoMulta: TipoMulta::PERCENTUAL,
    dataMulta: '2024-02-16',
    valorMulta: 2.0
);

$resultado = $cobranca->alterarValorMultaBoletos([$payload]);
```

### Alterar valor de juros de mora

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\AlterarValorJurosMora;
use Logics\SicoobSdk\Enum\TipoJurosMora;

$payload = new AlterarValorJurosMora(
    nossoNumero: 2588658,
    tipoJurosMora: TipoJurosMora::VALOR_FIXO,
    dataJurosMora: '2024-02-16',
    valorJurosMora: 1.0
);

$resultado = $cobranca->alterarValorJurosMoraBoletos([$payload]);
```

### Alterar valor de abatimento

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\AlterarValorAbatimento;

$payload = new AlterarValorAbatimento(
    nossoNumero: 2588658,
    dataAbatimento: '2024-02-10',
    valorAbatimento: 10.0
);

$resultado = $cobranca->alterarValorAbatimentoBoletos([$payload]);
```

### Alterar informações de desconto

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\AlterarInformacoesDesconto;
use Logics\SicoobSdk\Enum\TipoDesconto;

$payload = new AlterarInformacoesDesconto(
    nossoNumero: 2588658,
    tipoDesconto: TipoDesconto::VALOR_FIXO,
    dataDesconto: '2024-02-10',
    valorDesconto: 5.0
);

$resultado = $cobranca->alterarInformacoesDescontoBoletos([$payload]);
```

### Alterar valor nominal de cartão de crédito

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\AlterarValorNominalBoletoCartaoCredito;

$payload = new AlterarValorNominalBoletoCartaoCredito(
    nossoNumero: 2588658,
    valorNominal: 200.00
);

$resultado = $cobranca->alterarValorNominalBoletosCartaoCredito([$payload]);
```

### Alterar seu número / ID da empresa

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\AlterarSeuNumeroIdBoletoEmpresa;

$payload = new AlterarSeuNumeroIdBoletoEmpresa(
    nossoNumero: 2588658,
    seuNumero: 'NOVO_NUMERO',
    idBoletoEmpresa: 'ID_EMPRESA'
);

$resultado = $cobranca->alterarSeuNumeroOuIdBoletoEmpresaBoletos([$payload]);
```

### Alterar espécie de documento

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\AlterarEspecieDocumento;
use Logics\SicoobSdk\Enum\EspecieDocumento;

$payload = new AlterarEspecieDocumento(
    nossoNumero: 2588658,
    especieDocumento: EspecieDocumento::DUPLICATA
);

$resultado = $cobranca->alterarEspecieDocumentoBoletos([$payload]);
```

### Alterar informações do pagador

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\AlterarInformacoesPagador;

$payload = new AlterarInformacoesPagador(
    nossoNumero: 2588658,
    nome: 'Novo Nome',
    endereco: 'Novo Endereço',
    bairro: 'Novo Bairro',
    cidade: 'Nova Cidade',
    cep: '72320000',
    uf: 'DF',
    email: ['novo@email.com']
);

$resultado = $cobranca->alterarInformacoesPagadorBoletos([$payload]);
```

### Comandar baixa (em lote)

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\ComandarBaixa;

$boletosBaixa = [
    new ComandarBaixa(nossoNumero: 2588658),
    new ComandarBaixa(nossoNumero: 2588659),
];

$resultado = $cobranca->comandarBaixaBoletos($boletosBaixa);
// Máximo 10 boletos por requisição
```

### Comandar baixa (individual)

```php
$resultado = $cobranca->comandarBaixaBoleto(
    nossoNumero: 2588658,
    numeroCpfCnpj: '98765432185'
);
```

### Comandar rateio de crédito

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\ComandarRateioCredito;
use Logics\SicoobSdk\Enum\CodigoTipoValorRateio;
use Logics\SicoobSdk\Enum\CodigoTipoCalculoRateio;
use Logics\SicoobSdk\Enum\CodigoFinalidadeTED;
use Logics\SicoobSdk\Enum\CodigoTipoContaDestinoTED;

$rateio = new ComandarRateioCredito(
    nossoNumero: 2588658,
    numeroBanco: 1,
    numeroAgencia: 1234,
    numeroContaCorrente: 56789,
    contaPrincipal: true,
    codigoTipoValorRateio: CodigoTipoValorRateio::VALOR_CORRIGIDO,
    valorRateio: 150.00,
    codigoTipoCalculoRateio: CodigoTipoCalculoRateio::VALOR_FAIXA,
    numeroCpfCnpjTitular: '98765432185',
    nomeTitular: 'João da Silva',
    codigoFinalidadeTed: CodigoFinalidadeTED::OUTROS,
    codigoTipoContaDestinoTED: CodigoTipoContaDestinoTED::CONTA_CORRENTE,
    quantidadeDiasFloat: 0
);

$resultado = $cobranca->comandarRateioCreditoBoletos([$rateio]);
// quantidadeDiasFloat e dataFloatCredito são mutuamente exclusivos
```

### Alterar para PIX

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\AlterarParaUtilizarPIX;

$pix = new AlterarParaUtilizarPIX(nossoNumero: 2588658);

$resultado = $cobranca->alterarBoletoParaUtilizarPix([$pix]);
```

### Negativar pagadores

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\NegativarPagadores;

$negativacao = $cobranca->negativarPagadores([
    new NegativarPagadores(nossoNumero: 2588658)
]);
```

### Baixar negativação

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\BaixarNegativacao;

$baixa = $cobranca->baixarNegativacaoPagadores([
    new BaixarNegativacao(nossoNumero: 2588658)
]);
```

### Cancelar apontamento de negativação

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\CancelarApontamentoNegativacao;

$cancelamento = $cobranca->cancelarApontamentoNegativacaoPagadores([
    new CancelarApontamentoNegativacao(nossoNumero: 2588658)
]);
```

### Protestar boleto

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\ProtestarBoleto;
use Logics\SicoobSdk\Enum\CodigoProtesto;

$protesto = $cobranca->protestarBoletos([
    new ProtestarBoleto(nossoNumero: 2588658, codigoProtesto: CodigoProtesto::PROTESTAR)
]);
```

### Cancelar apontamento de protesto

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\CancelarApontamentoProtesto;

$cancelamento = $cobranca->cancelarApontamentoProtestoBoletos([
    new CancelarApontamentoProtesto(nossoNumero: 2588658)
]);
```

### Desistir do protesto

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\DesistirProtesto;

$desistencia = $cobranca->desistitProtestoBoletos([
    new DesistirProtesto(nossoNumero: 2588658)
]);
```

### Consultar faixas de nosso número disponíveis

```php
use Logics\SicoobSdk\Payload\CobrancaBancaria\FaixaNossoNumero;

$faixas = $cobranca->consultarFaixasNossoNumeroDisponiveis(
    new FaixaNossoNumero(
        numeroCliente: 25546454,
        codigoModalidade: 1,
        quantidade: 10
    )
);

foreach ($faixas as $faixa) {
    echo "Faixa: {$faixa->getNumeroInicial()} a {$faixa->getNumeroFinal()}";
}
```

### Movimentação da carteira

```php
use Logics\SicoobSdk\Enum\TipoMovimentacao;

// Solicitar movimentação (período máximo: 2 dias)
$solicitacao = $cobranca->solicitarMovimentacaoCarteiraBeneficiario(
    tipoMovimentacao: TipoMovimentacao::ENTRADA,
    dataInicial: '2024-01-01',
    dataFinal: '2024-01-03'
);
$codigoSolicitacao = $solicitacao['resultado']->codigoSolicitacao;

// Consultar situação da solicitação
$situacao = $cobranca->consultarSituacaoSolicitacaoMovimentacao($codigoSolicitacao);

// Download do arquivo de movimentação
$arquivo = $cobranca->downloadArquivoMovimentacao(
    codigoSolicitacao: $codigoSolicitacao,
    idArquivo: 30025254
);
// $arquivo['resultado']->arquivo contém o ZIP em base64
```

## Enums

### Modalidade

| Valor | Constante |
|-------|-----------|
| `1` | `SIMPLES_COM_REGISTRO` |
| `3` | `CAUCIONADA` |
| `4` | `VINCULADA` |
| `5` | `CARNE_PAGAMENTOS` |
| `6` | `INDEXADA` |
| `8` | `COBRANCA_CONTA_CAPITAL` |

### Espécie de Documento

| Valor | Constante |
|-------|-----------|
| `1` | `DUPLICATA_MERCANTIL` |
| `2` | `NOTA_PROMISSORIA` |
| `3` | `NOTA_CREDITO` |
| `4` | `RECIBO` |
| `5` | `LETRA_CAMBIO` |
| `10` | `DUPLICATA_SERVICO` |

### Tipo de Multa

| Valor | Constante |
|-------|-----------|
| `1` | `VALOR_FIXO` |
| `2` | `PERCENTUAL` |

### Tipo de Juros Mora

| Valor | Constante |
|-------|-----------|
| `1` | `VALOR_FIXO` |
| `2` | `PERCENTUAL` |

### Tipo de Desconto

| Valor | Constante |
|-------|-----------|
| `1` | `VALOR_FIXO` |
| `2` | `PERCENTUAL` |

### Tipo de Movimentação

| Valor | Constante |
|-------|-----------|
| `1` | `ENTRADA` |
| `2` | `SAIDA` |

### Código de Protesto

| Valor | Constante |
|-------|-----------|
| `1` | `PROTESTAR` |
| `2` | `CANCELAR_PROTESTO` |
| `3` | `SUSTAR_PROTESTO` |

### Código de Negativação

| Valor | Constante |
|-------|-----------|
| `1` | `INCLUIR` |
| `2` | `ALTERAR` |
| `3` | `BAIXAR` |

### Cadastrar PIX

| Valor | Constante |
|-------|-----------|
| `1` | `COM_PIX` |
| `2` | `SEM_PIX` |

## Tratamento de Erros

```php
use Logics\SicoobSdk\Exception\SicoobSdkException;
use Logics\SicoobSdk\Exception\AuthenticationException;
use Logics\SicoobSdk\Exception\ApiException;

try {
    $boleto = $cobranca->incluirBoleto($boleto);
} catch (AuthenticationException $e) {
    // Falha na autenticação (certificado inválido, client_id incorreto)
    echo "Erro de autenticação: " . $e->getMessage();
} catch (ApiException $e) {
    // Erro retornado pela API Sicoob
    echo "API error [{$e->getStatusCode()}]: " . $e->getMessage();
} catch (SicoobSdkException $e) {
    // Outros erros do SDK
    echo "SDK error: " . $e->getMessage();
}
```

### Hierarquia de exceções

```
Exception
└── SicoobSdkException
    ├── AuthenticationException  — erros de autenticação OAuth2/mTLS
    └── ApiException             — erros retornados pela API (com statusCode)
```

`ApiException` expõe `getStatusCode()` para inspecionar o código HTTP retornado pela API.

## Limites da API

| Operação | Limite |
|----------|--------|
| Alterações em lote | 10 boletos por requisição |
| Negativação/Protesto | 10 boletos por requisição |
| Período de movimentação | Máximo 2 dias entre `dataInicial` e `dataFinal` |

## Estrutura do projeto

```
src/
├── Authenticator.php                 # Autenticação OAuth2 mTLS
├── ClientRequest.php                 # Cliente HTTP Guzzle
├── CobrancaBancaria.php              # Módulo principal com todos os endpoints
├── Config/
│   ├── Authentication.php            # Constantes de autenticação
│   ├── ProductionData.php            # URLs e configs de produção
│   └── SandboxData.php               # URLs e configs de sandbox
├── DTO/
│   ├── Boleto.php                    # DTO de boleto
│   ├── Pagador.php                   # DTO de pagador
│   ├── BeneficiarioFinal.php         # DTO de beneficiário final
│   ├── MensagemInstrucao.php         # DTO de mensagens de instrução
│   ├── RateioCredito.php             # DTO de rateio de crédito
│   └── ResultRequest.php             # DTO genérico de resultado
├── Enum/                             # Enums tipados (Modalidade, EspecieDocumento, etc.)
├── Exception/
│   └── SicoobSdkException.php        # Hierarquia de exceções
├── Model/
│   └── CobrancaBancariaModel.php     # Model de parse de responses
└── Payload/
    └── CobrancaBancaria/             # Payloads para cada operação
```

## Executando testes

```bash
composer install
./vendor/bin/phpunit
```

## Licença

MIT — veja [LICENSE](LICENSE) para detalhes.

## Referências

- [Portal Desenvolvedor Sicoob](https://developers.sicoob.com.br/portal/documentacao)
- [Documentação API Cobrança Bancária V3](https://documenter.getpostman.com/view/20565799/Uzs6yNhe#intro)
