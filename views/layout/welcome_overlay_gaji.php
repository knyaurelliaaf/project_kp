<?php
/**
 * views/layout/welcome_overlay_gaji.php
 * Versi Awan Bergambar (awan-kiri.png & awan-kanan.png) + Efek Hujan khusus Admin Gaji / Payroll.
 * GORDEN/AWAN DIBUKA HANYA SAAT DIKLIK PENGGUNA (INTERAKTIF KLIK).
 */
$roleUser = $_SESSION['user']['role'] ?? '';
$allowedRoles = ['admin_gaji', 'payroll', 'payroll_wa'];

if (!in_array($roleUser, $allowedRoles)) {
    return;
}

$namaUser = $_SESSION['user']['nama'] ?? 'Admin Gaji';
?>
<div id="welcome-overlay-rain">
    <div id="rain-container"></div>

    <div class="welcome-text-rain">
        <div class="welcome-greeting-rain">Selamat Datang Kembali</div>
        <div class="welcome-nama-rain"><?= htmlspecialchars($namaUser) ?></div>
        <div class="welcome-badge-rain">
            <i class="fas fa-calculator me-1.5"></i> Admin Gaji & Payroll
        </div>
        <div class="welcome-click-hint-rain">
            <i class="fas fa-hand-pointer me-1.5"></i> Klik di mana saja untuk menyibak awan
        </div>
    </div>

    <div class="cloud-panel cloud-left"></div>
    <div class="cloud-panel cloud-right"></div>
</div>

<style>
#welcome-overlay-rain {
    position: fixed;
    inset: 0;
    z-index: 999999;
    pointer-events: auto;
    cursor: pointer;
    overflow: hidden;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    user-select: none;
}

.cloud-panel {
    position: fixed;
    top: 0;
    bottom: 0;
    width: 55%;
    z-index: 2;
    background-color: #2d5a8c;
    background-size: cover;
    background-repeat: no-repeat;
    transition: transform 1.3s cubic-bezier(.76, 0, .24, 1), opacity 1.3s ease;
    overflow: hidden;
}
.cloud-left {
    left: 0;
    background-image: url('<?= BASE_URL ?>/public/img/awan-kiri.png');
    background-position: left center;
}
.cloud-right {
    right: 0;
    background-image: url('<?= BASE_URL ?>/public/img/awan-kanan.png');
    background-position: right center;
}
.cloud-panel.open.cloud-left { transform: translate(-25%, -15%); opacity: 0; }
.cloud-panel.open.cloud-right { transform: translate(25%, -15%); opacity: 0; }

.welcome-text-rain {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 3;
    text-align: center;
    opacity: 1;
    transition: opacity .5s ease, transform .5s ease;
    width: 90%;
    max-width: 440px;
}
.welcome-text-rain.fade {
    opacity: 0;
    transform: translate(-50%, -60%);
}

.welcome-greeting-rain {
    font-size: 14px;
    letter-spacing: .15em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, .85);
    font-weight: 600;
}
.welcome-nama-rain {
    font-size: 32px;
    font-weight: 800;
    color: #fff;
    margin-top: 6px;
    text-shadow: 0 2px 14px rgba(20, 50, 90, .6);
}
.welcome-badge-rain {
    display: inline-flex;
    align-items: center;
    margin-top: 14px;
    padding: 6px 18px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 700;
    color: #FFFFFF;
}
.welcome-click-hint-rain {
    margin-top: 20px;
    font-size: 11.5px;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 600;
    animation: hintGlowRain 2s ease-in-out infinite alternate;
}

@keyframes hintGlowRain {
    0% { opacity: 0.6; }
    100% { opacity: 1; color: #FFFFFF; }
}

#rain-container {
    position: fixed;
    inset: 0;
    z-index: 4;
    overflow: hidden;
    pointer-events: none;
    transition: opacity 0.5s ease;
}
.raindrop {
    position: absolute;
    top: -60px;
    width: 2px;
    height: 60px;
    background: linear-gradient(180deg, rgba(190, 225, 255, 0) 0%, rgba(190, 225, 255, .85) 100%);
    animation-name: rain-fall;
    animation-timing-function: linear;
    animation-iteration-count: infinite; /* Hujan tanpa henti sampai diklik */
    will-change: transform;
}
@keyframes rain-fall {
    0% { transform: translate(0, 0); opacity: .9; }
    100% { transform: translate(var(--drift), 115vh); opacity: .5; }
}

@media (prefers-reduced-motion: reduce) {
    #welcome-overlay-rain { display: none; }
}
</style>

<script>
(function () {
    var overlay = document.getElementById('welcome-overlay-rain');
    if (!overlay) return;

    var rainContainer = document.getElementById('rain-container');
    var welcomeText = overlay.querySelector('.welcome-text-rain');
    var panels = overlay.querySelectorAll('.cloud-panel');
    var hasTriggered = false;

    // Hujan air terus-menerus
    var jumlahHujan = 60;
    for (var i = 0; i < jumlahHujan; i++) {
        var drop = document.createElement('div');
        drop.className = 'raindrop';
        drop.style.left = Math.random() * 100 + 'vw';
        drop.style.setProperty('--drift', (-30 + Math.random() * 20) + 'px');
        drop.style.animationDuration = (0.6 + Math.random() * 0.5) + 's';
        drop.style.animationDelay = (Math.random() * 1.8) + 's';
        drop.style.height = (40 + Math.random() * 40) + 'px';
        rainContainer.appendChild(drop);
    }

    // FUNGSI MEMBUKA AWAN HANYA SAAT DIKLIK:
    function triggerOpen() {
        if (hasTriggered) return;
        hasTriggered = true;

        if (welcomeText) welcomeText.classList.add('fade');
        if (rainContainer) rainContainer.style.opacity = '0';

        setTimeout(function () {
            panels.forEach(function (p) { p.classList.add('open'); });
        }, 150);

        setTimeout(function () {
            if (overlay) overlay.style.display = 'none';
        }, 1450);
    }

    // Dengarkan klik pengguna di mana saja pada overlay screen
    overlay.addEventListener('click', function () {
        triggerOpen();
    });
})();
</script>
