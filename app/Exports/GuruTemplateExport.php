<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class GuruTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function headings(): array
    {
        return [
            'nip',
            'nama',
            'jabatan',
            'tanggal_lahir',
            'email',
        ];
    }

    public function array(): array
    {
        return [
            ['198503152010011002', 'Drs. H. Ahmad Fauzi', 'Kepala Sekolah', '1985-03-15', 'ahmad.fauzi@sekolah.sch.id'],
            ['199008202015022003', 'Siti Rahmawati, S.Pd', 'Guru', '1990-08-20', 'siti.rahmawati@sekolah.sch.id'],
            ['198812052012011005', 'Budi Hermawan, M.Kom', 'Kepala Laboratorium', '1988-12-05', 'budi.hermawan@sekolah.sch.id'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
            '2:4' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 24,
            'B' => 35,
            'C' => 25,
            'D' => 18,
            'E' => 35,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(30);

                $lastRow = 4;
                $sheet->getStyle("A1:E{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);

                $sheet->getStyle('A2:E2')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EEF2FF');

                $sheet->getStyle('A3:E3')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F0FDF4');

                $sheet->getStyle('A4:E4')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFFBEB');

                $spreadsheet = $sheet->getParent();
                $petunjuk = $spreadsheet->createSheet();
                $petunjuk->setTitle('Petunjuk');

                $petunjuk->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE GURU');
                $petunjuk->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '4F46E5']],
                ]);

                $petunjuk->setCellValue('A3', 'KOLOM WAJIB:');
                $petunjuk->getStyle('A3')->getFont()->setBold(true);

                $instructions = [
                    ['A4',  'nip',              'WAJIB. Nomor Induk Pegawai atau NIK (30 karakter)'],
                    ['A5',  'nama',             'WAJIB. Nama lengkap beserta gelar akademik'],
                    ['A6',  'jabatan',          'OPSIONAL. Default "Guru". Pilihan: Kepala Sekolah, Wakil Kepala Sekolah, Bendahara, Bimbingan Konseling, Guru, dll.'],
                    ['A7',  'tanggal_lahir',    'OPSIONAL. Format: YYYY-MM-DD (contoh: 1985-03-15)'],
                    ['A8',  'email',            'WAJIB. Alamat email aktif untuk pembuatan akun login portal'],
                    ['A10', 'CATATAN PENTING:', ''],
                    ['A11', '1.', 'Jangan menghapus baris header (baris 1) di sheet utama.'],
                    ['A12', '2.', 'Baris 2-4 adalah contoh data, hapus sebelum melakukan upload.'],
                    ['A13', '3.', 'Format file yang didukung adalah .xlsx, .xls atau .csv'],
                    ['A14', '4.', 'Akun login guru akan otomatis dibuat dengan role "Guru". Password default adalah "guru123" atau disesuaikan.'],
                ];

                foreach ($instructions as $item) {
                    [$cell, $label, $desc] = $item;
                    $petunjuk->setCellValue($cell, $label);

                    if (!empty($desc)) {
                        $nextCol = 'B' . substr($cell, 1);
                        $petunjuk->setCellValue($nextCol, $desc);
                    }
                }

                $petunjuk->getStyle('A10')->getFont()->setBold(true)->setColor((new Color())->setRGB('DC2626'));

                $petunjuk->getColumnDimension('A')->setWidth(22);
                $petunjuk->getColumnDimension('B')->setWidth(75);
                $petunjuk->getStyle('B4:B14')->getAlignment()->setWrapText(true);

                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getSheet(0)->setTitle('Template Guru');
            },
        ];
    }
}
