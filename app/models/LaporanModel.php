<?php
class LaporanModel extends Model
{
    /**
     * Mengambil dokumen terbaru setiap crew yang sudah melewati tanggal berlaku.
     */
    public function getExpired($rigIds, $isAllRig, $filter_rig = '', $filter_dokumen = '')
    {
        return $this->getDokumenByStatus($rigIds, $isAllRig, $filter_rig, $filter_dokumen, 'expired');
    }

    /**
     * Mengambil dokumen terbaru setiap crew yang akan berakhir dalam 1-30 hari.
     */
    public function getWarning($rigIds, $isAllRig, $filter_rig = '', $filter_dokumen = '')
    {
        return $this->getDokumenByStatus($rigIds, $isAllRig, $filter_rig, $filter_dokumen, 'warning');
    }

    private function getDokumenByStatus($rigIds, $isAllRig, $filter_rig, $filter_dokumen, $status)
    {
        $dokumen = ['badge', 'mcu', 'sertifikat', 'pkwt'];
        if (!in_array($filter_dokumen, $dokumen, true)) {
            $filter_dokumen = '';
        }

        // Kolom `expired` adalah kolom tanggal MCU yang digunakan oleh skema aplikasi saat ini.
        $queries = [
            "SELECT c.id_crew, c.nama, c.posisi, c.crew, c.id_rig, r.kode_rig,
                    'Badge' AS jenis_dok, b.tanggal_expired AS tanggal_expired,
                    DATEDIFF(b.tanggal_expired, CURDATE()) AS sisa_hari
             FROM badge b
             JOIN crew c ON c.id_crew = b.id_crew
             JOIN rig r ON r.id_rig = c.id_rig
             WHERE c.status_aktif = 'aktif' AND b.id_badge = (SELECT MAX(b2.id_badge) FROM badge b2 WHERE b2.id_crew = b.id_crew)",
            "SELECT c.id_crew, c.nama, c.posisi, c.crew, c.id_rig, r.kode_rig,
                    'MCU' AS jenis_dok, m.expired AS tanggal_expired,
                    DATEDIFF(m.expired, CURDATE()) AS sisa_hari
             FROM mcu m
             JOIN crew c ON c.id_crew = m.id_crew
             JOIN rig r ON r.id_rig = c.id_rig
             WHERE c.status_aktif = 'aktif' AND m.id_mcu = (SELECT MAX(m2.id_mcu) FROM mcu m2 WHERE m2.id_crew = m.id_crew)",
            "SELECT c.id_crew, c.nama, c.posisi, c.crew, c.id_rig, r.kode_rig,
                    'Sertifikat' AS jenis_dok, s.tanggal_expired AS tanggal_expired,
                    DATEDIFF(s.tanggal_expired, CURDATE()) AS sisa_hari
             FROM sertifikat s
             JOIN crew c ON c.id_crew = s.id_crew
             JOIN rig r ON r.id_rig = c.id_rig
             WHERE c.status_aktif = 'aktif' AND s.id_sertifikat = (SELECT MAX(s2.id_sertifikat) FROM sertifikat s2 WHERE s2.id_crew = s.id_crew)",
            "SELECT c.id_crew, c.nama, c.posisi, c.crew, c.id_rig, r.kode_rig,
                    'PKWT' AS jenis_dok, p.tanggal_berakhir AS tanggal_expired,
                    DATEDIFF(p.tanggal_berakhir, CURDATE()) AS sisa_hari
             FROM pkwt p
             JOIN crew c ON c.id_crew = p.id_crew
             JOIN rig r ON r.id_rig = c.id_rig
             WHERE c.status_aktif = 'aktif' AND p.id_pkwt = (SELECT MAX(p2.id_pkwt) FROM pkwt p2 WHERE p2.id_crew = p.id_crew)"
        ];

        $sql = 'SELECT * FROM (' . implode(' UNION ALL ', $queries) . ') AS dokumen WHERE tanggal_expired IS NOT NULL';
        $params = [];

        if ($status === 'expired') {
            $sql .= ' AND sisa_hari < 0';
        } else {
            $sql .= ' AND sisa_hari BETWEEN 1 AND 30';
        }

        if (!$isAllRig) {
            $rigIds = array_values(array_filter(array_map('intval', (array) $rigIds)));
            if (empty($rigIds)) {
                return $this->query('SELECT * FROM (SELECT 1 AS kosong) AS data WHERE 1 = 0');
            }
            $sql .= ' AND id_rig IN (' . implode(',', array_fill(0, count($rigIds), '?')) . ')';
            $params = array_merge($params, $rigIds);
        }

        if ($filter_rig !== '') {
            $sql .= ' AND kode_rig = ?';
            $params[] = $filter_rig;
        }
        if ($filter_dokumen !== '') {
            $labels = ['badge' => 'Badge', 'mcu' => 'MCU', 'sertifikat' => 'Sertifikat', 'pkwt' => 'PKWT'];
            $sql .= ' AND jenis_dok = ?';
            $params[] = $labels[$filter_dokumen];
        }

        return $this->query($sql . ' ORDER BY kode_rig ASC, jenis_dok ASC, sisa_hari ASC, nama ASC', $params);
    }
}
