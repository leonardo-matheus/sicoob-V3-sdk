<?php

namespace Logics\SicoobSdk\Enum;

enum Modalidade: int
{
    case SIMPLES_COM_REGISTRO = 1;

    public static function get(int $code): Modalidade
    {
        return match ($code) {
            1 => Modalidade::SIMPLES_COM_REGISTRO,
            3 => Modalidade::CAUCIONADA,
            4 => Modalidade::VINCULADA,
            5 => Modalidade::CARNE_PAGAMENTOS,
            6 => Modalidade::INDEXADA,
            8 => Modalidade::COBRANCA_CONTA_CAPITAL,
        };
    }

}