<?php
namespace App\Services\Printing;

final class PrinterProfileRepository
{
    public function all(): array
    {
        return array_map(fn($id, $cfg) => $this->make($id, $cfg), array_keys(config('printers.profiles', [])), config('printers.profiles', []));
    }
    public function find(string $id): ?PrinterProfile
    {
        $cfg = config("printers.profiles.$id");
        return is_array($cfg) ? $this->make($id, $cfg) : null;
    }
    public function default(): PrinterProfile
    {
        return $this->find((string) config('printers.default', 'pt210_yichip'))
            ?? throw new \RuntimeException('Default printer profile is not configured.');
    }
    private function make(string $id, array $c): PrinterProfile
    {
        return new PrinterProfile(
            $id, $c['display_name'], $c['windows_printer_name'], $c['protocol'] ?? 'ESC/POS',
            (float) $c['paper_width_mm'], (int) $c['printable_columns'], $c['encoding'] ?? 'CP437',
            $c['font_preset'] ?? 'A', (int) ($c['density'] ?? 7), (bool) ($c['supports_qr'] ?? false),
            (bool) ($c['supports_barcode'] ?? false), (bool) ($c['supports_cut'] ?? false),
            (bool) ($c['supports_logo'] ?? false), (int) ($c['copies'] ?? 1), (bool) ($c['enabled'] ?? true)
        );
    }
}
