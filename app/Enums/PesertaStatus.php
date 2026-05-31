<?php

namespace App\Enums;

enum PesertaStatus: string
{
    case BELUM_MULAI = 'belum_mulai';
    case MENGERJAKAN = 'mengerjakan';
    case SELESAI = 'selesai';
    case DIDISKUALIFIKASI = 'didiskualifikasi';
}
