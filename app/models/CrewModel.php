    <?php
    class CrewModel extends Model {
        protected $table = 'crew';
        protected $primaryKey = 'id_crew';
        
        public function countByRig($rigIds, $isAllRig) {
            if ($isAllRig) return $this->count();
            if (empty($rigIds)) return 0;
            $placeholders = implode(',', array_fill(0, count($rigIds), '?'));
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE id_rig IN ({$placeholders})";
            $result = $this->query($sql, $rigIds);
            return $result->fetch_assoc()['total'];
        }

    public function getExpiredCrew($rigIds, $isAllRig) {
        $sql = "SELECT c.nama, c.posisi, r.kode_rig,
                lb.tanggal_expired as badge_exp, lb.status as badge_status,
                lm.expired as mcu_exp, lm.status as mcu_status,
                ls.tanggal_expired as sertifikat_exp, ls.status as sertifikat_status,
                lp.tanggal_berakhir as pkwt_exp, lp.status as pkwt_status
                FROM crew c
                JOIN rig r ON c.id_rig = r.id_rig
                LEFT JOIN (
                    SELECT id_crew, tanggal_expired,
                        CASE 
                            WHEN tanggal_expired < CURDATE() THEN 'exp'
                            WHEN tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                            ELSE 'ok'
                        END as status
                    FROM badge b1
                    WHERE id_badge = (SELECT MAX(id_badge) FROM badge b2 WHERE b2.id_crew = b1.id_crew)
                ) lb ON c.id_crew = lb.id_crew
                LEFT JOIN (
                    SELECT id_crew, expired,
                        CASE 
                            WHEN expired < CURDATE() THEN 'exp'
                            WHEN expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                            ELSE 'ok'
                        END as status
                    FROM mcu m1
                    WHERE id_mcu = (SELECT MAX(id_mcu) FROM mcu m2 WHERE m2.id_crew = m1.id_crew)
                ) lm ON c.id_crew = lm.id_crew
                LEFT JOIN (
                    SELECT id_crew, tanggal_expired,
                        CASE 
                            WHEN tanggal_expired < CURDATE() THEN 'exp'
                            WHEN tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                            ELSE 'ok'
                        END as status
                    FROM sertifikat s1
                    WHERE id_sertifikat = (SELECT MAX(id_sertifikat) FROM sertifikat s2 WHERE s2.id_crew = s1.id_crew)
                ) ls ON c.id_crew = ls.id_crew
                LEFT JOIN (
                    SELECT id_crew, tanggal_berakhir,
                        CASE 
                            WHEN tanggal_berakhir < CURDATE() THEN 'exp'
                            WHEN tanggal_berakhir BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                            ELSE 'ok'
                        END as status
                    FROM pkwt p1
                    WHERE id_pkwt = (SELECT MAX(id_pkwt) FROM pkwt p2 WHERE p2.id_crew = p1.id_crew)
                ) lp ON c.id_crew = lp.id_crew
                WHERE (
                    COALESCE(lb.status, 'ok') IN ('exp', 'soon')
                    OR COALESCE(lm.status, 'ok') IN ('exp', 'soon')
                    OR COALESCE(ls.status, 'ok') IN ('exp', 'soon')
                    OR COALESCE(lp.status, 'ok') IN ('exp', 'soon')
                )";

        if (!$isAllRig && !empty($rigIds)) {
            $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        }

        $sql .= " ORDER BY r.kode_rig, c.nama LIMIT 30";
        return $this->query($sql);
    }
        
        public function getAllWithStatus($rigIds, $isAllRig) {
            return $this->getAllWithStatusPaginated($rigIds, $isAllRig, 1000, 0);
        }

        public function countAllWithStatus($rigIds, $isAllRig) {
            $sql = "SELECT COUNT(DISTINCT c.id_crew) as total
                    FROM crew c
                    JOIN rig r ON c.id_rig = r.id_rig
                    WHERE 1=1";
            
            if (!$isAllRig && !empty($rigIds)) {
                $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
            }
            
            return $this->query($sql)->fetch_assoc()['total'];
        }

        public function getAllWithStatusPaginated($rigIds, $isAllRig, $limit, $offset) {
        $sql = "SELECT c.*, r.kode_rig,
                COALESCE(lb.status, 'ok') as badge_status,
                COALESCE(lm.status, 'ok') as mcu_status,
                COALESCE(ls.status, 'ok') as sertifikat_status,
                COALESCE(lp.status, 'ok') as pkwt_status
                FROM crew c
                JOIN rig r ON c.id_rig = r.id_rig
                LEFT JOIN (
                    SELECT id_crew, 
                        CASE 
                            WHEN tanggal_expired < CURDATE() THEN 'exp'
                            WHEN tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                            ELSE 'ok'
                        END as status
                    FROM badge b1
                    WHERE id_badge = (SELECT MAX(id_badge) FROM badge b2 WHERE b2.id_crew = b1.id_crew)
                ) lb ON c.id_crew = lb.id_crew
                LEFT JOIN (
                    SELECT id_crew,
                        CASE 
                            WHEN expired < CURDATE() THEN 'exp'
                            WHEN expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                            ELSE 'ok'
                        END as status
                    FROM mcu m1
                    WHERE id_mcu = (SELECT MAX(id_mcu) FROM mcu m2 WHERE m2.id_crew = m1.id_crew)
                ) lm ON c.id_crew = lm.id_crew
                LEFT JOIN (
                    SELECT id_crew,
                        CASE 
                            WHEN tanggal_expired < CURDATE() THEN 'exp'
                            WHEN tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                            ELSE 'ok'
                        END as status
                    FROM sertifikat s1
                    WHERE id_sertifikat = (SELECT MAX(id_sertifikat) FROM sertifikat s2 WHERE s2.id_crew = s1.id_crew)
                ) ls ON c.id_crew = ls.id_crew
                LEFT JOIN (
                    SELECT id_crew,
                        CASE 
                            WHEN tanggal_berakhir < CURDATE() THEN 'exp'
                            WHEN tanggal_berakhir BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                            ELSE 'ok'
                        END as status
                    FROM pkwt p1
                    WHERE id_pkwt = (SELECT MAX(id_pkwt) FROM pkwt p2 WHERE p2.id_crew = p1.id_crew)
                ) lp ON c.id_crew = lp.id_crew
                WHERE 1=1";
            
            if (!$isAllRig && !empty($rigIds)) {
                $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
            }
            
            $sql .= " GROUP BY c.id_crew ORDER BY r.kode_rig, c.nama LIMIT {$limit} OFFSET {$offset}";
            return $this->query($sql);
        }

        public function countFiltered($rigIds, $isAllRig, $filter_rig = '', $filter_status = '', $filter_search = '', $filter_crew = '') {
            $sql = "SELECT COUNT(DISTINCT c.id_crew) as total
                    FROM crew c
                    JOIN rig r ON c.id_rig = r.id_rig
                    WHERE 1=1";
            
            if (!$isAllRig && !empty($rigIds)) {
                $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
            }
            if (!empty($filter_rig)) {
                $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
            }
            if (!empty($filter_status)) {
                $sql .= " AND c.status_aktif = '" . $this->escape($filter_status) . "'";
            }
            if (!empty($filter_search)) {
            $s = $this->escape($filter_search);
            $sql .= " AND (c.nama LIKE '%{$s}%' OR c.posisi LIKE '%{$s}%' OR c.id_crew LIKE '%{$s}%')";
            }
            if (!empty($filter_crew)) {
                $sql .= " AND c.crew = '" . $this->escape($filter_crew) . "'";
            }
            
            return $this->query($sql)->fetch_assoc()['total'];
        }

        public function getFiltered($rigIds, $isAllRig, $filter_rig = '', $filter_status = '', $filter_search = '',$filter_crew = '', $limit = 10, $offset = 0, $sort = 'nama', $order = 'ASC') {
            $sql = "SELECT c.*, r.kode_rig, c.status_aktif,
                    COALESCE(lb.status, 'ok') as badge_status,
                    COALESCE(lm.status, 'ok') as mcu_status,
                    COALESCE(ls.status, 'ok') as sertifikat_status,
                    COALESCE(lp.status, 'ok') as pkwt_status
                    FROM crew c
                    JOIN rig r ON c.id_rig = r.id_rig
                    LEFT JOIN (
                        SELECT id_crew, 
                            CASE 
                                WHEN tanggal_expired < CURDATE() THEN 'exp'
                                WHEN tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                                ELSE 'ok'
                            END as status
                        FROM badge b1
                        WHERE id_badge = (SELECT MAX(id_badge) FROM badge b2 WHERE b2.id_crew = b1.id_crew)
                    ) lb ON c.id_crew = lb.id_crew
                    LEFT JOIN (
                        SELECT id_crew,
                            CASE 
                                WHEN expired < CURDATE() THEN 'exp'
                                WHEN expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                                ELSE 'ok'
                            END as status
                        FROM mcu m1
                        WHERE id_mcu = (SELECT MAX(id_mcu) FROM mcu m2 WHERE m2.id_crew = m1.id_crew)
                    ) lm ON c.id_crew = lm.id_crew
                    LEFT JOIN (
                        SELECT id_crew,
                            CASE 
                                WHEN tanggal_expired < CURDATE() THEN 'exp'
                                WHEN tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                                ELSE 'ok'
                            END as status
                        FROM sertifikat s1
                        WHERE id_sertifikat = (SELECT MAX(id_sertifikat) FROM sertifikat s2 WHERE s2.id_crew = s1.id_crew)
                    ) ls ON c.id_crew = ls.id_crew
                    LEFT JOIN (
                        SELECT id_crew,
                            CASE 
                                WHEN tanggal_berakhir < CURDATE() THEN 'exp'
                                WHEN tanggal_berakhir BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'soon'
                                ELSE 'ok'
                            END as status
                        FROM pkwt p1
                        WHERE id_pkwt = (SELECT MAX(id_pkwt) FROM pkwt p2 WHERE p2.id_crew = p1.id_crew)
                    ) lp ON c.id_crew = lp.id_crew
                    WHERE 1=1";
            
            if (!$isAllRig && !empty($rigIds)) {
                $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
            }
            if (!empty($filter_rig)) {
                $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
            }
            if (!empty($filter_status)) {
                $sql .= " AND c.status_aktif = '" . $this->escape($filter_status) . "'";
            }
            if (!empty($filter_search)) {
            $s = $this->escape($filter_search);
            $sql .= " AND (c.nama LIKE '%{$s}%' OR c.posisi LIKE '%{$s}%' OR c.id_crew LIKE '%{$s}%')";
            }
            if (!empty($filter_crew)) {
                $sql .= " AND c.crew = '" . $this->escape($filter_crew) . "'";
            }
            
        $allowedSorts = ['nama', 'posisi', 'id_crew', 'kode_rig'];
            if (!in_array($sort, $allowedSorts)) $sort = 'nama';
            $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

            $sql .= " GROUP BY c.id_crew ORDER BY {$sort} {$order} LIMIT {$limit} OFFSET {$offset}";
            return $this->query($sql);
        }

        public function getDetail($id) {
            $sql = "SELECT c.*, r.kode_rig, r.nama_rig
                    FROM {$this->table} c
                    JOIN rig r ON c.id_rig = r.id_rig
                    WHERE c.id_crew = ?";
            return $this->query($sql, [$id])->fetch_assoc();
        }

        public function getAllActive() {
        return $this->where('status_aktif', 'aktif');
        }

        public function getByBadge($nomorBadge)
        {
            $sql = "SELECT c.* FROM crew c INNER JOIN badge b ON b.id_crew = c.id_crew WHERE b.nomor_badge = ? LIMIT 1";
            return $this->query($sql, [$nomorBadge])->fetch_assoc();
        }

        public function getByName($nama)
        {
            $sql = "SELECT * FROM crew WHERE TRIM(LOWER(nama)) =TRIM(LOWER(?)) LIMIT 1";
            return $this->query($sql,[$nama])->fetch_assoc();
        }

        
    
    }
