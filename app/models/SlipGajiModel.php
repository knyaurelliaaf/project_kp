<?php
/**
 * app/models/SlipGajiModel.php
 */

class SlipGajiModel extends Model
{
    protected $table = 'slip_gaji_master';
    protected $primaryKey = 'id_slip';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function getByPeriodeAndCrew($periode, $idCrew)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE periode = ? AND id_crew = ?");
        $stmt->bind_param('si', $periode, $idCrew);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getById($idSlip)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_slip = ?");
        $stmt->bind_param('i', $idSlip);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getByPeriodeAndUrut($periode, $noUrut)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE periode = ? AND no_urut = ?");
        $stmt->bind_param('si', $periode, $noUrut);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllPeriode()
    {
        $stmt = $this->db->query("SELECT DISTINCT periode, tanggal_mulai FROM {$this->table} ORDER BY tanggal_mulai DESC");
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByPeriode($periode)
    {
        $stmt = $this->db->prepare("SELECT id_slip, no_urut, nama, jabatan FROM {$this->table} WHERE periode = ? ORDER BY no_urut ASC");
        $stmt->bind_param('s', $periode);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function update($id, $data)
    {
        return parent::update($id, $data);
    }

    public function getByBadge($noBadge)
    {
        $sql = "SELECT c.* FROM crew c
                INNER JOIN badge b ON b.id_crew = c.id_crew
                WHERE b.no_badge = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $noBadge);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
