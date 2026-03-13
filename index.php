<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PromptPay QR Generator</title>
    <link rel="icon" type="image/png" href="api/slip/uploads/logo/icon-website.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="container">
    <div class="api-key-header" style="text-align: right;">
        <a href="keys.php" class="btn-secondary" style="font-size: 0.8rem; padding: 6px 12px; border-radius: 20px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3m-3-3l2.5-2.5"></path></svg>
            จัดการ API Keys
        </a>
    </div>

    <h1>PromptPay QR</h1>
    <p class="subtitle">สร้าง QR Code พร้อมเพย์ง่ายๆ แค่ระบุจำนวนเงิน</p>
    
    <div class="form-group">
        <label for="phone">เบอร์พร้อมเพย์ / PromptPay ID</label>
        <div class="input-wrapper">
            <span style="font-size: 1.2rem;">📱</span>
            <input type="text" id="phone" placeholder="08xxxxxxxx หรือ เลขบัตรประชาชน 13 หลัก" maxlength="15">
        </div>
    </div>

    <div class="form-group">
        <label for="amount">จำนวนเงินที่ต้องการรับ (บาท)</label>
        <div class="input-wrapper">
            <span>฿</span>
            <input type="number" id="amount" placeholder="0.00" step="0.01" min="0">
        </div>
    </div>
    
    <button id="generateBtn" class="btn-primary" onclick="generateQR()">
        <span id="btnText">สร้าง QR Code</span>
    </button>
    
    <div id="error-message" class="error"></div>

    <div id="result">
        <div class="qr-card">
            <img id="qr-image" src="" alt="PromptPay QR Code">
        </div>
        <div class="amount-display">
            <div class="amount-display">
            ยอดโอน <span id="display-amount" style="color: #667eea;"></span> บาท
        </div>
        
        </div>

        <button class="btn-secondary" onclick="downloadQR()" style="margin-top: 1rem; width: 100%; justify-content: center;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            บันทึกรูปภาพ
        </button>
    </div>

    <!-- Slip Verification Section -->
    <div class="slip-section">
        <div class="divider">
            <span>หรือ</span>
        </div>
        <p class="subtitle" style="margin-bottom: 1rem;">ตรวจสอบสลิปการโอนเงิน</p>
        
        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('slipInput').click()">
            <input type="file" id="slipInput" accept="image/*" hidden onchange="uploadSlip()">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" class="upload-icon">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="17 8 12 3 7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p>คลิกเพื่ออัพโหลดสลิป</p>
            <span class="file-name" id="fileName"></span>
        </div>

        <div id="verifyResult" class="verify-result"></div>
        </div>
    </div>
</div>

<script>
    const API_KEY = '<?php echo FRONTEND_API_KEY; ?>';
    let lastGeneratedAmount = null;
    let lastGeneratedPhone = null;

    function copyText(id) {
        const text = document.getElementById(id).textContent;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.querySelector(`button[onclick="copyText('${id}')"]`);
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="#48bb78" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            setTimeout(() => btn.innerHTML = originalHTML, 2000);
        });
    }

    async function generateQR() {
        const phoneInput = document.getElementById('phone');
        const phone = phoneInput.value.trim();
        const amountInput = document.getElementById('amount');
        const amount = amountInput.value.trim();
        const btn = document.getElementById('generateBtn');
        const btnText = document.getElementById('btnText');
        const resultDiv = document.getElementById('result');
        const errorDiv = document.getElementById('error-message');
        const qrImage = document.getElementById('qr-image');
        const displayAmount = document.getElementById('display-amount');

        // Reset state
        errorDiv.style.display = 'none';
        resultDiv.style.display = 'none';

        if (!phone) {
            showError('กรุณาระบุเบอร์พร้อมเพย์ หรือ PromptPay ID');
            return;
        }

        if (!amount || Number(amount) <= 0) {
            showError('กรุณาระบุจำนวนเงินให้ถูกต้อง');
            return;
        }

        try {
            btn.disabled = true;
            btnText.innerHTML = 'กำลังสร้าง <div class="spinner"></div>';

            const formData = new FormData();
            formData.append('phone', phone);
            formData.append('amount', amount);

            const response = await fetch('api/generate_qr.php', {
                method: 'POST',
                headers: {
                    'X-API-KEY': API_KEY
                },
                body: formData
            });

            const json = await response.json();

            if (!response.ok || !json.success) {
                throw new Error(json.error || 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
            }

            const data = json.data;

            if (data.qr_image) {
                // Preload image
                await new Promise((resolve, reject) => {
                    qrImage.onload = resolve;
                    qrImage.onerror = reject;
                    qrImage.src = data.qr_image;
                });

                displayAmount.textContent = Number(amount).toLocaleString('th-TH', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                });
                lastGeneratedAmount = amount;
                lastGeneratedPhone = phone;
                resultDiv.style.display = 'block';
            } else {
                throw new Error('ไม่พบข้อมูล QR Code จากระบบ');
            }

        } catch (error) {
            console.error('Error:', error);
            showError(error.message);
        } finally {
            btn.disabled = false;
            btnText.textContent = 'สร้าง QR Code';
        }
    }

    function showError(msg) {
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = msg;
        errorDiv.style.display = 'block';
    }

    async function downloadQR() {
        const img = document.getElementById('qr-image');
        const url = img.src;
        
        try {
            const response = await fetch(url);
            const blob = await response.blob();
            const blobUrl = window.URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = blobUrl;
            link.download = `promptpay-qr-${Date.now()}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(blobUrl);
        } catch (e) {
            console.error('Download failed', e);
            window.open(url, '_blank');
        }
    }

    async function uploadSlip() {
        const input = document.getElementById('slipInput');
        const file = input.files[0];
        const fileName = document.getElementById('fileName');
        const resultDiv = document.getElementById('verifyResult');

        if (!file) return;

        // Client-side validation
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            showError('กรุณาอัปโหลดไฟล์รูปภาพ (JPG, PNG) เท่านั้น');
            input.value = ''; // Reset input
            return;
        }

        fileName.textContent = file.name;
        resultDiv.className = 'verify-result';
        resultDiv.innerHTML = '<span class="spinner"></span> [0/2] เตรียมความพร้อม...';
        resultDiv.style.display = 'block';

        const fetchWithTimeout = async (url, options, timeout = 15000) => {
            const controller = new AbortController();
            const id = setTimeout(() => controller.abort(), timeout);
            try {
                const response = await fetch(url, { ...options, signal: controller.signal });
                clearTimeout(id);
                return response;
            } catch (error) {
                clearTimeout(id);
                throw error;
            }
        };

        try {
            // 1. Upload File
            resultDiv.innerHTML = '<span class="spinner"></span> [1/2] กำลังส่งสลิปไปยังเซิร์ฟเวอร์...';
            const uploadData = new FormData();
            uploadData.append('slip', file);

            const uploadRes = await fetchWithTimeout('api/slip/upload.php', {
                method: 'POST',
                body: uploadData
            }, 10000); // 10s for upload

            const uploadJson = await uploadRes.json();
            if (!uploadJson.success) throw new Error(uploadJson.error || 'Upload failed');

            // 2. Verify File
            resultDiv.innerHTML = '<span class="spinner"></span> [2/2] กำลังแกะข้อมูล QR และตรวจสอบ...<br><span style="font-size: 0.7rem; color: #a0aec0;">(ใช้เวลาประมาณ 5-10 วินาที)</span>';
            const verifyData = new FormData();
            verifyData.append('file', uploadJson.file);
            
            const amountToCheck = lastGeneratedAmount || document.getElementById('amount')?.value.trim() || '0';
            const phoneToCheck = lastGeneratedPhone || document.getElementById('phone')?.value.trim() || '';
            
            verifyData.append('phone', phoneToCheck);
            verifyData.append('amount', amountToCheck);

            const verifyRes = await fetch('api/slip/verify.php', {
                method: 'POST',
                body: verifyData
            });
            const verifyJson = await verifyRes.json();

            if (verifyJson.error) {
                throw new Error(verifyJson.error);
            }

            resultDiv.className = 'verify-result success';
            const slipData = verifyJson.data;
            resultDiv.innerHTML = `
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; color: #2f855a; font-weight: 600;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    ตรวจสอบสำเร็จ: ยอดเงินถูกต้อง
                </div>
                <div style="font-size: 0.85rem; padding-left: 28px; line-height: 1.5; color: #4a5568;">
                    <div>• ยอดเงินที่พบ: <strong>${Number(slipData.amount).toLocaleString('th-TH', {minimumFractionDigits: 2})} บาท</strong></div>
                    <div>• วันที่โอน: ${slipData.date}</div>
                    <div>• อ้างอิง: ${slipData.transRef || '-'}</div>
                    <div style="color: #a0aec0; margin-top: 4px; border-top: 1px dotted #e2e8f0; padding-top: 4px;">⚡ ประมวลผลใน: ${slipData.debug?.execution_time || 'N/A'}</div>
                </div>
                <div style="font-size: 0.75rem; margin-top: 10px; color: #a0aec0; text-align: center;">หน้าเว็บจะเริ่มใหม่ใน <span id="countdown">5</span> วินาที...</div>
            `;

            // Countdown timer
            let seconds = 5;
            const countdownInterval = setInterval(() => {
                seconds--;
                const countdownEl = document.getElementById('countdown');
                if (countdownEl) countdownEl.textContent = seconds;
                
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    window.location.reload();
                }
            }, 1000);


        } catch (error) {
            console.error(error);
            resultDiv.className = 'verify-result error-badge';
            resultDiv.textContent = 'ตรวจสอบไม่ผ่าน: ' + error.message;
        }
    }
</script>

</body>
</html>
