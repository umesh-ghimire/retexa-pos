<?php
namespace App\Services\Printing;

final class EscPosRenderResult
{
    public function __construct(public readonly string $bytes, public readonly PrinterProfile $profile, public readonly array $warnings=[]) {}
    public function toPrintJob(string $jobId): array { return ['printer'=>$this->profile->windowsPrinterName,'job_id'=>$jobId,'copies'=>$this->profile->copies,'data'=>base64_encode($this->bytes)]; }
}
