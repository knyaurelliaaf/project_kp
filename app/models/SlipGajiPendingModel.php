<?php
/**
 * Menyimpan baris payroll yang belum dapat dipasangkan ke data crew.
 */
class SlipGajiPendingModel extends Model
{
    protected $table = 'slip_gaji_pending';
    protected $primaryKey = 'id_pending';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function getAllPending()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at ASC, id_pending ASC";
        return $this->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($idPending)
    {
        return $this->find($idPending);
    }

    public function getByPeriodeAndUrut($periode, $noUrut)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE periode = ? AND no_urut = ?");
        $stmt->bind_param('ss', $periode, $noUrut);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function deleteById($idPending)
    {
        return $this->delete($idPending);
    }
}
