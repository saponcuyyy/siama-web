<?php

namespace App\Services;

use DOMDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use XSLTProcessor;
use ZipArchive;

class WordImportService
{
    private $xsltProcessor;

    public function __construct()
    {
        $xslDoc = new DOMDocument;
        $xslPath = resource_path('xslt/omml2mml.xsl');
        if (file_exists($xslPath)) {
            try {
                // Laravel converts warnings to ErrorExceptions. We suppress warnings
                // because libxslt (PHP's default) often does not support XSLT 2.0
                $xslDoc->load($xslPath);
                $processor = new XSLTProcessor;

                $oldErrorLevel = error_reporting(0); // Suppress errors for this specific block
                $success = @$processor->importStyleSheet($xslDoc);
                error_reporting($oldErrorLevel);

                if ($success) {
                    $this->xsltProcessor = $processor;
                }
            } catch (\Exception $e) {
                $this->xsltProcessor = null;
            } catch (\Throwable $e) {
                $this->xsltProcessor = null;
            }
        }
    }

    public function importDocx($filePath)
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            throw new \Exception('Gagal membuka file Word (.docx).');
        }

        // 1. Extract relationships for media
        $rels = [];
        $relsContent = $zip->getFromName('word/_rels/document.xml.rels');
        if ($relsContent) {
            $relsXml = simplexml_load_string($relsContent);
            if ($relsXml) {
                foreach ($relsXml->Relationship as $rel) {
                    $rels[(string) $rel['Id']] = (string) $rel['Target'];
                }
            }
        }

        // 2. Extract media images and save them
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $mediaMap = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (str_contains($filename, 'word/media/')) {
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (! in_array($extension, $allowedExtensions, true)) {
                    continue;
                }

                $newName = 'soal-images/'.Str::random(12).'.'.$extension;

                $imageContent = $zip->getFromIndex($i);

                // Validate MIME type from content
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_buffer($finfo, $imageContent);
                finfo_close($finfo);

                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (! in_array($mime, $allowedMimes, true)) {
                    continue;
                }

                // Simpan ke MinIO
                Storage::disk('minio')->put($newName, $imageContent);

                // Gunakan proxy route Laravel agar URL bekerja di semua environment
                // /media/soal/{filename} → bukan direct MinIO URL
                $wordPath = str_replace('word/', '', $filename);
                $filename_only = basename($newName);
                $mediaMap[$wordPath] = url('/media/soal/'.$filename_only);
            }
        }

        // 3. Read main document content
        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! $documentXml) {
            throw new \Exception('File Word tidak memiliki konten utama yang valid.');
        }

        return $this->parseXmlContent($documentXml, $rels, $mediaMap);
    }

    private function parseXmlContent($xmlContent, $rels, $mediaMap)
    {
        $dom = new DOMDocument;
        // Load XML safely
        libxml_use_internal_errors(true);
        if (! $dom->loadXML($xmlContent)) {
            libxml_clear_errors();
            throw new \Exception('Gagal memuat XML konten dokumen Word.');
        }
        libxml_clear_errors();

        // Get all paragraph elements inside the body
        $paragraphs = $dom->getElementsByTagName('p');
        $parsedParagraphs = [];

        foreach ($paragraphs as $p) {
            $pContent = $this->parseNodeContent($p, $rels, $mediaMap);
            // Only add paragraphs with content or images
            if (trim(strip_tags($pContent)) !== '' || str_contains($pContent, '<img')) {
                $parsedParagraphs[] = $pContent;
            }
        }

        // 4. Compile paragraphs into questions structure
        $questions = [];
        $currentQuestion = null;
        $currentBuffer = null;
        $pilihanKode = null;

        foreach ($parsedParagraphs as $pText) {
            $trimmed = trim(strip_tags($pText));

            if (preg_match('/^\[SOAL\]/i', $trimmed)) {
                if ($currentQuestion) {
                    $questions[] = $this->finalizeQuestion($currentQuestion);
                }
                $currentQuestion = [
                    'tipe' => 'pg',
                    'bobot' => 1,
                    'pertanyaan' => '',
                    'kunci_jawaban' => '',
                    'pilihan' => [],
                    'pasangan' => [],
                ];
                $currentBuffer = 'pertanyaan';
                $pText = preg_replace('/^\[SOAL\]/i', '', $pText);
            }

            if (! $currentQuestion) {
                continue;
            }

            if (preg_match('/^\[(A|B|C|D|E)\]/i', $trimmed, $matches)) {
                $pilihanKode = strtoupper($matches[1]);
                $currentBuffer = 'pilihan.'.$pilihanKode;
                $currentQuestion['pilihan'][$pilihanKode] = '';
                $pText = preg_replace('/^\['.$pilihanKode.'\]/i', '', $pText);
            } elseif (preg_match('/^\[KUNCI\]/i', $trimmed)) {
                $currentBuffer = 'kunci';
                $pText = preg_replace('/^\[KUNCI\]/i', '', $pText);
            } elseif (preg_match('/^\[TIPE\]/i', $trimmed)) {
                $currentBuffer = 'tipe';
                $pText = preg_replace('/^\[TIPE\]/i', '', $pText);
            } elseif (preg_match('/^\[BOBOT\]/i', $trimmed)) {
                $currentBuffer = 'bobot';
                $pText = preg_replace('/^\[BOBOT\]/i', '', $pText);
            } elseif (preg_match('/^\[PASANGAN\]/i', $trimmed)) {
                $currentBuffer = 'pasangan';
                $pText = preg_replace('/^\[PASANGAN\]/i', '', $pText);
            }

            // Append to appropriate buffer
            if ($currentBuffer === 'pertanyaan') {
                $currentQuestion['pertanyaan'] .= ($currentQuestion['pertanyaan'] === '' ? '' : '<br>').$pText;
            } elseif (str_starts_with($currentBuffer, 'pilihan.')) {
                $kode = substr($currentBuffer, 8);
                $currentQuestion['pilihan'][$kode] .= $pText;
            } elseif ($currentBuffer === 'kunci') {
                $currentQuestion['kunci_jawaban'] .= trim(strip_tags($pText));
            } elseif ($currentBuffer === 'tipe') {
                $currentQuestion['tipe'] = trim(strip_tags($pText));
            } elseif ($currentBuffer === 'bobot') {
                $currentQuestion['bobot'] = trim(strip_tags($pText));
            } elseif ($currentBuffer === 'pasangan') {
                $line = trim(strip_tags($pText));
                if ($line && str_contains($line, '=')) {
                    $parts = explode('=', $line, 2);
                    $currentQuestion['pasangan'][] = [
                        'kiri' => trim($parts[0]),
                        'kanan' => trim($parts[1]),
                    ];
                }
            }
        }

        if ($currentQuestion) {
            $questions[] = $this->finalizeQuestion($currentQuestion);
        }

        return $questions;
    }

    private function finalizeQuestion($q)
    {
        $q['tipe'] = strtolower(trim($q['tipe']));
        if (! in_array($q['tipe'], ['pg', 'benar_salah', 'essay', 'menjodohkan'])) {
            $q['tipe'] = 'pg';
        }
        $q['bobot'] = floatval($q['bobot']) ?: 1.0;

        return $q;
    }

    private function parseNodeContent($node, $rels, $mediaMap)
    {
        $content = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $name = $child->localName;

                if ($name === 'r') {
                    $content .= $this->parseNodeContent($child, $rels, $mediaMap);
                } elseif ($name === 't') {
                    $content .= htmlspecialchars($child->textContent);
                } elseif ($name === 'drawing') {
                    // Extract drawing embed ID
                    $blipElements = $child->getElementsByTagNameNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'embed');
                    $rId = null;
                    if ($blipElements->length > 0) {
                        $rId = $blipElements->item(0)->nodeValue;
                    } else {
                        // fallback
                        $blips = $child->getElementsByTagName('blip');
                        if ($blips->length > 0) {
                            foreach ($blips->item(0)->attributes as $attr) {
                                if ($attr->localName === 'embed') {
                                    $rId = $attr->nodeValue;
                                    break;
                                }
                            }
                        }
                    }

                    if ($rId && isset($rels[$rId])) {
                        $target = $rels[$rId];
                        if (isset($mediaMap[$target])) {
                            $url = $mediaMap[$target];
                            $content .= ' <img src="'.$url.'" alt="Gambar" class="inline-block max-h-48 my-2 rounded-lg" /> ';
                        }
                    }
                } elseif ($name === 'oMath') {
                    if ($this->xsltProcessor) {
                        $content .= $this->convertOmmlToMathml($child);
                    } else {
                        // Fallback to text representation
                        $content .= htmlspecialchars($child->textContent);
                    }
                } elseif ($name === 'oMathPara') {
                    $content .= $this->parseNodeContent($child, $rels, $mediaMap);
                } elseif ($name === 'hyperlink') {
                    $content .= $this->parseNodeContent($child, $rels, $mediaMap);
                }
            }
        }

        return $content;
    }

    private function convertOmmlToMathml($ommlNode)
    {
        $tempDom = new DOMDocument('1.0', 'UTF-8');
        $importedNode = $tempDom->importNode($ommlNode, true);
        $tempDom->appendChild($importedNode);

        $mathml = $this->xsltProcessor->transformToXML($tempDom);
        if ($mathml === false) {
            return '';
        }

        // Strip xml declaration and namespaces for clean inline MathML rendering in browsers
        $cleanMathml = str_replace(
            ['m:', 'xmlns:m', '<?xml version="1.0" encoding="UTF-8"?>', '<?xml version="1.0"?>'],
            ['', '', '', ''],
            $mathml
        );

        return trim($cleanMathml);
    }
}
