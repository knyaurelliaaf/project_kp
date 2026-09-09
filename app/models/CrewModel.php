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

        public function getActiveCrewDetailed($rigIds, $isAllRig, $filterRig = '')
        {
            $sql = "SELECT c.*, r.kode_rig, r.nama_rig,
                    (SELECT b.nomor_badge FROM badge b WHERE b.id_crew = c.id_crew ORDER BY b.id_badge DESC LIMIT 1) as nomor_badge
                    FROM {$this->table} c
                    JOIN rig r ON c.id_rig = r.id_rig
                    WHERE c.status_aktif = 'aktif'";

            if (!$isAllRig && !empty($rigIds)) {
                $sql .= " AND c.id_rig IN (" . implode(',', array_map('intval', $rigIds)) . ")";
            }
            if (!empty($filterRig)) {
                $sql .= " AND r.kode_rig = '" . $this->escape($filterRig) . "'";
            }

            $sql .= " ORDER BY r.kode_rig ASC, c.nama ASC";
            return $this->query($sql);
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

        public function getTurnOverData($rigIds, $isAllRig, $periode, $tahun, $bulan)
        {
            $whereRig = '';
            if (!$isAllRig && !empty($rigIds)) {
                $whereRig = " AND c.id_rig IN (" . implode(',', array_map('intval', $rigIds)) . ")";
            }
            
            // Tentukan range tanggal berdasarkan periode
            if ($periode == 'bulan') {
                $dateStart = "$tahun-$bulan-01";
                $dateEnd = date('Y-m-t', strtotime($dateStart));
            } elseif ($periode == 'semester') {
                $semester = ($bulan <= 6) ? 1 : 2;
                $dateStart = ($semester == 1) ? "$tahun-01-01" : "$tahun-07-01";
                $dateEnd = ($semester == 1) ? "$tahun-06-30" : "$tahun-12-31";
            } else { // tahun
                $dateStart = "$tahun-01-01";
                $dateEnd = "$tahun-12-31";
            }

            // Crew keluar
            $sqlKeluar = "SELECT c.*, r.kode_rig 
                  FROM {$this->table} c 
                  JOIN rig r ON c.id_rig = r.id_rig 
                  WHERE c.status_aktif = 'nonaktif' 
                  AND c.tanggal_nonaktif BETWEEN '$dateStart' AND '$dateEnd'
                  $whereRig
                  ORDER BY c.tanggal_nonaktif DESC";

            $keluar = $this->query($sqlKeluar);

            // Hitung total
            $totalKeluar = $keluar->num_rows;

            // Crew masuk — dari SPK yang terbit di periode ini
            $sqlMasuk = "SELECT MIN(s.tanggal_moc) as tanggal_masuk, c.nama,
                         COALESCE(c.posisi, '') as posisi,
                         COALESCE(r.kode_rig, '') as kode_rig,
                         COALESCE(c.crew, '') as crew,
                         c.id_crew
                  FROM crew c
                  JOIN surat s ON TRIM(LOWER(s.keterangan)) = TRIM(LOWER(c.nama))
                  JOIN jenis_surat j ON s.id_jenis = j.id_jenis
                  JOIN rig r ON c.id_rig = r.id_rig
                  WHERE c.status_aktif = 'aktif'
                  AND TRIM(COALESCE(s.keterangan, '')) <> ''
                  AND j.kode = 'SPK'
                  AND s.tanggal_moc BETWEEN '$dateStart' AND '$dateEnd'
                  $whereRig
                  GROUP BY c.id_crew, c.nama, c.posisi, r.kode_rig, c.crew
                  ORDER BY tanggal_masuk DESC";
            $masukResult = $this->query($sqlMasuk);

            return [
                'keluar' => $keluar,
                'masuk' => $masukResult,
                'total_keluar' => $totalKeluar,
                'total_masuk' => $masukResult->num_rows,
                'date_start' => $dateStart,
                'date_end' => $dateEnd
            ];
        }

        public function getTurnOverChart($rigIds, $isAllRig, $tahun, $periode = 'tahun', $bulan = '01')
        {
            $labels = [];
            $dataMasuk = [];
            $dataKeluar = [];
            
            $whereRig = '';
            if (!$isAllRig && !empty($rigIds)) {
                $whereRig = " AND c.id_rig IN (" . implode(',', array_map('intval', $rigIds)) . ")";
            }

            if ($periode == 'bulan') {
                $numDays = cal_days_in_month(CAL_GREGORIAN, (int)$bulan, (int)$tahun);
                for ($d = 1; $d <= $numDays; $d++) {
                    $dayStr = str_pad($d, 2, '0', STR_PAD_LEFT);
                    $date = "$tahun-$bulan-$dayStr";
                    $labels[] = $d;

                    $sqlK = "SELECT COUNT(*) as total FROM crew c WHERE c.status_aktif = 'nonaktif' AND c.tanggal_nonaktif = '$date' $whereRig";
                    $resK = $this->query($sqlK)->fetch_assoc();
                    $dataKeluar[] = (int)$resK['total'];

                    $sqlM = "SELECT COUNT(DISTINCT c.id_crew) as total FROM crew c 
                             JOIN surat s ON TRIM(LOWER(s.keterangan)) = TRIM(LOWER(c.nama))
                             JOIN jenis_surat j ON s.id_jenis = j.id_jenis
                             WHERE j.kode = 'SPK' AND s.tanggal_moc = '$date' $whereRig";
                    $resM = $this->query($sqlM)->fetch_assoc();
                    $dataMasuk[] = (int)$resM['total'];
                }
            } else {
                $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                $startM = 1; $endM = 12;
                if ($periode == 'semester') {
                    $semester = ($bulan <= 6) ? 1 : 2;
                    $startM = ($semester == 1) ? 1 : 7;
                    $endM = ($semester == 1) ? 6 : 12;
                }

                for ($m = $startM; $m <= $endM; $m++) {
                    $mStr = str_pad($m, 2, '0', STR_PAD_LEFT);
                    $labels[] = $namaBulan[$m - 1];

                    $dateStart = "$tahun-$mStr-01";
                    $dateEnd = date('Y-m-t', strtotime($dateStart));

                    $sqlK = "SELECT COUNT(*) as total FROM crew c WHERE c.status_aktif = 'nonaktif' AND c.tanggal_nonaktif BETWEEN '$dateStart' AND '$dateEnd' $whereRig";
                    $resK = $this->query($sqlK)->fetch_assoc();
                    $dataKeluar[] = (int)$resK['total'];

                    $sqlM = "SELECT COUNT(DISTINCT c.id_crew) as total FROM crew c 
                             JOIN surat s ON TRIM(LOWER(s.keterangan)) = TRIM(LOWER(c.nama))
                             JOIN jenis_surat j ON s.id_jenis = j.id_jenis
                             WHERE j.kode = 'SPK' AND s.tanggal_moc BETWEEN '$dateStart' AND '$dateEnd' $whereRig";
                    $resM = $this->query($sqlM)->fetch_assoc();
                    $dataMasuk[] = (int)$resM['total'];
                }
            }

            return [
                'labels' => $labels,
                'masuk' => $dataMasuk,
                'keluar' => $dataKeluar
            ];
        }

        public function getComplianceSummary($rigIds, $isAllRig)
        {
            $sql = "SELECT 
                    COUNT(CASE WHEN max_status = 'ok' THEN 1 END) as compliant,
                    COUNT(CASE WHEN max_status = 'soon' THEN 1 END) as warning,
                    COUNT(CASE WHEN max_status = 'exp' THEN 1 END) as non_compliant
                FROM (
                    SELECT c.id_crew,
                        CASE 
                            WHEN COALESCE(lb.status, 'ok') = 'exp' OR COALESCE(lm.status, 'ok') = 'exp' OR COALESCE(ls.status, 'ok') = 'exp' OR COALESCE(lp.status, 'ok') = 'exp' THEN 'exp'
                            WHEN COALESCE(lb.status, 'ok') = 'soon' OR COALESCE(lm.status, 'ok') = 'soon' OR COALESCE(ls.status, 'ok') = 'soon' OR COALESCE(lp.status, 'ok') = 'soon' THEN 'soon'
                            ELSE 'ok'
                        END as max_status
                    FROM crew c
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
                    WHERE c.status_aktif = 'aktif'";

            if (!$isAllRig && !empty($rigIds)) {
                $sql .= " AND c.id_rig IN (" . implode(',', array_map('intval', $rigIds)) . ")";
            }

            $sql .= ") as crew_summary";

            return $this->query($sql)->fetch_assoc();
        }
    }
