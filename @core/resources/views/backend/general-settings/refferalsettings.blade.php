@extends('backend.admin-master')
@section('site-title')
    {{ __('Rafiki Rewards — Referral Settings') }}
@endsection

@section('style')
<style>
    .ref-settings .section-card{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:22px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.03)}
    .ref-settings .section-hd{display:flex;align-items:center;gap:10px;margin:0 0 6px;font-size:16px;font-weight:700;color:#1f2733}
    .ref-settings .section-hd .badge{font-size:10px;font-weight:600;padding:3px 8px;border-radius:999px;letter-spacing:.4px}
    .ref-settings .section-hd .badge.provider{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}
    .ref-settings .section-hd .badge.client{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}
    .ref-settings .section-hd .badge.system{background:#f3f4f6;color:#374151;border:1px solid #d1d5db}
    .ref-settings .section-hd .badge.legacy{background:#fef3c7;color:#92400e;border:1px solid #fde68a}
    .ref-settings .section-sub{font-size:13px;color:#6b7280;margin:0 0 16px}
    .ref-settings .row-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .ref-settings .form-group{margin-bottom:14px}
    .ref-settings .form-group label{font-weight:600;font-size:13px;color:#1f2733;display:flex;justify-content:space-between;align-items:center;margin-bottom:6px}
    .ref-settings .form-group .hint{font-weight:400;color:#8892a0;font-size:11px}
    .ref-settings .input-tzs{position:relative}
    .ref-settings .input-tzs .form-control{padding-right:50px}
    .ref-settings .input-tzs::after{content:'TZS';position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#8892a0;font-size:12px;font-weight:600;pointer-events:none}
    .ref-settings .input-days{position:relative}
    .ref-settings .input-days .form-control{padding-right:60px}
    .ref-settings .input-days::after{content:'days';position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#8892a0;font-size:12px;font-weight:600;pointer-events:none}
    .ref-settings .toggle-row{display:flex;align-items:center;gap:12px;background:#f8f9fb;border:1px solid #e6e9ef;border-radius:8px;padding:14px 18px}
    .ref-settings .toggle-row label{margin:0;font-weight:700;font-size:14px;flex:1;cursor:pointer}
    .ref-settings .toggle-row .switch{position:relative;width:46px;height:24px}
    .ref-settings .toggle-row .switch input{opacity:0;width:0;height:0}
    .ref-settings .toggle-row .slider{position:absolute;cursor:pointer;inset:0;background:#cbd5e1;border-radius:24px;transition:.2s}
    .ref-settings .toggle-row .slider:before{position:absolute;content:'';height:18px;width:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s}
    .ref-settings .toggle-row input:checked + .slider{background:#ff8a54}
    .ref-settings .toggle-row input:checked + .slider:before{transform:translateX(22px)}
    .ref-settings .totals-strip{background:linear-gradient(135deg,#fff7ed 0%,#fff 100%);border:1px solid #fed7aa;border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:#78350f}
    .ref-settings .totals-strip strong{color:#c2410c}
    @media (max-width: 720px){ .ref-settings .row-grid{grid-template-columns:1fr} }
</style>
@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30 ref-settings">
    <div class="row">
        <div class="col-12 mt-5">
            @include('backend.partials.message')

            @if($errors->any())
                @foreach($errors->all() as $error)
                    <div class="alert alert-danger">{{ $error }}</div>
                @endforeach
            @endif

            <form action="{{ route('admin.general.update.refferal') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- ═══ Program Toggle ═══ --}}
                <div class="section-card">
                    <h4 class="section-hd">{{ __('Rafiki Rewards Program') }}</h4>
                    <p class="section-sub">{{ __('Master switch. When off, no new referral rewards are created (existing balances stay).') }}</p>
                    <div class="toggle-row">
                        <label for="referral_enabled">{{ __('Enable Rafiki Rewards program') }}</label>
                        <span class="switch">
                            <input type="hidden" name="referral_enabled" value="0">
                            <input type="checkbox" id="referral_enabled" name="referral_enabled" value="1"
                                @if((int) get_static_option('referral_enabled') === 1) checked @endif>
                            <span class="slider"></span>
                        </span>
                    </div>
                </div>

                {{-- ═══ Provider (Freelancer) Rewards ═══ --}}
                <div class="section-card">
                    <h4 class="section-hd">{{ __('Provider (Freelancer) Track') }} <span class="badge provider">MAX 3,000 TZS</span></h4>
                    <p class="section-sub">{{ __('Rewards to the referrer when a referred freelancer completes each milestone.') }}</p>
                    <div class="row-grid">
                        <div class="form-group">
                            <label>{{ __('Stage 1 — Profile + Service Published') }} <span class="hint">{{ __('to referrer') }}</span></label>
                            <div class="input-tzs"><input type="number" step="0.01" name="referral_stage1_provider_amount"
                                class="form-control" value="{{ get_static_option('referral_stage1_provider_amount', 500) }}"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Stage 2 — First Paid Order (cash)') }} <span class="hint">{{ __('to referrer') }}</span></label>
                            <div class="input-tzs"><input type="number" step="0.01" name="referral_stage2_provider_cash"
                                class="form-control" value="{{ get_static_option('referral_stage2_provider_cash', 1000) }}"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Stage 2 — Welcome Credit (to new provider)') }} <span class="hint">{{ __('spendable promo credit') }}</span></label>
                            <div class="input-tzs"><input type="number" step="0.01" name="referral_stage2_provider_credit"
                                class="form-control" value="{{ get_static_option('referral_stage2_provider_credit', 1000) }}"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Stage 3 — 2nd Order or Subscription') }} <span class="hint">{{ __('to referrer') }}</span></label>
                            <div class="input-tzs"><input type="number" step="0.01" name="referral_stage3_provider_amount"
                                class="form-control" value="{{ get_static_option('referral_stage3_provider_amount', 1500) }}"></div>
                        </div>
                    </div>
                </div>

                {{-- ═══ Client Rewards ═══ --}}
                <div class="section-card">
                    <h4 class="section-hd">{{ __('Client Track') }} <span class="badge client">MAX 1,500 TZS</span></h4>
                    <p class="section-sub">{{ __('Rewards for referring buyers/clients. The welcome credit goes to the new client (spendable on their first booking).') }}</p>
                    <div class="row-grid">
                        <div class="form-group">
                            <label>{{ __('Welcome Credit (to new client)') }} <span class="hint">{{ __('spendable promo credit') }}</span></label>
                            <div class="input-tzs"><input type="number" step="0.01" name="referral_client_welcome_credit"
                                class="form-control" value="{{ get_static_option('referral_client_welcome_credit', 1000) }}"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('First Booking Reward') }} <span class="hint">{{ __('to referrer') }}</span></label>
                            <div class="input-tzs"><input type="number" step="0.01" name="referral_client_first_booking"
                                class="form-control" value="{{ get_static_option('referral_client_first_booking', 750) }}"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Second Booking Reward (within 60 days)') }} <span class="hint">{{ __('to referrer') }}</span></label>
                            <div class="input-tzs"><input type="number" step="0.01" name="referral_client_second_booking"
                                class="form-control" value="{{ get_static_option('referral_client_second_booking', 750) }}"></div>
                        </div>
                    </div>
                </div>

                {{-- ═══ System Settings ═══ --}}
                <div class="section-card">
                    <h4 class="section-hd">{{ __('System Settings') }} <span class="badge system">GLOBAL</span></h4>
                    <p class="section-sub">{{ __('Attribution, protection, and withdrawal thresholds.') }}</p>
                    <div class="row-grid">
                        <div class="form-group">
                            <label>{{ __('Attribution Window') }} <span class="hint">{{ __('cookie lifetime') }}</span></label>
                            <div class="input-days"><input type="number" name="referral_attribution_days"
                                class="form-control" value="{{ get_static_option('referral_attribution_days', 30) }}"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Protection Window') }} <span class="hint">{{ __('pending → approved delay') }}</span></label>
                            <div class="input-days"><input type="number" name="referral_protection_days"
                                class="form-control" value="{{ get_static_option('referral_protection_days', 14) }}"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Minimum Transfer to Main Wallet') }} <span class="hint">{{ __('below this the transfer button stays disabled') }}</span></label>
                            <div class="input-tzs"><input type="number" step="0.01" name="referral_min_withdrawal"
                                class="form-control" value="{{ get_static_option('referral_min_withdrawal', 5000) }}"></div>
                        </div>
                    </div>
                </div>

                {{-- ═══ Legacy compatibility — HIDDEN in UI, still saved untouched ═══
                     Values stay in static_options as-is because some pre-Rafiki
                     code paths still read sign_up_points / first_order_points /
                     first_purchase_points as fallbacks. Flip @if(true) to show. --}}
                @if(false)
                <div class="section-card">
                    <h4 class="section-hd">{{ __('Legacy Settings') }} <span class="badge legacy">FALLBACK</span></h4>
                    <p class="section-sub">{{ __('These older fields are still read by parts of the site that pre-date Rafiki Rewards. Leave them at defaults unless you know why you are changing them.') }}</p>
                    <div class="row-grid">
                        <div class="form-group">
                            <label>{{ __('Legacy: Sign-up Points') }}</label>
                            <div class="input-tzs"><input type="number" step="0.01" name="sign_up_points"
                                class="form-control" value="{{ get_static_option('sign_up_points', 100) }}"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Legacy: First Service Creation Points') }}</label>
                            <div class="input-tzs"><input type="number" step="0.01" name="first_order_points"
                                class="form-control" value="{{ get_static_option('first_order_points', 5) }}"></div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Legacy: First Purchase Points') }}</label>
                            <div class="input-tzs"><input type="number" step="0.01" name="first_purchase_points"
                                class="form-control" value="{{ get_static_option('first_purchase_points', 5) }}"></div>
                        </div>
                    </div>
                </div>
                @endif

                <button id="update" type="submit" class="btn btn-primary pr-4 pl-4">
                    <i class="las la-save"></i> {{ __('Save All Referral Settings') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    <x-btn.update/>
</script>
@endsection
