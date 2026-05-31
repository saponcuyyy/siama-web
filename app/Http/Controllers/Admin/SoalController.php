<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SoalTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\SoalImport;
use App\Models\BankSoal;
use App\Models\PasanganMenjodohkan;
use App\Models\PilihanJawaban;
use App\Models\Soal;
use App\Services\WordImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class SoalController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_soal_id' => 'required|exists:bank_soal,id',
            'tipe' => 'required|in:pg,benar_salah,essay,menjodohkan',
            'pertanyaan' => 'required|string',
            'bobot' => 'required|numeric|min:1',
            'kunci_jawaban' => 'nullable|string',
            // Untuk PG
            'pilihan' => 'nullable|array',
            'pilihan.*.kode' => 'required_with:pilihan|string|max:5',
            'pilihan.*.teks' => 'required_with:pilihan|string',
            'pilihan.*.is_kunci' => 'boolean',
            // Untuk Menjodohkan
            'pasangan' => 'nullable|array',
            'pasangan.*.kiri' => 'required_with:pasangan|string',
            'pasangan.*.kanan' => 'required_with:pasangan|string',
        ]);

        DB::transaction(function () use ($validated) {
            $urutanSoal = Soal::where('bank_soal_id', $validated['bank_soal_id'])->max('urutan') ?? 0;

            $soal = Soal::create([
                'bank_soal_id' => $validated['bank_soal_id'],
                'tipe' => $validated['tipe'],
                'pertanyaan' => $validated['pertanyaan'],
                'bobot' => $validated['bobot'],
                'kunci_jawaban' => $validated['kunci_jawaban'],
                'urutan' => $urutanSoal + 1,
            ]);

            if ($validated['tipe'] === 'pg' && ! empty($validated['pilihan'])) {
                $urutanPil = 1;
                foreach ($validated['pilihan'] as $pil) {
                    PilihanJawaban::create([
                        'soal_id' => $soal->id,
                        'kode' => $pil['kode'],
                        'teks' => $pil['teks'],
                        'urutan' => $urutanPil++,
                    ]);

                    if (! empty($pil['is_kunci'])) {
                        $soal->update(['kunci_jawaban' => $pil['kode']]);
                    }
                }
            }

            if ($validated['tipe'] === 'menjodohkan' && ! empty($validated['pasangan'])) {
                foreach ($validated['pasangan'] as $pas) {
                    PasanganMenjodohkan::create([
                        'soal_id' => $soal->id,
                        'kiri' => $pas['kiri'],
                        'kanan' => $pas['kanan'],
                    ]);
                }
            }
        });

        return back()->with('success', 'Soal berhasil ditambahkan ke bank soal.');
    }

    /**
     * Download template Excel untuk import soal massal.
     */
    public function downloadTemplate()
    {
        return Excel::download(
            new SoalTemplateExport,
            'template-import-soal.xlsx'
        );
    }

    /**
     * Import soal dari file Excel (.xlsx / .xls).
     */
    public function importExcel(Request $request, BankSoal $bankSoal)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // max 10MB
        ]);

        $import = new SoalImport($bankSoal->id);

        try {
            Excel::import($import, $request->file('file'));
        } catch (ValidationException $e) {
            $failures = collect($e->failures())->map(fn ($f) => "Baris {$f->row()}: ".implode(', ', $f->errors())
            )->toArray();

            return back()->with('import_errors', $failures)->with('error', 'Beberapa baris gagal divalidasi.');
        } catch (\Exception $e) {
            Log::error('Excel import failed', [
                'message' => $e->getMessage(),
                'file' => $request->file('file')->getClientOriginalName(),
                'user' => auth()->id(),
            ]);

            return back()->with('error', 'Gagal memproses file. Pastikan format file sesuai dengan template.');
        }

        $msg = "{$import->imported} soal berhasil diimpor.";

        if (! empty($import->errors)) {
            return back()
                ->with('import_errors', $import->errors)
                ->with('warning', $msg.' Namun ada '.count($import->errors).' baris yang gagal. Periksa detail di bawah.');
        }

        return back()->with('success', $msg);
    }

    /**
     * Download template Word untuk import soal massal.
     */
    public function downloadWordTemplate()
    {
        $phpWord = new PhpWord;
        $section = $phpWord->addSection();

        $section->addText('TEMPLATE SOAL UJIAN (FORMAT WORD)', ['bold' => true, 'size' => 16, 'color' => '4F46E5']);
        $section->addTextBreak(1);
        $section->addText('PETUNJUK PENGISIAN:', ['bold' => true, 'color' => 'DC2626']);
        $section->addText('- Gunakan penanda [TIPE] untuk menentukan jenis soal: pg, benar_salah, essay, menjodohkan.');
        $section->addText('- Gunakan penanda [SOAL] untuk memulai teks pertanyaan.');
        $section->addText('- Gunakan penanda [A], [B], [C], [D], [E] untuk opsi pilihan ganda.');
        $section->addText('- Gunakan penanda [KUNCI] untuk menentukan kunci jawaban yang benar.');
        $section->addText('- Gunakan penanda [BOBOT] untuk menentukan bobot nilai per soal (opsional, default 1).');
        $section->addTextBreak(2);

        $section->addText('=== CONTOH SOAL PILIHAN GANDA ===', ['bold' => true]);
        $section->addText('[TIPE] pg');
        $section->addText('[SOAL] Siapakah presiden pertama Indonesia?');
        $section->addText('[A] Soeharto');
        $section->addText('[B] B.J. Habibie');
        $section->addText('[C] Soekarno');
        $section->addText('[D] Megawati');
        $section->addText('[E] Joko Widodo');
        $section->addText('[KUNCI] C');
        $section->addText('[BOBOT] 2');

        $section->addTextBreak(2);

        $section->addText('=== CONTOH SOAL BENAR / SALAH ===', ['bold' => true]);
        $section->addText('[TIPE] benar_salah');
        $section->addText('[SOAL] Ibukota negara Jepang adalah Tokyo.');
        $section->addText('[KUNCI] Benar');

        $section->addTextBreak(2);

        $section->addText('=== CONTOH SOAL ESSAY ===', ['bold' => true]);
        $section->addText('[TIPE] essay');
        $section->addText('[SOAL] Jelaskan proses terjadinya hujan secara singkat!');
        $section->addText('[KUNCI] Hujan terjadi karena proses evaporasi (penguapan air), kondensasi (pembentukan awan), dan presipitasi (jatuhnya air).');

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'template-import-soal.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'phpword');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Import soal dari file Word (.docx).
     */
    public function import(Request $request, BankSoal $bankSoal)
    {
        $request->validate([
            'file' => 'required|file|mimes:docx|max:10240', // max 10MB
        ]);

        $file = $request->file('file');
        $importService = new WordImportService;

        try {
            $questions = $importService->importDocx($file->getRealPath());

            if (empty($questions)) {
                return back()->with('error', 'Tidak ada soal yang berhasil diproses dari file Word. Pastikan format penanda [SOAL], [A], [KUNCI] sudah benar.');
            }

            DB::transaction(function () use ($questions, $bankSoal) {
                foreach ($questions as $q) {
                    $soal = Soal::create([
                        'bank_soal_id' => $bankSoal->id,
                        'tipe' => $q['tipe'],
                        'pertanyaan' => $q['pertanyaan'],
                        'bobot' => $q['bobot'] ?? 1,
                        'kunci_jawaban' => $q['kunci_jawaban'],
                    ]);

                    if ($q['tipe'] === 'pg' && ! empty($q['pilihan'])) {
                        $urutan = 1;
                        foreach ($q['pilihan'] as $kode => $teks) {
                            PilihanJawaban::create([
                                'soal_id' => $soal->id,
                                'kode' => $kode,
                                'teks' => $teks,
                                'urutan' => $urutan++,
                                'is_benar' => (strtoupper($q['kunci_jawaban']) === $kode),
                            ]);
                        }
                    }

                    if ($q['tipe'] === 'benar_salah') {
                        $kunciNorm = strtolower($q['kunci_jawaban']);
                        foreach (['Benar', 'Salah'] as $i => $opt) {
                            PilihanJawaban::create([
                                'soal_id' => $soal->id,
                                'kode' => $opt,
                                'teks' => $opt,
                                'is_benar' => in_array($kunciNorm, ['benar', 'true', '1']) ? ($opt === 'Benar') : ($opt === 'Salah'),
                                'urutan' => $i + 1,
                            ]);
                        }
                    }

                    if ($q['tipe'] === 'menjodohkan' && ! empty($q['pasangan'])) {
                        foreach ($q['pasangan'] as $pas) {
                            PasanganMenjodohkan::create([
                                'soal_id' => $soal->id,
                                'kiri' => $pas['kiri'],
                                'kanan' => $pas['kanan'],
                            ]);
                        }
                    }
                }
            });

            return back()->with('success', count($questions).' soal berhasil diimpor dari file Word.');

        } catch (\Exception $e) {
            Log::error('Word import failed', [
                'message' => $e->getMessage(),
                'file' => $request->file('file')->getClientOriginalName(),
                'user' => auth()->id(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memproses file. Pastikan format penanda sesuai template.');
        }
    }

    public function updateBobot(Request $request, Soal $soal)
    {
        $request->validate([
            'bobot' => 'required|numeric|min:0.1',
        ]);

        $soal->update(['bobot' => $request->bobot]);

        return back()->with('success', 'Bobot soal berhasil diperbarui.');
    }

    public function destroy(Soal $soal)
    {
        $soal->delete();

        return back()->with('success', 'Soal berhasil dihapus.');
    }
}
