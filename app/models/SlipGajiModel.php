<?php

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
        $row = $stmt->get_result()->fetch_assoc();
        if ($row && !empty($row['data_json'])) {
            $jsonData = json_decode($row['data_json'], true);
            if (is_array($jsonData)) {
                // Rincian payroll tersimpan di data_json. Kolom rincian fisik
                // pada tabel lama masih bernilai default 0, jadi JSON harus
                // menimpa nilainya agar slip tidak tampil seluruhnya sebagai "-".
                $masterData = $row;
                $row = array_merge($row, $jsonData);

                // Tetap gunakan identitas dan total master dari tabel.
                foreach ([
                    'id_slip', 'id_rig', 'id_crew', 'periode', 'tanggal_mulai',
                    'tanggal_selesai', 'no_urut', 'nama', 'jabatan', 'badge_excel',
                    'gaji_bersih', 'data_json', 'created_at', 'updated_at'
                ] as $field) {
                    if (array_key_exists($field, $masterData)) {
                        $row[$field] = $masterData[$field];
                    }
                }
                if (!isset($row['badge']) && isset($row['badge_excel'])) {
                    $row['badge'] = $row['badge_excel'];
                }
            }
        }
        return $row;
    }

    public function getByPeriodeRigUrut($periode, $idRig, $noUrut)
    {
        if ($idRig === null || $idRig === '') {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE periode = ? AND id_rig IS NULL AND no_urut = ?");
            $stmt->bind_param('ss', $periode, $noUrut);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE periode = ? AND id_rig = ? AND no_urut = ?");
            $stmt->bind_param('sis', $periode, $idRig, $noUrut);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Cek duplikat berdasarkan periode + no_urut (tanpa filter rig).
     * Digunakan setelah import yang tidak memilih rig.
     */
    public function getByPeriodeUrut($periode, $noUrut)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE periode = ? AND no_urut = ?");
        $stmt->bind_param('ss', $periode, $noUrut);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getHistoryImport()
    {
        $sql = "SELECT s.periode, s.id_rig, r.kode_rig, r.nama_rig, 
                       COUNT(s.id_slip) as total_karyawan, 
                       SUM(s.gaji_bersih) as total_gaji, 
                       MAX(s.created_at) as created_at
                FROM {$this->table} s
                LEFT JOIN rig r ON s.id_rig = r.id_rig
                GROUP BY s.periode, s.id_rig, r.kode_rig, r.nama_rig
                ORDER BY MAX(s.created_at) DESC, s.periode DESC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAllPeriodeRig()
    {
        $sql = "SELECT s.periode, s.tanggal_mulai, s.id_rig, r.kode_rig, COUNT(*) as jumlah
                FROM {$this->table} s
                LEFT JOIN rig r ON s.id_rig = r.id_rig
                GROUP BY s.periode, s.tanggal_mulai, s.id_rig, r.kode_rig
                ORDER BY s.tanggal_mulai DESC, r.kode_rig ASC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getDashboardStats()
    {
        $sql = "SELECT 
                    COUNT(DISTINCT s.periode) as total_periode,
                    COUNT(DISTINCT s.id_rig) as total_rig,
                    COUNT(DISTINCT s.nama) as total_karyawan,
                    COUNT(*) as total_slip
                FROM {$this->table} s";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : [
            'total_periode' => 0,
            'total_rig' => 0,
            'total_karyawan' => 0,
            'total_slip' => 0
        ];
    }

    public function getByPeriode($periode, $idRig)
    {
        if ($idRig === null || $idRig === '') {
            $sql = "SELECT id_slip, no_urut, nama, jabatan, gaji_bersih FROM {$this->table}
                    WHERE periode = ? AND id_rig IS NULL
                    ORDER BY CAST(no_urut AS DECIMAL(10,2)) ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $periode);
        } else {
            $sql = "SELECT id_slip, no_urut, nama, jabatan, gaji_bersih FROM {$this->table}
                    WHERE periode = ? AND id_rig = ?
                    ORDER BY CAST(no_urut AS DECIMAL(10,2)) ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $periode, $idRig);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByPeriodeFiltered($periode, $idRig, $search = '')
    {
        $search = '%' . $search . '%';
        if ($idRig === null || $idRig === '') {
            $sql = "SELECT id_slip, no_urut, nama, jabatan, gaji_bersih FROM {$this->table}
                    WHERE periode = ? AND id_rig IS NULL
                    AND (nama LIKE ? OR jabatan LIKE ? OR no_urut LIKE ?)
                    ORDER BY CAST(no_urut AS DECIMAL(10,2)) ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ssss', $periode, $search, $search, $search);
        } else {
            $sql = "SELECT id_slip, no_urut, nama, jabatan, gaji_bersih FROM {$this->table}
                    WHERE periode = ? AND id_rig = ?
                    AND (nama LIKE ? OR jabatan LIKE ? OR no_urut LIKE ?)
                    ORDER BY CAST(no_urut AS DECIMAL(10,2)) ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('sisss', $periode, $idRig, $search, $search, $search);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countByPeriode($periode, $idRig)
    {
        if ($idRig === null || $idRig === '') {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE periode = ? AND id_rig IS NULL");
            $stmt->bind_param('s', $periode);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE periode = ? AND id_rig = ?");
            $stmt->bind_param('si', $periode, $idRig);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }

    public function deleteByPeriode($periode, $idRig)
    {
        if ($idRig === null || $idRig === '') {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE periode = ? AND id_rig IS NULL");
            $stmt->bind_param('s', $periode);
        } else {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE periode = ? AND id_rig = ?");
            $stmt->bind_param('si', $periode, $idRig);
        }
        return $stmt->execute();
    }

    public function getRingkasan($periode, $idRig)
    {
        if ($idRig === null || $idRig === '') {
            $sql = "SELECT COUNT(*) as jumlah_karyawan, SUM(gaji_bersih) as total_gaji_bersih
                    FROM {$this->table} WHERE periode = ? AND id_rig IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $periode);
        } else {
            $sql = "SELECT COUNT(*) as jumlah_karyawan, SUM(gaji_bersih) as total_gaji_bersih
                    FROM {$this->table} WHERE periode = ? AND id_rig = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $periode, $idRig);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getPeriodeByRig($idRig)
    {
        if ($idRig === null || $idRig === '') {
            $sql = "SELECT DISTINCT periode, tanggal_mulai FROM {$this->table}
                    WHERE id_rig IS NULL ORDER BY tanggal_mulai DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } else {
            $sql = "SELECT DISTINCT periode, tanggal_mulai FROM {$this->table}
                    WHERE id_rig = ? ORDER BY tanggal_mulai DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $idRig);
            $stmt->execute();
        }
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countPeriodeByRig($idRig)
    {
        if ($idRig === null || $idRig === '') {
            $sql = "SELECT COUNT(DISTINCT periode) as total FROM {$this->table} WHERE id_rig IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } else {
            $sql = "SELECT COUNT(DISTINCT periode) as total FROM {$this->table} WHERE id_rig = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $idRig);
            $stmt->execute();
        }
        return $stmt->get_result()->fetch_assoc()['total'];
    }

    /**
     * Assign / update rig untuk satu slip gaji.
     */
    public function updateRig($idSlip, $idRig)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET id_rig = ? WHERE id_slip = ?");
        $stmt->bind_param('ii', $idRig, $idSlip);
        return $stmt->execute();
    }

    /**
     * Assign rig secara massal untuk seluruh periode (yang belum punya rig).
     */
    public function assignRigToPeriode($periode, $idRig)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET id_rig = ? WHERE periode = ? AND id_rig IS NULL");
        $stmt->bind_param('is', $idRig, $periode);
        return $stmt->execute();
    }

    /**
     * Cek apakah periode memiliki data dengan rig yang belum ditentukan.
     */
    public function hasUnassignedRig($periode)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE periode = ? AND id_rig IS NULL");
        $stmt->bind_param('s', $periode);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'] > 0;
    }

    public function update($id, $data)
    {
        return parent::update($id, $data);
    }
}
