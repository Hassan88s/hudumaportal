<style>
/* ================================================================
   MY AVAILABILITY — merged Days + Schedules (Huduma orange theme)
   scoped under .av-wrap so nothing leaks
   ================================================================ */
.av-wrap {
    --av-primary: #ff6b2c;
    --av-primary-dark: #e55621;
    --av-primary-soft: #fff2eb;
    --av-primary-tint: #ffe9dd;
    --av-bg: #f7f8fb;
    --av-surface: #fff;
    --av-border: #ececf3;
    --av-border-soft: #f1f2f7;
    --av-text: #1f2433;
    --av-muted: #6b7280;
    --av-muted-2: #8b8fa3;
    --av-success: #1aae6f;
    --av-success-soft: #e6f7ef;
    --av-danger: #d9344b;
    --av-danger-soft: #fde7eb;
    --av-shadow: 0 6px 22px rgba(20,24,50,.06);
    --av-shadow-sm: 0 2px 8px rgba(20,24,50,.04);
    background: var(--av-bg);
    padding: 24px;
    border-radius: 16px;
}

/* ===== Header ===== */
.av-header { margin-bottom: 20px; }
.av-title  { font-size: 24px; font-weight: 800; color: var(--av-text); margin: 0; letter-spacing: .2px; }
.av-subtitle { font-size: 14px; color: var(--av-muted); margin: 6px 0 0; }

/* ===== Global settings cards ===== */
.av-settings {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.av-setting {
    background: var(--av-surface);
    border: 1px solid var(--av-border);
    border-radius: 14px;
    padding: 16px 18px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
    box-shadow: var(--av-shadow-sm);
}
.av-setting__icon {
    flex: 0 0 42px;
    width: 42px; height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--av-primary-soft), var(--av-primary-tint));
    color: var(--av-primary-dark);
    font-size: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.av-setting__body { flex: 1 1 auto; min-width: 0; }
.av-setting__label { display: block; font-size: 13.5px; font-weight: 700; color: var(--av-text); letter-spacing: .2px; margin: 0 0 2px; }
.av-setting__hint  { font-size: 12.5px; color: var(--av-muted); margin: 0 0 10px; }

.av-inline-form { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.av-select {
    flex: 1 1 auto;
    min-width: 160px;
    border: 1px solid var(--av-border) !important;
    background: var(--av-surface) !important;
    color: var(--av-text) !important;
    padding: 9px 12px !important;
    border-radius: 10px !important;
    font-size: 13.5px !important;
    height: auto !important;
    line-height: 1.3 !important;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='7' viewBox='0 0 12 7'><path fill='%236b7280' d='M6 7L0 0h12z'/></svg>") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    padding-right: 32px !important;
}
.av-select:focus { outline: none; border-color: var(--av-primary) !important; box-shadow: 0 0 0 3px rgba(255,107,44,.15) !important; }

/* ===== Buttons ===== */
.av-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all .18s ease;
    white-space: nowrap;
    line-height: 1;
}
.av-btn--sm { padding: 7px 12px; font-size: 12.5px; }
.av-btn--primary {
    /* Softer, less-saturated orange — matches the rest of the site's tone */
    background: #ff8a54 !important;
    color: #fff !important;
    border-color: #ff8a54 !important;
    box-shadow: 0 2px 6px rgba(255,138,84,.18);
}
.av-btn--primary:hover {
    background: #ff7a3f !important;
    border-color: #ff7a3f !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(255,138,84,.24);
}
.av-btn--primary:disabled { opacity: .45; cursor: not-allowed; transform: none; box-shadow: none; }
.av-btn--ghost {
    background: #fff5ee !important;
    color: #c7501e !important;
    border-color: #ffdcc7 !important;
}
.av-btn--ghost:hover { background: #ffe9dd !important; border-color: #ffcfad !important; }

/* ===== Weekly grid ===== */
.av-week {
    background: var(--av-surface);
    border: 1px solid var(--av-border);
    border-radius: 16px;
    box-shadow: var(--av-shadow);
    overflow: hidden;
}
.av-week__head {
    padding: 18px 22px;
    background: linear-gradient(135deg, #fff8f3, #fffdfb);
    border-bottom: 1px solid var(--av-border);
}
.av-week__head h4 { font-size: 15px; font-weight: 800; color: var(--av-text); margin: 0; text-transform: uppercase; letter-spacing: .3px; }
.av-week__head p  { font-size: 12.5px; color: var(--av-muted); margin: 4px 0 0; }

.av-day {
    padding: 16px 22px;
    border-bottom: 1px solid var(--av-border-soft);
    transition: background .15s;
}
.av-day:last-child { border-bottom: none; }
.av-day.is-off { background: #fafbfd; }

.av-day__head {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.av-day__label { flex: 1 1 auto; min-width: 0; }
.av-day__name  { display: block; font-size: 15px; font-weight: 700; color: var(--av-text); line-height: 1.2; }
.av-day__meta  { display: block; font-size: 12px; color: var(--av-muted); margin-top: 3px; }
.av-day__status {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .5px;
    background: var(--av-success-soft);
    color: var(--av-success);
}
.av-day__status--off { background: #eef0f6; color: var(--av-muted-2); }
.av-day__count { color: var(--av-muted-2); font-weight: 500; }
.av-day__actions { display: flex; gap: 8px; }

/* ===== Toggle switch ===== */
.av-toggle {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 26px;
    flex-shrink: 0;
}
.av-toggle__input { opacity: 0; width: 0; height: 0; }
.av-toggle__slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: #d1d5db;
    border-radius: 999px;
    transition: .2s;
}
.av-toggle__slider::before {
    content: "";
    position: absolute;
    height: 20px; width: 20px;
    left: 3px; top: 3px;
    background: #fff;
    border-radius: 50%;
    transition: .2s;
    box-shadow: 0 2px 4px rgba(20,24,50,.15);
}
.av-toggle__input:checked + .av-toggle__slider { background: #ff8a54; }
.av-toggle__input:checked + .av-toggle__slider::before { transform: translateX(20px); }

/* ===== Slots row (chips) ===== */
.av-day__slots {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    padding-left: 60px;   /* aligns with the label indent */
    margin-top: 10px;
    min-height: 26px;
}
.av-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--av-primary-soft);
    color: var(--av-primary-dark);
    border: 1px solid var(--av-primary-tint);
    padding: 6px 6px 6px 12px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    line-height: 1;
    transition: all .15s;
}
.av-chip:hover { border-color: var(--av-primary); background: #ffe4d5; }
/* Muted chip = the day is currently OFF but the slot is preserved */
.av-chip--muted {
    background: #f2f4f8 !important;
    color: var(--av-muted) !important;
    border-color: #e6e8ef !important;
    opacity: .8;
}
.av-chip--muted:hover { background: #e9ebf1 !important; border-color: #d8dce6 !important; }
.av-chip--muted .av-chip__x { color: var(--av-muted) !important; }
.av-chip--muted .av-chip__x:hover { background: var(--av-muted-2) !important; color: #fff !important; }
.av-chip i { font-size: 13px; opacity: .8; }
.av-chip__text { font-variant-numeric: tabular-nums; }
.av-chip__x {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: transparent;
    border: none;
    color: var(--av-primary-dark);
    font-size: 16px;
    line-height: 1;
    cursor: pointer;
    padding: 0;
    margin-left: 2px;
    transition: all .15s;
}
.av-chip__x:hover { background: var(--av-primary); color: #fff; }

.av-empty {
    color: var(--av-muted);
    font-size: 12.5px;
    font-style: italic;
    padding: 4px 0;
}
.av-empty--muted { color: var(--av-muted-2); }

/* ===== Inline add-slot form (redesigned with time pickers) ===== */
.av-inline-add {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    padding: 14px 16px;
    background: #fffbf8;
    border: 1px dashed var(--av-primary-tint);
    border-radius: 12px;
    margin-top: 4px;
}

/* Quick preset row */
.av-preset-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}
.av-preset-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--av-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-right: 4px;
}
.av-preset {
    background: #fff;
    border: 1px solid var(--av-primary-tint);
    color: var(--av-primary-dark, #c7501e);
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    line-height: 1.2;
}
.av-preset:hover {
    background: var(--av-primary-soft);
    border-color: var(--av-primary);
    transform: translateY(-1px);
}
.av-preset:active { transform: translateY(0); }

/* Time picker row */
.av-time-row {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    flex-wrap: wrap;
}
.av-time-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1 1 auto;
    min-width: 140px;
}
.av-time-label {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--av-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin: 0;
}
.av-time-input {
    border: 1px solid var(--av-border) !important;
    background: #fff !important;
    color: var(--av-text) !important;
    padding: 9px 12px !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 600;
    height: auto !important;
    line-height: 1.3 !important;
    outline: none !important;
    font-variant-numeric: tabular-nums;
    letter-spacing: .3px;
    cursor: pointer;
    width: 100%;
}
.av-time-input:focus {
    border-color: var(--av-primary) !important;
    box-shadow: 0 0 0 3px rgba(255,107,44,.15) !important;
}
.av-time-input::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(48%) sepia(30%) saturate(2000%) hue-rotate(10deg) brightness(1.1);
    opacity: .8;
}
.av-time-sep {
    font-size: 20px;
    font-weight: 700;
    color: var(--av-muted-2);
    padding: 0 4px 10px;
    align-self: flex-end;
}

/* Options + action buttons row */
.av-actions-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    padding-top: 4px;
    border-top: 1px dashed var(--av-border);
    margin-top: 2px;
}
.av-actions-btns {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.av-inline-add__all {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--av-text);
    cursor: pointer;
    margin: 0;
    font-weight: 500;
}
.av-inline-add__all input[type=checkbox] {
    width: 16px; height: 16px;
    accent-color: var(--av-primary);
    margin: 0;
    cursor: pointer;
}

/* ===== Footnote ===== */
.av-footnote {
    margin-top: 18px;
    font-size: 12.5px;
    color: var(--av-muted);
    display: flex;
    gap: 8px;
    align-items: center;
    padding: 10px 14px;
    background: var(--av-surface);
    border: 1px dashed var(--av-border);
    border-radius: 10px;
}
.av-footnote i { color: var(--av-primary); font-size: 15px; }

/* ===== Copy modal ===== */
.av-modal {
    border: none !important;
    border-radius: 16px !important;
    box-shadow: 0 20px 60px rgba(20,24,50,.25) !important;
    overflow: hidden;
}
.av-modal .modal-header {
    background: #ff8a54;
    color: #fff;
    padding: 14px 20px;
    border: none;
}
.av-modal .modal-title { color: #fff !important; font-weight: 700 !important; }
.av-modal__close { color: #fff !important; opacity: 1 !important; text-shadow: none !important; background: transparent !important; border: none !important; }
.av-modal .modal-body { padding: 18px 20px; }
.av-modal .modal-footer { border-top: 1px solid var(--av-border); padding: 12px 20px; }
.av-copy-from { font-size: 14px; margin: 0 0 12px; color: var(--av-text); }
.av-copy-targets { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 14px; }
.av-copy-target {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border: 1px solid var(--av-border);
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    color: var(--av-text);
    margin: 0;
    transition: all .15s;
}
.av-copy-target:hover { border-color: var(--av-primary-tint); background: #fffbf8; }
.av-copy-target input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--av-primary); margin: 0; }
.av-copy-warn { font-size: 12px; color: var(--av-muted); background: var(--av-primary-soft); border-radius: 8px; padding: 8px 12px; margin: 0; }

/* ===== Responsive ===== */
@media (max-width: 767px) {
    .av-wrap { padding: 14px; }
    .av-day  { padding: 14px 16px; }
    .av-day__head { gap: 10px; }
    .av-day__actions { width: 100%; margin-top: 4px; }
    .av-day__actions .av-btn { flex: 1 1 auto; justify-content: center; }
    .av-day__slots { padding-left: 0; }
    .av-title { font-size: 20px; }
    .av-copy-targets { grid-template-columns: 1fr; }
    .av-inline-add .av-btn { flex: 1 1 auto; justify-content: center; }
    .av-time-row { flex-direction: column; align-items: stretch; }
    .av-time-sep { display: none; }
    .av-time-field { min-width: 0; }
    .av-actions-row { flex-direction: column; align-items: stretch; }
    .av-actions-btns { justify-content: stretch; }
    .av-preset { font-size: 12px; padding: 5px 10px; }
    .av-preset-label { flex: 1 1 100%; margin: 0 0 4px; }
}
</style>
