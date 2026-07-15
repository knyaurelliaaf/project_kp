<?php
class CrewModel extends Model {
    protected $table = 'crew';
    protected $primaryKey = 'id_crew';
    
    public function countByRig($id_rig, $isSuperAdmin) {
        if ($isSuperAdmin) {
            return $this->count();
        }
        return $this->count('id_rig', $id_rig);
    }
    
    public function getExpiredCrew($id_rig, $isSuperAdmin) {
        $sql = "SELECT DISTINCT c.nama, c.posisi, r.kode_rig,
                b.tanggal_expired as badge_exp,
                m.expired as mcu_exp,
                s.tanggal_expired as sertifikat_exp,
                s.jenis as jenis_sertifikat
                FROM crew c
                JOIN rig r ON c.id_rig = r.id_rig
                LEFT JOIN badge b ON c.id_crew = b.id_crew
                LEFT JOIN mcu m ON c.id_crew = m.id_crew
                LEFT JOIN sertifikat s ON c.id_crew = s.id_crew
                WHERE (b.tanggal_expired <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                   OR m.expired <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                   OR s.tanggal_expired <= DATE_ADD(CURDATE(), INTERVAL 30 DAY))";
        
        if (!$isSuperAdmin) {
            $sql .= " AND c.id_rig = " . intval($id_rig);
        }
        
        $sql .= " ORDER BY r.kode_rig, c.nama LIMIT 10";
        
        return $this->query($sql);
    }
}