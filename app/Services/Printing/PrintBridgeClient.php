<?php

namespace App\Services\Printing;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class PrintBridgeClient
{
    public function __construct(
        private readonly string $baseUrl = 'http://127.0.0.1:9123',
    ) {}

    public function print(
        EscPosRenderResult $result,
        ?string $jobId = null
    ): array {
        $jobId ??= 'retexa-' . now()->format('YmdHis') . '-' . uniqid();

        /*
         * Build the COMPLETE print job.
         *
         * The Print Bridge expects:
         * - printer
         * - job_id
         * - copies
         * - data
         */
        $job = $result->toPrintJob($jobId);

        $client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/'),
            'connect_timeout' => 2,
            'timeout' => 10,
        ]);

        try {
            $response = $client->post('/print', [
                'json' => [
                    'printer' => $job['printer'],
                    'job_id' => $job['job_id'],
                    'copies' => $job['copies'],
                    'data' => $job['data'],
                ],
            ]);

            $data = json_decode(
                (string) $response->getBody(),
                true
            );

            return [
                'success' => true,
                'job_id' => $jobId,
                'printer' => $job['printer'],
                'response' => $data,
            ];
        } catch (GuzzleException $e) {
            throw new \RuntimeException(
                'Could not connect to RETEXA Print Bridge: ' .
                $e->getMessage(),
                0,
                $e
            );
        }
    }
}