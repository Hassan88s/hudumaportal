<?php

namespace App\Http\Controllers;

use App\CustomOffer;
use App\ExtraService;
use App\Order;
use App\Services\ClickPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Subscription\Entities\SellerSubscription;
use Modules\Subscription\Entities\SubscriptionHistory;
use Modules\Wallet\Entities\Wallet;
use Modules\Wallet\Entities\WalletHistory;

/**
 * ClickPesa webhook + browser-return receiver.
 *
 * - handle()             : server-to-server POST from ClickPesa. Payload:
 *                          { event, data: { orderReference, status, id, ... } }
 * - returnFromCheckout() : browser GET/POST when the customer returns from the
 *                          hosted checkout page. Verifies status via the API,
 *                          updates DB, and redirects the user to the right page.
 *
 * OrderReference prefixes (see ClickPesaService::makeOrderReference):
 *   HPW = wallet deposit          (handled by wallet controllers already)
 *   HPS = subscription            (updates SellerSubscription + SubscriptionHistory)
 *   HPO = service order           (updates Order)
 *   HPE = extra service order     (updates ExtraService)
 *   HPC = custom offer            (also an Order — same handling as HPO)
 *
 * Configure the webhook in ClickPesa Dashboard → Settings → Developers → Webhooks:
 *   https://YOUR-DOMAIN/clickpesa/webhook
 * All flows in Huduma Portal use the same webhook URL — routing happens by prefix.
 */
class ClickPesaWebhookController extends Controller
{
    /* ==========================================================
       WEBHOOK — server-to-server, no session, authoritative
       ========================================================== */
    public function handle(Request $request)
    {
        $payload = $request->all();
        Log::info('ClickPesa webhook received', $payload);

        $data     = $payload['data'] ?? [];
        $orderRef = $data['orderReference'] ?? null;

        if (!$orderRef) {
            return response()->json(['ok' => false, 'error' => 'missing orderReference'], 200);
        }

        $this->processPayment(
            $orderRef,
            ClickPesaService::normalizeStatus($data['status'] ?? null),
            $data['id'] ?? $data['paymentReference'] ?? ''
        );

        return response()->json(['ok' => true], 200);
    }

    /* ==========================================================
       BROWSER RETURN — user redirected back from hosted checkout
       ========================================================== */
    public function returnFromCheckout(Request $request)
    {
        $orderRef = $request->query('orderReference')
            ?? $request->query('order_reference')
            ?? session()->get('clickpesa_order_ref');

        if (!$orderRef) {
            toastr_warning(__('Payment reference missing.'));
            return $this->fallbackRedirect(null);
        }

        // Verify with ClickPesa (authoritative source of truth)
        $service = new ClickPesaService();
        $payment = $service->queryPayment($orderRef);

        if (!$payment) {
            toastr_warning(__('Could not verify payment yet — please refresh in a moment.'));
            return $this->fallbackRedirect($orderRef);
        }

        $status = ClickPesaService::normalizeStatus($payment['status'] ?? null);
        $txn    = $payment['id'] ?? $payment['paymentReference'] ?? '';

        $this->processPayment($orderRef, $status, $txn);

        $parsed = ClickPesaService::parseOrderReference($orderRef);
        $type   = $parsed['type'];
        $id     = $parsed['id'];

        if ($status === 'complete') {
            toastr_success(__('Payment successful.'));
            return $this->successRedirect($type, $id);
        }

        toastr_warning(__('Payment not completed. Status: ') . ($payment['status'] ?? 'unknown'));
        return $this->cancelRedirect($type, $id);
    }

    /* ==========================================================
       Shared: apply the state change based on type + status
       ========================================================== */
    protected function processPayment(string $orderRef, string $status, string $txn): void
    {
        $parsed = ClickPesaService::parseOrderReference($orderRef);
        $type   = $parsed['type'];
        $id     = $parsed['id'];
        if (!$type || !$id) return;

        if ($status === 'complete') {
            switch ($type) {
                case 'wallet':       $this->creditWallet($id, $txn); break;
                case 'subscription': $this->activateSubscription($id, $txn); break;
                case 'order':        $this->markOrderPaid($id, $txn); break;
                case 'custom':       $this->markOrderPaid($id, $txn); break;
                case 'extra':        $this->markExtraServicePaid($id, $txn); break;
            }
        } elseif ($status === 'failed') {
            switch ($type) {
                case 'wallet':       WalletHistory::where('id', $id)->update(['payment_status' => 'failed']); break;
                case 'subscription': SellerSubscription::where('id', $id)->update(['payment_status' => 'failed']); break;
                case 'order':
                case 'custom':       Order::where('id', $id)->update(['payment_status' => 'failed']); break;
                case 'extra':        ExtraService::where('id', $id)->update(['payment_status' => 'failed']); break;
            }
        }
    }

    /* ==========================================================
       Per-type update helpers — idempotent (safe to call twice)
       ========================================================== */
    protected function creditWallet(int $id, string $txn): void
    {
        $history = WalletHistory::find($id);
        if (!$history || $history->payment_status === 'complete') return;

        WalletHistory::where('id', $id)->update([
            'payment_status' => 'complete',
            'transaction_id' => $txn,
            'status'         => 1,
        ]);
        $wallet = Wallet::where('buyer_id', $history->buyer_id)->first();
        if ($wallet) {
            Wallet::where('buyer_id', $history->buyer_id)->update([
                'balance' => $wallet->balance + $history->amount,
            ]);
        }
    }

    protected function activateSubscription(int $id, string $txn): void
    {
        $sub = SellerSubscription::find($id);
        if (!$sub || $sub->payment_status === 'complete') return;

        SellerSubscription::where('id', $id)->update([
            'payment_status' => 'complete',
            'transaction_id' => $txn,
            'connect'        => ($sub->initial_connect ?? 0) + ($sub->connect ?? 0),
            'price'          => $sub->initial_price,
            'initial_service'=> $sub->initial_service,
            'initial_job'    => $sub->initial_job,
            'status'         => 1,
        ]);

        // Replace any previous SellerSubscription rows this seller had —
        // the newly-activated one is now their current plan.
        SellerSubscription::where('seller_id', $sub->seller_id)
            ->where('id', '!=', $id)
            ->delete();

        // Update the latest matching subscription-history row for this seller
        $history = SubscriptionHistory::where('seller_id', $sub->seller_id)
            ->orderBy('id', 'desc')
            ->first();
        if ($history) {
            SubscriptionHistory::where('id', $history->id)->update(['payment_status' => 'complete']);
        }
    }

    protected function markOrderPaid(int $id, string $txn): void
    {
        $order = Order::find($id);
        if (!$order || $order->payment_status === 'complete') return;

        Order::where('id', $id)->update([
            'payment_status' => 'complete',
            'status'         => 0,
            'transaction_id' => $txn,
        ]);

        // If this order came from a custom offer, mark the CustomOffer as accepted
        // (mirrors what the manual_payment branch does in BuyerCustomController).
        if (!empty($order->Custom_offer_id)) {
            CustomOffer::where('id', $order->Custom_offer_id)
                ->where('buyer_id', $order->buyer_id)
                ->update([
                    'status'         => '1',
                    'cjob_timelimit' => $order->offer_time_end,
                ]);
        }

        // If this order came from a job post (buyer hiring a freelancer), mark the
        // matching JobRequest as hired — mirrors the wallet-payment branch in
        // JobRequestController.
        if (!empty($order->job_post_id) && (int) $order->job_post_id > 0) {
            if (class_exists(\Modules\JobPost\Entities\JobRequest::class)) {
                \Modules\JobPost\Entities\JobRequest::where('job_post_id', $order->job_post_id)
                    ->where('buyer_id', $order->buyer_id)
                    ->where('seller_id', $order->seller_id)
                    ->update(['is_hired' => 1]);
            }
        }
    }

    protected function markExtraServicePaid(int $id, string $txn): void
    {
        $ex = ExtraService::find($id);
        if (!$ex || $ex->payment_status === 'complete') return;

        ExtraService::where('id', $id)->update([
            'payment_status' => 'complete',
            'transaction_id' => $txn,
            'status'         => 1,
        ]);
    }

    /* ==========================================================
       Redirects
       ========================================================== */
    /**
     * Always redirect back to the canonical APP_URL host so the browser
     * returns to the domain where the user's session cookie lives.
     * This matters when ClickPesa returns via a tunnel URL (ngrok / cloudflared /
     * tailscale funnel) — without this the browser stays on the tunnel domain,
     * has no session, and gets bounced to /login.
     */
    protected function appUrl(string $path): string
    {
        $base = rtrim(env('APP_URL', url('/')), '/');
        return $base . '/' . ltrim($path, '/');
    }

    protected function successRedirect(?string $type, ?int $id)
    {
        $wrapped = $id ? Str::random(30) . $id . Str::random(30) : null;
        switch ($type) {
            case 'wallet':
                // We don't know here if user is seller or buyer — seller is far more common for deposits
                return redirect($this->appUrl('/seller/wallet-history'));
            case 'subscription':
                return redirect($this->appUrl('/subscription/subscription-success/' . $wrapped));
            case 'order':
            case 'custom':
                return redirect($this->appUrl('/order-success/' . $wrapped));
            case 'extra':
                return redirect($this->appUrl('/buyer/orders'));
        }
        return redirect($this->appUrl('/'));
    }

    protected function cancelRedirect(?string $type, ?int $id)
    {
        switch ($type) {
            case 'wallet':
                return redirect($this->appUrl('/seller/wallet/deposit-cancel-static'));
            case 'subscription':
                return redirect($this->appUrl('/subscription/subscription-cancel-static'));
            case 'order':
            case 'custom':
                return redirect($this->appUrl('/order-cancel-static'));
            case 'extra':
                return redirect($this->appUrl('/buyer/orders'));
        }
        return redirect($this->appUrl('/'));
    }

    protected function fallbackRedirect(?string $orderRef)
    {
        if (!$orderRef) return redirect()->route('homepage');
        $parsed = ClickPesaService::parseOrderReference($orderRef);
        return $this->cancelRedirect($parsed['type'], $parsed['id']);
    }
}
