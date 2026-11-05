{{-- Scoped styling for the Spin & Win reward history (buyer + seller). --}}
<style>
    .spin_reward__stat {
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 10px;
        padding: 18px 20px;
        height: 100%;
        background: rgba(0, 0, 0, .015);
    }

    .spin_reward__stat__label {
        display: block;
        font-size: 13px;
        opacity: .7;
        margin-bottom: 6px;
    }

    .spin_reward__stat__value {
        font-size: 22px;
        font-weight: 600;
        margin: 0;
        line-height: 1.2;
    }

    .spin_reward__note {
        font-size: 14px;
        opacity: .75;
        margin-bottom: 0;
    }

    .spin_reward__prize {
        font-weight: 500;
    }

    .spin_reward__code {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px dashed rgba(0, 0, 0, .22);
        border-radius: 6px;
        padding: 5px 10px;
    }

    .spin_reward__code__text {
        font-family: monospace;
        font-size: 14px;
        letter-spacing: .5px;
    }

    .spin_reward__code__copy {
        border: 0;
        background: transparent;
        padding: 0;
        cursor: pointer;
        opacity: .6;
        line-height: 1;
    }

    .spin_reward__code__copy:hover {
        opacity: 1;
    }

    .spin_reward__code.is-spent {
        opacity: .5;
    }

    .spin_reward__code.is-spent .spin_reward__code__text {
        text-decoration: line-through;
    }

    .spin_reward__code__empty {
        opacity: .5;
    }

    .spin_reward__status {
        display: inline-block;
        font-size: 13px;
        font-weight: 500;
        border-radius: 4px;
        padding: 4px 10px;
    }

    .spin_reward__status.is-available {
        color: #1b8f5a;
        background: rgba(27, 143, 90, .1);
    }

    .spin_reward__status.is-used {
        color: inherit;
        opacity: .65;
        background: rgba(0, 0, 0, .06);
    }

    .spin_reward__status small {
        display: block;
        font-weight: 400;
        font-size: 11px;
    }
</style>
