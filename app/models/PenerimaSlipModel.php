<?php
class PenerimaSlipModel extends Model {
    protected $table = 'penerima_slip';
    protected $primaryKey = 'id_penerima';
    
    public function countAktif() {
        return $this->count('status_aktif', 'aktif');
    }
    
    public function getByNik($nik) {
        if (empty($nik)) return null;
        $result = $this->where('nik', $nik);
        return $result ? $result->fetch_assoc() : null;
    }

    public static function normalizeName($name) {
        $n = trim(strtoupper($name));
        $n = preg_replace('/^(SLIP\s*GAJI(\s*WOWS)?|SLIP|GAJI)\s+/i', '', $n);
        $n = trim(explode('-', $n)[0]);
        $n = preg_replace('/^(M|MUHD)\.?\s+/i', 'MUHAMMAD ', $n);
        $n = preg_replace('/\s+/', ' ', $n);
        return trim($n);
    }

    public function getByNama($identifier) {
        if (empty($identifier)) return null;
        $cleanIdentifier = trim($identifier);
        // Strip common prefixes like "SLIP GAJI", "SLIP GAJI WOWS", "SLIP", "GAJI"
        $cleanIdentifier = preg_replace('/^(SLIP\s*GAJI(\s*WOWS)?|SLIP|GAJI)\s+/i', '', $cleanIdentifier);
        $cleanIdentifier = preg_replace('/\s+/', ' ', trim($cleanIdentifier));

        // Extract name part before hyphen if format is "NAMA - JABATAN"
        $nameFromHyphen = trim(explode('-', $cleanIdentifier)[0]);
        $nameFromHyphen = preg_replace('/\s+/', ' ', $nameFromHyphen);

        $normHyphen = self::normalizeName($nameFromHyphen);
        $normFull = self::normalizeName($cleanIdentifier);

        $escFull = $this->db->real_escape_string($cleanIdentifier);
        $escHyphen = $this->db->real_escape_string($nameFromHyphen);
        $escNorm = $this->db->real_escape_string($normHyphen);

        // 1. Direct query matching full string, name before hyphen, or normalized M. <-> MUHAMMAD
        $res = $this->query("SELECT * FROM {$this->table} 
            WHERE LOWER(TRIM(nama)) = LOWER('{$escHyphen}') 
               OR LOWER(TRIM(nama)) = LOWER('{$escFull}') 
               OR LOWER(REPLACE(REPLACE(TRIM(nama), 'M. ', 'MUHAMMAD '), 'M ', 'MUHAMMAD ')) = LOWER('{$escNorm}')
            LIMIT 1");
        
        if ($res && $row = $res->fetch_assoc()) {
            return $row;
        }

        // 2. Flexible fallback: compare normalized names with active recipients
        $resAll = $this->getAllAktif();
        if ($resAll && $resAll->num_rows > 0) {
            $bestMatch = null;
            $bestDistance = 999;

            while ($p = $resAll->fetch_assoc()) {
                $pNorm = self::normalizeName($p['nama']);
                if (empty($pNorm)) continue;

                if ($pNorm === $normHyphen || $pNorm === $normFull || strpos($normFull, $pNorm) === 0 || strpos($pNorm, $normHyphen) === 0) {
                    return $p;
                }

                // Minor typo match (Levenshtein distance <= 2)
                $distHyphen = levenshtein(strtolower($normHyphen), strtolower($pNorm));
                $distFull = levenshtein(strtolower($normFull), strtolower($pNorm));
                $minDist = min($distHyphen, $distFull);

                if ($minDist <= 2 && $minDist < $bestDistance) {
                    $bestDistance = $minDist;
                    $bestMatch = $p;
                }
            }

            if ($bestMatch !== null) {
                return $bestMatch;
            }
        }

        return null;
    }

    public function getByNamaOrNik($identifier) {
        return $this->getByNama($identifier);
    }
    
    public function getAllAktif() {
        return $this->where('status_aktif', 'aktif');
    }

    public function getPaginated($search = null, $status = null, $limit = 10, $offset = 0, $orderBy = 'nama', $order = 'ASC', $crew = null) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        
        if (!empty($status)) {
            $sEsc = $this->db->real_escape_string($status);
            $sql .= " AND status_aktif = '{$sEsc}'";
        }
        
        if (!empty($crew)) {
            $cEsc = $this->db->real_escape_string($crew);
            $sql .= " AND crew = '{$cEsc}'";
        }
        
        if (!empty($search)) {
            $qEsc = $this->db->real_escape_string(trim($search));
            $sql .= " AND (nama LIKE '%{$qEsc}%' OR nomor_wa LIKE '%{$qEsc}%' OR kode_rig LIKE '%{$qEsc}%' OR crew LIKE '%{$qEsc}%' OR posisi LIKE '%{$qEsc}%')";
        }
        
        $allowedSort = ['id_penerima', 'nama', 'nomor_wa', 'kode_rig', 'crew', 'posisi', 'status_aktif', 'created_at'];
        $sortCol = in_array($orderBy, $allowedSort) ? $orderBy : 'nama';
        $sortOrder = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        
        $limitInt = max(1, (int)$limit);
        $offsetInt = max(0, (int)$offset);
        
        $sql .= " ORDER BY {$sortCol} {$sortOrder} LIMIT {$limitInt} OFFSET {$offsetInt}";
        return $this->query($sql);
    }

    public function countFiltered($search = null, $status = null, $crew = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        
        if (!empty($status)) {
            $sEsc = $this->db->real_escape_string($status);
            $sql .= " AND status_aktif = '{$sEsc}'";
        }
        
        if (!empty($crew)) {
            $cEsc = $this->db->real_escape_string($crew);
            $sql .= " AND crew = '{$cEsc}'";
        }
        
        if (!empty($search)) {
            $qEsc = $this->db->real_escape_string(trim($search));
            $sql .= " AND (nama LIKE '%{$qEsc}%' OR nomor_wa LIKE '%{$qEsc}%' OR kode_rig LIKE '%{$qEsc}%' OR crew LIKE '%{$qEsc}%' OR posisi LIKE '%{$qEsc}%')";
        }
        
        $res = $this->query($sql);
        return $res ? ($res->fetch_assoc()['total'] ?? 0) : 0;
    }

    public function syncWithCrewMaster() {
        $sql = "UPDATE {$this->table} p
                JOIN crew c ON LOWER(TRIM(p.nama)) = LOWER(TRIM(c.nama))
                LEFT JOIN rig r ON c.id_rig = r.id_rig
                SET p.id_rig = c.id_rig,
                    p.kode_rig = r.kode_rig,
                    p.crew = IF(p.crew IS NULL OR p.crew = '', c.crew, p.crew),
                    p.posisi = IF(p.posisi IS NULL OR p.posisi = '', c.posisi, p.posisi)";
        return $this->db->query($sql);
    }
}
