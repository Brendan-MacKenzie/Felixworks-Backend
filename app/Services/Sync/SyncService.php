<?php

namespace App\Services\Sync;

use Exception;
use App\Models\Agency;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use App\Exceptions\SyncException;
use Illuminate\Support\Facades\Log;

class SyncService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout'  => 2.0,
        ]);
    }

    public function notifyAgency(Agency $agency, string $type, mixed $data)
    {
        // Execute call on configured webhooks only.
        if (!$agency->webhook || !$agency->webhook_key) {
            throw new SyncException('Webhook is not configured right.', $agency->id, $type, $data);
        }

        // Execute call only on agencies with API keys.
        if (!$agency->api_key || !$agency->ip_address) {
            throw new SyncException('API is not configured right for this agency.', $agency->id, $type, $data);
        }

        $this->request($agency, $type, $data);
    }

    private function request(Agency $agency, string $type, mixed $data)
    {
        try {
            $response = $this->client->post(
                $agency->webhook,
                [
                    RequestOptions::HEADERS => [
                        'Authorization' => $agency->webhook_key,
                    ],
                    RequestOptions::JSON => [
                        'type' => $type,
                        'data' => $data,
                    ],
                ]
            );
        } catch (Exception $exception) {
            report($exception);
            throw new SyncException('Bad response on webhook call.', $agency->id, $type, $data);
        }

        $this->verifyResponse($agency, $type, $data, $response);
    }

    private function verifyResponse(Agency $agency, string $type, mixed $data, $response)
    {
        $code = $response->getStatusCode();

        if ($code !== 200 && $code !== 202) {
            $contents = $response->getBody()->getContents();

            throw new SyncException("Webhook response: {$contents}", $agency->id, $type, $data);
        }

        Log::info('[SYNC] Agency: '.$agency->id.' - '.$type.' - DONE.');
    }
}
