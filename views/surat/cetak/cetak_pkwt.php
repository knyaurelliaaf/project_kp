<?php
class CetakPkwt
{
    private string $templateDir;

    private array $templates = [
        'Accs Control' => ['file' => 'Accs Control.docx', 'sample' => 'Rangga Saputra', 'position' => 'Acces Contol', 'address' => 'Jl.Gelugur,Kel Sintong Pusaka,Kec Tanah Putih'],
        'Asst Derrickman' => ['file' => 'Asst Derrickman.docx', 'sample' => 'Safri Darwis Hasibuan', 'position' => 'Asst Derrickman', 'address' => 'Dusun Balam Utara,Bangko Bakti,Bangko Pusako'],
        'Asst Driller' => ['file' => 'Asst Driller.docx', 'sample' => 'Saipudin Pulungan', 'position' => 'Asst Driller', 'address' => ''],
        'Derrickman' => ['file' => 'Derrickman.docx', 'sample' => 'Yudi Agus Triwarman', 'position' => 'Derrickman', 'address' => ''],
        'Electric' => ['file' => 'Electric.docx', 'sample' => 'Nofri Al Sepri', 'position' => 'Electric', 'address' => ''],
        'Floorman' => ['file' => 'Floorman.docx', 'sample' => 'Harist Joyfull Hartman', 'position' => 'Floorman', 'address' => ''],
        'Mechanic' => ['file' => 'Mechanic.docx', 'sample' => 'Bayu Ardigo', 'position' => 'Mechanic', 'address' => ''],
        'Motorman' => ['file' => 'Motorman.docx', 'sample' => 'Abdullah', 'position' => 'Motorman', 'address' => ''],
        'Mudboy' => ['file' => 'Mudboy.docx', 'sample' => 'Hengki Tarnando', 'position' => 'Mudboy', 'address' => ''],
        'Room boy' => ['file' => 'Room boy.docx', 'sample' => 'Andre', 'position' => 'Roomboy', 'address' => ''],
        'Roustabout' => ['file' => 'Roustabout.docx', 'sample' => 'Awaldi Syukra', 'position' => 'Roustabout', 'address' => ''],
        'Teknisi Crane' => ['file' => 'Teknisi Crane.docx', 'sample' => 'Muhammad Hafiz', 'position' => 'Teknisi Crane', 'address' => ''],
        'Welder' => ['file' => 'Welder.docx', 'sample' => 'Pahrudin Ritonga', 'position' => 'Welder', 'address' => ''],
    ];

    private array $aliases = [
        'access control' => 'Accs Control',
        'accs control' => 'Accs Control',
        'access' => 'Accs Control',
        'assist derrickman' => 'Asst Derrickman',
        'assistant derrickman' => 'Asst Derrickman',
        'asst derrickman' => 'Asst Derrickman',
        'assist driller' => 'Asst Driller',
        'assistant driller' => 'Asst Driller',
        'asst driller' => 'Asst Driller',
        'derrickman' => 'Derrickman',
        'electrician' => 'Electric',
        'electric' => 'Electric',
        'floorman' => 'Floorman',
        'mechanic' => 'Mechanic',
        'mekanik' => 'Mechanic',
        'motorman' => 'Motorman',
        'mudboy' => 'Mudboy',
        'roomboy' => 'Room boy',
        'room boy' => 'Room boy',
        'roustabout' => 'Roustabout',
        'teknisi crane' => 'Teknisi Crane',
        'crane technician' => 'Teknisi Crane',
        'welder' => 'Welder',
        'juru las' => 'Welder'
    ];

    public function __construct()
    {
        $this->templateDir = dirname(__DIR__, 3) . '/data/pkwt_templates';
    }

    public function templateNames(): array
    {
        return array_keys($this->templates);
    }

    public function resolveTemplateKey(string $position): ?string
    {
        $trimPos = trim($position);
        if ($trimPos !== '' && isset($this->templates[$trimPos])) return $trimPos;

        $lowerPos = strtolower($trimPos);
        if (isset($this->aliases[$lowerPos])) return $this->aliases[$lowerPos];

        foreach ($this->templates as $key => $val) {
            if (strcasecmp($key, $trimPos) === 0) return $key;
        }

        foreach ($this->templates as $key => $val) {
            if ($lowerPos !== '' && (stripos($lowerPos, strtolower($key)) !== false || stripos(strtolower($key), $lowerPos) !== false)) {
                return $key;
            }
        }

        // Fallback default template if position is not in list
        return 'Floorman';
    }

    public function generate(string $position, array $crew, array $meta, string $output, array $rig = []): bool
    {
        $matchedKey = $this->resolveTemplateKey($position);
        if (!$matchedKey || !isset($this->templates[$matchedKey])) return false;
        $template = $this->templates[$matchedKey];
        $source = $this->templateDir . '/' . $template['file'];
        if (!is_file($source) || !copy($source, $output)) return false;

        $zip = new ZipArchive();
        if ($zip->open($output) !== true) return false;
        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) { $zip->close(); return false; }
        
        // Kurangi margin atas halaman agar kop surat lebih naik ke atas
        // 567 twips = sekitar 1 cm, 360 twips = sekitar 0.6 cm
        $xml = preg_replace('/(<w:pgMar[^>]*?w:top=")\d+(")/i', '${1}567${2}', $xml);
        $xml = preg_replace('/(<w:pgMar[^>]*?w:header=")\d+(")/i', '${1}360${2}', $xml);

        $xmlText = $this->plainText($xml);

        // Sebagian template tidak memiliki alamat contoh di konfigurasi.
        // Ambil nilai alamat PIHAK KEDUA dari template sebelum diganti.
        $oldAddress = '';
        $namePattern = preg_quote($template['sample'], '/');
        if (preg_match('/2\s*\.?\s*Nama\s*:?\s*' . $namePattern . '.*?Alamat\s*:?\s*(.*?)\s*Bertindak\s+untuk/isu', $xmlText, $match)) {
            $oldAddr = trim($match[1]);
            // Pencegahan agar label 'Nama' atau 'Alamat' tidak terekstrak sebagai address
            if (strlen($oldAddr) > 3 && strtolower($oldAddr) !== 'alamat' && strtolower($oldAddr) !== 'nama' && strtolower($oldAddr) !== 'alamat lengkap') {
                $oldAddress = $oldAddr;
            }
        }

        // Replace employee name
        $this->replaceText($xml, $template['sample'], trim((string)($crew['nama'] ?? '')));
        
        // Replace position
        $this->replaceText($xml, $template['position'], trim((string)($crew['posisi'] ?? $position)));
        
        // Replace address PIHAK KEDUA di semua template.
        $alamatCrew = trim((string) ($crew['alamat'] ?? ''));
        if ($oldAddress !== '' && $alamatCrew !== '') {
            $this->replaceText($xml, $oldAddress, $alamatCrew);
        } elseif ($template['address'] !== '' && $alamatCrew !== '') {
            $this->replaceText($xml, $template['address'], $alamatCrew);
        }

        // Replace letter number
        if (!empty($meta['nomor_surat'])) {
            $this->replaceText($xml, $this->findNumber($xml), $meta['nomor_surat']);
        }

        // Replace letter creation date (tanggal surat dibuat)
        if (!empty($meta['tanggal_surat'])) {
            $tanggalSurat = $this->formatDate($meta['tanggal_surat']);
            $this->replaceDatesForSignatureAndIntegrity($xml, $tanggalSurat);
        }

        // Replace PKWT period dates
        $start = $this->formatDate($meta['tanggal_mulai'] ?? date('Y-m-d'));
        $end = $this->formatDate($meta['tanggal_berakhir'] ?? date('Y-m-d'));
        $xmlText = $this->plainText($xml);
        if (preg_match('/terhitung mulai tanggal\s+(.+?)\s+s\/d\s+([^\.]+)\./i', $xmlText, $m)) {
            $this->replaceText($xml, trim($m[1]), $start);
            $this->replaceText($xml, trim($m[2]), $end);
        }

        // Isi Akta Integritas tanpa mengubah label "Nama"/"Alamat" pada
        // identitas PIHAK KEDUA di halaman pertama.
        $this->replaceEmptyIntegrityField($xml, 'Nama Lengkap', trim((string) ($crew['nama'] ?? '')));
        $this->replaceEmptyIntegrityField($xml, 'Jabatan / Bagian', trim((string) ($crew['posisi'] ?? $position)));
        $this->replaceEmptyIntegrityField($xml, 'Alamat Lengkap', $alamatCrew);

        // Replace signature name with employee name
        if (!empty($crew['nama'])) {
            $this->replaceText($xml, 'Hafis', $crew['nama']);
        }

        $zip->addFromString('word/document.xml', $xml);
        $zip->close();
        return true;
    }

    private function findNumber(string $xml): string
    {
        return preg_match('/ADK\/(?:RIG-)?[A-Z0-9-]+\/PKWT\/[0-9]+\/[0-9A-Z]+\/[0-9]{4}/i', $this->plainText($xml), $m) ? $m[0] : '';
    }

    private function formatDate(string $date): string
    {
        $months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $time = strtotime($date) ?: time();
        return date('d', $time) . ' ' . $months[(int)date('n', $time)] . ' ' . date('Y', $time);
    }

    private function replaceEmptyIntegrityField(string &$xml, string $label, string $value): void
    {
        if ($value === '') return;
        $plain = $this->plainText($xml);
        // Cari label beserta titik dua, spasi kosong, dan underscore
        if (preg_match('/' . preg_quote($label, '/') . '\s*:[ _\s]*/isu', $plain, $m)) {
            $exactMatch = $m[0];
            
            $pos = mb_strpos($plain, $exactMatch, 0, 'UTF-8');
            if ($pos !== false) {
                $afterMatch = ltrim(mb_substr($plain, $pos + mb_strlen($exactMatch, 'UTF-8'), 50, 'UTF-8'));
                
                // Jika sudah diawali dengan value yang akan diisi (case-insensitive)
                if (mb_stripos($afterMatch, $value, 0, 'UTF-8') === 0) {
                    return; // Hindari duplikasi / double print
                }

                // Jika sudah terisi teks lain (bukan label selanjutnya)
                if ($afterMatch !== '' && preg_match('/^[\p{L}\p{N}]/u', $afterMatch)) {
                    $isNextLabel = preg_match('/^(Nama|Jabatan|Alamat|Bertindak|Menerangkan|Menyatakan|Dengan ini)/isu', $afterMatch);
                    if (!$isNextLabel) {
                        return; // Field ini sudah terisi dari template, tidak perlu di-replace
                    }
                }
            }

            // Jika kosong, baru kita replace
            $this->replaceText($xml, $exactMatch, $label . ': ' . $value . ' ');
        }
    }

    private function replaceDatesForSignatureAndIntegrity(string &$xml, string $tanggalSurat): void
    {
        $text = $this->plainText($xml);
        // Cari tanggal di bagian "Pada tanggal: ..."
        if (preg_match('/Pada\s+tanggal\s*:?\s*([0-9_]{1,2}\s+[[:alpha:]_]+\s+[0-9_]{4}|\d{1,2}\s+[[:alpha:]]+\s+\d{4})/iu', $text, $match)) {
            $this->replaceText($xml, $match[1], $tanggalSurat);
        }

        $text = $this->plainText($xml);
        // Cari tanggal di bagian "Duri, ..."
        if (preg_match('/Duri,\s*([0-9_]{1,2}\s+[[:alpha:]_]+\s+[0-9_]{4}|\d{1,2}\s+[[:alpha:]]+\s+\d{4})/iu', $text, $match)) {
            $this->replaceText($xml, $match[1], $tanggalSurat);
        }
    }

    private function plainText(string $xml): string
    {
        preg_match_all('/<w:t(?: [^>]*)?>(.*?)<\/w:t>/s', $xml, $m);
        return html_entity_decode(implode('', $m[1] ?? []), ENT_XML1, 'UTF-8');
    }

    private function replaceText(string &$xml, string $search, string $replacement): void
    {
        if ($search === '') return;
        $pattern = '/(<w:t(?: [^>]*)?>)(.*?)(<\/w:t>)/s';
        preg_match_all($pattern, $xml, $matches, PREG_OFFSET_CAPTURE);
        $tokens = $matches[2] ?? [];
        $plain = '';
        $ranges = [];
        foreach ($tokens as $i => $token) {
            $value = html_entity_decode($token[0], ENT_XML1, 'UTF-8');
            $start = mb_strlen($plain, 'UTF-8');
            $plain .= $value;
            $ranges[$i] = [$start, mb_strlen($plain, 'UTF-8')];
        }

        $positions = [];
        $offset = 0;
        while (($pos = mb_strpos($plain, $search, $offset, 'UTF-8')) !== false) {
            $positions[] = $pos;
            $offset = $pos + mb_strlen($search, 'UTF-8');
        }

        if (empty($positions)) return;

        foreach (array_reverse($positions) as $at) {
            $end = $at + mb_strlen($search, 'UTF-8');
            $first = $last = null;
            foreach ($ranges as $i => [$a, $b]) {
                if ($first === null && $at < $b) $first = $i;
                if ($at < $b && $end > $a) $last = $i;
            }
            if ($first === null || $last === null) continue;
            for ($i = $last; $i >= $first; $i--) {
                $old = html_entity_decode($tokens[$i][0], ENT_XML1, 'UTF-8');
                $prefix = $i === $first ? mb_substr($old, 0, $at - $ranges[$i][0], 'UTF-8') : '';
                $suffix = $i === $last ? mb_substr($old, $end - $ranges[$i][0], null, 'UTF-8') : '';
                $new = $prefix . ($i === $first ? $replacement : '') . $suffix;
                $encoded = htmlspecialchars($new, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml = substr_replace($xml, $encoded, $tokens[$i][1], strlen($tokens[$i][0]));
            }
        }
    }
}
