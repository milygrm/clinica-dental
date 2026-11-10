<?php
/**
 * Generador de PDF mínimo en PHP puro (sin librerías externas),
 * pensado para hostings gratuitos. Usa las fuentes estándar
 * Helvetica / Helvetica-Bold y la codificación WinAnsi (tildes y ñ).
 * Coordenadas en puntos, origen arriba-izquierda. Página A4.
 */
class SimplePDF
{
    public float $w = 595.28;
    public float $h = 841.89;
    private array $pages = [];
    private int $cur = -1;

    // Anchos Helvetica (1/1000 em) para ASCII 32..126
    private const W_REG = [278,278,355,556,556,889,667,191,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,278,278,584,584,584,556,1015,667,667,722,722,667,611,778,722,278,500,667,556,833,722,778,667,778,722,667,611,722,667,944,667,667,611,278,278,278,469,556,333,556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,556,556,333,500,278,556,500,722,500,500,500,334,260,334,584];
    private const W_BOLD = [278,333,474,556,556,889,722,238,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,333,333,584,584,584,611,975,722,722,722,722,667,611,778,722,278,556,722,611,833,722,778,667,778,722,667,611,722,667,944,667,667,611,333,278,333,584,556,333,556,611,556,611,556,333,611,611,278,278,556,278,889,611,611,611,611,389,556,333,611,556,778,556,556,500,389,280,389,584];

    public function addPage(): void
    {
        $this->pages[] = '';
        $this->cur = count($this->pages) - 1;
    }

    public function pageCount(): int { return count($this->pages); }
    public function currentPage(): int { return $this->cur; }
    public function setPage(int $i): void { $this->cur = $i; }

    private function enc(string $s): string
    {
        $r = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $s);
        return $r === false ? $s : $r;
    }

    private function esc(string $s): string
    {
        return str_replace(['\\', '(', ')', "\r"], ['\\\\', '\\(', '\\)', ''], $s);
    }

    private function charW(string $ch, bool $bold): int
    {
        $o = ord($ch);
        if ($o >= 32 && $o <= 126) return ($bold ? self::W_BOLD : self::W_REG)[$o - 32];
        // Letras acentuadas CP1252 -> ancho de la letra base
        static $map = null;
        if ($map === null) {
            $map = [];
            $pares = ['ÁÀÂÄÃ' => 'A', 'áàâäã' => 'a', 'ÉÈÊË' => 'E', 'éèêë' => 'e', 'ÍÌÎÏ' => 'I', 'íìîï' => 'i',
                      'ÓÒÔÖÕ' => 'O', 'óòôöõ' => 'o', 'ÚÙÛÜ' => 'U', 'úùûü' => 'u', 'Ñ' => 'N', 'ñ' => 'n', 'Ç' => 'C', 'ç' => 'c'];
            foreach ($pares as $acc => $base) {
                foreach (preg_split('//u', $acc, -1, PREG_SPLIT_NO_EMPTY) as $a) {
                    $c = @iconv('UTF-8', 'CP1252', $a);
                    if ($c !== false) $map[ord($c)] = $base;
                }
            }
        }
        if (isset($map[$o])) return $this->charW($map[$o], $bold);
        return 556;
    }

    /** Ancho de un texto UTF-8 en puntos */
    public function width(string $s, float $size, bool $bold = false): float
    {
        $s = $this->enc($s);
        $t = 0;
        for ($i = 0, $n = strlen($s); $i < $n; $i++) $t += $this->charW($s[$i], $bold);
        return $t * $size / 1000;
    }

    /** Divide el texto en líneas que quepan en $maxW */
    public function wrap(string $s, float $maxW, float $size, bool $bold = false): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', trim($s)) as $parrafo) {
            $linea = '';
            foreach (preg_split('/\s+/', $parrafo, -1, PREG_SPLIT_NO_EMPTY) as $pal) {
                $prueba = $linea === '' ? $pal : $linea . ' ' . $pal;
                if ($this->width($prueba, $size, $bold) <= $maxW) { $linea = $prueba; continue; }
                if ($linea !== '') $out[] = $linea;
                // palabra más larga que la línea: partirla
                while ($this->width($pal, $size, $bold) > $maxW && mb_strlen($pal) > 1) {
                    $k = mb_strlen($pal);
                    while ($k > 1 && $this->width(mb_substr($pal, 0, $k), $size, $bold) > $maxW) $k--;
                    $out[] = mb_substr($pal, 0, $k);
                    $pal = mb_substr($pal, $k);
                }
                $linea = $pal;
            }
            $out[] = $linea;
        }
        return $out ?: [''];
    }

    private function add(string $op): void { $this->pages[$this->cur] .= $op . "\n"; }
    private function f(float $n): string { return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.') ?: '0'; }

    private function color(array $rgb, bool $fill): string
    {
        return sprintf('%.3f %.3f %.3f %s', $rgb[0] / 255, $rgb[1] / 255, $rgb[2] / 255, $fill ? 'rg' : 'RG');
    }

    /** Texto con la línea base en $y (desde arriba) */
    public function text(float $x, float $y, string $s, float $size = 10, bool $bold = false, array $rgb = [0, 0, 0], string $align = 'L', float $boxW = 0): void
    {
        if ($s === '') return;
        if ($align !== 'L' && $boxW > 0) {
            $tw = $this->width($s, $size, $bold);
            $x += $align === 'R' ? $boxW - $tw : ($boxW - $tw) / 2;
        }
        $this->add($this->color($rgb, true));
        $this->add(sprintf('BT /%s %s Tf %s %s Td (%s) Tj ET', $bold ? 'F2' : 'F1', $this->f($size), $this->f($x), $this->f($this->h - $y), $this->esc($this->enc($s))));
    }

    public function rect(float $x, float $y, float $w, float $h, ?array $fill = null, ?array $stroke = null, float $lw = 0.5): void
    {
        $op = '';
        if ($fill)   { $this->add($this->color($fill, true));    $op = 'f'; }
        if ($stroke) { $this->add($this->color($stroke, false)); $this->add($this->f($lw) . ' w'); $op = $fill ? 'B' : 'S'; }
        if ($op === '') return;
        $this->add(sprintf('%s %s %s %s re %s', $this->f($x), $this->f($this->h - $y - $h), $this->f($w), $this->f($h), $op));
    }

    public function line(float $x1, float $y1, float $x2, float $y2, array $rgb = [200, 200, 200], float $lw = 0.5): void
    {
        $this->add($this->color($rgb, false));
        $this->add($this->f($lw) . ' w');
        $this->add(sprintf('%s %s m %s %s l S', $this->f($x1), $this->f($this->h - $y1), $this->f($x2), $this->f($this->h - $y2)));
    }

    public function output(): string
    {
        $n = count($this->pages);
        $objs = [];
        $objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $kids = [];
        for ($i = 0; $i < $n; $i++) $kids[] = (5 + $i * 2) . ' 0 R';
        $objs[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $n . ' >>';
        $objs[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objs[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        foreach ($this->pages as $i => $stream) {
            $pid = 5 + $i * 2; $cid = $pid + 1;
            $objs[$pid] = sprintf('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %s %s] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>', $this->f($this->w), $this->f($this->h), $cid);
            $objs[$cid] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
        }
        ksort($objs);
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objs as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= "$num 0 obj\n$body\nendobj\n";
        }
        $xref = strlen($pdf);
        $max = max(array_keys($objs));
        $pdf .= "xref\n0 " . ($max + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= $max; $i++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        $pdf .= "trailer\n<< /Size " . ($max + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        return $pdf;
    }
}
