<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoalTemplateExport implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'tipe',
            'pertanyaan',
            'bobot',
            'tingkat_kesulitan',
            'pilihan_a',
            'pilihan_b',
            'pilihan_c',
            'pilihan_d',
            'pilihan_e',
            'kunci_jawaban',
            'bab',
            'pembahasan',
        ];
    }

    public function array(): array
    {
        return [
            // Contoh soal Pilihan Ganda
            [
                'pg',
                'Manakah yang merupakan satuan SI untuk gaya?',
                '2',
                'sedang',
                'Joule',
                'Watt',
                'Newton',
                'Pascal',
                'Ampere',
                'C',
                'Bab 1 - Dinamika',
                'Satuan SI untuk gaya adalah Newton (N), didefinisikan sebagai kg·m/s².',
            ],
            // Contoh soal Benar/Salah
            [
                'benar_salah',
                'Matahari terbit dari arah barat.',
                '1',
                'mudah',
                '',
                '',
                '',
                '',
                '',
                'Salah',
                'Bab 3 - Geografi',
                'Matahari terbit dari arah timur dan terbenam di barat.',
            ],
            // Contoh soal Essay
            [
                'essay',
                'Jelaskan perbedaan antara vektor dan skalar beserta contohnya masing-masing!',
                '5',
                'sulit',
                '',
                '',
                '',
                '',
                '',
                '',
                'Bab 2 - Vektor',
                'Vektor memiliki besar dan arah (contoh: kecepatan, gaya). Skalar hanya memiliki besar (contoh: massa, suhu).',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'], // Indigo
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
            // Data rows
            '2:4' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // tipe
            'B' => 55,  // pertanyaan
            'C' => 8,   // bobot
            'D' => 20,  // tingkat_kesulitan
            'E' => 30,  // pilihan_a
            'F' => 30,  // pilihan_b
            'G' => 30,  // pilihan_c
            'H' => 30,  // pilihan_d
            'I' => 30,  // pilihan_e
            'J' => 18,  // kunci_jawaban
            'K' => 25,  // bab
            'L' => 45,  // pembahasan
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Freeze header row
                $sheet->freezePane('A2');

                // Set header row height
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Set data row heights
                foreach ([2, 3, 4] as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(60);
                }

                // Border untuk semua sel data
                $lastRow = 4;
                $sheet->getStyle("A1:L{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);

                // Highlight baris contoh dengan warna berbeda
                $sheet->getStyle('A2:L2')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EEF2FF'); // Light indigo (PG)

                $sheet->getStyle('A3:L3')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F0FDF4'); // Light green (B/S)

                $sheet->getStyle('A4:L4')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFFBEB'); // Light amber (Essay)

                // Dropdown validation untuk kolom tipe (A)
                $validation = $sheet->getDataValidation('A5:A1000');
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(false);
                $validation->setShowDropDown(false);
                $validation->setFormula1('"pg,benar_salah,essay,menjodohkan"');
                $validation->setShowErrorMessage(true);
                $validation->setErrorTitle('Tipe Tidak Valid');
                $validation->setError('Pilih salah satu: pg, benar_salah, essay, menjodohkan');

                // Dropdown validation untuk kolom tingkat_kesulitan (D)
                $validationD = $sheet->getDataValidation('D5:D1000');
                $validationD->setType(DataValidation::TYPE_LIST);
                $validationD->setFormula1('"mudah,sedang,sulit"');
                $validationD->setShowDropDown(false);

                // Tambah sheet petunjuk
                $spreadsheet = $sheet->getParent();
                $petunjuk = $spreadsheet->createSheet();
                $petunjuk->setTitle('Petunjuk');

                $petunjuk->setCellValue('A1', 'PETUNJUK PENGISIAN FORMAT SOAL');
                $petunjuk->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '4F46E5']],
                ]);

                $petunjuk->setCellValue('A3', 'KOLOM WAJIB:');
                $petunjuk->getStyle('A3')->getFont()->setBold(true);

                $instructions = [
                    ['A4',  'tipe',             'WAJIB. Isi dengan: pg | benar_salah | essay | menjodohkan'],
                    ['A5',  'pertanyaan',        'WAJIB. Teks soal/pertanyaan (bisa panjang, wrapText aktif)'],
                    ['A6',  'bobot',             'WAJIB. Angka poin soal ini. Contoh: 1, 2, 5'],
                    ['A7',  'tingkat_kesulitan', 'WAJIB. Isi dengan: mudah | sedang | sulit (default: sedang)'],
                    ['A9',  'KOLOM PILIHAN GANDA (hanya untuk tipe = pg):', ''],
                    ['A10', 'pilihan_a',         'Teks pilihan jawaban A'],
                    ['A11', 'pilihan_b',         'Teks pilihan jawaban B'],
                    ['A12', 'pilihan_c',         'Teks pilihan jawaban C'],
                    ['A13', 'pilihan_d',         'Teks pilihan jawaban D'],
                    ['A14', 'pilihan_e',         'Teks pilihan jawaban E (boleh kosong)'],
                    ['A15', 'kunci_jawaban',     'Untuk PG: huruf kunci (A/B/C/D/E). Untuk B/S: Benar atau Salah. Untuk Essay: kosongkan.'],
                    ['A17', 'KOLOM OPSIONAL:', ''],
                    ['A18', 'bab',              'Nama bab/topik soal. Contoh: Bab 1 - Dinamika'],
                    ['A19', 'pembahasan',        'Penjelasan jawaban benar (ditampilkan setelah ujian selesai)'],
                    ['A21', 'CATATAN PENTING:', ''],
                    ['A22', '1.',                'Jangan hapus baris header (baris 1)'],
                    ['A23', '2.',                'Baris 2-4 adalah contoh, hapus sebelum upload atau biarkan (akan diimport juga)'],
                    ['A24', '3.',                'Untuk tipe menjodohkan: input manual satu per satu via form, belum tersedia via Excel'],
                    ['A25', '4.',                'Format file yang didukung: .xlsx dan .xls'],
                    ['A26', '5.',                'Maksimum 500 soal per upload'],
                ];

                foreach ($instructions as $item) {
                    [$cell, $label, $desc] = $item;
                    $petunjuk->setCellValue($cell, $label);

                    if (! empty($desc)) {
                        $nextCol = 'B'.substr($cell, 1);
                        $petunjuk->setCellValue($nextCol, $desc);
                    }
                }

                $petunjuk->getStyle('A9')->getFont()->setBold(true)->setColor((new Color)->setRGB('2563EB'));
                $petunjuk->getStyle('A17')->getFont()->setBold(true)->setColor((new Color)->setRGB('16A34A'));
                $petunjuk->getStyle('A21')->getFont()->setBold(true)->setColor((new Color)->setRGB('DC2626'));

                $petunjuk->getColumnDimension('A')->setWidth(22);
                $petunjuk->getColumnDimension('B')->setWidth(75);
                $petunjuk->getStyle('B4:B26')->getAlignment()->setWrapText(true);

                // Set sheet aktif ke sheet pertama (Template)
                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getSheet(0)->setTitle('Template Soal');
            },
        ];
    }
}
