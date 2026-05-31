<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaTemplateExport implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'nisn',
            'nama',
            'tanggal_lahir',
            'agama',
        ];
    }

    public function array(): array
    {
        return [
            ['1234567890', 'Budi Santoso',     '2010-05-15', 'Islam'],
            ['0987654321', 'Siti Aminah',      '2010-08-20', 'Kristen'],
            ['1112223334', 'Andi Darmawan',    '2010-01-01', 'Hindu'],
            ['5556667778', 'Dewi Lestari',     '2011-03-10', 'Katolik'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            '2:5' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 35,
            'C' => 18,
            'D' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(30);

                $lastRow = 5;
                $sheet->getStyle("A1:D{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);

                $sheet->getStyle('A2:D2')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EEF2FF');

                $sheet->getStyle('A3:D3')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F0FDF4');

                $sheet->getStyle('A4:D4')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFFBEB');

                $sheet->getStyle('A5:D5')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F5F3FF');

                $spreadsheet = $sheet->getParent();
                $petunjuk = $spreadsheet->createSheet();
                $petunjuk->setTitle('Petunjuk');

                $petunjuk->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE SISWA');
                $petunjuk->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '4F46E5']],
                ]);

                $petunjuk->setCellValue('A3', 'KOLOM WAJIB:');
                $petunjuk->getStyle('A3')->getFont()->setBold(true);

                $instructions = [
                    ['A4',  'nisn',             'WAJIB. Nomor Induk Siswa Nasional (10 digit)'],
                    ['A5',  'nama',             'WAJIB. Nama lengkap siswa'],
                    ['A6',  'tanggal_lahir',    'WAJIB. Format: YYYY-MM-DD (contoh: 2010-05-15)'],
                    ['A7',  'agama',            'OPSIONAL. Islam | Kristen | Katolik | Hindu | Buddha | Konghucu | Lainnya'],
                    ['A9',  'CATATAN PENTING:', ''],
                    ['A10', '1.', 'Jangan hapus baris header (baris 1)'],
                    ['A11', '2.', 'Baris 2-5 adalah contoh, hapus sebelum upload atau biarkan (akan diimport juga)'],
                    ['A12', '3.', 'Format file yang didukung: .xlsx dan .xls'],
                    ['A13', '4.', 'Sistem otomatis membuat akun login untuk setiap siswa (Username = NISN, Password = ddmmyyyy* dari tanggal lahir)'],
                    ['A14', '5.', 'Upload data siswa dilakukan per Rombel (kelas). Pilih rombel tujuan sebelum upload.'],
                ];

                foreach ($instructions as $item) {
                    [$cell, $label, $desc] = $item;
                    $petunjuk->setCellValue($cell, $label);

                    if (! empty($desc)) {
                        $nextCol = 'B'.substr($cell, 1);
                        $petunjuk->setCellValue($nextCol, $desc);
                    }
                }

                $petunjuk->getStyle('A9')->getFont()->setBold(true)->setColor((new Color)->setRGB('DC2626'));

                $petunjuk->getColumnDimension('A')->setWidth(22);
                $petunjuk->getColumnDimension('B')->setWidth(75);
                $petunjuk->getStyle('B4:B14')->getAlignment()->setWrapText(true);

                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getSheet(0)->setTitle('Template Siswa');
            },
        ];
    }
}
