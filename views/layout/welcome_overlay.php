<?php
/**
 * views/layout/welcome_overlay.php
 * Efek gorden soft pastel + kelopak bunga sakura khusus Super Admin.
 * GORDEN DIBUKA INTERAKTIF DENGAN KLIK PENGGUNA.
 * HUJAN KELOPAK BUNGA BERLANGSUNG TERUS-MENERUS (INFINITE) SAMPAI GORDEN DIKLIK.
 */
$roleUser = $_SESSION['user']['role'] ?? '';
if ($roleUser !== 'super_admin') {
    return;
}

$namaUser = $_SESSION['user']['nama'] ?? 'Super Admin';
?>
<div id="welcome-overlay">
    <!-- Kelopak bunga sakura & kilau -->
    <div id="petal-container"></div>

    <!-- Card Teks Sambutan Soft Glassmorphism -->
    <div class="welcome-text-wrap">
        <div class="welcome-card">
            <div class="welcome-crown">
                <i class="fas fa-crown"></i>
            </div>
            <div class="welcome-greeting">Selamat Datang Kembali</div>
            <div class="welcome-nama"><?= htmlspecialchars($namaUser) ?></div>
            <div class="welcome-role-badge">
                <i class="fas fa-user-shield me-1.5"></i> Super Admin
            </div>
        </div>
    </div>

    <!-- Gorden Kiri & Kanan (Soft Curtain Folds) -->
    <div class="curtain curtain-left">
        <div class="curtain-folds"></div>
    </div>
    <div class="curtain curtain-right">
        <div class="curtain-folds"></div>
    </div>
</div>

<style>
#welcome-overlay {
    position: fixed;
    inset: 0;
    z-index: 999999;
    pointer-events: auto;
    cursor: pointer;
    overflow: hidden;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    user-select: none;
}

/* ── SOFT CURTAIN STYLING (Lipatan Gorden Soft Pink) ── */
.curtain {
    position: fixed;
    top: 0;
    bottom: 0;
    width: 50.5%;
    z-index: 2;
    transition: transform 1.25s cubic-bezier(0.77, 0, 0.175, 1);
    box-shadow: 0 0 30px rgba(125, 38, 69, 0.18);
}

.curtain-left {
    left: 0;
    background: linear-gradient(135deg, #FDF0F5 0%, #F8D3E2 50%, #EEA7C3 100%);
    border-right: 3px solid rgba(232, 120, 154, 0.5);
}

.curtain-right {
    right: 0;
    background: linear-gradient(225deg, #FDF0F5 0%, #F8D3E2 50%, #EEA7C3 100%);
    border-left: 3px solid rgba(232, 120, 154, 0.5);
}

/* Efek Lipatan Gorden Kain Sutra */
.curtain-folds {
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(
        90deg,
        rgba(255, 255, 255, 0.55) 0px,
        rgba(255, 255, 255, 0.15) 18px,
        rgba(176, 48, 96, 0.08) 36px,
        rgba(100, 20, 50, 0.04) 54px
    );
    opacity: 0.85;
}

.curtain.open.curtain-left {
    transform: translateX(-100%);
}
.curtain.open.curtain-right {
    transform: translateX(100%);
}

/* ── SAMBUTAN SOFT GLASSMORPHISM CARD ── */
.welcome-text-wrap {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 3;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    width: 90%;
    max-width: 440px;
}

.welcome-card {
    background: rgba(255, 245, 249, 0.92);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1.5px solid rgba(232, 120, 154, 0.45);
    box-shadow: 0 20px 50px rgba(125, 38, 69, 0.2), 0 0 30px rgba(255, 255, 255, 0.9);
    border-radius: 24px;
    padding: 36px 30px;
    color: #4A1527;
    animation: welcomeCardPop 0.6s cubic-bezier(0.16, 1, .3, 1) both;
}

@keyframes welcomeCardPop {
    0% { opacity: 0; transform: scale(0.88); }
    100% { opacity: 1; transform: scale(1); }
}

.welcome-text-wrap.fade {
    opacity: 0;
    transform: translate(-50%, -60%) scale(0.92);
}

.welcome-crown {
    font-size: 28px;
    color: #D4AF37;
    margin-bottom: 8px;
    filter: drop-shadow(0 2px 6px rgba(212, 175, 55, 0.4));
    animation: crownPulse 1.8s ease-in-out infinite;
}

@keyframes crownPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.12); }
}

.welcome-greeting {
    font-size: 12.5px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: #B03060;
    font-weight: 700;
}

.welcome-nama {
    font-size: 26px;
    font-weight: 800;
    color: #3A0C1E;
    margin-top: 6px;
    line-height: 1.25;
}

.welcome-role-badge {
    display: inline-flex;
    align-items: center;
    margin-top: 14px;
    padding: 5px 16px;
    background: rgba(176, 48, 96, 0.1);
    border: 1px solid rgba(176, 48, 96, 0.25);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    color: #B03060;
}

/* ── KELOPAK BUNGA SAKURA (HUJAN CONTINUOUS INFINITE) ── */
#petal-container {
    position: fixed;
    inset: 0;
    z-index: 4;
    overflow: hidden;
    transition: opacity 0.5s ease;
}

.petal {
    position: absolute;
    top: -50px;
    opacity: 0.9;
    animation-name: petal-fall;
    animation-timing-function: linear;
    animation-iteration-count: infinite; /* Hujan tanpa henti sampai diklik */
    will-change: transform;
    filter: drop-shadow(0 2px 4px rgba(176, 48, 96, 0.12));
}

@keyframes petal-fall {
    0% {
        transform: translateY(0) translateX(0) rotate(0deg);
        opacity: 0.95;
    }
    85% {
        opacity: 0.85;
    }
    100% {
        transform: translateY(108vh) translateX(var(--drift)) rotate(var(--spin));
        opacity: 0.2;
    }
}

@media (prefers-reduced-motion: reduce) {
    #welcome-overlay { display: none !important; }
}
</style>

<script>
(function () {
    var overlay = document.getElementById('welcome-overlay');
    var petalContainer = document.getElementById('petal-container');
    var welcomeWrap = overlay.querySelector('.welcome-text-wrap');
    var curtains = overlay.querySelectorAll('.curtain');
    var hasOpened = false;

    // Sebar kelopak bunga sakura acak dengan durasi hujan kontinyu
    var jenisBunga = ['🌸', '🌸', '💮', '✨', '🌸', '🌼', '🌹'];
    var jumlahBunga = 36;

    for (var i = 0; i < jumlahBunga; i++) {
        var petal = document.createElement('div');
        petal.className = 'petal';
        petal.textContent = jenisBunga[Math.floor(Math.random() * jenisBunga.length)];
        petal.style.left = (Math.random() * 98) + 'vw';
        petal.style.setProperty('--drift', (Math.random() * 180 - 90) + 'px');
        petal.style.setProperty('--spin', (Math.random() * 720 - 360) + 'deg');
        petal.style.animationDuration = (3.5 + Math.random() * 3.5) + 's';
        petal.style.animationDelay = (Math.random() * 3.0) + 's';
        petal.style.fontSize = (15 + Math.random() * 15) + 'px';
        petalContainer.appendChild(petal);
    }

    // FUNGSI MEMBUKA GORDEN SAAT DIKLIK:
    function triggerCurtainOpen() {
        if (hasOpened) return;
        hasOpened = true;

        if (welcomeWrap) welcomeWrap.classList.add('fade');
        if (petalContainer) petalContainer.style.opacity = '0';

        setTimeout(function () {
            curtains.forEach(function (c) { c.classList.add('open'); });
        }, 150);

        setTimeout(function () {
            if (overlay) overlay.style.display = 'none';
        }, 1450);
    }

    // Dengarkan Klik di Mana Saja pada Overlay Screen
    if (overlay) {
        overlay.addEventListener('click', function (e) {
            triggerCurtainOpen();
        });
    }
})();
</script>
