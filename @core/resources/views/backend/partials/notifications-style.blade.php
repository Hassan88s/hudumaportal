{{-- Styling for the admin notification bell.

     The admin panel ships dark mode as a separate stylesheet that never
     touched this dropdown, so the old popup was white-on-dark. Colours here
     follow the actual setting instead of assuming the light theme. --}}
@php $hpDark = get_static_option('site_admin_dark_mode') == 'on'; @endphp
<style>
    .notification-area .dropdown-menu.hp-notify__box {
        width: 340px;
        max-width: calc(100vw - 24px);
        padding: 0;
        overflow: hidden;
        border-radius: 10px;
        background: {{ $hpDark ? '#303640' : '#fff' }};
        color: {{ $hpDark ? '#dfe3ec' : '#1b1f33' }};
        border: 1px solid {{ $hpDark ? '#424956' : 'rgba(0,0,0,.08)' }};
        box-shadow: 0 12px 34px rgba(0, 0, 0, {{ $hpDark ? '.45' : '.12' }});
    }

    .hp-notify__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 16px;
        border-bottom: 1px solid {{ $hpDark ? '#424956' : 'rgba(0,0,0,.08)' }};
    }

    .hp-notify__title {
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        color: {{ $hpDark ? '#fff' : '#1b1f33' }};
    }

    .hp-notify__count {
        font-style: normal;
        font-size: 11px;
        font-weight: 600;
        line-height: 1;
        padding: 3px 7px;
        border-radius: 20px;
        background: #ff5d7a;
        color: #fff;
    }

    .notification-area .hp-notify__all {
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
        color: {{ $hpDark ? '#8fb6ff' : '#2f6fdd' }} !important;
        text-decoration: none;
    }

    .hp-notify__all:hover { text-decoration: underline; }

    .hp-notify__list {
        max-height: 330px;
        overflow-y: auto;
    }

    .hp-notify__list::-webkit-scrollbar { width: 6px; }
    .hp-notify__list::-webkit-scrollbar-thumb {
        background: {{ $hpDark ? '#4d5564' : 'rgba(0,0,0,.18)' }};
        border-radius: 6px;
    }

    .notification-area .hp-notify__item {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 12px 16px;
        border-bottom: 1px solid {{ $hpDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)' }};
        transition: background .15s ease;
        color: {{ $hpDark ? '#dfe3ec' : '#1b1f33' }} !important;
        text-decoration: none;
    }

    .hp-notify__item:last-child { border-bottom: 0; }
    .notification-area .hp-notify__item:hover { background: {{ $hpDark ? '#383d48' : 'rgba(0,0,0,.04)' }}; }

    .hp-notify__icon {
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .hp-notify__icon.is-order  { background: rgba(76, 141, 255, .18); color: #6ba3ff; }
    .hp-notify__icon.is-ticket { background: rgba(255, 138, 84, .18); color: #ff9a68; }
    .hp-notify__icon.is-job    { background: rgba(57, 208, 201, .18); color: #3fd0c9; }

    .hp-notify__body {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .hp-notify__text {
        font-size: 13px;
        line-height: 1.35;
        word-break: break-word;
    }

    .hp-notify__text b { font-weight: 600; }

    .hp-notify__time {
        font-size: 11px;
        opacity: .62;
    }

    /* The full notification list reuses the bell's icons and badges. An
       unread row is marked with a rail down its leading edge rather than a
       tinted background, which the table's own row colours would fight. */
    .hp-notify__row--unread > td:first-child {
        box-shadow: inset 3px 0 0 0 #ff5d7a;
    }

    .hp-notify__row--unread > td {
        font-weight: 500;
    }

    .hp-notify__icon.d-inline-flex {
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        font-size: 15px;
    }

    .hp-notify__empty {
        padding: 34px 16px;
        text-align: center;
        opacity: .6;
    }

    .hp-notify__empty i { font-size: 30px; display: block; margin-bottom: 8px; }
    .hp-notify__empty p { font-size: 13px; margin: 0; }

    .notification-area .hp-notify__more {
        display: block;
        text-align: center;
        font-size: 12px;
        font-weight: 500;
        padding: 11px 16px;
        border-top: 1px solid {{ $hpDark ? '#424956' : 'rgba(0,0,0,.08)' }};
        color: {{ $hpDark ? '#8fb6ff' : '#2f6fdd' }} !important;
        text-decoration: none;
    }

    .hp-notify__more:hover { background: {{ $hpDark ? '#383d48' : 'rgba(0,0,0,.04)' }}; }
</style>
