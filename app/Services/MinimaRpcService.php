<?php

namespace App\Services;

/**
 * Minima RPC Service — sends commands to Minima node via HTTP RPC.
 */
class MinimaRpcService
{
    protected string $rpcHost;
    protected int $rpcPort = 9005;
    protected string $rpcUser = 'minima';
    protected string $rpcPass = 'privseaipwd';

    const DADA_TOKEN_ID = '0x51F4A2CDB46F814C755931991294BCEB7D2827F931D399C19B33FE83C9B1F9EE';

    public function __construct()
    {
        $this->rpcHost = '172.20.0.1';
    }

    public function sendCommand(string $command): ?array
    {
        $url = "https://{$this->rpcHost}:{$this->rpcPort}/";
        $auth = base64_encode("{$this->rpcUser}:{$this->rpcPass}");

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $command,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/plain',
                "Authorization: Basic {$auth}",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno !== 0) {
            error_log("MinimaRpc: curl error {$errno}: {$error} | cmd: {$command}");
            return null;
        }

        $decoded = json_decode($response, true);
        if (!$decoded) {
            error_log("MinimaRpc: JSON parse failed | cmd: {$command}");
            return null;
        }

        return $decoded;
    }

    public function getBalance(): ?array
    {
        return $this->sendCommand('balance');
    }

    public function getDadaBalance(): int
    {
        $result = $this->getBalance();
        if (!$result || !isset($result['response'])) return 0;

        foreach ($result['response'] as $entry) {
            if (($entry['tokenid'] ?? '') === self::DADA_TOKEN_ID) {
                return (int) ($entry['confirmed'] ?? 0);
            }
        }
        return 0;
    }

    public function getStatus(): ?array
    {
        return $this->sendCommand('status');
    }

    public function newAddress(): ?array
    {
        return $this->sendCommand('newaddress');
    }

    /**
     * Send DADA_AI tokens using Minima's named parameter format.
     * Format: send address:<addr> amount:<amt> tokenid:<tokenid>
     */
    public function sendDada(string $address, int $amount): ?array
    {
        $command = "send address:{$address} amount:{$amount} tokenid:" . self::DADA_TOKEN_ID;
        return $this->sendCommand($command);
    }
}
