<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Label - {{ $product->name }}</title>
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
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 24px;
            background: #f4f6f8;
        }

        .label-controls {
            max-width: 420px;
            margin: 0 auto 24px auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 16px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .label-controls label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 4px;
            margin-top: 10px;
        }

        .label-controls .field-row {
            display: flex;
            gap: 10px;
        }
        .label-controls .field-row > div {
            flex: 1;
        }

        .label-controls select,
        .label-controls input {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .label-controls small {
            display: block;
            color: #888;
            font-size: 0.75rem;
            margin-top: 2px;
        }

        .label-controls button {
            margin-top: 14px;
            width: 100%;
            padding: 10px 0;
            background: #1e3a8a;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }

        .label-sheet {
            display: flex;
            flex-wrap: wrap;
            gap: var(--label-gap, 2mm);
            justify-content: center;
        }

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

        .label-item .label-shop {
            font-size: var(--label-shop-font, 8px);
            font-weight: bold;
        }

        .label-item .label-product {
            font-size: var(--label-product-font, 9px);
            font-weight: bold;
            margin: 1px 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 100%;
        }

        .label-item svg {
            width: 90%;
            height: auto;
        }

        .label-item .label-price {
            font-size: var(--label-price-font, 10px);
            font-weight: bold;
            margin-top: 1px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .label-controls {
                display: none;
            }
            .label-item {
                border: none;
            }
        }
    </style>
<script src="{{ asset('js/vendor/JsBarcode.all.min.js') }}"></script>
<body>

    <div class="label-controls">
        <h3 style="margin:0;">Print Barcode Label</h3>
        <p style="font-size:0.85rem; color:#666; margin:4px 0 0;">{{ $product->name }} — {{ $product->barcode }}</p>
        <p style="font-size:0.78rem; color:#999; margin:4px 0 0;">Pre-filled from your saved Label Printer settings — adjust just for this print if needed.</p>

        <div class="field-row">
            <div>
                <label>Label Width (mm)</label>
                <input type="number" id="labelWidth" min="10" max="200" value="{{ (int) $labelVars['width'] }}">
            </div>
            <div>
                <label>Label Height (mm)</label>
                <input type="number" id="labelHeight" min="10" max="200" value="{{ (int) $labelVars['height'] }}">
            </div>
        </div>

        <div class="field-row">
            <div>
                <label>Gap Between Labels (mm)</label>
                <input type="number" id="labelGap" min="0" max="50" value="{{ (int) $labelVars['gap'] }}">
            </div>
            <div>
                <label>Number of Copies</label>
                <input type="number" id="copyCount" value="1" min="1" max="100">
            </div>
        </div>

        <button type="button" onclick="renderLabels()">Update Preview</button>
        <button type="button" onclick="window.print()" style="background:#0d9488;">Print</button>
    </div>

    <div class="label-sheet" id="labelSheet"></div>

    <script>
        const productName = @json($product->name);
        const productPrice = @json(number_format($product->price));
        const barcodeValue = @json($product->barcode);
        const shopName = @json($shopName);

        function renderLabels() {
            const w = parseFloat(document.getElementById('labelWidth').value) || 50;
            const h = parseFloat(document.getElementById('labelHeight').value) || 25;
            const gap = parseFloat(document.getElementById('labelGap').value) || 0;
            const copies = parseInt(document.getElementById('copyCount').value) || 1;

            document.documentElement.style.setProperty('--label-w', w + 'mm');
            document.documentElement.style.setProperty('--label-h', h + 'mm');
            document.documentElement.style.setProperty('--label-gap', gap + 'mm');

            const sheet = document.getElementById('labelSheet');
            sheet.innerHTML = '';

            for (let i = 0; i < copies; i++) {
                const label = document.createElement('div');
                label.className = 'label-item';

                label.innerHTML = `
                    <div class="label-shop">${shopName}</div>
                    <div class="label-product">${productName}</div>
                    <svg class="barcode-svg"></svg>
                    <div class="label-price">Rs. ${productPrice}</div>
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
        }

        renderLabels();
    </script>

</body>
</html>
