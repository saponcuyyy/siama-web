<?php

namespace App\Services\Website;

use Illuminate\Support\Facades\Process;

class PdfTableExtractor
{
    public const MIN_CONSECUTIVE_ROWS = 2;
    public const MIN_COLUMNS = 2;
    public const MAX_COLUMNS = 20;
    public const MIN_CONTENT_THRESHOLD = 0.3;

    /**
     * Extract tables from a PDF file using pdftotext layout analysis.
     */
    public function extract(string $pdfPath): array
    {
        $text = $this->getPdfText($pdfPath);
        return $this->detectTables($text);
    }

    protected function getPdfText(string $pdfPath): string
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        if (!$disk->exists($pdfPath)) {
            throw new \InvalidArgumentException("PDF file not found: {$pdfPath}");
        }

        $realPath = $disk->path($pdfPath);

        $result = Process::run([
            'pdftotext',
            '-layout',
            '-nopgbrk',
            $realPath,
            '-',
        ]);

        if (!$result->successful()) {
            throw new \RuntimeException(
                "Failed to extract text from PDF: " . $result->errorOutput()
            );
        }

        return $result->output();
    }

    /**
     * Detect tables by scanning lines for column-aligned content.
     */
    protected function detectTables(string $text): array
    {
        // Strip PDF formatting tags (pdftotext outputs <b>, <i>, <u> etc.
        // for bold/italic text, which break column alignment detection and
        // appear as literal text in extracted cells).
        $text = strip_tags($text);
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $lines = array_map('rtrim', $lines);
        // Remove leading layout padding; internal spacing is preserved.
        $lines = array_map(fn($l) => ltrim($l), $lines);
        $tables = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            if ($this->isProbablyTableRow($line)) {
                $tableLines = [$line];
                $i++;

                while ($i < count($lines)) {
                    $nextLine = $lines[$i];
                    if ($this->isProbablyTableRow($nextLine)) {
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
     * Check if a line has multiple columns separated by gaps.
     * Uses density analysis instead of fixed-width splitting.
     */
    protected function isProbablyTableRow(string $line): bool
    {
        $trimmed = trim($line);
        if (empty($trimmed)) return false;
        if (preg_match('/^[\s\-\_\=\.]+$/', $trimmed)) return false;

        $columns = $this->splitByDensity($line);
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
