<?php

class PayrollModel extends Model
{
    protected $table = 'slip_gaji_master';
    protected $primaryKey = 'id_slip';

    /**
     * Ambil daftar periode unik dari tabel slip_gaji_master
     * @return mysqli_result|false
     */
    public function getPeriodeList()
    {
        $sql = "SELECT DISTINCT periode FROM {$this->table} ORDER BY periode DESC";
        return $this->db->query($sql);
    }
}
