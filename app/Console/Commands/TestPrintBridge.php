<?php

namespace App\Console\Commands;

use App\Services\Printing\Escpos\EscPosCommandBuilder;
use App\Services\Printing\EscPosRenderResult;
use App\Services\Printing\PrinterProfileRepository;
use App\Services\Printing\PrintBridgeClient;
use Illuminate\Console\Command;

class TestPrintBridge extends Command
{
    protected $signature = 'printer:test-bridge';

    protected $description = 'Test Laravel to RETEXA Print Bridge printing';

    public function handle(
        PrinterProfileRepository $profiles,
        PrintBridgeClient $bridge
    ): int {
        $profile = $profiles->default();

        $this->info('Printer: ' . $profile->windowsPrinterName);
        $this->info('Paper: ' . $profile->paperWidthMm . 'mm');
        $this->info('Columns: ' . $profile->printableColumns);

        $builder = new EscPosCommandBuilder();

        $builder
            ->init()
            ->align('center')
            ->bold(true)
            ->size('double')
            ->text('RETEXA POS')
            ->newline()
            ->size('normal')
            ->bold(false)
            ->text('Laravel Print Bridge Test')
            ->newline()
            ->newline()
            ->align('left')
            ->text('Printer: YICHIP POS58')
            ->newline()
            ->text('Connection: USB001')
            ->newline()
            ->text('Protocol: ESC/POS')
            ->newline()
            ->newline()
            ->align('center')
            ->bold(true)
            ->text('PRINT SUCCESS')
            ->newline()
            ->bold(false)
            ->feed(3);

        $result = new EscPosRenderResult(
            $builder->toBytes(),
            $profile
        );

        try {
            $response = $bridge->print(
                $result,
                'laravel-test-' . now()->format('YmdHis')
            );

            $this->newLine();
            $this->info('PRINT SENT SUCCESSFULLY');

            $this->line(json_encode(
                $response,
                JSON_PRETTY_PRINT
            ));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('PRINT FAILED');
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}