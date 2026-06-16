<?php

namespace App\Enums;

enum Recommandation: string
{
    case Convoquer = 'convoquer';
    case Attente = 'attente';
    case Rejeter = 'rejeter';
}
