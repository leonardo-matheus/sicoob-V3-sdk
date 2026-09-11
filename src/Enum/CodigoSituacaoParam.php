<?php

namespace Logics\SicoobSdk\Enum;

enum CodigoSituacaoParam: int
{
    case EM_ABERTO = 1;
    case BAIXADO = 2;
    case LIQUIDADO = 3;

    public static function get(int $code): self
    {
        return match ($code) {
            1 => self::EM_ABERTO,
            2 => self::BAIXADO,
            3 => self::LIQUIDADO,
        };
    }
}
