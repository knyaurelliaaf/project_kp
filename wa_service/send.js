const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const fs = require('fs');
const path = require('path');

// Konfigurasi Periode dari Argumen CLI
// Konfigurasi Periode & Phone dari Argumen CLI
let PERIODE = dateToYearMonth(new Date());
let PHONE = null;

for (let i = 2; i < process.argv.length; i++) {
    if (process.argv[i] === '--periode' && process.argv[i + 1]) {
        PERIODE = process.argv[i + 1];
    } else if (process.argv[i] === '--phone' && process.argv[i + 1]) {
        PHONE = process.argv[i + 1];
    }
}

const phoneFile = path.join(__dirname, 'phone.txt');
if (!PHONE && fs.existsSync(phoneFile)) {
    try {
        PHONE = fs.readFileSync(phoneFile, 'utf-8').trim();
    } catch(e) {}
}

function dateToYearMonth(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    return `${y}-${m}`;
}

const UPLOAD_DIR = path.join(__dirname, 'uploads', PERIODE);
const DELAY_MIN = 12000;  // Jeda minimal 12 detik antar pesan
const DELAY_MAX = 25000;  // Jeda maksimal 25 detik antar pesan (randomized)
const BATCH_SIZE = 15;    // Istirahat setiap 15 pesan
const REST_MIN = 180000;  // Istirahat batch minimal 3 menit (180 detik)
const REST_MAX = 300000;  // Istirahat batch maksimal 5 menit (300 detik)

if (!fs.existsSync(UPLOAD_DIR)) {
    try { fs.mkdirSync(UPLOAD_DIR, { recursive: true }); } catch(e) {}
}

// Baca daftar file PDF
let files = [];
if (fs.existsSync(UPLOAD_DIR)) {
    files = fs.readdirSync(UPLOAD_DIR).filter(f => f.toLowerCase().endsWith('.pdf'));
}

console.log(`📋 Periode: ${PERIODE}`);
console.log(`📂 Ditemukan ${files.length} file slip gaji`);

// Baca mapping JSON
const mappingFile = path.join(UPLOAD_DIR, 'mapping.json');
let mapping = {};
if (fs.existsSync(mappingFile)) {
    try {
        mapping = JSON.parse(fs.readFileSync(mappingFile, 'utf-8'));
    } catch (e) {
        console.error('⚠️ Gagal membaca mapping.json:', e.message);
    }
}

// Auto-Detect Chrome Executable Path across OS (Windows, Linux Hosting, VPS)
let chromeExecutablePath = null;
const possiblePaths = [
    '/usr/bin/google-chrome',
    '/usr/bin/google-chrome-stable',
    '/usr/bin/chromium-browser',
    '/usr/bin/chromium',
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe'
];

for (const p of possiblePaths) {
    if (fs.existsSync(p)) {
        chromeExecutablePath = p;
        break;
    }
}

const puppeteerConfig = {
    headless: true,
    args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-accelerated-2d-canvas',
        '--no-first-run',
        '--no-zygote',
        '--disable-gpu'
    ]
};

if (chromeExecutablePath) {
    puppeteerConfig.executablePath = chromeExecutablePath;
}

// Aktifkan pairing code sejak client dibuat. Ini adalah alur yang didukung
// whatsapp-web.js untuk menautkan perangkat dengan nomor telepon.
const pairWithPhoneNumber = PHONE && PHONE.replace(/[^0-9]/g, '').length >= 10
    ? {
        phoneNumber: PHONE.replace(/[^0-9]/g, ''),
        showNotification: true,
        intervalMs: 180000
    }
    : undefined;
const client = new Client({
    authStrategy: new LocalAuth({ dataPath: path.join(__dirname, 'whatsapp-session') }),
    webVersionCache: {
        type: 'remote',
        remotePath: 'https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.3000.1014133501-alpha.html'
    },
    pairWithPhoneNumber,
    puppeteer: puppeteerConfig
});

client.on('qr', async qr => {
    console.log('\n📱 QR Code Generated');

    // Dalam mode pairing, kode diterima melalui event `code` di bawah ini.
    if (PHONE) return;
    if (false) {
        // Pairing code mode — do NOT write qr.txt so QR panel doesn't flash
        try {
            const cleanPhone = PHONE.replace(/[^0-9]/g, '');
            if (cleanPhone.length >= 10) {
                console.log(`📞 Requesting pairing code for ${cleanPhone}...`);
                const pairingCode = await client.requestPairingCode(cleanPhone);
                console.log(`\n🔑 KODE PAIRING WA (8-DIGIT): ${pairingCode}`);
                fs.writeFileSync(path.join(__dirname, 'pairing_code.txt'), pairingCode);
                // Remove qr.txt in case it existed
                try { fs.unlinkSync(path.join(__dirname, 'qr.txt')); } catch(e) {}
            } else {
                // Phone invalid, fall back to QR
                fs.writeFileSync(path.join(__dirname, 'qr.txt'), qr);
            }
        } catch(e) {
            console.error('⚠️ Gagal generate pairing code:', e.message, '— tampilkan QR biasa');
            // Fall back: show QR if pairing code request fails
            try { fs.writeFileSync(path.join(__dirname, 'qr.txt'), qr); } catch(e2) {}
        }
    } else {
        // Normal QR mode
        try {
            fs.writeFileSync(path.join(__dirname, 'qr.txt'), qr);
        } catch(e) {}
    }
});

client.on('code', pairingCode => {
    console.log(`Pairing code received: ${pairingCode}`);
    try {
        fs.writeFileSync(path.join(__dirname, 'pairing_code.txt'), pairingCode);
        try { fs.unlinkSync(path.join(__dirname, 'qr.txt')); } catch(e) {}
        try { fs.unlinkSync(path.join(__dirname, 'qr_loading.txt')); } catch(e) {}
    } catch(e) {
        console.error('Failed to save pairing code:', e.message);
    }
});

client.on('auth_failure', message => {
    console.error('WhatsApp authentication failed:', message);
    try {
        fs.writeFileSync(path.join(__dirname, 'wa_error.txt'), `Authentication failed: ${message}`);
        try { fs.unlinkSync(path.join(__dirname, 'qr_loading.txt')); } catch(e) {}
    } catch(e) {}
});

client.on('ready', async () => {
    console.log('\n✅ WhatsApp siap & terhubung!');
    try {
        const qrFile = path.join(__dirname, 'qr.txt');
        if (fs.existsSync(qrFile)) fs.unlinkSync(qrFile);
        const codeFile = path.join(__dirname, 'pairing_code.txt');
        if (fs.existsSync(codeFile)) fs.unlinkSync(codeFile);
        const phoneFile2 = path.join(__dirname, 'phone.txt');
        if (fs.existsSync(phoneFile2)) fs.unlinkSync(phoneFile2);
        const loadingFile = path.join(__dirname, 'qr_loading.txt');
        if (fs.existsSync(loadingFile)) fs.unlinkSync(loadingFile);
    } catch(e) {}

    if (files.length === 0) {
        console.log('⚠️ Belum ada file PDF slip gaji untuk dikirim. Sesi WA tersimpan & siap digunakan.');
        process.exit(0);
    } else {
        await startSending();
    }
});

client.on('disconnected', (reason) => {
    console.log(`❌ WhatsApp disconnected: ${reason}. Restart program.`);
    try {
        const qrFile = path.join(__dirname, 'qr.txt');
        if (fs.existsSync(qrFile)) fs.unlinkSync(qrFile);
    } catch(e) {}
    process.exit(1);
});

async function startSending() {
    let count = 0;
    
    for (const file of files) {
        const identifier = path.parse(file).name;
        const filePath = path.join(UPLOAD_DIR, file);
        
        const penerima = mapping[identifier];
        
        if (!penerima) {
            console.log(`⚠️ ${identifier} tidak ditemukan di daftar penerima/mapping, skip.`);
            updateStatus(identifier, 'gagal', 'Penerima tidak terdaftar / nonaktif');
            continue;
        }
        
        let noWaClean = (penerima.nomor_wa || '').replace(/[^0-9]/g, '');
        if (noWaClean.startsWith('0')) {
            noWaClean = '62' + noWaClean.substring(1);
        }
        
        if (!noWaClean || noWaClean.length < 9) {
            console.log(`⚠️ Nomor WA (${penerima.nama}) tidak valid, skip.`);
            updateStatus(identifier, 'gagal', 'Nomor WA tidak valid');
            continue;
        }
        
        try {
            const chatId = noWaClean + '@c.us';
            const media = MessageMedia.fromFilePath(filePath);
            
            const captionPesan = `Selamat pagi Pak. Berikut kami kirimkan slip gaji bulan ini. Terima kasih.`;

            await client.sendMessage(chatId, media, {
                caption: captionPesan
            });
            
            console.log(`✅ [${++count}/${files.length}] Terkirim ke ${penerima.nama} (${noWaClean})`);
            
            // Update status di database via API
            updateStatus(identifier, 'terkirim');
            
        } catch (err) {
            console.log(`❌ Gagal kirim ke ${penerima.nama}: ${err.message}`);
            updateStatus(identifier, 'gagal', err.message);
        }
        
        // Jeda random (anti-banned)
        const delay = Math.floor(Math.random() * (DELAY_MAX - DELAY_MIN) + DELAY_MIN);
        await sleep(delay);
        
        // Istirahat tiap batch
        if (count > 0 && count % BATCH_SIZE === 0 && count < files.length) {
            const rest = Math.floor(Math.random() * (REST_MAX - REST_MIN) + REST_MIN);
            console.log(`⏸️ Istirahat batch selama ${Math.round(rest/1000)} detik...`);
            await sleep(rest);
        }
    }
    
    console.log('\n🎉 Semua slip gaji selesai diproses!');
    process.exit(0);
}

function updateStatus(identifier, status, error = '') {
    try {
        const http = require('http');
        const data = JSON.stringify({ identifier, nik: identifier, periode: PERIODE, status, error });
        const req = http.request({
            hostname: 'localhost',
            port: 80,
            path: '/project_kp/admin_gaji/updateStatus',
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(data)
            }
        });
        req.on('error', (e) => {
            // Log error silently
        });
        req.write(data);
        req.end();
    } catch(e) {
        // Log exception silently
    }
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

client.initialize();
