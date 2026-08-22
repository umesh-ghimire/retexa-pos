<?php
namespace App\Services\Printing;

final class PrinterProfile
{
    public function __construct(
        public readonly string $id,
        public readonly string $displayName,
        public readonly string $windowsPrinterName,
        public readonly string $protocol,
        public readonly float $paperWidthMm,
        public readonly int $printableColumns,
        public readonly string $encoding = 'CP437',
        public readonly string $fontPreset = 'A',
        public readonly int $density = 7,
        public readonly bool $supportsQr = false,
        public readonly bool $supportsBarcode = false,
        public readonly bool $supportsCut = false,
        public readonly bool $supportsLogo = false,
        public readonly int $copies = 1,
        public readonly bool $enabled = true,
    ) {}
}
