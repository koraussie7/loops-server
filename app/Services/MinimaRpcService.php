<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MinimaRpcService
{
    protected string $host;
    protected int $port;
    protected string $password;
    protected bool $ssl;

    public function __construct()
    {
        $this->host = config('minima.rpc.host', '185.55.240.110');
        $this->port = (int) config('minima.rpc.port', 9005);
        $this->password = config('minima.rpc.password', 'privseairpc');
        $this->ssl = (bool) config('minima.rpc.ssl', true);
    }

    protected function baseUrl(): string
    {
        $scheme = $this->ssl ? 'https' : 'http';
        return "{$scheme}://{$this->host}:{$this->port}";
    }

    /**
     * Send a raw command to Minima RPC.
     */
    public function sendCommand(string $command, array $params = []): ?array
    {
        $url = $this->baseUrl() . '/';

        try {
            $body = $command;
            if (!empty($params)) {
                $body = $command;
                $query = http_build_query($params);
                $body = $command . ' ' . $query;
            }

            $response = Http::timeout(10)
                ->withOptions(['verify' => false])
                ->withBody($body, 'text/plain')
                ->post($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Minima RPC error', [
                'command' => $command,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Minima RPC exception', [
                'command' => $command,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Send DADA AI tokens to a Minima address.
     */
    public function sendDada(string $toAddress, int $amount): ?array
    {
        $tokenId = config('minima.dada_tokenid');
        $command = "send address:{$toAddress} amount:{$amount} tokenid:{$tokenId}";
        return $this->sendCommand($command);
    }

    /**
     * Get wallet balance (all tokens).
     */
    public function getBalance(): ?array
    {
        return $this->sendCommand('balance');
    }

    /**
     * Get DADA AI token balance specifically.
     */
    public function getDadaBalance(): ?array
    {
        $balances = $this->getBalance();
        if (!$balances || !isset($balances['response'])) {
            return null;
        }

        $tokenId = config('minima.dada_tokenid');
        foreach ($balances['response'] as $item) {
            if (($item['tokenid'] ?? '') === $tokenId) {
                return $item;
            }
        }

        return [
            'token' => 'DADA AI',
            'tokenid' => $tokenId,
            'confirmed' => 0,
            'sendable' => 0,
        ];
    }

    /**
     * Generate a new address.
     */
    public function newAddress(): ?string
    {
        $result = $this->sendCommand('newaddress');
        return $result['response'] ?? null;
    }
public function verifySignature(string $address, string $data, string $signature): bool
    {
        $command = "checksig address:{$address} data:{$data} signature:{$signature}";
        $result = $this->sendCommand($command);

        if ($result === null) {
            return false;
        }

        $raw = $result["response"] ?? false;
        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }

}
