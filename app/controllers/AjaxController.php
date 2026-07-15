<?php
class AjaxController extends Controller
{

    public function crewTable()
    {
        $this->requireLogin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $sort = $_GET['sort'] ?? 'nama';
        $order = $_GET['order'] ?? 'ASC';

        $crewModel = $this->model('CrewModel');
        $rigIds = $this->getCurrentRigIds();
        $isAllRig = $this->isAdminAllRig();
        $filter_status = $_GET['status'] ?? '';
        $filter_crew = $_GET['crew'] ?? '';

        $source = $_GET['source'] ?? 'dashboard';

        if ($source == 'crew') {
            $filter_rig = $_GET['rig'] ?? '';
            $filter_search = $_GET['search'] ?? '';
            $totalCrew = $crewModel->countFiltered($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $filter_crew);
            $crew = $crewModel->getFiltered($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $filter_crew, $perPage, $offset, $sort, $order);
        } else {
            $crew = $crewModel->getAllWithStatusPaginated($rigIds, $isAllRig, $perPage, $offset);
            $totalCrew = $crewModel->countAllWithStatus($rigIds, $isAllRig);
        }

        $totalPages = ceil($totalCrew / $perPage);

        ob_start();

        if ($crew && $crew->num_rows > 0):
            $no = $offset;
            $colors = ['#2B2A4C', '#B31312', '#0f9b58', '#7c3aed', '#0891b2', '#b45309', '#be185d', '#0369a1'];
            while ($c = $crew->fetch_assoc()): $no++;
                $init = strtoupper(substr($c['nama'], 0, 2));
                $color = $colors[($no - 1) % count($colors)];
                $bs = $c['badge_status'] ?? 'ok';
                $ms = $c['mcu_status'] ?? 'ok';
                $ss = $c['sertifikat_status'] ?? 'ok';
                $ps = $c['pkwt_status'] ?? 'ok';
                $sl = ['ok' => '<span class="db dok"><i class="fas fa-check"></i> Valid</span>', 'soon' => '<span class="db dsn"><i class="fas fa-clock"></i> Segera</span>', 'exp' => '<span class="db dex"><i class="fas fa-times"></i> Expired</span>'];
                if (in_array('exp', [$bs, $ms, $ss, $ps])) $ov = '<span class="stbadge st-n">Non-Comply</span>';
                elseif (in_array('soon', [$bs, $ms, $ss, $ps])) $ov = '<span class="stbadge st-w">Warning</span>';
                else $ov = '<span class="stbadge st-c">Comply</span>';
?>
                <tr>
                    <td class="td-muted"><?= $no ?></td>
                    <td>
                        <div class="crew-av">
                            <div class="cav" style="background:<?= $color ?>"><?= $init ?></div>
                            <div>
                                <a href="<?= BASE_URL ?>/crew/detail/<?= $c['id_crew'] ?>" class="cn-link">
                                    <div class="cn"><?= $c['nama'] ?></div>
                                </a>
                                <div class="cp">ID: <?= $c['id_crew'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="td-muted"><?= $c['posisi'] ?></td>
                    <td><span class="rtag"><?= $c['kode_rig'] ?></span></td>
                    <td><span class="rtag"><?= htmlspecialchars($c['crew'] ?? '-') ?></span></td>
                    <td><?= $sl[$bs] ?></td>
                    <td><?= $sl[$ms] ?></td>
                    <td><?= $sl[$ss] ?></td>
                    <td><?= $sl[$ps] ?></td>
                    <td>
                        <?php $isNonaktif = ($c['status_aktif'] ?? 'aktif') == 'nonaktif'; ?>
                        <?php if (!$isNonaktif): ?>
                            <a href="<?= BASE_URL ?>/crew/edit/<?= $c['id_crew'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/crew/toggleStatus/<?= $c['id_crew'] ?>"
                            class="btn btn-sm <?= $isNonaktif ? 'btn-outline-success' : 'btn-outline-danger' ?>"
                            title="<?= $isNonaktif ? 'Aktifkan' : 'Nonaktifkan' ?>"
                            onclick="return confirm('<?= $isNonaktif ? 'Aktifkan' : 'Nonaktifkan' ?> crew ini?')">
                            <i class="fas <?= $isNonaktif ? 'fa-user-check' : 'fa-user-slash' ?>"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr>
                <td colspan="10" class="td-empty">Belum ada crew</td>
            </tr>
            <?php endif;

        $html = ob_get_clean();

        $pagination = '';
        $start = max(1, $page - 4);
        $end = min($totalPages, $start + 9);
        $start = max(1, $end - 9);
        for ($p = $start; $p <= $end; $p++) {
            $active = $p == $page ? ' active' : '';
            $pagination .= '<a href="javascript:void(0)" onclick="loadCrewPage(' . $p . ')" class="btn btn-sm btn-page' . $active . '">' . $p . '</a>';
        }

        echo json_encode([
            'html' => $html,
            'pagination' => $pagination,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $totalCrew
        ]);
    }

    public function suratTable()
    {
        $this->requireLogin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $suratModel = $this->model('SuratModel');
        $rigIds = $this->getCurrentRigIds();
        $isAllRig = $this->isAdminAllRig();

        $filter_rig = $_GET['rig'] ?? '';
        $filter_jenis = $_GET['jenis'] ?? '';
        $filter_tahun = $_GET['tahun'] ?? '';
        $filter_search = $_GET['search'] ?? '';

        $totalSuratAll = $suratModel->countFiltered($rigIds, $isAllRig, $filter_rig, $filter_jenis, $filter_tahun, $filter_search);
        $totalPages = ceil($totalSuratAll / $perPage);
        $surat = $suratModel->getFiltered($rigIds, $isAllRig, $filter_rig, $filter_jenis, $filter_tahun, $filter_search, $perPage, $offset);

        ob_start();
        if ($surat && $surat->num_rows > 0):
            $no = $offset;
            while ($s = $surat->fetch_assoc()): $no++;
            ?>
                <tr>
                    <td class="td-muted"><?= $no ?></td>
                    <td><strong class="cn"><?= $s['nomor_surat'] ?></strong></td>
                    <td><span class="rtag"><?= $s['kode_jenis'] ?></span></td>
                    <td><?= $s['kode_rig'] ?></td>
                    <td class="td-sm"><?= $s['tanggal_moc'] ? date('d/m/Y', strtotime($s['tanggal_moc'])) : '-' ?></td>
                    <td class="td-ellipsis"><?= htmlspecialchars($s['keterangan'] ?? '-') ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/surat/cetak/<?= $s['id_surat'] ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Cetak"><i class="fas fa-print"></i></a>
                        <a href="<?= BASE_URL ?>/surat/edit/<?= $s['id_surat'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="<?= BASE_URL ?>/surat/delete/<?= $s['id_surat'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Hapus" onclick="return confirm('Hapus surat ini?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr>
                <td colspan="7" class="td-empty">Belum ada surat</td>
            </tr>
            <?php endif;
        $html = ob_get_clean();

        $pagination = '';
        $start = max(1, $page - 4);
        $end = min($totalPages, $start + 9);
        $start = max(1, $end - 9);
        for ($p = $start; $p <= $end; $p++) {
            $active = $p == $page ? ' active' : '';
            $pagination .= '<a href="javascript:void(0)" onclick="loadSuratPage(' . $p . ')" class="btn btn-sm btn-page' . $active . '">' . $p . '</a>';
        }

        echo json_encode([
            'html' => $html,
            'pagination' => $pagination,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $totalSuratAll
        ]);
    }

    public function badgeTable()
    {
        $this->requireLogin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $badgeModel = $this->model('BadgeModel');
        $rigIds = $this->getCurrentRigIds();
        $isAllRig = $this->isAdminAllRig();

        $filter_rig = $_GET['rig'] ?? '';
        $filter_status = $_GET['status'] ?? '';
        $filter_search = $_GET['search'] ?? '';

        $totalBadges = $badgeModel->countMonitoring($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search);
        $totalPages = ceil($totalBadges / $perPage);
        $badges = $badgeModel->getMonitoring($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $perPage, $offset);

        ob_start();
        if ($badges && $badges->num_rows > 0):
            $no = $offset;
            while ($b = $badges->fetch_assoc()): $no++;
                $sisa = $b['sisa_hari'];
                $statusClass = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                $statusText = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa . ' hari' : 'Valid');
            ?>
                <tr>
                    <td class="td-muted"><?= $no ?></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $b['id_crew'] ?>" class="cn-link"><strong><?= $b['nama'] ?></strong></a></td>
                    <td><span class="rtag"><?= $b['kode_rig'] ?></span></td>
                    <td><?= $b['nomor_badge'] ?? '-' ?></td>
                    <td class="td-sm"><?= $b['tanggal_expired'] ? date('d/m/Y', strtotime($b['tanggal_expired'])) : '-' ?></td>
                    <td class="td-bold"><?= $sisa ?> hari</td>
                    <td><span class="db <?= $statusClass ?>"><?= $statusText ?></span></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $b['id_crew'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr>
                <td colspan="8" class="td-empty">Tidak ada data badge</td>
            </tr>
            <?php endif;
        $html = ob_get_clean();

        $pagination = '';
        for ($p = max(1, $page - 4); $p <= min($totalPages, $page + 5); $p++) {
            $active = $p == $page ? ' active' : '';
            $pagination .= '<a href="javascript:void(0)" onclick="loadBadgePage(' . $p . ')" class="btn btn-sm btn-page' . $active . '">' . $p . '</a>';
        }

        echo json_encode([
            'html' => $html,
            'pagination' => $pagination,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function mcuTable()
    {
        $this->requireLogin();
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        $model = $this->model('McuModel');
        $rigIds = $this->getCurrentRigIds();
        $isAllRig = $this->isAdminAllRig();
        $fr = $_GET['rig'] ?? '';
        $fs = $_GET['status'] ?? '';
        $fsearch = $_GET['search'] ?? '';
        $total = $model->countMonitoring($rigIds, $isAllRig, $fr, $fs, $fsearch);
        $totalPages = ceil($total / $perPage);
        $data = $model->getMonitoring($rigIds, $isAllRig, $fr, $fs, $fsearch, $perPage, $offset);
        ob_start();
        $no = $offset;
        if ($data && $data->num_rows > 0):
            while ($m = $data->fetch_assoc()): $no++;
                $sisa = $m['sisa_hari'];
                $sc = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                $st = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa . ' hari' : 'Valid');
            ?>
                <tr>
                    <td class="td-muted"><?= $no ?></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $m['id_crew'] ?>" class="cn-link"><strong><?= $m['nama'] ?></strong></a></td>
                    <td><span class="rtag"><?= $m['kode_rig'] ?></span></td>
                    <td><?= $m['derajat_kesehatan'] ?? '-' ?></td>
                    <td class="td-sm"><?= $m['expired'] ? date('d/m/Y', strtotime($m['expired'])) : '-' ?></td>
                    <td class="td-bold"><?= $sisa ?> hari</td>
                    <td><span class="db <?= $sc ?>"><?= $st ?></span></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $m['id_crew'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr>
                <td colspan="9" class="td-empty">Tidak ada data MCU</td>
            </tr>
            <?php endif;
        $html = ob_get_clean();
        $pag = '';
        for ($p = max(1, $page - 4); $p <= min($totalPages, $page + 5); $p++) $pag .= '<a href="javascript:void(0)" onclick="loadMcuPage(' . $p . ')" class="btn btn-sm btn-page' . ($p == $page ? ' active' : '') . '">' . $p . '</a>';
        echo json_encode(['html' => $html, 'pagination' => $pag, 'page' => $page, 'totalPages' => $totalPages, 'total' => $total]);
    }

    public function sertifikatTable()
    {
        $this->requireLogin();
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        $model = $this->model('SertifikatModel');
        $rigIds = $this->getCurrentRigIds();
        $isAllRig = $this->isAdminAllRig();
        $fr = $_GET['rig'] ?? '';
        $fs = $_GET['status'] ?? '';
        $fsearch = $_GET['search'] ?? '';
        $total = $model->countMonitoring($rigIds, $isAllRig, $fr, $fs, $fsearch);
        $totalPages = ceil($total / $perPage);
        $data = $model->getMonitoring($rigIds, $isAllRig, $fr, $fs, $fsearch, $perPage, $offset);
        ob_start();
        $no = $offset;
        if ($data && $data->num_rows > 0):
            while ($s = $data->fetch_assoc()): $no++;
                $sisa = $s['sisa_hari'];
                $sc = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                $st = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa . ' hari' : 'Valid');
            ?>
                <tr>
                    <td class="td-muted"><?= $no ?></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $s['id_crew'] ?>" class="cn-link"><strong><?= $s['nama'] ?></strong></a></td>
                    <td><span class="rtag"><?= $s['kode_rig'] ?></span></td>
                    <td><?= $s['jenis'] ?></td>
                    <td class="td-sm"><?= $s['tanggal_expired'] ? date('d/m/Y', strtotime($s['tanggal_expired'])) : '-' ?></td>
                    <td class="td-bold"><?= $sisa ?> hari</td>
                    <td><span class="db <?= $sc ?>"><?= $st ?></span></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $s['id_crew'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr>
                <td colspan="9" class="td-empty">Tidak ada data sertifikat</td>
            </tr>
            <?php endif;
        $html = ob_get_clean();
        $pag = '';
        for ($p = max(1, $page - 4); $p <= min($totalPages, $page + 5); $p++) $pag .= '<a href="javascript:void(0)" onclick="loadSertPage(' . $p . ')" class="btn btn-sm btn-page' . ($p == $page ? ' active' : '') . '">' . $p . '</a>';
        echo json_encode(['html' => $html, 'pagination' => $pag, 'page' => $page, 'totalPages' => $totalPages, 'total' => $total]);
    }

    public function pkwtTable()
    {
        $this->requireLogin();
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        $model = $this->model('PkwtModel');
        $rigIds = $this->getCurrentRigIds();
        $isAllRig = $this->isAdminAllRig();
        $fr = $_GET['rig'] ?? '';
        $fs = $_GET['status'] ?? '';
        $fsearch = $_GET['search'] ?? '';
        $total = $model->countMonitoring($rigIds, $isAllRig, $fr, $fs, $fsearch);
        $totalPages = ceil($total / $perPage);
        $data = $model->getMonitoring($rigIds, $isAllRig, $fr, $fs, $fsearch, $perPage, $offset);
        ob_start();
        $no = $offset;
        if ($data && $data->num_rows > 0):
            while ($p = $data->fetch_assoc()): $no++;
                $sisa = $p['sisa_hari'];
                $sc = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                $st = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa . ' hari' : 'Valid');
            ?>
                <tr>
                    <td class="td-muted"><?= $no ?></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $p['id_crew'] ?>" class="cn-link"><strong><?= $p['nama'] ?></strong></a></td>
                    <td><span class="rtag"><?= $p['kode_rig'] ?></span></td>
                    <td class="td-sm"><?= $p['tanggal_mulai'] ? date('d/m/Y', strtotime($p['tanggal_mulai'])) : '-' ?></td>
                    <td class="td-sm"><?= $p['tanggal_berakhir'] ? date('d/m/Y', strtotime($p['tanggal_berakhir'])) : '-' ?></td>
                    <td class="td-bold"><?= $sisa ?> hari</td>
                    <td><span class="db <?= $sc ?>"><?= $st ?></span></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $p['id_crew'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr>
                <td colspan="8" class="td-empty">Tidak ada data PKWT</td>
            </tr>
<?php endif;
        $html = ob_get_clean();
        $pag = '';
        for ($p = max(1, $page - 4); $p <= min($totalPages, $page + 5); $p++) $pag .= '<a href="javascript:void(0)" onclick="loadPkwtPage(' . $p . ')" class="btn btn-sm btn-page' . ($p == $page ? ' active' : '') . '">' . $p . '</a>';
        echo json_encode(['html' => $html, 'pagination' => $pag, 'page' => $page, 'totalPages' => $totalPages, 'total' => $total]);
    }

    public function getCrewData($id_crew)
    {
        $this->requireLogin();

        $crewModel = $this->model('CrewModel');
        $badgeModel = $this->model('BadgeModel');

        $crew = $crewModel->getDetail($id_crew);
        $badge = $badgeModel->getLastBadge($id_crew);

        echo json_encode([
            'nama' => $crew['nama'] ?? '',
            'posisi' => $crew['posisi'] ?? '',
            'kode_rig' => $crew['kode_rig'] ?? '',
            'crew' => $crew['crew'] ?? '',
            'nomor_badge' => $badge['nomor_badge'] ?? '-'
        ]);
    }
}
