<?php
/**
 * pdf.php
 * ---------------------------------------------------------------------
 * A deliberately small PDF writer for the report download
 * (includes/reports.php): lines of text, stat boxes and tables that break
 * across A4 pages, in the PDF standard Helvetica fonts -- so no library
 * is needed. Text is converted to Windows-1252 (those fonts' encoding);
 * anything outside it prints as "?", and the peso sign as "PHP".
 * Positions are in points from the top-left of the page; PsPdf turns
 * them into PDF's bottom-up coordinates.
 * ---------------------------------------------------------------------
 */
final class PsPdf
{
    const PAGE_W = 595.28;
    const PAGE_H = 841.89;
    const MARGIN = 40;

    const INK    = [0.24, 0.07, 0.13];
    const MAROON = [0.36, 0.11, 0.18];
    const BODY   = [0.28, 0.23, 0.22];
    const MUTED  = [0.42, 0.36, 0.34];
    const HEAD   = [0.95, 0.92, 0.85];
    const STRIPE = [0.985, 0.97, 0.94];
    const RULE   = [0.93, 0.89, 0.81];

    // Helvetica and Helvetica-Bold advance widths (1/1000 em) for ASCII 32-126.
    const WIDTHS = [
        [
            278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278, // space ! " # $ % & ' ( ) * + , - . /
            556, 556, 556, 556, 556, 556, 556, 556, 556, 556,                               // 0-9
            278, 278, 584, 584, 584, 556, 1015,                                             // : ; < = > ? @
            667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833,                // A-M
            722, 778, 667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611,                // N-Z
            278, 278, 278, 469, 556, 333,                                                   // [ \ ] ^ _ `
            556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833,                // a-m
            556, 556, 556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500,                // n-z
            334, 260, 334, 584,                                                             // { | } ~
        ],
        [
            278, 333, 474, 556, 556, 889, 722, 238, 333, 333, 389, 584, 278, 333, 278, 278,
            556, 556, 556, 556, 556, 556, 556, 556, 556, 556,
            333, 333, 584, 584, 584, 611, 975,
            722, 722, 722, 722, 667, 611, 778, 722, 278, 556, 722, 611, 833,
            722, 778, 667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611,
            333, 278, 333, 584, 556, 333,
            556, 611, 556, 611, 556, 333, 611, 611, 278, 278, 556, 278, 889,
            611, 611, 611, 611, 389, 556, 333, 611, 556, 778, 556, 556, 500,
            389, 280, 389, 584,
        ],
    ];

    private $title;
    private $pages = [];   // finished page content streams
    private $stream = '';  // the page being written
    private $y = self::MARGIN;

    public function __construct($title) {
        $this->title = (string) $title;
    }

    /** A line of text at the cursor, moving it down. */
    public function write($text, $size = 10, $bold = false, array $rgb = self::BODY, $gapAfter = 4) {
        $height = $size * 1.25;
        $this->ensure($height);
        $this->stream .= self::textOps(self::MARGIN, $this->y + $size, self::fit($text, self::PAGE_W - 2 * self::MARGIN, $size, $bold), $size, $bold, $rgb);
        $this->y += $height + $gapAfter;
    }

    /** A section heading, kept on the same page as the start of what follows. */
    public function heading($text) {
        $this->ensure(80);
        $this->write($text, 12, true, self::MAROON, 6);
    }

    /** A row of stat boxes: [[label, value], ...]. */
    public function stats(array $items) {
        $gap = 8;
        $width = (self::PAGE_W - 2 * self::MARGIN - $gap * (count($items) - 1)) / count($items);
        $height = 44;
        $this->ensure($height);
        foreach (array_values($items) as $index => [$label, $value]) {
            $x = self::MARGIN + $index * ($width + $gap);
            $this->stream .= self::rectOps($x, $this->y, $width, $height, self::HEAD);
            $valueSize = 13; // shrink a long value (an amount) before cutting it short
            while ($valueSize > 9 && self::width($value, $valueSize, true) > $width - 16) {
                $valueSize -= 0.5;
            }
            $this->stream .= self::textOps($x + 8, $this->y + 21, self::fit($value, $width - 16, $valueSize, true), $valueSize, true, self::INK);
            $this->stream .= self::textOps($x + 8, $this->y + 35, self::fit($label, $width - 16, 8, false), 8, false, self::MUTED);
        }
        $this->y += $height + 16;
    }

    /**
     * A table. $columns: [[heading, relative width, 'L' | 'R'], ...];
     * $rows: lists of cell texts. The heading row repeats on each new page.
     */
    public function table(array $columns, array $rows, $emptyText = 'Nothing in this range.') {
        $scale = (self::PAGE_W - 2 * self::MARGIN) / array_sum(array_column($columns, 1));
        foreach ($columns as &$column) {
            $column[1] *= $scale;
        }
        unset($column);

        $rowHeight = 17;
        $this->ensure($rowHeight * 2);
        $this->tableRow($columns, array_column($columns, 0), $rowHeight, true);
        if (!$rows) {
            $this->y += 4;
            $this->write($emptyText, 9, false, self::MUTED);
        }
        foreach (array_values($rows) as $index => $cells) {
            if ($this->ensure($rowHeight)) {
                $this->tableRow($columns, array_column($columns, 0), $rowHeight, true);
            }
            $this->tableRow($columns, $cells, $rowHeight, false, $index % 2 === 1);
        }
        $this->y += 16;
    }

    /** The finished file. */
    public function output() {
        $pages = $this->pages;
        if ($this->stream !== '' || !$pages) {
            $pages[] = $this->stream;
        }
        $count = count($pages);

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
            5 => '<< /Title (' . self::escape(self::encode($this->title)) . ') /Producer (ParishServe) /CreationDate (D:' . date('YmdHis') . ') >>',
        ];
        $kids = [];
        foreach ($pages as $index => $content) {
            $footerTop = self::PAGE_H - self::MARGIN + 22;
            $pageLabel = 'Page ' . ($index + 1) . ' of ' . $count;
            $content .= self::textOps(self::MARGIN, $footerTop, $this->title, 8, false, self::MUTED)
                . self::textOps(self::PAGE_W - self::MARGIN - self::width($pageLabel, 8, false), $footerTop, $pageLabel, 8, false, self::MUTED);

            $pageObject = 6 + $index * 2;
            $kids[] = "{$pageObject} 0 R";
            $objects[$pageObject] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                self::PAGE_W, self::PAGE_H, $pageObject + 1
            );
            $objects[$pageObject + 1] = '<< /Length ' . strlen($content) . " >>\nstream\n{$content}\nendstream";
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . "] /Count {$count} >>";
        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$body}\nendobj\n";
        }
        $xref = strlen($pdf);
        $size = count($objects) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        for ($number = 1; $number < $size; $number++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$number]);
        }
        return $pdf . "trailer\n<< /Size {$size} /Root 1 0 R /Info 5 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }

    /** Starts a new page if $height doesn't fit on this one; true when it did. */
    private function ensure($height) {
        if ($this->y + $height <= self::PAGE_H - self::MARGIN) {
            return false;
        }
        $this->pages[] = $this->stream;
        $this->stream = '';
        $this->y = self::MARGIN;
        return true;
    }

    private function tableRow(array $columns, array $cells, $height, $isHead, $striped = false) {
        $width = self::PAGE_W - 2 * self::MARGIN;
        if ($isHead || $striped) {
            $this->stream .= self::rectOps(self::MARGIN, $this->y, $width, $height, $isHead ? self::HEAD : self::STRIPE);
        }
        $size = 8.5;
        $x = self::MARGIN;
        foreach (array_values($columns) as $index => [, $columnWidth, $align]) {
            $text = self::fit((string) ($cells[$index] ?? ''), $columnWidth - 8, $size, $isHead);
            $textX = $align === 'R' ? $x + $columnWidth - 4 - self::width($text, $size, $isHead) : $x + 4;
            $this->stream .= self::textOps($textX, $this->y + $height / 2 + $size * 0.35, $text, $size, $isHead, $isHead ? self::INK : self::BODY);
            $x += $columnWidth;
        }
        $this->stream .= self::lineOps(self::MARGIN, $this->y + $height, self::MARGIN + $width, self::RULE);
        $this->y += $height;
    }

    /** Text whose baseline sits $baseline points below the top of the page. */
    private static function textOps($x, $baseline, $text, $size, $bold, array $rgb) {
        return sprintf("BT %.3F %.3F %.3F rg /F%d %.2F Tf %.2F %.2F Td (%s) Tj ET\n",
            $rgb[0], $rgb[1], $rgb[2], $bold ? 2 : 1, $size, $x, self::PAGE_H - $baseline, self::escape(self::encode($text)));
    }

    private static function rectOps($x, $top, $width, $height, array $rgb) {
        return sprintf("%.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f\n", $rgb[0], $rgb[1], $rgb[2], $x, self::PAGE_H - $top - $height, $width, $height);
    }

    private static function lineOps($x1, $top, $x2, array $rgb) {
        $y = self::PAGE_H - $top;
        return sprintf("%.3F %.3F %.3F RG 0.6 w %.2F %.2F m %.2F %.2F l S\n", $rgb[0], $rgb[1], $rgb[2], $x1, $y, $x2, $y);
    }

    /** Width in points of UTF-8 $text. */
    private static function width($text, $size, $bold) {
        $encoded = self::encode($text);
        $widths = self::WIDTHS[$bold ? 1 : 0];
        $sum = 0;
        for ($i = 0, $length = strlen($encoded); $i < $length; $i++) {
            $code = ord($encoded[$i]);
            $sum += $code >= 32 && $code <= 126 ? $widths[$code - 32] : ($bold ? 611 : 556);
        }
        return $sum * $size / 1000;
    }

    /** $text, cut short with "..." if it's wider than $maxWidth. */
    private static function fit($text, $maxWidth, $size, $bold) {
        $text = (string) $text;
        if (self::width($text, $size, $bold) <= $maxWidth) {
            return $text;
        }
        while ($text !== '' && self::width($text . '...', $size, $bold) > $maxWidth) {
            $text = mb_substr($text, 0, -1, 'UTF-8');
        }
        return rtrim($text) . '...';
    }

    private static function encode($text) {
        $text = strtr((string) $text, ['₱' => 'PHP ', "\r" => '', "\n" => ' ', "\t" => ' ']);
        return mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
    }

    private static function escape($text) {
        return strtr($text, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']);
    }
}
