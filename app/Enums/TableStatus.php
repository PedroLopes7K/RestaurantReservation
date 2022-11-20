<?php

namespace App\Enums;

enum TableStatus: string
{
  case Pendente = 'pendente';
  case Disponivel = 'disponivel';
  case Indisponivel = 'indisponivel';
}
