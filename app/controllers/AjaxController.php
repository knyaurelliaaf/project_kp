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
        $filter_rig = $_GET['rig'] ?? '';
        $filter_search = $_GET['search'] ?? '';

        if ($source == 'crew') {
            $_SESSION['crew_filters'] = [
                'rig' => $filter_rig,
                'status' => $filter_status,
                'crew' => $filter_crew,
                'search' => $filter_search,
                'sort' => $sort,
                'order' => $order,
                'page' => $page
            ];
            $totalCrew = $crewModel->countFiltered($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $filter_crew);
            $crew = $crewModel->getFiltered($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $filter_crew, $perPage, $offset, $sort, $order);
        } else {
            $crew = $crewModel->getAllWithStatusPaginated($rigIds, $isAllRig, $perPage, $offset);
            $totalCrew = $crewModel->countAllWithStatus($rigIds, $isAllRig);
        }

        $totalPages = ceil($totalCrew / $perPage);

        $filterParams = array_filter([
            'rig' => $filter_rig,
            'status' => $filter_status,
            'crew' => $filter_crew,
            'search' => $filter_search,
            'sort' => $sort !== 'nama' ? $sort : null,
            'order' => $order !== 'ASC' ? $order : null,
            'page' => $page > 1 ? $page : null
        ]);
        $filterQuery = http_build_query($filterParams);
        $fq = !empty($filterQuery) ? '?' . $filterQuery : '';

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
                                <a href="<?= BASE_URL ?>/crew/detail/<?= $c['id_crew'] ?><?= $fq ?>" class="cn-link">
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
                        <a href="<?= BASE_URL ?>/crew/detail/<?= $c['id_crew'] ?><?= $fq ?>" class="btn btn-sm btn-outline-info" title="Detail"><i class="fas fa-eye"></i></a>
                        <?php if (!$isNonaktif): ?>
                            <a href="<?= BASE_URL ?>/crew/edit/<?= $c['id_crew'] ?><?= $fq ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
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

        $pagination = Helper::renderPaginationNumbers($page, $totalPages, 'loadCrewPage');

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
                    <td class="table-actions-cell">
                        <div class="table-actions">
                            <a href="<?= BASE_URL ?>/surat/cetak/<?= $s['id_surat'] ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Cetak"><i class="fas fa-print"></i></a>
                            <a href="<?= BASE_URL ?>/surat/edit/<?= $s['id_surat'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= BASE_URL ?>/surat/delete/<?= $s['id_surat'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Hapus" onclick="return confirm('Hapus surat ini?')"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr>
                <td colspan="7" class="td-empty">Belum ada surat</td>
            </tr>
            <?php endif;
        $html = ob_get_clean();

        $pagination = Helper::renderPaginationNumbers($page, $totalPages, 'loadSuratPage');

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
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $b['id_crew'] ?>?from=badge" class="cn-link"><strong><?= $b['nama'] ?></strong></a></td>
                    <td><span class="rtag"><?= $b['kode_rig'] ?></span></td>
                    <td><?= $b['posisi'] ?? '-' ?></td>
                    <td><?= $b['nomor_badge'] ?? '-' ?></td>
                    <td class="td-sm"><?= $b['tanggal_expired'] ? date('d/m/Y', strtotime($b['tanggal_expired'])) : '-' ?></td>
                    <td class="td-bold"><?= $sisa ?> hari</td>
                    <td><span class="db <?= $statusClass ?>"><?= $statusText ?></span></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $b['id_crew'] ?>?from=badge" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr>
                <td colspan="9" class="td-empty">Tidak ada data badge</td>
            </tr>
            <?php endif;
        $html = ob_get_clean();

        $pagination = Helper::renderPaginationNumbers($page, $totalPages, 'loadBadgePage');

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
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $m['id_crew'] ?>?from=mcu" class="cn-link"><strong><?= $m['nama'] ?></strong></a></td>
                    <td><span class="rtag"><?= $m['kode_rig'] ?></span></td>
                    <td><?= $m['posisi'] ?? '-' ?></td>
                    <td><?= !empty($m['derajat_kesehatan']) ? htmlspecialchars($m['derajat_kesehatan']) : '-' ?></td>
                    <td class="td-sm"><?= $m['expired'] ? date('d/m/Y', strtotime($m['expired'])) : '-' ?></td>
                    <td class="td-bold"><?= $sisa ?> hari</td>
                    <td><span class="db <?= $sc ?>"><?= $st ?></span></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $m['id_crew'] ?>?from=mcu" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr>
                <td colspan="10" class="td-empty">Tidak ada data MCU</td>
            </tr>
            <?php endif;
        $html = ob_get_clean();
        $pag = Helper::renderPaginationNumbers($page, $totalPages, 'loadMcuPage');
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
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $s['id_crew'] ?>?from=sertifikat" class="cn-link"><strong><?= $s['nama'] ?></strong></a></td>
                    <td><span class="rtag"><?= $s['kode_rig'] ?></span></td>
                    <td><?= $s['posisi'] ?? '-' ?></td>
                    <td><?= $s['jenis'] ?></td>
                    <td class="td-sm"><?= $s['tanggal_expired'] ? date('d/m/Y', strtotime($s['tanggal_expired'])) : '-' ?></td>
                    <td class="td-bold"><?= $sisa ?> hari</td>
                    <td><span class="db <?= $sc ?>"><?= $st ?></span></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $s['id_crew'] ?>?from=sertifikat" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr>
                <td colspan="9" class="td-empty">Tidak ada data sertifikat</td>
            </tr>
            <?php endif;
        $html = ob_get_clean();
        $pag = Helper::renderPaginationNumbers($page, $totalPages, 'loadSertPage');
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
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $p['id_crew'] ?>?from=pkwt" class="cn-link"><strong><?= $p['nama'] ?></strong></a></td>
                    <td><span class="rtag"><?= $p['kode_rig'] ?></span></td>
                    <td><?= $p['posisi'] ?? '-' ?></td>
                    <td class="td-sm"><?= $p['tanggal_mulai'] ? date('d/m/Y', strtotime($p['tanggal_mulai'])) : '-' ?></td>
                    <td class="td-sm"><?= $p['tanggal_berakhir'] ? date('d/m/Y', strtotime($p['tanggal_berakhir'])) : '-' ?></td>
                    <td class="td-bold"><?= $sisa ?> hari</td>
                    <td><span class="db <?= $sc ?>"><?= $st ?></span></td>
                    <td><a href="<?= BASE_URL ?>/crew/detail/<?= $p['id_crew'] ?>?from=pkwt" class="btn btn-sm btn-outline-info" title="Lihat detail crew"><i class="fas fa-eye"></i></a></td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr>
                <td colspan="9" class="td-empty">Tidak ada data PKWT</td>
            </tr>
<?php endif;
        $html = ob_get_clean();
        $pag = Helper::renderPaginationNumbers($page, $totalPages, 'loadPkwtPage');
        echo json_encode(['html' => $html, 'pagination' => $pag, 'page' => $page, 'totalPages' => $totalPages, 'total' => $total]);
    }

    public function slipGajiTable()
    {
        $this->requireLogin();
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        $periode = $_GET['periode'] ?? '';
        $search = $_GET['search'] ?? '';

        if (empty($periode)) {
            echo json_encode(['html' => '<tr><td colspan="5" class="text-center py-4 text-muted">Periode tidak valid</td></tr>', 'pagination' => '', 'page' => 1, 'totalPages' => 1, 'total' => 0]);
            return;
        }

        $slipModel = $this->model('SlipGajiModel');
        $total = $slipModel->countByPeriodeFiltered($periode, $search);
        $totalPages = ceil($total / $perPage);
        $data = $slipModel->getByPeriodePaginated($periode, $search, $perPage, $offset);

        ob_start();
        if (!empty($data)):
            $no = $offset;
            foreach ($data as $k): $no++;
                ?>
                <tr>
                    <td style="color:var(--muted)"><?= htmlspecialchars($k['no_urut']) ?></td>
                    <td><strong><?= htmlspecialchars($k['nama']) ?></strong></td>
                    <td><?= htmlspecialchars($k['jabatan']) ?></td>
                    <td>Rp <?= number_format($k['gaji_bersih'], 0, ',', '.') ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/payroll/cetak/<?= $k['id_slip'] ?>" class="btn btn-sigma btn-sm" target="_blank">
                            <i class="fas fa-print"></i> Lihat / Cetak
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data yang cocok</td></tr>
        <?php endif;
        $html = ob_get_clean();

        $pagination = Helper::renderPaginationNumbers($page, $totalPages, 'loadSlipPage');

        echo json_encode([
            'html' => $html,
            'pagination' => $pagination,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }

    public function getCrewData($id_crew)
    {
        $this->requireLogin();

        $crewModel = $this->model('CrewModel');
        $badgeModel = $this->model('BadgeModel');

        $crew = $crewModel->getDetail($id_crew);
        $badge = $badgeModel->getLastBadge($id_crew);

        // Calculate age from NIK or use default
        $umur = '';
        if (!empty($crew['nik_ktp'])) {
            $nik = $crew['nik_ktp'];
            // NIK format: 6 digits date of birth (DDMMYY) + ...
            if (strlen($nik) >= 6) {
                $tglLahir = substr($nik, 6, 2);
                $blnLahir = substr($nik, 8, 2);
                $thnLahir = substr($nik, 10, 2);
                // Determine century
                $thnLahirFull = ($thnLahir > date('y')) ? '19' . $thnLahir : '20' . $thnLahir;
                $birthDate = $thnLahirFull . '-' . $blnLahir . '-' . $tglLahir;
                $age = date_diff(date_create($birthDate), date_create('today'))->y;
                $umur = $age . ' Tahun';
            }
        }

        echo json_encode([
            'id_rig' => (int) ($crew['id_rig'] ?? 0),
            'nama' => $crew['nama'] ?? '',
            'posisi' => $crew['posisi'] ?? '',
            'kode_rig' => $crew['kode_rig'] ?? '',
            'crew' => $crew['crew'] ?? '',
            'nomor_badge' => $badge['nomor_badge'] ?? '-',
            'alamat' => $crew['alamat'] ?? '',
            'nik_ktp' => $crew['nik_ktp'] ?? '',
            'umur' => $umur
        ]);
    }
}
