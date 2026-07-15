<?php
class RigModel extends Model {
    protected $table = 'rig';
    protected $primaryKey = 'id_rig';
    
    public function allActive() {
        return $this->where('status', 'aktif');
    }
}