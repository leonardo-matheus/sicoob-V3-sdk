<?php

namespace Logics\SicoobSdk\Payload\CobrancaBancaria;

final class FaixaNossoNumero
{
    private int $numeroCliente;
    private int $codigoModalidade;
    private ?int $quantidade;
    private ?int $numeroContratoCobranca;

    public function __construct(
        int $numeroCliente,
        int $codigoModalidade,
        ?int $quantidade = null,
        ?int $numeroContratoCobranca = null
    ) {
        $this->numeroCliente = $numeroCliente;
        $this->codigoModalidade = $codigoModalidade;
        $this->quantidade = $quantidade;
        $this->numeroContratoCobranca = $numeroContratoCobranca;
    }

    public function getNumeroCliente(): int
    {
        return $this->numeroCliente;
    }

    public function getCodigoModalidade(): int
    {
        return $this->codigoModalidade;
    }

    public function getQuantidade(): ?int
    {
        return $this->quantidade;
    }

    public function getNumeroContratoCobranca(): ?int
    {
        return $this->numeroContratoCobranca;
    }

    public function toArray(): array
    {
        $data = [
            'numeroCliente' => $this->numeroCliente,
            'codigoModalidade' => $this->codigoModalidade,
        ];

        if ($this->quantidade !== null) {
            $data['quantidade'] = $this->quantidade;
        }

        if ($this->numeroContratoCobranca !== null) {
            $data['numeroContratoCobranca'] = $this->numeroContratoCobranca;
        }

        return $data;
    }

    public static function createFromJsonArray($data): self
    {
        return new self(
            $data->numeroCliente,
            $data->codigoModalidade,
            $data->quantidade ?? null,
            $data->numeroContratoCobranca ?? null
        );
    }
}
