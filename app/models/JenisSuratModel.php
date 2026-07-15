<?php
class JenisSuratModel extends Model {
    protected $table = 'jenis_surat';
    protected $primaryKey = 'id_jenis';
    
    public function getByKode($kode) {
        $result = $this->where('kode', $kode);
        return $result->fetch_assoc();
    }
}