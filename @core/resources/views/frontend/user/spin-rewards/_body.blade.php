{{--
    Shared Spin & Win reward history body. Included by both the buyer and the
    seller dashboard pages, which differ only in which sidebar they render.
--}}
<div class="dashboard__body">
    <div class="dashboard__inner">

        <!-- summary start -->
        <div class="dashboard__inner__item dashboard_border padding-20 radius-10 bg-white">
            <div class="dashboard_table__title__flex">
                <h4 class="dashboard_table__title">{{ __('Spin & Win Rewards') }}</h4>
                @if($settings['enabled'] && $tries_left > 0)
                    <div class="btn-wrapper">
                        <a href="{{ route('spin.wheel') }}" class="dashboard_table__title__btn btn-bg-1 radius-5">
                            <i class="las la-dharmachakra"></i> {{ __('Spin the Wheel') }}
                        </a>
                    </div>
                @endif
            </div>

            <div class="row g-4 mt-3 spin_reward__stats">
                <div class="col-lg-3 col-sm-6">
                    <div class="spin_reward__stat">
                        <span class="spin_reward__stat__label">{{ __('Spins Used') }}</span>
                        <h3 class="spin_reward__stat__value">{{ $tries_used }} / {{ $total_tries }}</h3>
                        @if($bonus_spins > 0)
                            <span class="spin_reward__stat__hint">
                                +{{ $bonus_spins }} {{ __('earned by inviting friends') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="spin_reward__stat">
                        <span class="spin_reward__stat__label">{{ __('Spins Left') }}</span>
                        <h3 class="spin_reward__stat__value">{{ $tries_left }}</h3>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="spin_reward__stat">
                        <span class="spin_reward__stat__label">{{ __('Rewards Won') }}</span>
                        <h3 class="spin_reward__stat__value">{{ $total_wins }}</h3>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="spin_reward__stat">
                        <span class="spin_reward__stat__label">{{ __('Best Reward') }}</span>
                        <h3 class="spin_reward__stat__value">
                            {{ $best_win > 0 ? rtrim(rtrim(number_format($best_win, 2, '.', ''), '0'), '.') . '%' : '—' }}
                        </h3>
                    </div>
                </div>
            </div>

            @if($total_wins > 0)
                <p class="spin_reward__note mt-3">
                    {{ __('To use a reward, paste its code into the "Coupon or Reward Code" box when you book a service. Each code works once.') }}
                </p>
            @endif

            @if(! $settings['enabled'])
                <p class="spin_reward__note mt-3">{{ __('The Spin & Win game is currently switched off.') }}</p>
            @elseif($tries_left < 1)
                <p class="spin_reward__note mt-3">{{ __('You have used all of your spins. Any reward you won stays valid below.') }}</p>
            @endif
        </div>
        <!-- summary end -->

        <!-- history start -->
        <div class="dashboard_table__wrapper dashboard_border padding-20 radius-10 bg-white">
            <div class="dashboard_table__title__flex">
                <h4 class="dashboard_table__title">{{ __('Reward History') }}</h4>
            </div>

            @if($spins->count() >= 1)
                <div class="dashboard_table__main custom--table mt-4">
                    <table>
                        <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Spin') }}</th>
                            <th>{{ __('Result') }}</th>
                            <th>{{ __('Reward') }}</th>
                            <th>{{ __('Discount Code') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($spins as $spin)
                            <tr>
                                <td>
                                    <div class="dashboard_table__main__ticket">
                                        <h5 class="dashboard_table__main__ticket__title">
                                            {{ $spin->created_at ? $spin->created_at->format('d M Y') : '—' }}
                                        </h5>
                                        <span class="dashboard_table__main__ticket__id mt-2">
                                            {{ $spin->created_at ? $spin->created_at->format('H:i') : '' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="dashboard_table__main__orderId">
                                        {{ __('Try') }} #{{ $spin->try_number }}
                                    </div>
                                </td>
                                <td>
                                    <div class="dashboard_table__main__priority">
                                        @if($spin->is_win)
                                            <a href="javascript:void(0)" class="priorityBtn medium">{{ __('Won') }}</a>
                                        @else
                                            <a href="javascript:void(0)" class="priorityBtn pending">{{ __('Try Again') }}</a>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="spin_reward__prize">
                                        {{ $spin->segment_label ?: __('No reward') }}
                                    </div>
                                </td>
                                <td>
                                    @if($spin->code)
                                        <div class="spin_reward__code @if($spin->used_at) is-spent @endif">
                                            <span class="spin_reward__code__text">{{ $spin->code }}</span>
                                            @if(! $spin->used_at)
                                                <button type="button"
                                                        class="spin_reward__code__copy"
                                                        data-code="{{ $spin->code }}"
                                                        title="{{ __('Copy code') }}">
                                                    <i class="far fa-copy"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="spin_reward__code__empty">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(! $spin->code)
                                        <span class="spin_reward__code__empty">—</span>
                                    @elseif($spin->used_at)
                                        <span class="spin_reward__status is-used">
                                            {{ __('Used') }}
                                            @if($spin->used_order_id)
                                                <small>{{ __('Order #') }}{{ $spin->used_order_id }}</small>
                                            @endif
                                        </span>
                                    @else
                                        <span class="spin_reward__status is-available">{{ __('Available') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="blog-pagination margin-top-55">
                    <div class="custom-pagination mt-4 mt-lg-5">
                        {!! $spins->links() !!}
                    </div>
                </div>
            @else
                <div class="chat_wrapper__details__inner__chat__contents mt-3">
                    <p class="no_data_found_for_buyer_seller_panel">
                        {{ __('You have not spun the wheel yet.') }}
                    </p>
                </div>
            @endif
        </div>
        <!-- history end -->

    </div>
</div>
