<?php
class KirimLogModel extends Model {
    protected $table = 'kirim_log';
    protected $primaryKey = 'id_log';
    
    public function getStatus($periode = null, $status = null, $search = null) {
        return $this->getStatusPaginated($periode, $status, $search, null, 0);
    }

    public function getStatusPaginated($periode = null, $status = null, $search = null, $limit = 10, $offset = 0) {
        $sql = "SELECT kl.*, p.nama, p.nomor_wa, p.kode_rig, p.crew, p.posisi 
                FROM {$this->table} kl
                JOIN penerima_slip p ON kl.id_penerima = p.id_penerima
                WHERE 1=1";
        
        if (!empty($periode)) {
            $pEsc = $this->db->real_escape_string($periode);
            $sql .= " AND kl.periode = '{$pEsc}'";
        }
        if (!empty($status)) {
            $sEsc = $this->db->real_escape_string($status);
            $sql .= " AND kl.status = '{$sEsc}'";
        }
        if (!empty($search)) {
            $qEsc = $this->db->real_escape_string(trim($search));
            $sql .= " AND (p.nama LIKE '%{$qEsc}%' OR p.nomor_wa LIKE '%{$qEsc}%' OR p.kode_rig LIKE '%{$qEsc}%' OR p.crew LIKE '%{$qEsc}%' OR p.posisi LIKE '%{$qEsc}%' OR kl.nama_file LIKE '%{$qEsc}%')";
        }

        $sql .= " ORDER BY kl.created_at DESC, kl.id_log DESC";

        if ($limit !== null) {
            $limitInt = max(1, (int)$limit);
            $offsetInt = max(0, (int)$offset);
            $sql .= " LIMIT {$limitInt} OFFSET {$offsetInt}";
        }

        return $this->query($sql);
    }

    public function countStatusFiltered($periode = null, $status = null, $search = null) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} kl
                JOIN penerima_slip p ON kl.id_penerima = p.id_penerima
                WHERE 1=1";
        
        if (!empty($periode)) {
            $pEsc = $this->db->real_escape_string($periode);
            $sql .= " AND kl.periode = '{$pEsc}'";
        }
        if (!empty($status)) {
            $sEsc = $this->db->real_escape_string($status);
            $sql .= " AND kl.status = '{$sEsc}'";
        }
        if (!empty($search)) {
            $qEsc = $this->db->real_escape_string(trim($search));
            $sql .= " AND (p.nama LIKE '%{$qEsc}%' OR p.nomor_wa LIKE '%{$qEsc}%' OR p.kode_rig LIKE '%{$qEsc}%' OR p.crew LIKE '%{$qEsc}%' OR p.posisi LIKE '%{$qEsc}%' OR kl.nama_file LIKE '%{$qEsc}%')";
        }

        $res = $this->query($sql);
        return $res ? ($res->fetch_assoc()['total'] ?? 0) : 0;
    }

    public function getDaftarPeriodeLog() {
        $sql = "SELECT DISTINCT periode FROM {$this->table} ORDER BY periode DESC";
        $res = $this->query($sql);
        $periodes = [];
        if ($res && $res->num_rows > 0) {
            while ($r = $res->fetch_assoc()) {
                $periodes[] = $r['periode'];
            }
        }
        return $periodes;
    }
    
    public function getPending($limit = 5) {
        $limitInt = (int)$limit;
        $sql = "SELECT kl.*, p.nama, p.nomor_wa 
                FROM {$this->table} kl
                JOIN penerima_slip p ON kl.id_penerima = p.id_penerima
                WHERE kl.status = 'pending'
                ORDER BY kl.id_log ASC
                LIMIT {$limitInt}";
        return $this->query($sql);
    }
    
    public function countByStatus($status) {
        $statusEsc = $this->db->real_escape_string($status);
        $result = $this->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = '{$statusEsc}'");
        return $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
    }

    public function updateStatusByNik($nik, $periode, $status, $error = null) {
        return $this->updateStatusByIdentifier($nik, $periode, $status, $error);
    }

    public function updateStatusByIdentifier($identifier, $periode, $status, $error = null) {
        $identTrim = trim($identifier);
        
        // Strip common prefixes like "SLIP GAJI", "SLIP GAJI WOWS", "SLIP", "GAJI"
        $cleanIdentifier = preg_replace('/^(SLIP\s*GAJI(\s*WOWS)?|SLIP|GAJI)\s+/i', '', $identTrim);
        $cleanIdentifier = preg_replace('/\s+/', ' ', trim($cleanIdentifier));

        // Extract name before hyphen if format is "NAMA - JABATAN"
        $nameFromHyphen = trim(explode('-', $cleanIdentifier)[0]);
        $nameFromHyphen = preg_replace('/\s+/', ' ', $nameFromHyphen);

        $identEsc = $this->db->real_escape_string($identTrim);
        $cleanEsc = $this->db->real_escape_string($cleanIdentifier);
        $nameHyphenEsc = $this->db->real_escape_string($nameFromHyphen);
        
        $periodeEsc = $this->db->real_escape_string($periode);
        $statusEsc = $this->db->real_escape_string($status);
        $errorSql = ($error !== null && $error !== '') ? "'" . $this->db->real_escape_string($error) . "'" : "NULL";
        
        $sql = "UPDATE {$this->table} kl
                JOIN penerima_slip p ON kl.id_penerima = p.id_penerima
                SET kl.status = '{$statusEsc}', 
                    kl.pesan_error = {$errorSql},
                    kl.dikirim_pada = NOW()
                WHERE (
                    LOWER(TRIM(p.nama)) = LOWER('{$nameHyphenEsc}')
                 OR LOWER(TRIM(p.nama)) = LOWER('{$cleanEsc}')
                 OR LOWER(TRIM(p.nama)) = LOWER('{$identEsc}')
                 OR kl.nama_file = '{$identEsc}.pdf'
                 OR kl.nama_file = '{$identEsc}'
                 OR kl.nama_file = '{$cleanEsc}.pdf'
                 OR kl.nama_file = '{$cleanEsc}'
                )";
        
        if (!empty($periodeEsc)) {
            $sql .= " AND kl.periode = '{$periodeEsc}'";
        }
        
        return $this->db->query($sql);
    }
}
