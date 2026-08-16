<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Label</title>
    <style>
        :root {
            --label-w: {{ $labelVars['width'] }};
            --label-h: {{ $labelVars['height'] }};
            --label-margin-top: {{ $labelVars['margin_top'] }};
            --label-margin-right: {{ $labelVars['margin_right'] }};
            --label-margin-bottom: {{ $labelVars['margin_bottom'] }};
            --label-margin-left: {{ $labelVars['margin_left'] }};
            --label-gap: {{ $labelVars['gap'] }};
            --label-shop-font: {{ $labelVars['shop_font_size'] }};
            --label-product-font: {{ $labelVars['product_font_size'] }};
            --label-price-font: {{ $labelVars['price_font_size'] }};
        }
        body { font-family: Arial, Helvetica, sans-serif; margin:0; padding:24px; background:#f4f6f8; }
        .label-controls { max-width:380px; margin:0 auto 20px; background:#fff; border-radius:8px; padding:16px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.1); text-align:center; }
        .label-controls button { margin-top:10px; padding:10px 20px; background:#0d9488; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer; }
        .label-sheet { display:flex; flex-wrap:wrap; gap:var(--label-gap, 2mm); justify-content:center; }
        .label-item {
            width: var(--label-w, 50mm);
            height: var(--label-h, 25mm);
            padding: var(--label-margin-top, 0) var(--label-margin-right, 0) var(--label-margin-bottom, 0) var(--label-margin-left, 0);
            border: 1px dashed #999;
            box-sizing: border-box;
            text-align: center;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .label-item .label-shop { font-size: var(--label-shop-font, 8px); font-weight: bold; }
        .label-item .label-product { font-size: var(--label-product-font, 9px); font-weight: bold; margin: 1px 0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; width:100%; }
        .label-item svg { width: 90%; height: auto; }
        .label-item .label-price { font-size: var(--label-price-font, 10px); font-weight: bold; margin-top: 1px; }
        @media print {
            .label-controls { display:none; }
            body { background:#fff; padding:0; }
            .label-item { border:none; }
        }
    </style>
    <script src="{{ asset('js/vendor/JsBarcode.all.min.js') }}"></script>
</head>
<body>
    <div class="label-controls">
        <h3 style="margin:0;">Label Test Print</h3>
        <p style="font-size:0.85rem; color:#666;">
            Prints sample labels using your current Label Printer settings
            ({{ $labelVars['width'] }} × {{ $labelVars['height'] }}, copies {{ $labelVars['copies'] }}).
        </p>
        <button type="button" onclick="window.print()">Print Test Label</button>
    </div>

    <div class="label-sheet" id="labelSheet"></div>

    <script>
        const shopName = @json($shopName);
        const productName = @json($sampleLabel['product_name']);
        const price = @json($sampleLabel['price']);
        const barcodeValue = @json($sampleLabel['barcode']);
        const copies = {{ $labelVars['copies'] }};

        const sheet = document.getElementById('labelSheet');
        for (let i = 0; i < copies; i++) {
            const label = document.createElement('div');
            label.className = 'label-item';
            label.innerHTML = `
                <div class="label-shop">${shopName}</div>
                <div class="label-product">${productName}</div>
                <svg class="barcode-svg"></svg>
                <div class="label-price">Rs. ${price}</div>
            `;
            sheet.appendChild(label);

            JsBarcode(label.querySelector('.barcode-svg'), barcodeValue, {
                format: 'CODE128',
                displayValue: true,
                fontSize: 10,
                height: 30,
                margin: 2,
            });
        }
    </script>
</body>
</html>
