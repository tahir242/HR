
let scanner     = null;
let isFlashOn   = false;
let isScanning  = false;
let lastScannedValue = null;
let lastScanTime     = 0;
const SCAN_COOLDOWN_MS = 1500;

const statusEl = document.getElementById('scanStatus');
const readerEl = document.getElementById('reader');

/* ─── Focus Helper ─────────────────────────────────────────────────────────── */
function tryContinuousFocus() {
    if (!scanner) return Promise.resolve();
    return scanner.applyVideoConstraints({
        advanced: [{ focusMode: 'continuous' }]
    }).catch(() => Promise.resolve());
}

/* ─── Start Camera ─────────────────────────────────────────────────────────── */
function startScanner() {
    if (!window.isSecureContext) {
        Swal.fire('Error', 'Camera access requires HTTPS or localhost.', 'error');
        return;
    }

    const config = {
        fps: 15,
        qrbox: (w, h) => {
            const edge = Math.min(w, h);
            const size = Math.floor(edge * 0.8);
            return { width: size, height: size };
        },
        disableFlip: true,
        formatsToSupport: [
            Html5QrcodeSupportedFormats.CODE_128,
            Html5QrcodeSupportedFormats.CODE_39,
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8,
            Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E,
            Html5QrcodeSupportedFormats.QR_CODE
        ],
        experimentalFeatures: { useBarCodeDetectorIfSupported: true }
    };

    const startWithCamera = (camCfg) => {
        scanner = new Html5Qrcode('reader');
        return scanner.start(camCfg, config, onScanSuccess, onScanFailure);
    };

    const tryConfigs = (configs) =>
        configs.reduce((p, cfg) => p.catch(() => startWithCamera(cfg)), Promise.reject());

    const preferred = [
        { facingMode: { exact: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
        { facingMode: 'environment',            width: { ideal: 1280 }, height: { ideal: 720 } },
        { facingMode: 'environment',            width: { ideal: 1920 }, height: { ideal: 1080 } },
        { facingMode: 'environment' }
    ];

    tryConfigs(preferred)
    .catch(() =>
        Html5Qrcode.getCameras().then(cameras => {
            if (!cameras || !cameras.length) throw new Error('No camera found');
            const back = cameras.find(c => /back|rear|environment/i.test(c.label));
            return startWithCamera({ deviceId: { exact: (back || cameras[0]).id } });
        })
    )
    .then(() => {
        isScanning       = true;
        lastScannedValue = null;
        document.getElementById('startBtn').classList.add('d-none');
        document.getElementById('stopBtn').classList.remove('d-none');
        document.getElementById('flashBtn').classList.remove('d-none');
        document.getElementById('uploadBtn').classList.remove('d-none');
        if (statusEl) statusEl.textContent = 'Point the camera at the barcode';
        tryContinuousFocus();
    })
    .catch(err => Swal.fire('Error', 'Failed to start camera: ' + err, 'error'));
}

/* ─── Stop Camera ──────────────────────────────────────────────────────────── */
function stopScanner() {
    if (!scanner) return Promise.resolve();
    return scanner.stop().then(() => {
        isScanning = false;
        scanner    = null;
        document.getElementById('startBtn').classList.remove('d-none');
        document.getElementById('stopBtn').classList.add('d-none');
        document.getElementById('flashBtn').classList.add('d-none');
        document.getElementById('flashBtn').innerHTML = '<i class="fa fa-bolt"></i> Switch On Torch';
        isFlashOn = false;
        document.getElementById('uploadBtn').classList.add('d-none');
        if (statusEl) statusEl.textContent = 'Scanner stopped';
    }).catch(err => Swal.fire('Error', 'Failed to stop camera: ' + err, 'error'));
}

/* ─── Torch Toggle ─────────────────────────────────────────────────────────── */
function toggleFlash() {
    if (!scanner) return;
    isFlashOn = !isFlashOn;
    scanner.applyVideoConstraints({ advanced: [{ torch: isFlashOn }] })
    .then(() => {
        document.getElementById('flashBtn').innerHTML = isFlashOn
            ? '<i class="fa fa-bolt"></i> Switch Off Torch'
            : '<i class="fa fa-bolt"></i> Switch On Torch';
    })
    .catch(() => {
        isFlashOn = false;
        document.getElementById('flashBtn').innerHTML = '<i class="fa fa-bolt"></i> Switch On Torch';
        Swal.fire('Warning', 'Torch is not supported on this device.', 'warning');
    });
}

/* ─── Scan Callbacks ───────────────────────────────────────────────────────── */
function onScanSuccess(decodedText) {
    const now = Date.now();
    if (!decodedText) return;
    if (decodedText === lastScannedValue && now - lastScanTime < SCAN_COOLDOWN_MS) return;
    lastScannedValue = decodedText;
    lastScanTime     = now;
    if (navigator.vibrate) navigator.vibrate(50);
    sendData(decodedText.trim(), true);
}

function onScanFailure() {
    if (statusEl && isScanning) statusEl.textContent = 'Scanning...';
}

/* ─── Scan From Image File ─────────────────────────────────────────────────── */
function scanImage() {
    const file = document.getElementById('uploadInput').files[0];
    if (!file) {
        Swal.fire('Warning', 'Please select an image file first.', 'warning');
        return;
    }
    const imgScanner = new Html5Qrcode('reader');
    imgScanner.scanFile(file, true)
        .then(decodedText => sendData(decodedText, false))
        .catch(err => Swal.fire('Error', 'Failed to scan image: ' + err, 'error'));
}

/* ─── Manual Code Entry ────────────────────────────────────────────────────── */
document.getElementById('empCodeInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        submitManualCode();
    }
});

function submitManualCode() {
    const text = document.getElementById('empCodeInput').value.trim();
    if (!text) {
        Swal.fire('Warning', 'Please enter an Employee ID.', 'warning');
        return;
    }
    sendData(text, false);
}

/* ─── Send to Server ───────────────────────────────────────────────────────── */
function sendData(decodedText, isQRCode) {
    const stop = isQRCode ? stopScanner() : Promise.resolve();
    stop.then(() => {
        if (statusEl) statusEl.textContent = 'Validating...';
        axios.post('../_inc/ration.php', {
            action_type: 'SUBMITBARCODE',
            value: decodedText
        }).then(response => {
            if (response.data.valid === true) {
                showIssueDialog(response.data.data, decodedText, isQRCode);
            } else {
                Swal.fire({
                    html: response.data.msg,
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then(() => {
                    if (isQRCode) startScanner();
                    if (statusEl) statusEl.textContent = 'Ready';
                });
            }
        }).catch(error => {
            Swal.fire({
                title: 'Error!',
                text: error.response?.data?.errorMsg || 'An unknown error occurred.',
                icon: 'error'
            }).then(() => {
                if (isQRCode) startScanner();
                if (statusEl) statusEl.textContent = 'Ready';
            });
        });
    });
}

/* ─── Employee Detail Dialog ───────────────────────────────────────────────── */
function showIssueDialog(employee, decodedText, isQRCode) {
    const row = (label, value) => `
        <div class="row border-bottom py-1">
            <div class="col-4" style="color:#3085d6;font-weight:600;">${label}</div>
            <div class="col-1">:</div>
            <div class="col-7" style="color:#333;">${value}</div>
        </div>`;

    const cardHTML = `
        <div class="rounded p-2" style="background:#f1f3f5;font-size:0.95rem;text-align:left;">
            ${row('Code',        employee.Employee_ID)}
            ${row('Name',        employee.Name)}
            ${row('Department',  employee.Department)}
            ${row('Designation', employee.Designation)}
            ${row('CNIC',        employee.CNIC)}
        </div>`;

    const buttonsHTML = `
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:8px;">
            <button id="issueOil"    class="swal2-confirm swal2-styled">Issue with 10 Kg Oil</button>
            <button id="issueGhee"   class="swal2-confirm swal2-styled">Issue with 10 Kg Ghee</button>
            <button id="issueBoth"   class="swal2-confirm swal2-styled">Issue with 5 Kg Oil &amp; 5 Kg Ghee</button>
            <button id="cancelIssue" class="swal2-cancel swal2-styled" style="background:#e74c3c;">Cancel</button>
        </div>`;

    Swal.fire({
        title: 'Issue Ration',
        html: cardHTML + buttonsHTML,
        showConfirmButton: false,
        showCancelButton:  false,
        didOpen: () => {
            document.getElementById('issueOil').addEventListener('click',    () => sendIssue(decodedText, 'Oil',  isQRCode));
            document.getElementById('issueGhee').addEventListener('click',   () => sendIssue(decodedText, 'Ghee', isQRCode));
            document.getElementById('issueBoth').addEventListener('click',   () => sendIssue(decodedText, 'Both', isQRCode));
            document.getElementById('cancelIssue').addEventListener('click', () => {
                Swal.close();
                if (isQRCode) startScanner();
                document.getElementById('empCodeInput').value = '';
                if (statusEl) statusEl.textContent = 'Ready';
            });
        }
    });
}

/* ─── Submit Issue ─────────────────────────────────────────────────────────── */
function sendIssue(decodedText, issueType, isQRCode) {
    axios.post('../_inc/ration.php', {
        action_type: 'ISSUERATION',
        value: decodedText,
        issue: issueType
    }).then(response => {
        if (response.data.valid === true) {
            Swal.fire({
                title: 'Ration Issued!',
                icon:  'success',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                if (isQRCode) startScanner();
                document.getElementById('empCodeInput').value = '';
                if (statusEl) statusEl.textContent = 'Ready';
            });
        } else {
            Swal.fire({
                html: response.data.msg,
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                if (isQRCode) startScanner();
                document.getElementById('empCodeInput').value = '';
                if (statusEl) statusEl.textContent = 'Ready';
            });
        }
    }).catch(error => {
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.errorMsg || 'An unknown error occurred.',
            icon: 'error',
            confirmButtonText: 'Okay!'
        }).then(() => {
            if (isQRCode) startScanner();
            if (statusEl) statusEl.textContent = 'Ready';
        });
    });
}

/* ─── Image Upload Listener ────────────────────────────────────────────────── */
document.getElementById('uploadInput').addEventListener('change', function () {
    if (this.files && this.files.length > 0) {
        scanImage();
    }
});

/* ─── Click-to-focus on reader ────────────────────────────────────────────── */
if (readerEl) {
    readerEl.addEventListener('click', tryContinuousFocus);
}
