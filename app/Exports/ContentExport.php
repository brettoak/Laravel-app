<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use RuntimeException;
use XMLWriter;

class ContentExport
{
    public function create(Collection $articles): string
    {
        $path = tempnam(sys_get_temp_dir(), 'content-export-');

        if ($path === false) {
            throw new RuntimeException('Unable to create the Excel export.');
        }

        unlink($path);
        $path .= '.xlsx';

        $archive = new \PharData($path, 0, null, \Phar::ZIP);

        $files = [
            '[Content_Types].xml' => $this->contentTypes(),
            '_rels/.rels' => $this->rootRelationships(),
            'docProps/app.xml' => $this->appProperties(),
            'docProps/core.xml' => $this->coreProperties(),
            'xl/workbook.xml' => $this->workbook(),
            'xl/_rels/workbook.xml.rels' => $this->workbookRelationships(),
            'xl/styles.xml' => $this->styles(),
            'xl/worksheets/sheet1.xml' => $this->worksheet($articles),
        ];

        foreach ($files as $name => $contents) {
            $archive[$name] = $contents;
        }

        unset($archive);

        return $path;
    }

    private function worksheet(Collection $articles): string
    {
        $writer = $this->writer();
        $writer->startElement('worksheet');
        $writer->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $writer->startElement('sheetViews');
        $writer->startElement('sheetView');
        $writer->writeAttribute('workbookViewId', '0');
        $writer->startElement('pane');
        $writer->writeAttribute('ySplit', '1');
        $writer->writeAttribute('topLeftCell', 'A2');
        $writer->writeAttribute('activePane', 'bottomLeft');
        $writer->writeAttribute('state', 'frozen');
        $writer->endElement();
        $writer->endElement();
        $writer->endElement();

        $writer->startElement('sheetFormatPr');
        $writer->writeAttribute('defaultRowHeight', '15');
        $writer->endElement();

        $writer->startElement('cols');
        foreach ([10, 32, 30, 24, 12, 70, 21, 21] as $index => $width) {
            $writer->startElement('col');
            $writer->writeAttribute('min', (string) ($index + 1));
            $writer->writeAttribute('max', (string) ($index + 1));
            $writer->writeAttribute('width', (string) $width);
            $writer->writeAttribute('customWidth', '1');
            $writer->endElement();
        }
        $writer->endElement();

        $writer->startElement('sheetData');
        $writer->startElement('row');
        $writer->writeAttribute('r', '1');
        $writer->writeAttribute('ht', '24');
        $writer->writeAttribute('customHeight', '1');

        foreach (['ID', 'Title', 'Slug', 'Author', 'Views', 'Content', 'Created At', 'Updated At'] as $column => $heading) {
            $this->stringCell($writer, $this->cellReference($column + 1, 1), $heading, 1);
        }
        $writer->endElement();

        foreach ($articles->values() as $index => $article) {
            $row = $index + 2;
            $writer->startElement('row');
            $writer->writeAttribute('r', (string) $row);

            $this->numberCell($writer, "A{$row}", $article->id);
            $this->stringCell($writer, "B{$row}", $article->title);
            $this->stringCell($writer, "C{$row}", $article->slug);
            $this->stringCell($writer, "D{$row}", $article->user?->name ?? 'Unknown');
            $this->numberCell($writer, "E{$row}", $article->views);
            $this->stringCell($writer, "F{$row}", $article->content, 3);
            $this->numberCell($writer, "G{$row}", $this->excelDate($article->created_at), 2);
            $this->numberCell($writer, "H{$row}", $this->excelDate($article->updated_at), 2);

            $writer->endElement();
        }

        $writer->endElement();
        $writer->startElement('autoFilter');
        $writer->writeAttribute('ref', 'A1:H'.max(1, $articles->count() + 1));
        $writer->endElement();
        $writer->endElement();

        return $writer->outputMemory();
    }

    private function stringCell(XMLWriter $writer, string $reference, mixed $value, int $style = 0): void
    {
        $writer->startElement('c');
        $writer->writeAttribute('r', $reference);
        $writer->writeAttribute('t', 'inlineStr');

        if ($style > 0) {
            $writer->writeAttribute('s', (string) $style);
        }

        $writer->startElement('is');
        $writer->startElement('t');
        $writer->writeAttribute('xml:space', 'preserve');
        $writer->text($this->safeText($value));
        $writer->endElement();
        $writer->endElement();
        $writer->endElement();
    }

    private function numberCell(XMLWriter $writer, string $reference, int|float $value, int $style = 0): void
    {
        $writer->startElement('c');
        $writer->writeAttribute('r', $reference);

        if ($style > 0) {
            $writer->writeAttribute('s', (string) $style);
        }

        $writer->writeElement('v', (string) $value);
        $writer->endElement();
    }

    private function safeText(mixed $value): string
    {
        $value = mb_substr((string) $value, 0, 32767);

        return preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $value) ?? '';
    }

    private function excelDate($date): float
    {
        return round(25569 + ($date->timestamp / 86400), 8);
    }

    private function cellReference(int $column, int $row): string
    {
        return chr(64 + $column).$row;
    }

    private function writer(): XMLWriter
    {
        $writer = new XMLWriter;
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8', 'yes');

        return $writer;
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'</Types>';
    }

    private function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            .'</Relationships>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Content" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="1"><numFmt numFmtId="164" formatCode="yyyy-mm-dd hh:mm:ss"/></numFmts>'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/><family val="2"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/><family val="2"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF4F46E5"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left/><right/><top/><bottom style="thin"><color rgb="FF3730A3"/></bottom><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf><xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function appProperties(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Laravel</Application></Properties>';
    }

    private function coreProperties(): string
    {
        $createdAt = now()->utc()->format('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            .'<dc:title>Content Export</dc:title><dc:creator>Laravel</dc:creator><dcterms:created xsi:type="dcterms:W3CDTF">'.$createdAt.'</dcterms:created>'
            .'</cp:coreProperties>';
    }
}
