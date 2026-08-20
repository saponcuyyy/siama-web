<?php

namespace App\Services\Website;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class PdfTableExtractor
{
    public const MIN_CONSECUTIVE_ROWS = 2;
    public const MIN_COLUMNS = 2;
    public const MAX_COLUMNS = 20;
    public const MIN_CONTENT_THRESHOLD = 0.3;

    /**
     * Extract tables from a PDF file.
     * Tries pdftotext CLI first; falls back to direct coordinate-based smalot extraction.
     */
    public function extract(string $pdfPath): array
    {
        $disk = Storage::disk('public');
        if (!$disk->exists($pdfPath)) {
            throw new \InvalidArgumentException("PDF file not found: {$pdfPath}");
        }

        $realPath = $this->resolveRealPath($disk, $pdfPath);

        // Try pdftotext CLI first (most accurate layout)
        try {
            $result = Process::run(['pdftotext', '-layout', '-nopgbrk', $realPath, '-']);
            if ($result->successful()) {
                $this->cleanupTempFile($realPath, $pdfPath);
                return $this->detectTables($result->output());
            }
            throw new \RuntimeException("pdftotext failed: " . $result->errorOutput());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::info(
                'PdfTableExtractor: pdftotext tidak tersedia, menggunakan smalot coordinate extraction. ' . $e->getMessage()
            );
        }

        // Fallback: direct coordinate-based table extraction (bypasses character-grid)
        $tables = $this->extractTablesWithCoordinates($realPath);
        $this->cleanupTempFile($realPath, $pdfPath);
        return $tables;
    }

    /**
     * Direct coordinate-based table extraction using smalot/pdfparser.
     * Groups text by Y (rows), clusters X positions into columns,
     * then builds structured table rows — no character grid needed.
     */
    protected function extractTablesWithCoordinates(string $realPath): array
    {
        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            throw new \RuntimeException('smalot/pdfparser not installed.');
        }

        $config = new \Smalot\PdfParser\Config();
        $config->setDataTmFontInfoHasToBeIncluded(true);
        $parser = new \Smalot\PdfParser\Parser([], $config);
        $pdf    = $parser->parseFile($realPath);

        $allTables = [];

        foreach ($pdf->getPages() as $page) {
            try { $dataTm = $page->getDataTm(); } catch (\Exception $e) { continue; }
            if (empty($dataTm)) continue;

            // 1. Group elements into rows by Y coordinate (tolerance ±3 units)
            $rowMap = [];
            foreach ($dataTm as $el) {
                if (!isset($el[0][4], $el[0][5], $el[1])) continue;
                $x    = (float) $el[0][4];
                $y    = (float) $el[0][5];
                $text = trim((string) $el[1]);
                if ($text === '') continue;

                $matched = null;
                foreach ($rowMap as $ky => $items) {
                    if (abs((float)$ky - $y) <= 3) { $matched = $ky; break; }
                }
                $key = $matched ?? number_format($y, 4, '.', '');
                $rowMap[$key][] = ['x' => $x, 'text' => $text];
            }
            if (count($rowMap) < self::MIN_CONSECUTIVE_ROWS) continue;

            // 2. Sort rows top-to-bottom (Y descending in PDF space)
            krsort($rowMap);
            $sortedRows = array_values($rowMap);

            // 3. Collect all X start positions → cluster into column boundaries
            $allX = [];
            foreach ($sortedRows as $items) {
                foreach ($items as $item) { $allX[] = $item['x']; }
            }
            sort($allX);

            // Gap threshold: 1% of X range, minimum 8 units
            $xRange      = empty($allX) ? 500 : (max($allX) - min($allX));
            $gapThreshold = max(8.0, $xRange * 0.01);

            $colCenters = [];
            $cluster    = [$allX[0]];
            for ($i = 1; $i < count($allX); $i++) {
                if ($allX[$i] - end($cluster) > $gapThreshold) {
                    $colCenters[] = array_sum($cluster) / count($cluster);
                    $cluster = [];
                }
                $cluster[] = $allX[$i];
            }
            $colCenters[] = array_sum($cluster) / count($cluster);

            if (count($colCenters) < self::MIN_COLUMNS) continue;

            // 4. Build structured rows: assign each text item to nearest column
            $structuredRows = [];
            foreach ($sortedRows as $items) {
                usort($items, fn($a, $b) => $a['x'] <=> $b['x']);

                $cells = array_fill(0, count($colCenters), '');
                foreach ($items as $item) {
                    // Find nearest column center
                    $best = 0;
                    $bestDist = PHP_FLOAT_MAX;
                    foreach ($colCenters as $ci => $cx) {
                        $d = abs($item['x'] - $cx);
                        if ($d < $bestDist) { $bestDist = $d; $best = $ci; }
                    }
                    $cells[$best] = $cells[$best] !== ''
                        ? $cells[$best] . ' ' . $item['text']
                        : $item['text'];
                }
                $structuredRows[] = $cells;
            }

            // 5. Detect table groups (consecutive rows where most cells are non-empty)
            $pageTables = $this->detectStructuredTableGroups($structuredRows);
            foreach ($pageTables as $t) { $allTables[] = $t; }
        }

        return $allTables;
    }

    /**
     * Detect table groups from pre-structured rows (cells already assigned to columns).
     * Splits into a new table when the header row signature repeats.
     */
    protected function detectStructuredTableGroups(array $structuredRows): array
    {
        $tables    = [];
        $n         = count($structuredRows);
        $i         = 0;

        while ($i < $n) {
            $row      = $structuredRows[$i];
            $nonEmpty = count(array_filter($row, fn($c) => trim($c) !== ''));

            if ($nonEmpty < self::MIN_COLUMNS) { $i++; continue; }

            $headerSig  = $this->cellSignature($row);
            $tableRows  = [$row];
            $i++;

            while ($i < $n) {
                $next        = $structuredRows[$i];
                $nextNonEmpty = count(array_filter($next, fn($c) => trim($c) !== ''));

                // Repeated header → flush current, start new table
                if (
                    $this->cellSignature($next) === $headerSig
                    && count($tableRows) > self::MIN_CONSECUTIVE_ROWS
                ) {
                    $tables[] = ['columns' => $tableRows[0], 'rows' => array_slice($tableRows, 1)];
                    $tableRows = [$next];
                    $i++;
                    continue;
                }

                if ($nextNonEmpty >= 1) {
                    $tableRows[] = $next;
                    $i++;
                } else {
                    $i++;
                    // Allow one blank row inside a table
                    if ($i < $n && count(array_filter($structuredRows[$i], fn($c) => trim($c) !== '')) >= 1) {
                        continue;
                    }
                    break;
                }
            }

            if (count($tableRows) >= self::MIN_CONSECUTIVE_ROWS + 1) {
                $tables[] = ['columns' => $tableRows[0], 'rows' => array_slice($tableRows, 1)];
            }
        }

        return $tables;
    }

    /** Normalized signature for a cells array (used for repeated-header detection). */
    protected function cellSignature(array $cells): string
    {
        $n = array_map(fn($c) => strtolower(trim((string)$c)), $cells);
        sort($n);
        return implode('|', $n);
    }

    /**
     * Resolve real filesystem path from storage disk.
     * Handles both local and S3/minio disks by downloading to temp if needed.
     */
    protected function resolveRealPath($disk, string $pdfPath): string
    {
        try {
            $realPath = $disk->path($pdfPath);
            if ($realPath && file_exists($realPath)) {
                return $realPath;
            }
        } catch (\Exception $e) {
            // Disk doesn't support path() (e.g. S3/minio) — download to temp
        }

        $tempDir = sys_get_temp_dir() . '/pdf-tables-' . uniqid();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/' . basename($pdfPath);
        file_put_contents($tempPath, $disk->get($pdfPath));

        return $tempPath;
    }

    /**
     * Clean up temp file if it was downloaded for non-local disk.
     */
    protected function cleanupTempFile(string $realPath, string $originalPath): void
    {
        try {
            $storagePath = Storage::disk('public')->path($originalPath);
        } catch (\Exception $e) {
            $storagePath = null;
        }
        if ($realPath !== $storagePath && file_exists($realPath)) {
            @unlink($realPath);
            $dir = dirname($realPath);
            if (is_dir($dir) && str_starts_with($dir, sys_get_temp_dir())) {
                @rmdir($dir);
            }
        }
    }

    /**
     * Ekstrak teks dengan layout menggunakan smalot/pdfparser.
     * Merekonstruksi posisi kolom dari koordinat X/Y setiap elemen teks,
     * sehingga menghasilkan output yang mirip dengan pdftotext -layout.
     */
    protected function extractTextWithSmalot(string $realPath): string
    {
        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            throw new \RuntimeException(
                'smalot/pdfparser tidak terinstall. Jalankan: composer require smalot/pdfparser'
            );
        }

        $config = new \Smalot\PdfParser\Config();
        $config->setDataTmFontInfoHasToBeIncluded(true);

        $parser = new \Smalot\PdfParser\Parser([], $config);
        $pdf    = $parser->parseFile($realPath);
        $pages  = $pdf->getPages();

        $outputLines = [];

        foreach ($pages as $page) {
            // Dapatkan semua elemen teks beserta matriks posisi
            try {
                $dataTm = $page->getDataTm();
            } catch (\Exception $e) {
                // Halaman tidak punya data posisi, skip
                continue;
            }

            if (empty($dataTm)) {
                $outputLines[] = $this->getFallbackTextFromPage($page);
                continue;
            }

            // Kelompokkan elemen teks per baris berdasarkan koordinat Y
            // Gunakan toleransi ±2 unit agar teks satu baris yang sedikit berbeda Y-nya
            // tetap dianggap satu baris
            $rows = [];
            foreach ($dataTm as $element) {
                // $element[0] = [a, b, c, d, x, y] — transformation matrix
                // $element[1] = teks
                if (!isset($element[0][5], $element[1])) {
                    continue;
                }

                $x    = (float) $element[0][4];
                $y    = (float) $element[0][5];
                $text = (string) $element[1];

                if (trim($text) === '') {
                    continue;
                }

                // Cari baris yang Y-nya dalam toleransi ±2
                $matchedRow = null;
                foreach ($rows as $rowY => $rowItems) {
                    if (abs((float)$rowY - $y) <= 2) {
                        $matchedRow = $rowY;
                        break;
                    }
                }

                if ($matchedRow !== null) {
                    $rows[$matchedRow][] = ['x' => $x, 'text' => $text];
                } else {
                    // Use string key to avoid float-to-int implicit conversion
                    $key = number_format($y, 4, '.', '');
                    $rows[$key][] = ['x' => $x, 'text' => $text];
                }
            }

            if (empty($rows)) {
                continue;
            }

            // Sort baris dari Y besar ke kecil (PDF Y = 0 di bawah)
            krsort($rows);

            // Hitung lebar karakter rata-rata untuk memetakan X → kolom karakter
            // Coba kalkulasi dari selisih X antar teks berurutan dalam baris yang sama
            $charWidth = $this->calculateCharWidth($rows);

            foreach ($rows as $rowItems) {
                // Sort elemen dalam baris dari kiri ke kanan (X kecil ke besar)
                usort($rowItems, fn($a, $b) => $a['x'] <=> $b['x']);

                if (empty($rowItems)) {
                    continue;
                }

                // Gabungkan blok teks yang sangat dekat (kerning/tracking) atau terpisah 1 spasi
                $mergedItems = [];
                foreach ($rowItems as $item) {
                    if (empty($mergedItems)) {
                        $mergedItems[] = $item;
                    } else {
                        $lastIdx = count($mergedItems) - 1;
                        $lastItem = &$mergedItems[$lastIdx];

                        $lastWidth = strlen($lastItem['text']) * $charWidth;
                        $lastEnd   = $lastItem['x'] + $lastWidth;
                        $charGap   = ($item['x'] - $lastEnd) / $charWidth;

                        if ($charGap < 1.0) {
                            // Gabungkan tanpa spasi jika gap sangat kecil
                            $lastItem['text'] .= $item['text'];
                        } elseif ($charGap < 2.0) {
                            // Gabungkan dengan 1 spasi jika gap setara 1 karakter
                            $lastItem['text'] .= ' ' . $item['text'];
                        } else {
                            $mergedItems[] = $item;
                        }
                    }
                }
                $rowItems = $mergedItems;

                // Hitung offset X minimum agar baris dimulai dari kolom 0
                $minX   = $rowItems[0]['x'];
                $maxCol = 0;

                // Bangun array of (col, text)
                $colTexts = [];
                foreach ($rowItems as $item) {
                    $col        = (int) round(($item['x'] - $minX) / $charWidth);
                    $colTexts[] = ['col' => $col, 'text' => $item['text']];
                    $maxCol     = max($maxCol, $col + strlen($item['text']));
                }

                // Buat string baris dengan spasi sesuai posisi kolom
                $lineBuffer = str_repeat(' ', $maxCol + 10);

                foreach ($colTexts as $ct) {
                    $col  = $ct['col'];
                    $text = $ct['text'];
                    $len  = strlen($text);

                    if ($col + $len <= strlen($lineBuffer)) {
                        $lineBuffer = substr_replace($lineBuffer, $text, $col, $len);
                    } else {
                        $lineBuffer .= ' ' . $text;
                    }
                }

                $outputLines[] = rtrim($lineBuffer);
            }

            // Tambahkan pemisah antar halaman
            $outputLines[] = '';
        }

        return implode("\n", $outputLines);
    }

    /**
     * Fallback when getDataTm() returns empty: gunakan getText() biasa.
     */
    protected function getFallbackTextFromPage($page): string
    {
        try {
            $text = $page->getText();
        } catch (\Exception $e) {
            return '';
        }
        return trim($text);
    }

    /**
     * Hitung lebar karakter rata-rata dari data baris yang ada,
     * agar mapping X → kolom karakter lebih akurat untuk berbagai PDF.
     * Jika tidak bisa dihitung, gunakan default 6.0.
     */
    protected function calculateCharWidth(array $rows): float
    {
        $deltas = [];
        foreach ($rows as $rowItems) {
            $items = array_values($rowItems);
            for ($i = 1; $i < count($items); $i++) {
                $delta = abs($items[$i]['x'] - $items[$i - 1]['x']);
                $textLen = max(strlen($items[$i - 1]['text']), 1);
                $deltas[] = $delta / $textLen;
            }
        }
        $deltas = array_filter($deltas, fn($d) => $d > 1 && $d < 20);
        if (count($deltas) < 3) {
            return 6.0;
        }
        return array_sum($deltas) / count($deltas);
    }

    /**
     * Detect tables by scanning lines for column-aligned content.
     * Splits into a new table whenever a repeated header signature is detected.
     */
    protected function detectTables(string $text): array
    {
        // Strip PDF formatting tags (pdftotext outputs <b>, <i>, <u> etc.)
        $text = strip_tags($text);
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $lines = array_map('rtrim', $lines);
        $lines = array_map(fn($l) => ltrim($l), $lines);
        $tables = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            if ($this->isProbablyTableRow($line)) {
                // Collect the first "header candidate" line signature
                $headerSignature = $this->getRowSignature($line);
                $tableLines = [$line];
                $i++;

                while ($i < count($lines)) {
                    $nextLine = $lines[$i];

                    if ($this->isProbablyTableRow($nextLine)) {
                        // If we see the same header signature again after at least
                        // MIN_CONSECUTIVE_ROWS rows, it's a new section/sub-table.
                        $sig = $this->getRowSignature($nextLine);
                        if (
                            $sig === $headerSignature
                            && count($tableLines) >= self::MIN_CONSECUTIVE_ROWS + 1
                        ) {
                            // Flush current table, start a new one with this header row
                            if (count($tableLines) >= self::MIN_CONSECUTIVE_ROWS) {
                                $table = $this->parseTable($tableLines);
                                if ($table !== null) {
                                    $tables[] = $table;
                                }
                            }
                            $tableLines = [$nextLine];
                            $i++;
                            continue;
                        }

                        $tableLines[] = $nextLine;
                        $i++;
                    } elseif (trim($nextLine) === '') {
                        $i++;
                    } else {
                        break;
                    }
                }

                if (count($tableLines) >= self::MIN_CONSECUTIVE_ROWS) {
                    $table = $this->parseTable($tableLines);
                    if ($table !== null) {
                        $tables[] = $table;
                    }
                }
                continue;
            }
            $i++;
        }

        return $tables;
    }

    /**
     * Get a normalized "signature" for a row: a lowercased, sorted list
     * of trimmed cell values, used to detect repeated header rows.
     */
    protected function getRowSignature(string $line): string
    {
        $cells = $this->splitByDensity($line);
        $normalized = array_map(fn($c) => strtolower(trim($c)), $cells);
        sort($normalized);
        return implode('|', $normalized);
    }

    /**
     * Check if a line has multiple columns separated by gaps.
     * Uses density analysis instead of fixed-width splitting.
     */
    protected function isProbablyTableRow(string $line): bool
    {
        $trimmed = trim($line);
        if (empty($trimmed)) return false;
        if (preg_match('/^[\s\-\_\=\.]+$/', $trimmed)) return false;

        $columns = $this->splitByDensity($line);

        // Filter out key-value metadata lines (e.g. "NAMA SEKOLAH : SMAN 2")
        foreach ($columns as $col) {
            $colTrimmed = trim($col);
            if (str_starts_with($colTrimmed, ':') || str_ends_with($colTrimmed, ':')) {
                return false;
            }
        }

        $count = count($columns);
        return $count >= self::MIN_COLUMNS && $count <= self::MAX_COLUMNS;
    }

    /**
     * Parse a group of consecutive rows into a structured table.
     */
    protected function parseTable(array $lines): ?array
    {
        if (empty($lines)) return null;

        // Detect column gaps by analyzing content density across all lines
        $gaps = $this->detectColumnGaps($lines);

        if (count($gaps) < 1) return null;

        // Extract rows using detected gaps
        $rows = [];
        foreach ($lines as $line) {
            $cells = $this->extractCellsByGaps($line, $gaps);
            if (!empty($cells)) {
                $rows[] = $cells;
            }
        }

        if (count($rows) < self::MIN_CONSECUTIVE_ROWS) return null;

        // First row is header
        $columns = $rows[0];
        $dataRows = array_slice($rows, 1);

        return [
            'columns' => $columns,
            'rows'    => $dataRows,
        ];
    }

    /**
     * Detect column gap positions using content density across all lines.
     *
     * Builds a density profile: for each character position,
     * what fraction of lines have non-whitespace content there.
     * Gaps are contiguous regions where density is below threshold.
     */
    protected function detectColumnGaps(array $lines): array
    {
        $maxLen = 0;
        $lengths = [];
        foreach ($lines as $line) {
            $len = strlen(rtrim($line));
            $lengths[] = $len;
            if ($len > $maxLen) $maxLen = $len;
        }

        if ($maxLen === 0) return [];

        // Exclude outlier lines (e.g., total rows with condensed spacing)
        // that are substantially shorter than the longest line.
        $minLen = $maxLen * 0.8;
        $filtered = [];
        foreach ($lines as $i => $line) {
            if ($lengths[$i] >= $minLen) {
                $filtered[] = $line;
            }
        }

        if (count($filtered) < self::MIN_CONSECUTIVE_ROWS) {
            $filtered = $lines;
        }

        // Count lines with content at each position
        $contentCount = array_fill(0, $maxLen, 0);
        $numLines = count($filtered);

        foreach ($filtered as $line) {
            for ($pos = 0; $pos < $maxLen; $pos++) {
                if ($pos < strlen($line) && $line[$pos] !== ' ') {
                    $contentCount[$pos]++;
                }
            }
        }

        $threshold = max(1, (int)($numLines * self::MIN_CONTENT_THRESHOLD));

        // Find column regions (high density) and gaps (low density)
        // A gap starts when density drops below threshold and stays there
        $gaps = [];
        $inGap = false;
        $gapStart = 0;

        for ($pos = 0; $pos < $maxLen; $pos++) {
            $hasContent = $contentCount[$pos] >= $threshold;

            if (!$hasContent && !$inGap) {
                $inGap = true;
                $gapStart = $pos;
            } elseif ($hasContent && $inGap) {
                $gapWidth = $pos - $gapStart;
                if ($gapWidth >= 2) {
                    $gaps[] = (int)(($gapStart + $pos) / 2);
                }
                $inGap = false;
            }
        }

        return $gaps;
    }

    /**
     * Split a line into columns based on detected gap positions.
     */
    protected function extractCellsByGaps(string $line, array $gaps): array
    {
        $cells = [];
        $prev = 0;

        foreach ($gaps as $gap) {
            $cell = trim(substr($line, $prev, $gap - $prev));
            $cells[] = $cell;
            $prev = $gap;
        }

        // Last column
        $cells[] = trim(substr($line, $prev));

        return $cells;
    }

    /**
     * Split a line into columns by detecting gaps of 2+ spaces.
     * Single spaces within a column are preserved (e.g., "John Doe").
     */
    protected function splitByDensity(string $line): array
    {
        $len = strlen($line);
        if ($len === 0) return [];

        $columns = [];
        $current = '';
        $spaceCount = 0;

        for ($i = 0; $i < $len; $i++) {
            if ($line[$i] !== ' ') {
                // Commit accumulated spaces as separator if 2+
                if ($spaceCount >= 2 && $current !== '') {
                    $columns[] = trim($current);
                    $current = '';
                }
                $current .= $line[$i];
                $spaceCount = 0;
            } else {
                $spaceCount++;
            }
        }

        // Last column
        $remaining = trim($current);
        if ($remaining !== '') {
            $columns[] = $remaining;
        }

        return $columns;
    }
}
