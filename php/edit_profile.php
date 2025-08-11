<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$userId = (int)$user['id'];

// Пътища за оригинал и запазено състояние на кропа
$uploadsDir     = "../uploads/";
$originalsDir   = $uploadsDir . "originals/";
$cropsDir       = $uploadsDir . "crops/";

// Намираме оригиналната снимка по шаблон profile_{userId}.*
$originalSrc = '';
if (is_dir($originalsDir)) {
    $matches = glob($originalsDir . "profile_{$userId}.*");
    if ($matches && count($matches) > 0) {
        $originalSrc = "../uploads/originals/" . basename($matches[0]);
    }
}

// Зареждаме предишното състояние на кропа, ако има
$savedCrop = null;
$cropJsonPath = $cropsDir . "profile_{$userId}.json";
if (is_file($cropJsonPath)) {
    $json = @file_get_contents($cropJsonPath);
    if ($json) {
        $decoded = json_decode($json, true);
        if (is_array($decoded)) $savedCrop = $decoded;
    }
}

// Показваната (кропната) снимка – за други места в сайта
$currentCropped = !empty($user['profile_image'])
    ? "../uploads/" . htmlspecialchars($user['profile_image'])
    : "../img/default-profile.png";

// Ако имаме оригинал → за кропера ползваме него; иначе – падаме към кропнатата/дефолт
$initialForCropper = $originalSrc ?: $currentCropped;
$hasRealImage = $originalSrc ? 1 : (!empty($user['profile_image']) ? 1 : 0);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8" />
    <title>Редакция на профил - Fixora</title>
    <link rel="stylesheet" href="../css/edit-profile.css" />
</head>
<body>
    <div class="page-actions">
        <a href="profil.php" class="btn btn-secondary">⬅️ Назад към профила</a>
    </div>

    <div class="form-container card">
        <h2 class="card-title">Редакция на профил</h2>

        <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="profile-form">
            <!-- Снимка + Вграден кропер -->
            <div class="field-group">
                <label class="field-label">Снимка</label>

                <div class="profile-pic-wrapper" id="cropperArea" data-has-image="<?= $hasRealImage ?>">
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display:none">
                    <label for="profile_image" id="profilePicLabel" class="profile-pic-label" title="Кликни за избор на снимка">
                        <canvas id="cropCanvas" width="400" height="400"></canvas>
                    </label>
                </div>

                <div class="crop-controls">
                    <div class="zoom-row">
                        <button type="button" id="zoomOutBtn" class="zoom-btn" aria-label="Намали">−</button>
                        <input id="zoomRange" type="range" value="1" min="1" max="3" step="0.01">
                        <button type="button" id="zoomInBtn" class="zoom-btn" aria-label="Увеличи">+</button>
                    </div>
                    <small class="hint">Плъзни изображението с мишката/пръста, за да го позиционираш.</small>
                </div>

                <!-- Скрит PNG резултат + състояние на кропа -->
                <input type="hidden" name="cropped_image" id="cropped_image">
                <input type="hidden" name="crop_scale" id="crop_scale">
                <input type="hidden" name="crop_pos_x" id="crop_pos_x">
                <input type="hidden" name="crop_pos_y" id="crop_pos_y">
            </div>

            <!-- Полета -->
            <div class="form-grid">
                <div class="field-group">
                    <label class="field-label">Потребителско име</label>
                    <input class="input" type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="field-group">
                    <label class="field-label">Име</label>
                    <input class="input" type="text" name="ime" value="<?= htmlspecialchars($user['ime']) ?>" required>
                </div>

                <div class="field-group">
                    <label class="field-label">Фамилия</label>
                    <input class="input" type="text" name="familiq" value="<?= htmlspecialchars($user['familiq']) ?>" required>
                </div>

                <div class="field-group">
                    <label class="field-label">Имейл</label>
                    <input class="input" type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                    <label class="check"><input type="checkbox" name="show_email" <?= $user['show_email'] ? 'checked' : '' ?>> Показвай имейла</label>
                </div>

                <div class="field-group">
                    <label class="field-label">Телефон</label>
                    <input class="input" type="text" name="telefon" value="<?= htmlspecialchars($user['telefon']) ?>">
                    <label class="check"><input type="checkbox" name="show_phone" <?= $user['show_phone'] ? 'checked' : '' ?>> Показвай телефона</label>
                </div>

                <div class="field-group">
                    <label class="field-label">Град</label>
                    <input class="input" type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>">
                    <label class="check"><input type="checkbox" name="show_city" <?= $user['show_city'] ? 'checked' : '' ?>> Показвай града</label>
                </div>

                <div class="field-group">
                    <label class="field-label">Години</label>
                    <input class="input" type="number" name="age" value="<?= htmlspecialchars($user['age']) ?>">
                    <label class="check"><input type="checkbox" name="show_age" <?= $user['show_age'] ? 'checked' : '' ?>> Показвай възрастта</label>
                </div>

                <div class="field-group">
                    <label class="field-label">Стара парола</label>
                    <div class="password-field">
                        <input class="input" type="password" name="old_password" id="old_password" autocomplete="new-password">
                        <span class="toggle-password" onclick="togglePassword('old_password')">👁️</span>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Нова парола</label>
                    <div class="password-field">
                        <input class="input" type="password" name="new_password" id="new_password" autocomplete="new-password">
                        <span class="toggle-password" onclick="togglePassword('new_password')">👁️</span>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Потвърди нова парола</label>
                    <div class="password-field">
                        <input class="input" type="password" name="confirm_password" id="confirm_password" autocomplete="new-password">
                        <span class="toggle-password" onclick="togglePassword('confirm_password')">👁️</span>
                    </div>
                </div>
            </div>

            <button class="btn btn-primary submit-btn" type="submit">Запази промените</button>
        </form>
    </div>

    <script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        field.type = field.type === "password" ? "text" : "password";
    }

    /* --- Вграден кропер с запазване на състоянието --- */
    let cropState = {
        img: null,
        scale: 1,
        minScale: 1,
        maxScale: 3,
        pos: { x: 0, y: 0 },
        dragging: false,
        dragStart: { x: 0, y: 0 },
        imgStart: { x: 0, y: 0 }
    };

    const canvas = document.getElementById('cropCanvas');
    const ctx = canvas.getContext('2d');
    const zoomRange = document.getElementById('zoomRange');
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');
    const fileInput = document.getElementById('profile_image');
    const croppedHidden = document.getElementById('cropped_image');
    const cropScaleHidden = document.getElementById('crop_scale');
    const cropPosXHidden = document.getElementById('crop_pos_x');
    const cropPosYHidden = document.getElementById('crop_pos_y');
    const cropperArea = document.getElementById('cropperArea');

    const labelEl = document.getElementById('profilePicLabel');
    let suppressNextLabelClick = false;
    let dragStartClient = { x: 0, y: 0 };

    function getClient(e) {
      return {
        x: e.touches ? e.touches[0].clientX : e.clientX,
        y: e.touches ? e.touches[0].clientY : e.clientY
      };
    }

    // Блокиране на click върху label, ако е имало реално drag
    labelEl.addEventListener('click', (e) => {
      if (suppressNextLabelClick) {
        e.preventDefault();
        e.stopPropagation();
        suppressNextLabelClick = false;
      }
    });

    const INITIAL_SRC = "<?= htmlspecialchars($initialForCropper, ENT_QUOTES) ?>";
    const HAS_IMAGE = <?= $hasRealImage ? 'true' : 'false' ?>;
    const SAVED_CROP = <?php echo json_encode($savedCrop ?: null); ?>;

    document.addEventListener('DOMContentLoaded', () => {
        if (INITIAL_SRC && HAS_IMAGE) {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => initCropperWithImage(img, SAVED_CROP);
            img.src = INITIAL_SRC + (INITIAL_SRC.indexOf('?') === -1 ? '?' : '&') + 'v=' + Date.now();
        } else {
            drawEmptyCircle();
            setControlsEnabled(false);
        }
    });

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            const img = new Image();
            img.onload = () => initCropperWithImage(img, null);
            img.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });

    function initCropperWithImage(image, saved) {
        cropState.img = image;

        canvas.width = 400;
        canvas.height = 400;

        const scaleX = canvas.width / image.width;
        const scaleY = canvas.height / image.height;
        cropState.minScale = Math.max(scaleX, scaleY);
        cropState.maxScale = Math.max(3, cropState.minScale * 3);

        if (saved && typeof saved.scale === 'number' && typeof saved.posX === 'number' && typeof saved.posY === 'number') {
            cropState.scale = Math.max(cropState.minScale, Math.min(saved.scale, cropState.maxScale));
            cropState.pos = { x: saved.posX, y: saved.posY };
        } else {
            cropState.scale = cropState.minScale;
            cropState.pos = { x: 0, y: 0 };
        }

        zoomRange.min = cropState.minScale.toString();
        zoomRange.max = cropState.maxScale.toString();
        zoomRange.step = ((cropState.maxScale - cropState.minScale) / 100).toFixed(4);
        zoomRange.value = cropState.scale.toString();

        setControlsEnabled(true);
        bindDragHandlers();
        draw();
        updateHiddenCropState();
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#eee';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        if (cropState.img) {
            const drawW = cropState.img.width * cropState.scale;
            const drawH = cropState.img.height * cropState.scale;
            const cx = canvas.width / 2 + cropState.pos.x;
            const cy = canvas.height / 2 + cropState.pos.y;
            ctx.drawImage(cropState.img, cx - drawW / 2, cy - drawH / 2, drawW, drawH);
        }

        // Кръгла маска
        ctx.save();
        ctx.globalCompositeOperation = 'destination-in';
        ctx.beginPath();
        ctx.arc(canvas.width/2, canvas.height/2, canvas.width/2 - 2, 0, Math.PI*2);
        ctx.closePath();
        ctx.fill();
        ctx.restore();

        // Рамка
        ctx.beginPath();
        ctx.arc(canvas.width/2, canvas.height/2, canvas.width/2 - 2, 0, Math.PI*2);
        ctx.strokeStyle = '#1f4365';
        ctx.lineWidth = 2;
        ctx.stroke();
    }

    function drawEmptyCircle() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        canvas.width = 400; canvas.height = 400;
        ctx.fillStyle = '#f0f4f8';
        ctx.fillRect(0,0,canvas.width,canvas.height);

        ctx.save();
        ctx.globalCompositeOperation = 'destination-in';
        ctx.beginPath();
        ctx.arc(canvas.width/2, canvas.height/2, canvas.width/2 - 2, 0, Math.PI*2);
        ctx.fill();
        ctx.restore();

        ctx.beginPath();
        ctx.arc(canvas.width/2, canvas.height/2, canvas.width/2 - 2, 0, Math.PI*2);
        ctx.strokeStyle = '#c9d4e2';
        ctx.lineWidth = 2;
        ctx.stroke();
    }

    function setControlsEnabled(enabled) {
        zoomRange.disabled = !enabled;
        zoomInBtn.disabled = !enabled;
        zoomOutBtn.disabled = !enabled;
    }

    function bindDragHandlers() {
        function getPoint(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = (e.touches ? e.touches[0].clientX : e.clientX);
            const clientY = (e.touches ? e.touches[0].clientY : e.clientY);
            return {
                x: clientX - rect.left - canvas.width/2,
                y: clientY - rect.top - canvas.height/2
            };
        }

        function onDown(e) {
            if (!cropState.img) return;
            cropState.dragging = true;
            cropState.dragStart = getPoint(e);
            dragStartClient = getClient(e);
            suppressNextLabelClick = false;
            cropState.imgStart = { x: cropState.pos.x, y: cropState.pos.y };
            e.preventDefault();
        }

        function onMove(e) {
            if (!cropState.dragging) return;
            const pt = getPoint(e);
            cropState.pos.x = cropState.imgStart.x + (pt.x - cropState.dragStart.x);
            cropState.pos.y = cropState.imgStart.y + (pt.y - cropState.dragStart.y);

            const c = getClient(e);
            const moved = Math.abs(c.x - dragStartClient.x) > 4 || Math.abs(c.y - dragStartClient.y) > 4;
            if (moved) suppressNextLabelClick = true;

            draw();
            updateHiddenCropState();
            e.preventDefault();
        }

        function onUp() { cropState.dragging = false; }

        canvas.onmousedown = onDown;
        window.onmousemove = onMove;
        window.onmouseup   = onUp;

        canvas.ontouchstart = onDown;
        window.ontouchmove  = onMove;
        window.ontouchend   = onUp;
    }

    zoomRange.oninput = (e) => {
        if (!cropState.img) return;
        cropState.scale = parseFloat(e.target.value);
        draw();
        updateHiddenCropState();
    };
    zoomInBtn.onclick = () => {
        if (!cropState.img) return;
        const step = (cropState.maxScale - cropState.minScale) / 20;
        const v = Math.min(cropState.maxScale, parseFloat(zoomRange.value) + step);
        zoomRange.value = v.toString();
        cropState.scale = v;
        draw();
        updateHiddenCropState();
    };
    zoomOutBtn.onclick = () => {
        if (!cropState.img) return;
        const step = (cropState.maxScale - cropState.minScale) / 20;
        const v = Math.max(cropState.minScale, parseFloat(zoomRange.value) - step);
        zoomRange.value = v.toString();
        cropState.scale = v;
        draw();
        updateHiddenCropState();
    };

    function updateHiddenCropState() {
        cropScaleHidden.value = cropState.scale;
        cropPosXHidden.value = cropState.pos.x;
        cropPosYHidden.value = cropState.pos.y;
    }

    // При submit – генерираме изрязан квадрат 400x400 (PNG) в hidden input
    document.querySelector('form[action="update_profile.php"]').addEventListener('submit', () => {
        if (!cropState.img) return;
        const outCanvas = document.createElement('canvas');
        outCanvas.width = 400; outCanvas.height = 400;
        const octx = outCanvas.getContext('2d');

        const drawW = cropState.img.width * cropState.scale;
        const drawH = cropState.img.height * cropState.scale;
        const cx = outCanvas.width / 2 + cropState.pos.x;
        const cy = outCanvas.height / 2 + cropState.pos.y;
        octx.drawImage(cropState.img, cx - drawW / 2, cy - drawH / 2, drawW, drawH);

        const dataURL = outCanvas.toDataURL('image/png', 0.95);
        croppedHidden.value = dataURL;

        updateHiddenCropState();
    });
    </script>
</body>
</html>
