<?php

namespace App\Enums;

enum SesiStatus: string
{
    case MENUNGGU = 'menunggu';
    case BERLANGSUNG = 'berlangsung';
    case SELESAI = 'selesai';
    case DIBATALKAN = 'dibatalkan';
}
