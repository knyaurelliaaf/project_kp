const express = require('express');
const cors = require('cors');
const fs = require('fs');
const path = require('path');
const http = require('http');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');

const app = express();
const PORT = process.env.PORT || 3000;
const API_KEY = process.env.WA_SERVICE_API_KEY || 'kp_wa_secret_key_2026';

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Global State
let client = null;
let clientStatus = 'INITIALIZING'; // INITIALIZING, QR, PAIRING, CONNECTED, DISCONNECTED, ERROR
let qrRawData = null;
let pairingCodeData = null;
let waErrorMessage = null;
let isSending = false;
let sendProgress = { current: 0, total: 0, periode: null };
let pairingPhone = null;

// Helper: Auto-detect Chrome Path
function getChromeExecutablePath() {
    const possiblePaths = [
        '/usr/bin/google-chrome',
        '/usr/bin/google-chrome-stable',
        '/usr/bin/chromium-browser',
        '/usr/bin/chromium',
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe'
    ];
    for (const p of possiblePaths) {
        if (fs.existsSync(p)) return p;
    }
    return null;
}

// Authentication Middleware
function checkApiKey(req, res, next) {
    const key = req.headers['x-api-key'] || req.query.api_key || req.body?.api_key;
    if (key && key === API_KEY) {
        return next();
    }
    return res.status(401).json({ success: false, error: 'Unauthorized: Invalid API Key' });
}

// Initialize WhatsApp Client
function initWhatsAppClient(targetPhone = null) {
    console.log('🔄 Initializing WhatsApp Client...');
    clientStatus = 'INITIALIZING';
    qrRawData = null;
    pairingCodeData = null;
    waErrorMessage = null;
    pairingPhone = targetPhone;

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

    const chromePath = getChromeExecutablePath();
    if (chromePath) {
        puppeteerConfig.executablePath = chromePath;
    }

    const sessionDir = path.join(__dirname, 'whatsapp-session');
    
    let pairWithPhoneNumber = undefined;
    if (targetPhone) {
        const cleanPhone = targetPhone.replace(/[^0-9]/g, '');
        if (cleanPhone.length >= 10) {
            pairWithPhoneNumber = {
                phoneNumber: cleanPhone,
                showNotification: true,
                intervalMs: 180000
            };
        }
    }

    client = new Client({
        authStrategy: new LocalAuth({ dataPath: sessionDir }),
        webVersionCache: {
            type: 'remote',
            remotePath: 'https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.3000.1014133501-alpha.html'
        },
        pairWithPhoneNumber,
        puppeteer: puppeteerConfig
    });

    client.on('qr', qr => {
        console.log('📱 New QR Code generated');
        qrRawData = qr;
        clientStatus = 'QR';
    });

    client.on('code', code => {
        console.log(`🔑 Pairing code received: ${code}`);
        pairingCodeData = code;
        qrRawData = null;
        clientStatus = 'PAIRING';
    });

    client.on('authenticated', () => {
        console.log('🔒 WhatsApp Authenticated');
        clientStatus = 'AUTHENTICATED';
    });

    client.on('ready', () => {
        console.log('✅ WhatsApp Client is Ready & Connected!');
        clientStatus = 'CONNECTED';
        qrRawData = null;
        pairingCodeData = null;
        waErrorMessage = null;
    });

    client.on('auth_failure', msg => {
        console.error('❌ WA Auth Failure:', msg);
        clientStatus = 'ERROR';
        waErrorMessage = `Authentication failed: ${msg}`;
    });

    client.on('disconnected', reason => {
        console.log(`⚠️ WA Disconnected: ${reason}`);
        clientStatus = 'DISCONNECTED';
        qrRawData = null;
        pairingCodeData = null;
    });

    client.initialize().catch(err => {
        console.error('❌ Initialization error:', err.message);
        clientStatus = 'ERROR';
        waErrorMessage = err.message;
    });
}

// Clear Session Directory
function clearSessionFolder() {
    const sessionDir = path.join(__dirname, 'whatsapp-session');
    if (fs.existsSync(sessionDir)) {
        try {
            fs.rmSync(sessionDir, { recursive: true, force: true });
            console.log('🧹 Session directory deleted successfully');
        } catch (err) {
            console.error('⚠️ Failed to delete session directory:', err.message);
        }
    }
}

// ENDPOINTS

// 1. Health Check
app.get('/', (req, res) => {
    res.json({ service: 'WA-Service-v2', status: clientStatus, time: new Date() });
});

// 2. Status Endpoint
app.get('/status', checkApiKey, (req, res) => {
    res.json({
        status: clientStatus,
        authenticated: clientStatus === 'CONNECTED',
        qrRaw: qrRawData,
        pairingCode: pairingCodeData,
        loading: clientStatus === 'INITIALIZING',
        error: waErrorMessage,
        sending: isSending,
        progress: sendProgress
    });
});

// 3. Reset Session Endpoint (Programmatically destroys & re-inits)
app.post('/reset-session', checkApiKey, async (req, res) => {
    console.log('🔄 Reset session requested...');
    isSending = false;

    if (client) {
        try {
            await client.destroy();
        } catch (e) {
            console.error('Error destroying client:', e.message);
        }
        client = null;
    }

    // Short delay to let chromium exit completely
    setTimeout(() => {
        clearSessionFolder();
        initWhatsAppClient();
        res.json({ success: true, message: 'WhatsApp session reset successfully. Generating new QR code...' });
    }, 1000);
});

// 4. Request Pairing Code Endpoint
app.post('/request-pairing', checkApiKey, async (req, res) => {
    const phone = req.body.nomor_wa || req.body.phone || req.body.phoneNumber;
    if (!phone) {
        return res.status(400).json({ success: false, error: 'Nomor WA / Phone parameter is required' });
    }

    const cleanPhone = phone.replace(/[^0-9]/g, '');
    if (cleanPhone.length < 10) {
        return res.status(400).json({ success: false, error: 'Format nomor WA tidak valid' });
    }

    console.log(`🔑 Requesting pairing code for phone: ${cleanPhone}`);
    isSending = false;

    if (client) {
        try {
            await client.destroy();
        } catch (e) {}
        client = null;
    }

    setTimeout(() => {
        clearSessionFolder();
        initWhatsAppClient(cleanPhone);
        res.json({ success: true, message: `Request pairing code disiapkan untuk ${cleanPhone}` });
    }, 1000);
});

// 5. Send Batch Endpoint
app.post('/send-batch', checkApiKey, async (req, res) => {
    const { periode, dry_run, dryRun } = req.body;
    const isDryRunMode = Boolean(dry_run || dryRun || process.env.WA_DRY_RUN === 'true');

    if (!periode) {
        return res.status(400).json({ success: false, error: 'Periode is required' });
    }

    if (clientStatus !== 'CONNECTED' && !isDryRunMode) {
        return res.status(400).json({ success: false, error: 'WhatsApp client is not connected' });
    }

    if (isSending) {
        return res.status(409).json({ success: false, error: 'Proses pengiriman slip sedang berlangsung', progress: sendProgress });
    }

    // Determine Upload Directory (Local wa_service_v2 or fallback to wa_service)
    let uploadDir = path.join(__dirname, 'uploads', periode);
    if (!fs.existsSync(uploadDir)) {
        const fallbackDir = path.join(__dirname, '..', 'wa_service', 'uploads', periode);
        if (fs.existsSync(fallbackDir)) {
            uploadDir = fallbackDir;
        }
    }

    if (!fs.existsSync(uploadDir)) {
        return res.status(404).json({ success: false, error: `Folder upload untuk periode ${periode} tidak ditemukan` });
    }

    const files = fs.readdirSync(uploadDir).filter(f => f.toLowerCase().endsWith('.pdf'));
    if (files.length === 0) {
        return res.status(404).json({ success: false, error: `Tidak ada file PDF di periode ${periode}` });
    }

    // Start Sending Process in Background
    processSendBatch(uploadDir, files, periode, isDryRunMode);

    res.json({
        success: true,
        dryRun: isDryRunMode,
        message: `Pengiriman batch${isDryRunMode ? ' (SIMULASI / DRY RUN)' : ''} untuk periode ${periode} dimulai (${files.length} file PDF).`,
        totalFiles: files.length
    });
});

// 6. Stop Sending Endpoint
app.post('/stop-send', checkApiKey, (req, res) => {
    isSending = false;
    console.log('🛑 Batch sending process stopped manually');
    res.json({ success: true, message: 'Proses pengiriman telah dihentikan secara manual!' });
});

// Internal Async Batch Processor
async function processSendBatch(uploadDir, files, periode, isDryRun = false) {
    isSending = true;
    sendProgress = { current: 0, total: files.length, periode, dryRun: isDryRun };

    const mappingFile = path.join(uploadDir, 'mapping.json');
    let mapping = {};
    if (fs.existsSync(mappingFile)) {
        try {
            mapping = JSON.parse(fs.readFileSync(mappingFile, 'utf-8'));
        } catch (e) {
            console.error('Error reading mapping.json:', e.message);
        }
    }

    console.log(`🚀 Starting batch sending for ${files.length} salary slips (Periode: ${periode})...`);

    const DELAY_MIN = 12000;  // Jeda minimal 12 detik antar pesan
    const DELAY_MAX = 25000;  // Jeda maksimal 25 detik antar pesan (randomized)
    const BATCH_SIZE = 15;    // Istirahat setiap 15 pesan
    const REST_MIN = 180000;  // Istirahat batch minimal 3 menit (180 detik)
    const REST_MAX = 300000;  // Istirahat batch maksimal 5 menit (300 detik)

    let count = 0;

    for (const file of files) {
        if (!isSending) break; // Allow cancel if reset requested

        const identifier = path.parse(file).name;
        const filePath = path.join(uploadDir, file);
        const penerima = mapping[identifier];

        if (!penerima) {
            console.log(`⚠️ ${identifier} tidak ada di mapping.json, status: gagal`);
            updateStatusToPhp(identifier, periode, 'gagal', 'Penerima tidak terdaftar / nonaktif');
            continue;
        }

        // STRICT ANTI-DUPLICATE: Skip if already marked as terkirim!
        if (penerima.status === 'terkirim') {
            console.log(`⏩ [SKIP] Slip gaji untuk ${penerima.nama} SUDAH TERKIRIM. Melewati untuk mencegah pesan ganda.`);
            continue;
        }

        let noWaClean = (penerima.nomor_wa || '').replace(/[^0-9]/g, '');
        if (noWaClean.startsWith('0')) {
            noWaClean = '62' + noWaClean.substring(1);
        }

        if (!noWaClean || noWaClean.length < 9) {
            console.log(`⚠️ Nomor WA (${penerima.nama}) tidak valid, status: gagal`);
            updateStatusToPhp(identifier, periode, 'gagal', 'Nomor WA tidak valid');
            continue;
        }

        try {
            const chatId = noWaClean + '@c.us';
            const media = MessageMedia.fromFilePath(filePath);
            const captionPesan = `Selamat pagi Pak. Berikut kami kirimkan slip gaji bulan ini. Terima kasih.`;

            if (isDryRun) {
                count++;
                sendProgress.current = count;
                console.log(`🧪 [SIMULATION ${count}/${files.length}] Slip terverifikasi untuk ${penerima.nama} (${noWaClean}) [DRY-RUN - WA TIDAK DIKIRIM]`);
                updateStatusToPhp(identifier, periode, 'terkirim');
            } else {
                await client.sendMessage(chatId, media, {
                    caption: captionPesan
                });

                count++;
                sendProgress.current = count;
                console.log(`✅ [${count}/${files.length}] Slip terkirim ke ${penerima.nama} (${noWaClean})`);
                updateStatusToPhp(identifier, periode, 'terkirim');
            }

        } catch (err) {
            console.error(`❌ Gagal kirim ke ${penerima.nama}: ${err.message}`);
            updateStatusToPhp(identifier, periode, 'gagal', err.message);
        }

        // Delay anti-banned
        const delay = Math.floor(Math.random() * (DELAY_MAX - DELAY_MIN) + DELAY_MIN);
        await sleep(delay);

        // Batch Rest
        if (count > 0 && count % BATCH_SIZE === 0 && count < files.length) {
            const rest = Math.floor(Math.random() * (REST_MAX - REST_MIN) + REST_MIN);
            console.log(`⏸️ Batch rest for ${Math.round(rest / 1000)} seconds...`);
            await sleep(rest);
        }
    }

    console.log(`🎉 Batch sending for ${periode} completed!`);
    isSending = false;
}

// Send Status Callback to PHP API & Sync mapping.json on disk
function updateStatusToPhp(identifier, periode, status, error = '') {
    try {
        // 1. Sync local mapping.json on disk immediately
        if (periode) {
            const uploadDir = path.join(__dirname, 'uploads', periode);
            const mappingFile = path.join(uploadDir, 'mapping.json');
            if (fs.existsSync(mappingFile)) {
                try {
                    let mapping = JSON.parse(fs.readFileSync(mappingFile, 'utf-8'));
                    if (mapping[identifier]) {
                        mapping[identifier].status = status;
                        if (error) mapping[identifier].error = error;
                        fs.writeFileSync(mappingFile, JSON.stringify(mapping, null, 4), 'utf-8');
                    }
                } catch (errMap) {
                    console.error('Error updating local mapping.json:', errMap.message);
                }
            }
        }

        // 2. Send callback to PHP backend
        const data = JSON.stringify({ identifier, nik: identifier, periode, status, error });
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
            console.error('Error updating PHP status:', e.message);
        });
        req.write(data);
        req.end();
    } catch (e) {
        console.error('Exception in updateStatusToPhp:', e.message);
    }
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// Start HTTP Server & Initialize WhatsApp
app.listen(PORT, () => {
    console.log(`🚀 WA-Service-v2 is running on http://localhost:${PORT}`);
    initWhatsAppClient();
});
