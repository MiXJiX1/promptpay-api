<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage API Keys - PromptPay API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .key-list {
            margin-top: 2rem;
            text-align: left;
        }
        .key-item {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        .key-item:hover {
            border-color: #cbd5e0;
            transform: scale(1.01);
        }
        .key-info {
            flex-grow: 1;
        }
        .key-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }
        .key-date {
            font-size: 0.75rem;
            color: #a0aec0;
        }
        .delete-btn {
            background: none;
            border: none;
            color: #e53e3e;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .delete-btn:hover {
            background: #fff5f5;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: var(--shadow-xl);
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #718096;
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #667eea;
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 500px;">
    <a href="index.php" class="back-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        กลับหน้าหลัก
    </a>

    <h1>API Key Management</h1>
    <p class="subtitle">สร้างและจัดการคีย์สำหรับเชื่อมต่อแอปภายนอก</p>

    <div class="form-group">
        <label for="newKeyName">ชื่อคีย์ (เช่น แอปของฉัน)</label>
        <div class="input-wrapper">
            <span style="font-size: 1.1rem;">🏷️</span>
            <input type="text" id="newKeyName" placeholder="ระบุชื่อเพื่อให้จำง่าย">
        </div>
    </div>
    
    <button class="btn-primary" onclick="createKey()">
        สร้าง API Key ใหม่
    </button>

    <div id="keyList" class="key-list">
        <!-- Keys will be loaded here -->
        <p style="color: #a0aec0; text-align: center;">กำลังโหลดข้อมูล...</p>
    </div>
</div>

<!-- Success Modal for new key -->
<div id="keyModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-top: 0; color: #48bb78;">สร้างคีย์สำเร็จ!</h3>
        <p style="font-size: 0.9rem; color: #718096; margin-bottom: 1.5rem;">คัดลอกคีย์นี้เก็บไว้ เพราะจะไม่แสดงให้เห็นอีกครั้ง</p>
        
        <div class="key-container" style="background: #f1f5f9; margin-bottom: 1.5rem;">
            <div class="key-label">Your New API Key</div>
            <div class="key-value-wrapper">
                <div id="newKeyValue" class="key-value" style="font-size: 0.8rem;"></div>
                <button class="copy-btn" onclick="copyText('newKeyValue')" title="Copy Key">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                </button>
            </div>
        </div>

        <button class="btn-primary" onclick="closeModal()">ตกลง</button>
    </div>
</div>

<script>
    async function fetchKeys() {
        try {
            const response = await fetch('api/keys/list.php');
            const res = await response.json();
            
            if (!res.success) throw new Error(res.error);
            
            const list = document.getElementById('keyList');
            if (res.data.length === 0) {
                list.innerHTML = '<p style="color: #a0aec0; text-align: center;">ยังไม่มี API Key</p>';
                return;
            }
            
            list.innerHTML = res.data.map(key => `
                <div class="key-item">
                    <div class="key-info">
                        <div class="key-name">${escapeHtml(key.name)}</div>
                        <div class="key-date">สร้างเมื่อ: ${new Date(key.created_at).toLocaleDateString('th-TH')}</div>
                    </div>
                    <button class="delete-btn" onclick="deleteKey(${key.id})" title="ลบคีย์">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            `).join('');
            
        } catch (error) {
            console.error(error);
            document.getElementById('keyList').innerHTML = `<p style="color: #e53e3e; text-align: center;">Error: ${error.message}</p>`;
        }
    }

    async function createKey() {
        const nameInput = document.getElementById('newKeyName');
        const name = nameInput.value.trim();
        if (!name) return alert('กรุณาระบุชื่อคีย์');
        
        try {
            const formData = new FormData();
            formData.append('name', name);
            
            const response = await fetch('api/keys/create.php', {
                method: 'POST',
                body: formData
            });
            const res = await response.json();
            
            if (!res.success) throw new Error(res.error);
            
            document.getElementById('newKeyValue').textContent = res.data.key;
            document.getElementById('keyModal').style.display = 'flex';
            nameInput.value = '';
            fetchKeys();
            
        } catch (error) {
            alert('สร้างคีย์ไม่สำเร็จ: ' + error.message);
        }
    }

    async function deleteKey(id) {
        if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบคีย์นี้? การเชื่อมต่อทั้งหมดที่ใช้คีย์นี้จะใช้งานไม่ได้ทันที')) return;
        
        try {
            const formData = new FormData();
            formData.append('id', id);
            
            const response = await fetch('api/keys/delete.php', {
                method: 'POST',
                body: formData
            });
            const res = await response.json();
            
            if (!res.success) throw new Error(res.error);
            fetchKeys();
            
        } catch (error) {
            alert('ลบคีย์ไม่สำเร็จ: ' + error.message);
        }
    }

    function closeModal() {
        document.getElementById('keyModal').style.display = 'none';
    }

    function copyText(id) {
        const text = document.getElementById(id).textContent;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.querySelector(`button[onclick="copyText('${id}')"]`);
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="#48bb78" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            setTimeout(() => btn.innerHTML = originalHTML, 2000);
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initial load
    fetchKeys();
</script>

</body>
</html>
