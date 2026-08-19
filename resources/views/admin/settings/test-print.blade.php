<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Print</title>
    <link rel="stylesheet" href="{{ asset('css/billing.css') }}?v={{ filemtime(public_path('css/billing.css')) }}">
    <style>
        :root {
            --print-paper-width: {{ $printerVars['width'] }};
            --print-page-length: {{ $printerVars['length'] }};
            --print-font-size: {{ $printerVars['font_size'] }};
            --print-font-weight: {{ $printerVars['font_weight'] }};
        }
        body { font-family: Arial, sans-serif; background:#f4f6f8; padding:24px; }
        .test-controls { max-width:380px; margin:0 auto 20px; background:#fff; border-radius:8px; padding:16px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.1); text-align:center; }
        .test-controls button { margin-top:10px; padding:10px 20px; background:#0d9488; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer; }
        .receipt-preview-frame { display:flex; justify-content:center; }

        /* Make the ON-SCREEN preview reflect your actual Printer
           Settings (width / font size), not just the print dialog.
           #id selector so it beats the .receipt-content.paper-XXmm
           classes coming from the Bill Designer's screen-preview
           sizing (Bill Designer still owns receipt content/layout —
           this only overrides physical size/font for this preview). */
        #testReceiptContent {
            width: var(--print-paper-width) !important;
            max-width: var(--print-paper-width) !important;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: var(--print-font-size) !important;
            font-weight: var(--print-font-weight) !important;
            background: #fff;
            padding: 2mm 3mm;
            box-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        @media print { .test-controls { display:none; } }
    </style>
</head>
<body>
    <div class="test-controls">
        <h3 style="margin:0;">Printer Test Print</h3>
        <p style="font-size:0.85rem; color:#666;">
            Prints a sample receipt using your current Printer Settings
            (width {{ $printerVars['width'] }}, length {{ $printerVars['length'] }}, copies {{ $printerVars['copies'] }}).
        </p>
        <p style="font-size:0.78rem; color:#999;">
            The box below is sized to your saved Paper Width so you can check it before printing.
        </p>
        <button type="button" onclick="printTestReceipt()">Print Test Receipt</button>
    </div>

    <div class="receipt-preview-frame">
        <div class="receipt-content" id="testReceiptContent"></div>
    </div>

    <script>
        const activeTemplate = @json($template);
        const sampleSale = @json($sampleSale);
        const printerVars = @json($printerVars);
        const printerCopies = {{ $printerVars['copies'] }};
    </script>
    <script src="{{ asset('js/receipt-renderer.js') }}?v={{ filemtime(public_path('js/receipt-renderer.js')) }}"></script>
    <script>
        const tpl = activeTemplate || buildFallbackTemplate('Test Shop');
        const order = getSectionOrder(tpl);
        const container = document.getElementById('testReceiptContent');
        applyReceiptContainerClasses(container, tpl, printerVars);
        container.innerHTML = renderReceiptForTemplate(tpl, sampleSale, order);

        // Uses the exact same copies pattern as Billing and Admin
        // reprint, so Test Print always matches the real pipeline.
        function printTestReceipt() {
            const copies = (typeof printerCopies !== "undefined" && printerCopies > 1) ? printerCopies : 1;
            if (copies > 1) {
                const original = container.innerHTML;
                const cutLine = '<div style="border-top:1px dashed #000; margin:6mm 0;"></div>';
                container.innerHTML = Array(copies).fill(original).join(cutLine);
                window.print();
                container.innerHTML = original;
            } else {
                window.print();
            }
        }
    </script>
</body>
</html>