<?php

namespace App\Enums;

enum JournalLineType: string
{
    case Debit  = 'debit';
    case Credit = 'credit';
}