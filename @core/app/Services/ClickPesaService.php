<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ClickPesa hosted-checkout service.
 *
 * Reusable across wallet deposit, subscription buy, service orders, extra services.
 * All configuration is read from .env — see CLICKPESA_* keys.
 *
 * Docs: https://docs.clickpesa.com/
 */
class ClickPesaService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $apiKey;
    protected string $currency;
    protected int    $timeout = 30;

    public function __construct()
    {
        $this->baseUrl  = rtrim(env('CLICKPESA_BASE_URL', 'https://api.clickpesa.com'), '/');
        $this->clientId = (string) env('CLICKPESA_CLIENT_ID', '');
        $this->apiKey   = (string) env('CLICKPESA_API_KEY', '');
        $this->currency = strtoupper((string) env('CLICKPESA_CURRENCY', 'TZS'));
    }

    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->apiKey !== '';
    }

    /**
     * Get a JWT bearer token, cached for ~55 min (token is valid 60 min).
     */
    public function getToken(bool $forceFresh = false): ?string
    {
        $cacheKey = 'clickpesa_token_' . md5($this->clientId);

        if (!$forceFresh) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'client-id' => $this->clientId,
                    'api-key'   => $this->apiKey,
                ])
                ->post($this->baseUrl . '/third-parties/generate-token');

            if (!$response->successful()) {
                Log::warning('ClickPesa generate-token failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $json  = $response->json();
            $token = $json['token'] ?? null;

            if (!$token) {
                Log::warning('ClickPesa generate-token returned no token', ['body' => $json]);
                return null;
            }

            // Normalize: sometimes returned prefixed with "Bearer "
            $token = preg_replace('/^Bearer\s+/i', '', $token);

            Cache::put($cacheKey, $token, now()->addMinutes(55));
            return $token;
        } catch (\Throwable $e) {
            Log::error('ClickPesa token exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a hosted-checkout URL.
     *
     * @param array $payload Required keys:
     *   - orderReference (string, alphanumeric only)
     *   - amount (numeric)
     *   - callbackUrl (string, absolute URL — where ClickPesa returns the customer after payment)
     *   Optional:
     *   - description, customerName, customerEmail, customerPhone (255XXXXXXXXX)
     *   - currency (defaults to CLICKPESA_CURRENCY)
     *
     * @return array{ok:bool,url?:string,error?:string,raw?:mixed}
     */
    public function createCheckoutLink(array $payload): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'ClickPesa is not configured (missing client-id/api-key).'];
        }

        $token = $this->getToken();
        if (!$token) {
            return ['ok' => false, 'error' => 'Could not obtain ClickPesa auth token.'];
        }

        $orderReference = preg_replace('/[^A-Za-z0-9]/', '', (string) ($payload['orderReference'] ?? ''));
        if ($orderReference === '') {
            return ['ok' => false, 'error' => 'orderReference is required and must be alphanumeric.'];
        }

        $body = array_filter([
            'totalPrice'     => (string) ($payload['amount'] ?? ''),
            'orderReference' => $orderReference,
            'orderCurrency'  => strtoupper($payload['currency'] ?? $this->currency),
            'customerName'   => $payload['customerName']  ?? null,
            'customerEmail'  => $payload['customerEmail'] ?? null,
            'customerPhone'  => $payload['customerPhone'] ?? null,
            'description'    => $payload['description']   ?? null,
            'callbackUrl'    => $payload['callbackUrl']   ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->acceptJson()
                ->asJson()
                ->post($this->baseUrl . '/third-parties/checkout-link/generate-checkout-url', $body);

            if (!$response->successful()) {
                Log::warning('ClickPesa generate-checkout-url failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'sent'   => $body,
                ]);
                $msg = $response->json('message') ?? ('HTTP ' . $response->status());
                return ['ok' => false, 'error' => $msg, 'raw' => $response->json()];
            }

            $json = $response->json();
            $url  = $json['checkoutLink'] ?? null;

            if (!$url) {
                return ['ok' => false, 'error' => 'No checkoutLink in response', 'raw' => $json];
            }

            return ['ok' => true, 'url' => $url, 'raw' => $json];
        } catch (\Throwable $e) {
            Log::error('ClickPesa createCheckoutLink exception: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Query payment status by orderReference.
     *
     * Returns the first matching transaction (there is usually only one per order-ref)
     * or null if none / on error.
     */
    public function queryPayment(string $orderReference): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        $orderReference = preg_replace('/[^A-Za-z0-9]/', '', $orderReference);

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->acceptJson()
                ->get($this->baseUrl . '/third-parties/payments/' . $orderReference);

            if (!$response->successful()) {
                Log::warning('ClickPesa queryPayment failed', [
                    'ref'    => $orderReference,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            if (is_array($data) && isset($data[0])) {
                return $data[0];
            }
            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            Log::error('ClickPesa queryPayment exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Normalize a payment status to one of: complete | failed | pending.
     */
    public static function normalizeStatus(?string $status): string
    {
        $status = strtoupper((string) $status);
        if (in_array($status, ['SUCCESS', 'SETTLED', 'COMPLETED', 'COMPLETE'], true)) {
            return 'complete';
        }
        if (in_array($status, ['FAILED', 'CANCELED', 'CANCELLED', 'DECLINED'], true)) {
            return 'failed';
        }
        return 'pending';
    }

    /**
     * Build an order reference for a given type + numeric id.
     *
     * ClickPesa requires globally-unique alphanumeric references across all attempts.
     * So we always append a random alpha suffix — the format is:
     *
     *     <prefix><digits (id)><random letters (suffix)>
     *     e.g.  HPW42ZQPX     HPS2ABCD    HPO187LMNX
     *
     * parseOrderReference() extracts only the leading digits after the known prefix,
     * so both new (with suffix) and old (without suffix) references parse correctly.
     */
    public static function makeOrderReference(string $type, $id): string
    {
        $prefixMap = [
            'wallet'       => 'HPW',
            'subscription' => 'HPS',
            'order'        => 'HPO',
            'extra'        => 'HPE',
            'custom'       => 'HPC',
        ];
        $prefix = $prefixMap[$type] ?? 'HP';
        $digits = preg_replace('/[^0-9]/', '', (string) $id);
        // 5-char random alpha suffix — 26^5 = ~12M combinations, collisions extremely unlikely
        $suffix = '';
        for ($i = 0; $i < 5; $i++) {
            $suffix .= chr(random_int(65, 90)); // A-Z
        }
        return $prefix . $digits . $suffix;
    }

    /**
     * Extract the numeric id from an order reference produced by makeOrderReference().
     * Handles both new-format (HPS2ABCDE) and legacy (HPS2) references.
     */
    public static function parseOrderReference(string $ref): array
    {
        $prefixToType = [
            'HPW' => 'wallet',
            'HPS' => 'subscription',
            'HPO' => 'order',
            'HPE' => 'extra',
            'HPC' => 'custom',
        ];
        foreach ($prefixToType as $prefix => $type) {
            if (strncasecmp($ref, $prefix, strlen($prefix)) === 0) {
                $rest = substr($ref, strlen($prefix));
                if (preg_match('/^(\d+)/', $rest, $m)) {
                    return ['type' => $type, 'id' => (int) $m[1]];
                }
            }
        }
        return ['type' => null, 'id' => null];
    }
}
