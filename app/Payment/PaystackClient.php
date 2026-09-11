<?php
declare(strict_types=1);

namespace TIS\Payment;

final class PaystackClient
{
    private const BASE_URL = 'https://api.paystack.co';

    public function __construct(private readonly string $secretKey)
    {
    }

    public function initialize(array $payload): array
    {
        return $this->request('POST', '/transaction/initialize', $payload);
    }

    public function verify(string $reference): array
    {
        return $this->request('GET', '/transaction/verify/' . rawurlencode($reference));
    }

    private function request(string $method, string $path, ?array $payload = null): array
    {
        $handle = curl_init(self::BASE_URL . $path);
        if ($handle === false) {
            throw new PaystackException('Unable to initialise a secure Paystack connection.');
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->secretKey,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            // Allow for occasional slow shared-host/DNS connections without failing checkout early.
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($payload !== null) {
            $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $encoded);
        }

        $body = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $curlErrorNumber = curl_errno($handle);
        $curlError = curl_error($handle);

        if ($body === false || $curlError !== '') {
            throw new PaystackException(sprintf(
                'Paystack connection failed (cURL %d: %s).',
                $curlErrorNumber,
                $curlError !== '' ? $curlError : 'empty response'
            ));
        }

        $decoded = json_decode((string) $body, true);
        if (!is_array($decoded)) {
            throw new PaystackException('Paystack returned an unreadable response.', $status);
        }

        if ($status < 200 || $status >= 300 || empty($decoded['status'])) {
            $message = is_string($decoded['message'] ?? null)
                ? $decoded['message']
                : 'Paystack could not process this request.';
            throw new PaystackException($message, $status);
        }

        return $decoded;
    }
}
