<?php

namespace App\Exports;

use App\Models\Rombel;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NilaiExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $rombel;

    protected $mapels;

    protected $rows;

    public function __construct(Rombel $rombel, $mapels, $rows)
    {
        $this->rombel = $rombel;
        $this->mapels = $mapels;
        $this->rows = $rows;
    }

    public function view(): View
    {
        return view('exports.nilai', [
            'rombel' => $this->rombel,
            'mapels' => $this->mapels,
            'rows' => $this->rows,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
