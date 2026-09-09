<?php
class SertifikatModel extends Model
{
    protected $table = 'sertifikat';
    protected $primaryKey = 'id_sertifikat';

    public function getMonitoring($rigIds, $isAllRig, $filter_rig = '', $filter_status = '', $filter_search = '', $limit = 15, $offset = 0)
    {
        $sql = "SELECT s.*, c.nama, c.posisi, r.kode_rig,
            DATEDIFF(s.tanggal_expired, CURDATE()) as sisa_hari
            FROM sertifikat s
            JOIN crew c ON s.id_crew = c.id_crew
            JOIN rig r ON c.id_rig = r.id_rig
            WHERE s.id_sertifikat = (SELECT MAX(id_sertifikat) FROM sertifikat s2 WHERE s2.id_crew = s.id_crew)
            AND c.status_aktif = 'aktif'";
        if (!$isAllRig && !empty($rigIds)) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        if (!empty($filter_rig)) $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
        if (!empty($filter_status)) {
            if ($filter_status == 'exp') $sql .= " AND s.tanggal_expired < CURDATE()";
            elseif ($filter_status == 'soon') $sql .= " AND s.tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            elseif ($filter_status == 'ok') $sql .= " AND s.tanggal_expired > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        }
        if (!empty($filter_search)) $sql .= " AND c.nama LIKE '%" . $this->escape($filter_search) . "%'";
        $sql .= " ORDER BY s.tanggal_expired ASC LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql);
    }

    public function countMonitoring($rigIds, $isAllRig, $filter_rig = '', $filter_status = '', $filter_search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM sertifikat s
                JOIN crew c ON s.id_crew = c.id_crew
                JOIN rig r ON c.id_rig = r.id_rig
                WHERE s.id_sertifikat = (SELECT MAX(id_sertifikat) FROM sertifikat s2 WHERE s2.id_crew = s.id_crew)
                AND c.status_aktif = 'aktif'";
        if (!$isAllRig && !empty($rigIds)) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        if (!empty($filter_rig)) $sql .= " AND r.kode_rig = '" . $this->escape($filter_rig) . "'";
        if (!empty($filter_status)) {
            if ($filter_status == 'exp') $sql .= " AND s.tanggal_expired < CURDATE()";
            elseif ($filter_status == 'soon') $sql .= " AND s.tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            elseif ($filter_status == 'ok') $sql .= " AND s.tanggal_expired > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        }
        if (!empty($filter_search)) $sql .= " AND c.nama LIKE '%" . $this->escape($filter_search) . "%'";
        return $this->query($sql)->fetch_assoc()['total'];
    }

    public function countActive($rigIds, $isAllRig)
    {
        $sql = "SELECT COUNT(*) as total FROM sertifikat s 
                JOIN crew c ON s.id_crew = c.id_crew 
                WHERE s.id_sertifikat = (SELECT MAX(id_sertifikat) FROM sertifikat s2 WHERE s2.id_crew = s.id_crew)
                AND c.status_aktif = 'aktif'
                AND s.tanggal_expired > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        return $this->query($sql)->fetch_assoc()['total'];
    }

    public function countSoon($rigIds, $isAllRig)
    {
        $sql = "SELECT COUNT(*) as total FROM sertifikat s 
                JOIN crew c ON s.id_crew = c.id_crew 
                WHERE s.id_sertifikat = (SELECT MAX(id_sertifikat) FROM sertifikat s2 WHERE s2.id_crew = s.id_crew)
                AND c.status_aktif = 'aktif'
                AND s.tanggal_expired BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        return $this->query($sql)->fetch_assoc()['total'];
    }

    public function countExpired($rigIds, $isAllRig)
    {
        $sql = "SELECT COUNT(*) as total FROM sertifikat s 
                JOIN crew c ON s.id_crew = c.id_crew 
                WHERE s.id_sertifikat = (SELECT MAX(id_sertifikat) FROM sertifikat s2 WHERE s2.id_crew = s.id_crew)
                AND c.status_aktif = 'aktif'
                AND s.tanggal_expired < CURDATE()";
        if (!$isAllRig) $sql .= " AND c.id_rig IN (" . implode(',', $rigIds) . ")";
        return $this->query($sql)->fetch_assoc()['total'];
    }

    public function getByCrew($id_crew, $orderBy = 'id_sertifikat', $order = 'DESC')
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_crew = ? ORDER BY {$orderBy} {$order}");
        $stmt->bind_param('i', $id_crew);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function update($id, $data)
    {
        $fields = '';
        $values = [];
        $types = '';

        foreach ($data as $key => $val) {
            $fields .= "`{$key}` = ?, ";
            $values[] = $val;
            $types .= (is_int($val) ? 'i' : 's');
        }

        $fields = rtrim($fields, ', ');
        $values[] = $id;
        $types .= 'i';

        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$fields} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }
}
