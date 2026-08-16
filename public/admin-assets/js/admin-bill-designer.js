/* ============================================
   VISUAL BILL DESIGNER
   Drag/move/resize, layers, properties, save/load,
   undo/redo, zoom, pan tool, rulers, drag-and-drop.
   ============================================ */

const SCALE = 3; // px per mm at 100% zoom
let bdZoom = 1;

let bdElements = (bdTemplate.canvas_layout && bdTemplate.canvas_layout.elements) || [];
let bdSelectedId = null;
let bdIdCounter = bdElements.length;
let bdActiveTool = 'select';
let bdPaperWidthOverride = null; // client-side preview override only; not persisted

const bdCanvasEl = document.getElementById('bdCanvas');
const bdCanvasFrameEl = document.getElementById('bdCanvasFrame');
const bdLayersListEl = document.getElementById('bdLayersList');
const bdPropertiesPanelEl = document.getElementById('bdPropertiesPanel');
const bdRulerHEl = document.getElementById('bdRulerH');
const bdRulerVEl = document.getElementById('bdRulerV');

const ELEMENT_LABELS = {
    text: 'Text', dynamic_field: 'Dynamic Field', logo: 'Logo', image: 'Image',
    qr: 'QR Code', barcode: 'Barcode', line: 'Line', rectangle: 'Rectangle',
    table_items: 'Items Table', spacer: 'Spacer',
};

const ELEMENT_ICONS = {
    text: 'type', dynamic_field: 'code', logo: 'image', image: 'image',
    qr: 'grid', barcode: 'align-justify', line: 'minus', rectangle: 'square',
    table_items: 'list', spacer: 'square',
};

const DYNAMIC_FIELD_OPTIONS = [
    'shop_name', 'shop_address', 'shop_phone', 'bill_number', 'date', 'time',
    'customer', 'cashier', 'subtotal', 'discount', 'total', 'payment_method',
    'cash_received', 'change',
];

const FONT_FAMILIES = ['Arial', 'Helvetica', 'Courier New', 'Georgia', 'Times New Roman', 'Verdana'];

function refreshIcons() {
    if (window.feather) {
        try { feather.replace(); } catch (e) { /* noop */ }
    }
}

function paperWidthMm() {
    return bdPaperWidthOverride || printerPaperWidthMm;
}

function effScale() {
    return SCALE * bdZoom;
}

function px(mm) {
    return mm * effScale();
}

/* ---------------- History (undo/redo) ---------------- */

let bdHistory = [];
let bdHistoryIndex = -1;
const BD_HISTORY_LIMIT = 60;
let bdHistoryDebounce = null;

function snapshotElements() {
    return JSON.parse(JSON.stringify(bdElements));
}

function pushHistory() {
    bdHistory = bdHistory.slice(0, bdHistoryIndex + 1);
    bdHistory.push(snapshotElements());
    if (bdHistory.length > BD_HISTORY_LIMIT) {
        bdHistory.shift();
    }
    bdHistoryIndex = bdHistory.length - 1;
    updateHistoryButtons();
}

function pushHistoryDebounced() {
    clearTimeout(bdHistoryDebounce);
    bdHistoryDebounce = setTimeout(pushHistory, 350);
}

function updateHistoryButtons() {
    const undoBtn = document.getElementById('bdUndoBtn');
    const redoBtn = document.getElementById('bdRedoBtn');
    if (undoBtn) undoBtn.disabled = bdHistoryIndex <= 0;
    if (redoBtn) redoBtn.disabled = bdHistoryIndex >= bdHistory.length - 1;
}

function undo() {
    if (bdHistoryIndex <= 0) return;
    bdHistoryIndex -= 1;
    bdElements = JSON.parse(JSON.stringify(bdHistory[bdHistoryIndex]));
    if (!bdElements.find((e) => e.id === bdSelectedId)) bdSelectedId = null;
    renderAll();
    updateHistoryButtons();
}

function redo() {
    if (bdHistoryIndex >= bdHistory.length - 1) return;
    bdHistoryIndex += 1;
    bdElements = JSON.parse(JSON.stringify(bdHistory[bdHistoryIndex]));
    if (!bdElements.find((e) => e.id === bdSelectedId)) bdSelectedId = null;
    renderAll();
    updateHistoryButtons();
}

function renderAll() {
    renderCanvas();
    renderLayers();
    renderProperties();
}

/* ---------------- Element creation ---------------- */

function newElementDefaults(type) {
    const defaults = {
        text: { width: 40, height: 8, content: 'Text', font_size: 12, bold: false, italic: false, underline: false, align: 'left', color: '#000000', font_family: 'Arial' },
        dynamic_field: { width: 40, height: 8, content: '{{shop_name}}', font_size: 12, bold: false, italic: false, underline: false, align: 'left', color: '#000000', font_family: 'Arial' },
        logo: { width: 20, height: 20 },
        image: { width: 20, height: 20, src: null },
        qr: { width: 25, height: 25 },
        barcode: { width: 40, height: 15 },
        line: { width: 60, height: 2, thickness: 1, style: 'solid' },
        rectangle: { width: 30, height: 15, border_width: 1, border_color: '#000000', fill: 'transparent', radius: 0 },
        table_items: { width: 70, height: 30, font_size: 11 },
        spacer: { width: 40, height: 6 },
    };
    return defaults[type] || { width: 30, height: 10 };
}

function addElement(type, x, y) {
    const id = 'el_' + (++bdIdCounter);
    const el = Object.assign({
        id, type, x: (x === undefined ? 10 : x), y: (y === undefined ? 10 : y), visible: true, locked: false,
    }, newElementDefaults(type));

    bdElements.push(el);
    selectElement(id);
    renderCanvas();
    renderLayers();
    pushHistory();
}

function duplicateElement(id) {
    const source = bdElements.find((e) => e.id === id);
    if (!source) return;
    const copy = JSON.parse(JSON.stringify(source));
    copy.id = 'el_' + (++bdIdCounter);
    copy.x = Math.min(paperWidthMm() - copy.width, source.x + 4);
    copy.y = source.y + 4;
    bdElements.push(copy);
    selectElement(copy.id);
    renderCanvas();
    renderLayers();
    pushHistory();
}

document.querySelectorAll('.bd-element-btn').forEach((btn) => {
    btn.addEventListener('click', () => addElement(btn.dataset.add));
    btn.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', btn.dataset.add);
        e.dataTransfer.effectAllowed = 'copy';
    });
});

bdCanvasFrameEl.addEventListener('dragover', (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
});

bdCanvasFrameEl.addEventListener('drop', (e) => {
    e.preventDefault();
    const type = e.dataTransfer.getData('text/plain');
    if (!type) return;
    const rect = bdCanvasEl.getBoundingClientRect();
    const mmX = Math.max(0, Math.round((e.clientX - rect.left) / effScale()));
    const mmY = Math.max(0, Math.round((e.clientY - rect.top) / effScale()));
    addElement(type, mmX, mmY);
});

/* ---------------- Left panel tabs (Elements / Sections) ---------------- */

document.querySelectorAll('.bd-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.bd-tab').forEach((t) => t.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('bdPanelElements').style.display = tab.dataset.tab === 'elements' ? 'block' : 'none';
        document.getElementById('bdPanelSections').style.display = tab.dataset.tab === 'sections' ? 'block' : 'none';
    });
});

document.getElementById('bdLayersBtn').addEventListener('click', () => {
    document.querySelector('.bd-tab[data-tab="sections"]').click();
});

/* ---------------- Right panel tabs (Properties / Settings) ---------------- */

document.querySelectorAll('.bd-right-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.bd-right-tab').forEach((t) => t.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('bdPanelProperties').style.display = tab.dataset.rightTab === 'properties' ? 'block' : 'none';
        document.getElementById('bdPanelSettings').style.display = tab.dataset.rightTab === 'settings' ? 'block' : 'none';
        if (tab.dataset.rightTab === 'settings') {
            const countEl = document.getElementById('bdElementCount');
            if (countEl) countEl.value = bdElements.length;
        }
    });
});

const bdShowGridToggle = document.getElementById('bdShowGridToggle');
if (bdShowGridToggle) {
    bdShowGridToggle.addEventListener('change', () => {
        bdCanvasFrameEl.classList.toggle('bd-no-grid', !bdShowGridToggle.checked);
    });
}

/* ---------------- Rendering ---------------- */

function elementPreviewHtml(el) {
    switch (el.type) {
        case 'text':
        case 'dynamic_field': {
            const align = el.align || 'left';
            const weight = el.bold ? '700' : '400';
            const fontStyle = el.italic ? 'italic' : 'normal';
            const textDecoration = el.underline ? 'underline' : 'none';
            const color = el.color || '#000000';
            const family = el.font_family || 'Arial';
            return `<div style="width:100%; height:100%; text-align:${align}; font-weight:${weight}; font-style:${fontStyle}; text-decoration:${textDecoration}; color:${color}; font-family:'${family}', sans-serif; font-size:${(el.font_size || 12)}px; overflow:hidden; white-space:pre-wrap;">${el.content || ''}</div>`;
        }
        case 'logo':
            return bdTemplate.logo_path
                ? `<img src="/storage/${bdTemplate.logo_path}" style="width:100%; height:100%; object-fit:contain;">`
                : `<div class="bd-placeholder-box">Logo</div>`;
        case 'image':
            return el.src
                ? `<img src="${el.src}" style="width:100%; height:100%; object-fit:contain;">`
                : `<div class="bd-placeholder-box">Image</div>`;
        case 'qr':
            return paymentQrImageUrl
                ? `<img src="${paymentQrImageUrl}" style="width:100%; height:100%; object-fit:contain;">`
                : `<div class="bd-placeholder-box">QR</div>`;
        case 'barcode':
            return `<div class="bd-placeholder-box">||| Barcode |||</div>`;
        case 'line':
            return `<div style="width:100%; border-top:${el.thickness || 1}px ${el.style || 'solid'} #000; margin-top:${((el.height || 2) * effScale()) / 2}px;"></div>`;
        case 'rectangle': {
            const border = el.border_width ? `${el.border_width}px solid ${el.border_color || '#000'}` : '1px dashed #ccc';
            return `<div style="width:100%; height:100%; border:${border}; background:${el.fill || 'transparent'}; border-radius:${el.radius || 0}px; box-sizing:border-box;"></div>`;
        }
        case 'table_items':
            return `<div class="bd-placeholder-box" style="flex-direction:column; align-items:stretch; font-size:0.65rem;">
                <div style="font-weight:700; border-bottom:1px solid #9ca3af; padding-bottom:2px;">Item / Qty / Price / Total</div>
                <div style="color:#9ca3af; padding-top:4px;">(sample rows shown on Preview)</div>
            </div>`;
        case 'spacer':
            return `<div class="bd-placeholder-box" style="border-style:dotted;">Spacer</div>`;
        default:
            return '';
    }
}

function renderCanvas() {
    const widthMm = paperWidthMm();
    const maxBottom = bdElements.reduce((max, el) => Math.max(max, (el.y || 0) + (el.height || 8)), 30);
    const heightMm = maxBottom + 15;

    bdCanvasEl.style.width = px(widthMm) + 'px';
    bdCanvasEl.style.height = px(heightMm) + 'px';
    bdCanvasEl.innerHTML = '';

    bdElements.forEach((el) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'bd-canvas-element'
            + (el.id === bdSelectedId ? ' bd-selected' : '')
            + (el.locked ? ' bd-locked' : '');
        wrapper.style.left = px(el.x) + 'px';
        wrapper.style.top = px(el.y) + 'px';
        wrapper.style.width = px(el.width) + 'px';
        wrapper.style.height = px(el.height || 8) + 'px';
        wrapper.style.display = el.visible === false ? 'none' : 'block';
        wrapper.dataset.id = el.id;

        wrapper.innerHTML = elementPreviewHtml(el);

        if (el.id === bdSelectedId && !el.locked) {
            ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'].forEach((corner) => {
                const handle = document.createElement('div');
                handle.className = 'bd-resize-handle ' + corner;
                handle.dataset.corner = corner;
                handle.addEventListener('pointerdown', (e) => startResize(e, el));
                wrapper.appendChild(handle);
            });
        }

        wrapper.addEventListener('pointerdown', (e) => {
            if (bdActiveTool === 'pan') return;
            if (e.target.classList.contains('bd-resize-handle')) return;
            selectElement(el.id);
            if (!el.locked) startDrag(e, el);
        });

        bdCanvasEl.appendChild(wrapper);
    });

    renderRulers(widthMm, heightMm);
}

bdCanvasEl.addEventListener('pointerdown', (e) => {
    if (e.target === bdCanvasEl) {
        bdSelectedId = null;
        renderCanvas();
        renderLayers();
        renderProperties();
    }
});

/* ---------------- Rulers ---------------- */

function renderRulers(widthMm, heightMm) {
    if (!bdRulerHEl || !bdRulerVEl) return;
    const stepMm = bdZoom < 0.75 ? 20 : 10;

    bdRulerHEl.style.width = px(widthMm) + 'px';
    bdRulerHEl.innerHTML = '';
    for (let mm = 0; mm <= widthMm; mm += stepMm) {
        const tick = document.createElement('div');
        tick.className = 'bd-ruler-tick';
        tick.style.left = px(mm) + 'px';
        tick.textContent = mm;
        bdRulerHEl.appendChild(tick);
    }

    bdRulerVEl.style.height = px(heightMm) + 'px';
    bdRulerVEl.innerHTML = '';
    for (let mm = 0; mm <= heightMm; mm += stepMm) {
        const tick = document.createElement('div');
        tick.className = 'bd-ruler-v-tick';
        tick.style.top = px(mm) + 'px';
        tick.textContent = mm;
        bdRulerVEl.appendChild(tick);
    }
}

bdCanvasFrameEl.addEventListener('scroll', () => {
    if (bdRulerHEl) bdRulerHEl.style.transform = `translateX(${-bdCanvasFrameEl.scrollLeft}px)`;
    if (bdRulerVEl) bdRulerVEl.style.transform = `translateY(${-bdCanvasFrameEl.scrollTop}px)`;
});

/* ---------------- Drag / resize (pointer events: mouse + touch) ---------------- */

function startDrag(e, el) {
    e.preventDefault();
    if (e.target.setPointerCapture && e.pointerId !== undefined) {
        try { e.target.setPointerCapture(e.pointerId); } catch (err) { /* noop */ }
    }
    const startX = e.clientX;
    const startY = e.clientY;
    const originX = el.x;
    const originY = el.y;
    const maxX = paperWidthMm() - el.width;

    function onMove(moveEvent) {
        const dxMm = (moveEvent.clientX - startX) / effScale();
        const dyMm = (moveEvent.clientY - startY) / effScale();
        el.x = Math.min(Math.max(0, Math.round(originX + dxMm)), Math.max(0, maxX));
        el.y = Math.max(0, Math.round(originY + dyMm));
        renderCanvas();
    }

    function onUp() {
        document.removeEventListener('pointermove', onMove);
        document.removeEventListener('pointerup', onUp);
        renderProperties();
        pushHistory();
    }

    document.addEventListener('pointermove', onMove);
    document.addEventListener('pointerup', onUp);
}

function startResize(e, el) {
    e.preventDefault();
    e.stopPropagation();
    if (e.target.setPointerCapture && e.pointerId !== undefined) {
        try { e.target.setPointerCapture(e.pointerId); } catch (err) { /* noop */ }
    }
    const corner = e.target.dataset.corner;
    const startX = e.clientX;
    const startY = e.clientY;
    const origin = { x: el.x, y: el.y, width: el.width, height: el.height || 8 };
    const paperW = paperWidthMm();

    function clampWidth(w, x) {
        return Math.min(Math.max(5, w), paperW - x);
    }

    function onMove(moveEvent) {
        const dxMm = (moveEvent.clientX - startX) / effScale();
        const dyMm = (moveEvent.clientY - startY) / effScale();

        if (corner.includes('e')) {
            el.width = clampWidth(Math.round(origin.width + dxMm), el.x);
        }
        if (corner.includes('s')) {
            el.height = Math.max(3, Math.round(origin.height + dyMm));
        }
        if (corner.includes('w')) {
            const newWidth = Math.max(5, Math.round(origin.width - dxMm));
            el.x = Math.max(0, Math.round(origin.x + dxMm));
            el.width = clampWidth(newWidth, el.x);
        }
        if (corner.includes('n')) {
            el.height = Math.max(3, Math.round(origin.height - dyMm));
            el.y = Math.max(0, Math.round(origin.y + dyMm));
        }
        renderCanvas();
    }

    function onUp() {
        document.removeEventListener('pointermove', onMove);
        document.removeEventListener('pointerup', onUp);
        renderProperties();
        pushHistory();
    }

    document.addEventListener('pointermove', onMove);
    document.addEventListener('pointerup', onUp);
}

/* ---------------- Selection ---------------- */

function selectElement(id) {
    bdSelectedId = id;
    renderCanvas();
    renderLayers();
    renderProperties();
}

function getSelectedElement() {
    return bdElements.find((el) => el.id === bdSelectedId);
}

/* ---------------- Layers / Sections list ---------------- */

function renderLayers() {
    bdLayersListEl.innerHTML = '';

    bdElements.forEach((el, index) => {
        const li = document.createElement('li');
        li.className = 'bd-layer-item' + (el.id === bdSelectedId ? ' bd-selected' : '');
        li.draggable = true;
        li.dataset.index = index;

        li.innerHTML = `
            <span class="bd-drag-handle"><i data-feather="more-vertical"></i></span>
            <span class="bd-layer-name">${ELEMENT_LABELS[el.type] || el.type}</span>
            <button type="button" class="bd-eye-btn ${el.visible === false ? '' : 'bd-hidden-active'}" title="Show/Hide">
                <i data-feather="${el.visible === false ? 'eye-off' : 'eye'}"></i>
            </button>
            <button type="button" class="bd-lock-btn ${el.locked ? 'bd-locked-active' : ''}" title="Lock/Unlock">
                <i data-feather="${el.locked ? 'lock' : 'unlock'}"></i>
            </button>
            <button type="button" class="bd-delete-btn" title="Delete">
                <i data-feather="trash-2"></i>
            </button>
        `;

        li.querySelector('.bd-layer-name').addEventListener('click', () => selectElement(el.id));
        li.querySelector('.bd-eye-btn').addEventListener('click', () => {
            el.visible = el.visible === false ? true : false;
            renderCanvas();
            renderLayers();
            pushHistory();
        });
        li.querySelector('.bd-lock-btn').addEventListener('click', () => {
            el.locked = !el.locked;
            renderCanvas();
            renderLayers();
            pushHistory();
        });
        li.querySelector('.bd-delete-btn').addEventListener('click', () => {
            if (!confirm('Delete this element?')) return;
            bdElements = bdElements.filter((e) => e.id !== el.id);
            if (bdSelectedId === el.id) bdSelectedId = null;
            renderCanvas();
            renderLayers();
            renderProperties();
            pushHistory();
        });

        li.addEventListener('dragstart', () => li.classList.add('bd-dragging'));
        li.addEventListener('dragend', () => li.classList.remove('bd-dragging'));
        li.addEventListener('dragover', (e) => e.preventDefault());
        li.addEventListener('drop', (e) => {
            e.preventDefault();
            const draggingEl = document.querySelector('.bd-dragging');
            if (!draggingEl) return;
            const draggedIndex = parseInt(draggingEl.dataset.index);
            const targetIndex = index;
            const [moved] = bdElements.splice(draggedIndex, 1);
            bdElements.splice(targetIndex, 0, moved);
            renderCanvas();
            renderLayers();
            pushHistory();
        });

        bdLayersListEl.appendChild(li);
    });

    refreshIcons();
}

/* ---------------- Properties panel ---------------- */

function iconSvgTag(name) {
    return `<i data-feather="${name}"></i>`;
}

function renderProperties() {
    const el = getSelectedElement();

    const countEl = document.getElementById('bdElementCount');
    if (countEl) countEl.value = bdElements.length;

    if (!el) {
        bdPropertiesPanelEl.innerHTML = '<p class="bd-hint">Select an element to edit its properties</p>';
        return;
    }

    const isTextLike = el.type === 'text' || el.type === 'dynamic_field';
    let extraFields = '';

    if (isTextLike) {
        if (el.type === 'dynamic_field') {
            extraFields += `<label>Field</label>
                <select id="propToken">
                    ${DYNAMIC_FIELD_OPTIONS.map((t) => `<option value="${t}" ${el.content === '{{' + t + '}}' ? 'selected' : ''}>${t}</option>`).join('')}
                </select>`;
        } else {
            extraFields += `<label>Content</label><textarea id="propContent">${el.content || ''}</textarea>`;
        }

        extraFields += `
            <div class="bd-section-title">Font Settings</div>
            <label>Font Family</label>
            <select id="propFontFamily">
                ${FONT_FAMILIES.map((f) => `<option value="${f}" ${(el.font_family || 'Arial') === f ? 'selected' : ''}>${f}</option>`).join('')}
            </select>
            <div class="bd-row-2">
                <div>
                    <label>Font Size</label>
                    <input type="number" id="propFontSize" value="${el.font_size || 12}" min="6" max="48">
                </div>
                <div class="bd-checkbox-row" style="margin-top:26px;">
                    <input type="checkbox" id="propBold" ${el.bold ? 'checked' : ''}>
                    <label style="margin:0; text-transform:none; font-weight:600;">Bold</label>
                </div>
            </div>
            <div class="bd-style-row">
                <button type="button" data-style="bold" class="${el.bold ? 'active' : ''}">B</button>
                <button type="button" data-style="italic" class="${el.italic ? 'active' : ''}">I</button>
                <button type="button" data-style="underline" class="${el.underline ? 'active' : ''}">U</button>
            </div>
            <label>Align</label>
            <div class="bd-align-row">
                <button type="button" data-align="left" class="${(el.align || 'left') === 'left' ? 'active' : ''}">${iconSvgTag('align-left')}</button>
                <button type="button" data-align="center" class="${el.align === 'center' ? 'active' : ''}">${iconSvgTag('align-center')}</button>
                <button type="button" data-align="right" class="${el.align === 'right' ? 'active' : ''}">${iconSvgTag('align-right')}</button>
            </div>
            <label>Text Color</label>
            <div class="bd-color-row">
                <input type="color" id="propColor" value="${el.color || '#000000'}">
                <input type="text" id="propColorHex" value="${el.color || '#000000'}">
            </div>
        `;
    }

    if (el.type === 'line') {
        extraFields += `
            <div class="bd-section-title">Line Settings</div>
            <label>Thickness (px)</label>
            <input type="number" id="propThickness" value="${el.thickness || 1}" min="1" max="10">
            <label>Style</label>
            <select id="propLineStyle">
                <option value="solid" ${el.style === 'solid' ? 'selected' : ''}>Solid</option>
                <option value="dashed" ${el.style === 'dashed' ? 'selected' : ''}>Dashed</option>
                <option value="dotted" ${el.style === 'dotted' ? 'selected' : ''}>Dotted</option>
            </select>
        `;
    }

    if (el.type === 'rectangle') {
        extraFields += `
            <div class="bd-section-title">Rectangle Settings</div>
            <label>Border Width (px)</label>
            <input type="number" id="propBorderWidth" value="${el.border_width || 0}" min="0" max="10">
            <label>Border Color</label>
            <div class="bd-color-row">
                <input type="color" id="propBorderColorPicker" value="${/^#/.test(el.border_color) ? el.border_color : '#000000'}">
                <input type="text" id="propBorderColor" value="${el.border_color || '#000000'}">
            </div>
            <label>Fill Color (or "transparent")</label>
            <input type="text" id="propFill" value="${el.fill || 'transparent'}">
            <label>Corner Radius (px)</label>
            <input type="number" id="propRadius" value="${el.radius || 0}" min="0" max="30">
        `;
    }

    bdPropertiesPanelEl.innerHTML = `
        <label>Element Type</label>
        <div class="bd-element-type-field">${iconSvgTag(ELEMENT_ICONS[el.type] || 'square')} ${ELEMENT_LABELS[el.type] || el.type}</div>
        ${extraFields}
        <div class="bd-section-title">Dimensions</div>
        <div class="bd-row-2">
            <div><label>X (mm)</label><input type="number" id="propX" value="${el.x}"></div>
            <div><label>Y (mm)</label><input type="number" id="propY" value="${el.y}"></div>
        </div>
        <div class="bd-row-2">
            <div><label>Width (mm)</label><input type="number" id="propWidth" value="${el.width}"></div>
            <div><label>Height (mm)</label><input type="number" id="propHeight" value="${el.height || 8}"></div>
        </div>
        <div class="bd-section-title">Other</div>
        <label>Align on canvas</label>
        <select id="propQuickAlign">
            <option value="">Choose an action…</option>
            <option value="left">Snap left</option>
            <option value="center">Center horizontally</option>
            <option value="right">Snap right</option>
        </select>
        <div class="bd-toggle-row">
            <label>Lock Element</label>
            <button type="button" class="bd-toggle ${el.locked ? 'active' : ''}" id="propLockToggle"></button>
        </div>
        <button type="button" class="bd-danger-btn" id="propDeleteBtn">${iconSvgTag('trash-2')} Delete Element</button>
    `;

    document.getElementById('propX').addEventListener('input', (e) => { el.x = parseFloat(e.target.value) || 0; renderCanvas(); pushHistoryDebounced(); });
    document.getElementById('propY').addEventListener('input', (e) => { el.y = parseFloat(e.target.value) || 0; renderCanvas(); pushHistoryDebounced(); });
    document.getElementById('propWidth').addEventListener('input', (e) => { el.width = parseFloat(e.target.value) || 5; renderCanvas(); pushHistoryDebounced(); });
    document.getElementById('propHeight').addEventListener('input', (e) => { el.height = parseFloat(e.target.value) || 5; renderCanvas(); pushHistoryDebounced(); });
    document.getElementById('propDeleteBtn').addEventListener('click', () => {
        if (!confirm('Delete this element?')) return;
        bdElements = bdElements.filter((e) => e.id !== el.id);
        bdSelectedId = null;
        renderCanvas();
        renderLayers();
        renderProperties();
        pushHistory();
    });

    document.getElementById('propLockToggle').addEventListener('click', () => {
        el.locked = !el.locked;
        renderCanvas();
        renderLayers();
        renderProperties();
        pushHistory();
    });

    document.getElementById('propQuickAlign').addEventListener('change', (e) => {
        const mode = e.target.value;
        if (mode === 'left') el.x = 0;
        if (mode === 'center') el.x = Math.max(0, Math.round((paperWidthMm() - el.width) / 2));
        if (mode === 'right') el.x = Math.max(0, paperWidthMm() - el.width);
        renderCanvas();
        renderProperties();
        pushHistory();
        e.target.value = '';
    });

    if (document.getElementById('propContent')) {
        document.getElementById('propContent').addEventListener('input', (e) => { el.content = e.target.value; renderCanvas(); pushHistoryDebounced(); });
    }
    if (document.getElementById('propToken')) {
        document.getElementById('propToken').addEventListener('change', (e) => { el.content = '{{' + e.target.value + '}}'; renderCanvas(); pushHistory(); });
    }
    if (document.getElementById('propFontFamily')) {
        document.getElementById('propFontFamily').addEventListener('change', (e) => { el.font_family = e.target.value; renderCanvas(); pushHistory(); });
    }
    if (document.getElementById('propFontSize')) {
        document.getElementById('propFontSize').addEventListener('input', (e) => { el.font_size = parseInt(e.target.value) || 12; renderCanvas(); pushHistoryDebounced(); });
    }
    if (document.getElementById('propBold')) {
        document.getElementById('propBold').addEventListener('change', (e) => { el.bold = e.target.checked; renderCanvas(); renderProperties(); pushHistory(); });
    }
    document.querySelectorAll('.bd-style-row button').forEach((btn) => {
        btn.addEventListener('click', () => {
            const style = btn.dataset.style;
            if (style === 'bold') el.bold = !el.bold;
            if (style === 'italic') el.italic = !el.italic;
            if (style === 'underline') el.underline = !el.underline;
            renderCanvas();
            renderProperties();
            pushHistory();
        });
    });
    document.querySelectorAll('.bd-align-row button').forEach((btn) => {
        btn.addEventListener('click', () => { el.align = btn.dataset.align; renderCanvas(); renderProperties(); pushHistory(); });
    });
    if (document.getElementById('propColor')) {
        document.getElementById('propColor').addEventListener('input', (e) => {
            el.color = e.target.value;
            document.getElementById('propColorHex').value = e.target.value;
            renderCanvas();
            pushHistoryDebounced();
        });
    }
    if (document.getElementById('propColorHex')) {
        document.getElementById('propColorHex').addEventListener('input', (e) => {
            el.color = e.target.value;
            if (/^#[0-9a-fA-F]{6}$/.test(e.target.value)) document.getElementById('propColor').value = e.target.value;
            renderCanvas();
            pushHistoryDebounced();
        });
    }
    if (document.getElementById('propThickness')) {
        document.getElementById('propThickness').addEventListener('input', (e) => { el.thickness = parseInt(e.target.value) || 1; renderCanvas(); pushHistoryDebounced(); });
    }
    if (document.getElementById('propLineStyle')) {
        document.getElementById('propLineStyle').addEventListener('change', (e) => { el.style = e.target.value; renderCanvas(); pushHistory(); });
    }
    if (document.getElementById('propBorderWidth')) {
        document.getElementById('propBorderWidth').addEventListener('input', (e) => { el.border_width = parseInt(e.target.value) || 0; renderCanvas(); pushHistoryDebounced(); });
    }
    if (document.getElementById('propBorderColor')) {
        document.getElementById('propBorderColor').addEventListener('input', (e) => {
            el.border_color = e.target.value;
            if (/^#[0-9a-fA-F]{6}$/.test(e.target.value)) document.getElementById('propBorderColorPicker').value = e.target.value;
            renderCanvas();
            pushHistoryDebounced();
        });
    }
    if (document.getElementById('propBorderColorPicker')) {
        document.getElementById('propBorderColorPicker').addEventListener('input', (e) => {
            el.border_color = e.target.value;
            document.getElementById('propBorderColor').value = e.target.value;
            renderCanvas();
            pushHistoryDebounced();
        });
    }
    if (document.getElementById('propFill')) {
        document.getElementById('propFill').addEventListener('input', (e) => { el.fill = e.target.value; renderCanvas(); pushHistoryDebounced(); });
    }
    if (document.getElementById('propRadius')) {
        document.getElementById('propRadius').addEventListener('input', (e) => { el.radius = parseInt(e.target.value) || 0; renderCanvas(); pushHistoryDebounced(); });
    }

    refreshIcons();
}

/* ---------------- Canvas toolbar: tools, align, duplicate, delete, lock ---------------- */

function setActiveTool(tool) {
    bdActiveTool = tool;
    document.querySelectorAll('.bd-tool-btn[data-tool]').forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.tool === tool);
    });
    bdCanvasFrameEl.classList.toggle('bd-pan-mode', tool === 'pan');
}

document.getElementById('bdToolSelect').addEventListener('click', () => setActiveTool('select'));
document.getElementById('bdToolPan').addEventListener('click', () => setActiveTool('pan'));
document.getElementById('bdToolMarquee').addEventListener('click', () => setActiveTool('marquee'));

// Pan-to-scroll behaviour when the Pan tool is active.
bdCanvasFrameEl.addEventListener('pointerdown', (e) => {
    if (bdActiveTool !== 'pan') return;
    e.preventDefault();
    bdCanvasFrameEl.classList.add('bd-panning');
    const startX = e.clientX;
    const startY = e.clientY;
    const startScrollLeft = bdCanvasFrameEl.scrollLeft;
    const startScrollTop = bdCanvasFrameEl.scrollTop;

    function onMove(moveEvent) {
        bdCanvasFrameEl.scrollLeft = startScrollLeft - (moveEvent.clientX - startX);
        bdCanvasFrameEl.scrollTop = startScrollTop - (moveEvent.clientY - startY);
    }
    function onUp() {
        bdCanvasFrameEl.classList.remove('bd-panning');
        document.removeEventListener('pointermove', onMove);
        document.removeEventListener('pointerup', onUp);
    }
    document.addEventListener('pointermove', onMove);
    document.addEventListener('pointerup', onUp);
});

document.getElementById('bdAlignLeftBtn').addEventListener('click', () => {
    const el = getSelectedElement();
    if (!el) return;
    el.x = 0;
    renderCanvas();
    renderProperties();
    pushHistory();
});

document.getElementById('bdAlignCenterBtn').addEventListener('click', () => {
    const el = getSelectedElement();
    if (!el) return;
    el.x = Math.max(0, Math.round((paperWidthMm() - el.width) / 2));
    renderCanvas();
    renderProperties();
    pushHistory();
});

document.getElementById('bdDuplicateBtn').addEventListener('click', () => {
    if (!bdSelectedId) return;
    duplicateElement(bdSelectedId);
});

document.getElementById('bdDeleteBtn').addEventListener('click', () => {
    if (!bdSelectedId) return;
    if (!confirm('Delete this element?')) return;
    bdElements = bdElements.filter((e) => e.id !== bdSelectedId);
    bdSelectedId = null;
    renderCanvas();
    renderLayers();
    renderProperties();
    pushHistory();
});

document.getElementById('bdLockBtn').addEventListener('click', () => {
    const el = getSelectedElement();
    if (!el) return;
    el.locked = !el.locked;
    renderCanvas();
    renderLayers();
    renderProperties();
    pushHistory();
});

/* ---------------- Undo / redo buttons ---------------- */

document.getElementById('bdUndoBtn').addEventListener('click', undo);
document.getElementById('bdRedoBtn').addEventListener('click', redo);

/* ---------------- Zoom ---------------- */

const ZOOM_STEPS = [0.5, 0.75, 1, 1.25, 1.5, 2];

function setZoom(zoom) {
    bdZoom = Math.min(2, Math.max(0.5, zoom));
    document.getElementById('bdZoomValue').textContent = Math.round(bdZoom * 100) + '%';
    renderCanvas();
}

document.getElementById('bdZoomInBtn').addEventListener('click', () => {
    const next = ZOOM_STEPS.find((z) => z > bdZoom + 0.001);
    setZoom(next || 2);
});

document.getElementById('bdZoomOutBtn').addEventListener('click', () => {
    const prev = [...ZOOM_STEPS].reverse().find((z) => z < bdZoom - 0.001);
    setZoom(prev || 0.5);
});

/* ---------------- Paper width preview selector ---------------- */

const bdPaperWidthSelect = document.getElementById('bdPaperWidthSelect');
if (bdPaperWidthSelect) {
    // Default the dropdown to whichever standard size is closest to the printer's configured width.
    const closest = [58, 72, 80].reduce((a, b) => (Math.abs(b - printerPaperWidthMm) < Math.abs(a - printerPaperWidthMm) ? b : a));
    bdPaperWidthSelect.value = String(closest);
    bdPaperWidthOverride = closest;

    bdPaperWidthSelect.addEventListener('change', (e) => {
        bdPaperWidthOverride = parseFloat(e.target.value);
        renderCanvas();
    });
}

/* ---------------- Keyboard shortcuts ---------------- */

document.addEventListener('keydown', (e) => {
    const tag = (e.target.tagName || '').toLowerCase();
    const isEditable = tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable;

    if ((e.ctrlKey || e.metaKey) && !isEditable) {
        if (e.key.toLowerCase() === 'z' && !e.shiftKey) { e.preventDefault(); undo(); return; }
        if ((e.key.toLowerCase() === 'z' && e.shiftKey) || e.key.toLowerCase() === 'y') { e.preventDefault(); redo(); return; }
    }

    if (isEditable) return;

    const el = getSelectedElement();
    if (!el) return;

    if (e.key === 'Delete' || e.key === 'Backspace') {
        e.preventDefault();
        bdElements = bdElements.filter((x) => x.id !== el.id);
        bdSelectedId = null;
        renderCanvas();
        renderLayers();
        renderProperties();
        pushHistory();
        return;
    }

    const step = e.shiftKey ? 5 : 1;
    let moved = false;
    if (e.key === 'ArrowLeft' && !el.locked) { el.x = Math.max(0, el.x - step); moved = true; }
    if (e.key === 'ArrowRight' && !el.locked) { el.x = Math.min(paperWidthMm() - el.width, el.x + step); moved = true; }
    if (e.key === 'ArrowUp' && !el.locked) { el.y = Math.max(0, el.y - step); moved = true; }
    if (e.key === 'ArrowDown' && !el.locked) { el.y = el.y + step; moved = true; }

    if (moved) {
        e.preventDefault();
        renderCanvas();
        renderProperties();
        pushHistoryDebounced();
    }
});

/* ---------------- Preview & Save (unchanged behaviour) ---------------- */

document.getElementById('bdPreviewBtn').addEventListener('click', () => {
    const tpl = Object.assign({}, bdTemplate, {
        canvas_layout: { elements: bdElements },
    });
    const sale = bdLatestSale || {
        bill_number: '000125', created_at: new Date().toISOString(),
        customer: { name: 'Walk-in Customer' }, subtotal: 470, discount: 20, total: 450,
        cash_received: 500, change_amount: 50,
        items: [{ item_name: 'Sample Item', quantity: 1, unit_price: 450, line_total: 450 }],
    };

    const container = document.getElementById('bdPreviewContent');
    applyReceiptContainerClasses(container, tpl);
    container.classList.add('designer-paper');
    container.innerHTML = renderReceiptForTemplate(tpl, sale);

    $('#bdPreviewModal').modal('show');
});

document.getElementById('bdSaveBtn').addEventListener('click', async () => {
    const btn = document.getElementById('bdSaveBtn');
    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.textContent = 'Saving...';

    try {
        const response = await fetch(bdSaveLayoutUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                canvas_layout: { elements: bdElements },
            }),
        });

        if (!response.ok) {
            alert('Failed to save. Please try again.');
            btn.innerHTML = originalHtml;
        } else {
            btn.textContent = 'Saved!';
            setTimeout(() => { btn.innerHTML = originalHtml; refreshIcons(); }, 1500);
        }
    } catch (error) {
        alert('Could not reach the server.');
        btn.innerHTML = originalHtml;
    }

    btn.disabled = false;
    refreshIcons();
});

/* ---------------- Init ---------------- */

renderAll();
pushHistory(); // baseline snapshot so the first real change is undoable
refreshIcons();
