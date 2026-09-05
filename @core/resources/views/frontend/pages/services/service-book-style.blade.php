<style>
    /* ========== Modern Booking Page Redesign ========== */
    :root{
        --bk-bg:#f5f7fb;
        --bk-card:#ffffff;
        --bk-border:#e8eaf2;
        --bk-text:#1e293b;
        --bk-muted:#6b7280;
        --bk-soft:#94a3b8;
        --bk-primary:#ff6b2c;
        --bk-primary-dark:#e55621;
        --bk-primary-light:#ffe9dd;
        --bk-success:#10b981;
        --bk-success-light:#d1fae5;
        --bk-warning:#f59e0b;
        --bk-danger:#ef4444;
        --bk-info:#0ea5e9;
    }

    /* Page background */
    .new_service_details_area{background:var(--bk-bg) !important;padding-top:48px !important;padding-bottom:60px !important}

    /* Service header card */
    .new_serviceDetails{background:#fff;border:1px solid var(--bk-border);border-radius:14px !important;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04)}
    .new_serviceDetails__flex{align-items:center}
    .new_serviceDetails__author__flex{display:flex;align-items:center;gap:16px}
    .new_serviceDetails__author__thumb{width:80px;height:80px;border-radius:14px;overflow:hidden;flex-shrink:0;background:var(--bk-primary-light);display:flex;align-items:center;justify-content:center}
    .new_serviceDetails__author__thumb img{width:100%;height:100%;object-fit:cover}
    .new_serviceDetails__author__title{font-size:20px;font-weight:700;color:var(--bk-text);margin:0 0 6px}
    .new_serviceDetails__author__title a{color:var(--bk-text);text-decoration:none}
    .new_serviceDetails__author__title a:hover{color:var(--bk-primary)}
    .new_serviceDetails__author__para{font-size:13px;color:var(--bk-muted);margin:0}
    .new_serviceDetails__author__para a{color:var(--bk-primary);text-decoration:none;font-weight:600}
    .new_serviceDetails__author__para a:hover{text-decoration:underline}

    /* Step indicator list (left sidebar steps) */
    .new_stepForm_list{margin-top:24px !important;background:#fff;border:1px solid var(--bk-border);border-radius:14px;padding:8px;list-style:none}
    .new_stepForm_list__item{border-radius:10px;margin-bottom:4px;transition:all .15s;border:1px solid transparent;background:#fff}
    .new_stepForm_list__item:last-child{margin-bottom:0}
    .new_stepForm_list__item.active{background:linear-gradient(90deg,var(--bk-primary-light) 0%,#fff8f3 100%);border-color:var(--bk-primary-light)}
    .new_stepForm_list__item__flex{display:flex;justify-content:space-between;align-items:center;padding:12px 14px}
    .new_stepForm_list__item__click{display:flex;align-items:center;gap:14px;text-decoration:none;flex:1;min-width:0}
    .new_stepForm_list__item__click__icon{width:42px;height:42px;border-radius:12px;background:#f3f4f6;color:var(--bk-muted);display:inline-flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;transition:all .15s;border:1px solid var(--bk-border)}
    .new_stepForm_list__item.active .new_stepForm_list__item__click__icon{background:var(--bk-primary);color:#fff;border-color:var(--bk-primary);box-shadow:0 2px 8px rgba(255,107,44,0.25)}
    .new_stepForm_list__item__click__contents{flex:1;min-width:0}
    .new_stepForm_list__item__click__title{font-size:15px;font-weight:700;color:var(--bk-text);margin:0;line-height:1.3}
    .new_stepForm_list__item.active .new_stepForm_list__item__click__title{color:var(--bk-primary)}
    .new_stepForm_list__item__click__para{font-size:12px;color:var(--bk-muted);margin-top:3px;display:block;line-height:1.4}
    .new_stepForm_list__item__click__para strong{color:var(--bk-text);font-weight:600}
    .new_stepForm_list__item__click__para .details{margin-right:6px}
    .new_stepForm_list__item__btn{flex-shrink:0;margin-left:8px}
    .new_stepForm_list__item__btn__edit{display:inline-block;padding:6px 14px;background:#fff;color:var(--bk-muted);border:1px solid var(--bk-border);border-radius:8px !important;font-size:12px;font-weight:600;text-decoration:none;transition:all .15s}
    .new_stepForm_list__item__btn__edit:hover{background:var(--bk-primary);color:#fff;border-color:var(--bk-primary)}
    .new_stepForm_list__item.active .new_stepForm_list__item__btn__edit{background:#fff;color:var(--bk-primary);border-color:var(--bk-primary)}

    /* Fieldsets — booking step content cards */
    fieldset.confirm-location,
    fieldset.edit_style_service_info,
    fieldset.confirm-information,
    fieldset.confirm-date-time,
    fieldset.edit_style_payment_option{background:#fff;border:1px solid var(--bk-border);border-radius:14px;padding:28px !important;margin-top:24px !important;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding-top:28px !important}

    /* Form inputs */
    .form--control,
    .custom-form .form--control,
    .single-input input.form--control,
    input.form--control,
    textarea.form--control{background:#fff !important;border:1px solid var(--bk-border) !important;border-radius:10px !important;padding:11px 14px !important;font-size:14px !important;color:var(--bk-text) !important;width:100% !important;outline:none !important;transition:all .15s !important;box-shadow:none !important}
    .form--control:focus,
    input.form--control:focus,
    textarea.form--control:focus{border-color:var(--bk-primary) !important;box-shadow:0 0 0 3px rgba(255,107,44,0.12) !important}
    .form--control::placeholder{color:#a0aec0 !important}
    textarea.form--control{min-height:96px !important;resize:vertical !important;line-height:1.5}

    /* Labels */
    .label-title{font-size:13px;font-weight:600;color:var(--bk-text);margin-bottom:6px;display:inline-block}
    .label-title .text-danger{color:var(--bk-danger)}
    .single-input{margin-bottom:0}

    /* Select2 styled dropdowns */
    .select2-container--default .select2-selection--single{height:44px !important;border:1px solid var(--bk-border) !important;border-radius:10px !important;padding:5px 12px !important;background:#fff !important}
    .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:32px !important;color:var(--bk-text) !important;padding-left:0 !important}
    .select2-container--default .select2-selection--single .select2-selection__arrow{height:42px !important;right:8px !important}
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single{border-color:var(--bk-primary) !important;box-shadow:0 0 0 3px rgba(255,107,44,0.12) !important}
    .select2-dropdown{border:1px solid var(--bk-border) !important;border-radius:10px !important;box-shadow:0 8px 24px rgba(0,0,0,0.08) !important;overflow:hidden}
    .select2-results__option--highlighted{background:var(--bk-primary) !important;color:#fff !important}
    .single-input-select select{width:100%;background:#fff;border:1px solid var(--bk-border);border-radius:10px;padding:11px 14px;font-size:14px;color:var(--bk-text);outline:none}
    .single-input-select select:focus{border-color:var(--bk-primary);box-shadow:0 0 0 3px rgba(255,107,44,0.12)}

    /* Section titles inside fieldsets */
    .new_packageBook__details__title{font-size:18px;font-weight:700;color:var(--bk-text);margin:0 0 12px}
    .new_packageBook__header{margin-bottom:8px}
    .new_packageBook__details__item{margin-bottom:24px}
    .new_packageBook__details__item:last-child{margin-bottom:0}

    /* Package items (Service includes & additionals) */
    .new_packageBook__addFeature{background:#fafbfd;border:1px solid var(--bk-border);border-radius:12px !important;padding:18px 18px 16px;transition:all .15s;height:100%}
    .new_packageBook__addFeature:hover{border-color:var(--bk-primary-light);background:#fffaf5}
    .new_packageBook__addFeature__flex{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap}
    .new_packageBook__addFeature__contents{flex:1;min-width:0}
    .new_packageBook__addFeature__title{font-size:15px;font-weight:600;color:var(--bk-text);margin:0;line-height:1.4}
    .new_packageBook__addFeature__price{font-size:16px;font-weight:800;color:var(--bk-primary);margin:6px 0 0;line-height:1.2}
    .new_packageBook__list{margin:0 !important;padding:0;list-style:none}
    .new_packageBook__list li{margin:0;padding:0}

    /* Checkbox inline (extras) */
    .checkbox-inlines{display:flex;align-items:center;gap:10px}
    .checkbox-inlines .check-input,
    input.check-input[type="checkbox"]{width:18px;height:18px;border:2px solid var(--bk-soft);border-radius:5px;cursor:pointer;-webkit-appearance:none;appearance:none;background:#fff;transition:all .15s;flex-shrink:0;position:relative;margin:0}
    input.check-input[type="checkbox"]:checked{background:var(--bk-primary);border-color:var(--bk-primary)}
    input.check-input[type="checkbox"]:checked::after{content:"✓";position:absolute;color:#fff;font-size:13px;font-weight:700;top:50%;left:50%;transform:translate(-50%,-50%);line-height:1}

    /* Quantity selector (+ / -) */
    .package_quantity{display:inline-flex;align-items:center;gap:0;border:1px solid var(--bk-border);border-radius:10px;background:#fff;overflow:hidden}
    .package_quantity__icon{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;color:var(--bk-muted);font-size:12px;transition:all .15s;user-select:none;background:#fff;border:none}
    .package_quantity__icon:hover{background:var(--bk-primary-light);color:var(--bk-primary)}
    .package_quantity__input{width:42px !important;height:34px !important;text-align:center !important;border:none !important;border-left:1px solid var(--bk-border) !important;border-right:1px solid var(--bk-border) !important;background:#fff !important;color:var(--bk-text) !important;font-weight:700 !important;font-size:14px !important;outline:none !important;padding:0 !important;border-radius:0 !important;-moz-appearance:textfield}
    .package_quantity__input::-webkit-outer-spin-button,
    .package_quantity__input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}

    /* Remove service button */
    .remove-service-list{background:transparent;padding:0 !important;border:none !important;display:inline-flex !important}
    .remove-service-list a.remove{color:var(--bk-danger) !important;font-size:12px;font-weight:600;text-decoration:none}
    .remove-service-list a.remove:hover{text-decoration:underline}

    /* Date picker section */
    .date-overview{margin:0 !important;align-items:flex-start}
    .date-time-title{font-size:15px;font-weight:700;color:var(--bk-text);margin-bottom:14px;display:block}
    .overview-location{background:#fafbfd;border:1px solid var(--bk-border);border-radius:12px;padding:14px;display:inline-block;width:100%;max-width:360px}
    .show-date{margin:0 !important;padding:0;list-style:none;display:block}
    .show-date li{list-style:none}

    /* FlatPickr calendar styling */
    .flatpickr-calendar.inline{box-shadow:none !important;background:transparent !important;width:100% !important;max-width:340px;margin:0 auto;border:none !important}
    .flatpickr-calendar.inline .flatpickr-rContainer,
    .flatpickr-calendar.inline .flatpickr-days{width:100% !important}
    .flatpickr-calendar.inline .dayContainer{width:100% !important;min-width:100% !important;max-width:100% !important;display:grid !important;grid-template-columns:repeat(7,1fr);gap:4px;justify-content:space-between;padding:6px 0}
    .flatpickr-calendar .flatpickr-months{background:var(--bk-primary) !important;border-radius:10px 10px 0 0;padding:8px 12px;align-items:center;display:flex !important;justify-content:space-between !important;position:relative;overflow:hidden}
    .flatpickr-calendar .flatpickr-month{color:#fff !important;height:auto !important;line-height:1.2 !important;flex:1;min-width:0;text-align:center;background:transparent !important;overflow:visible !important}
    .flatpickr-calendar .flatpickr-current-month{font-size:15px !important;font-weight:700 !important;padding:0 !important;height:auto !important;position:static !important;width:auto !important;left:auto !important;display:inline-flex !important;align-items:center;justify-content:center;gap:8px;color:#fff !important;line-height:1.2 !important;text-align:center}
    .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-months{color:#fff !important;background:transparent !important;border:none !important;font-weight:700 !important;font-size:15px !important;padding:2px 4px !important;width:auto !important;min-width:80px;text-align:center;text-align-last:center;cursor:pointer;-webkit-appearance:none;appearance:none}
    .flatpickr-calendar .flatpickr-current-month input.cur-year{color:#fff !important;background:transparent !important;border:none !important;font-weight:700 !important;font-size:15px !important;padding:2px 4px !important;width:60px !important;text-align:center;cursor:default}
    .flatpickr-calendar .flatpickr-current-month .numInputWrapper{width:auto !important;background:transparent;display:inline-flex !important;align-items:center}
    .flatpickr-calendar .flatpickr-current-month .numInputWrapper span{border:none !important;background:transparent !important}
    .flatpickr-calendar .flatpickr-current-month .numInputWrapper span.arrowUp:after{border-bottom-color:#fff !important}
    .flatpickr-calendar .flatpickr-current-month .numInputWrapper span.arrowDown:after{border-top-color:#fff !important}
    .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-months option{color:var(--bk-text) !important;background:#fff !important;padding:4px 8px}
    .flatpickr-calendar .flatpickr-prev-month,
    .flatpickr-calendar .flatpickr-next-month{color:#fff !important;fill:#fff !important;padding:4px !important;border-radius:6px;transition:all .15s;position:relative !important;top:auto !important;height:auto !important;width:auto !important;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center}
    .flatpickr-calendar .flatpickr-prev-month svg,
    .flatpickr-calendar .flatpickr-next-month svg{fill:#fff !important;width:14px;height:14px}
    .flatpickr-calendar .flatpickr-prev-month:hover,
    .flatpickr-calendar .flatpickr-next-month:hover{background:rgba(255,255,255,0.2) !important;color:#fff !important}
    .flatpickr-calendar .flatpickr-prev-month:hover svg,
    .flatpickr-calendar .flatpickr-next-month:hover svg{fill:#fff !important}
    .flatpickr-calendar .flatpickr-weekdays{background:transparent !important;margin-top:12px}
    .flatpickr-calendar .flatpickr-weekdaycontainer{display:grid !important;grid-template-columns:repeat(7,1fr);gap:4px;width:100% !important;justify-content:space-between}
    .flatpickr-calendar .flatpickr-weekday{font-size:11px !important;font-weight:700 !important;color:var(--bk-muted) !important;text-transform:uppercase;background:transparent !important;line-height:1.5;text-align:center;flex:none !important;width:auto !important;max-width:none !important}
    .flatpickr-calendar .flatpickr-day{border-radius:50% !important;width:36px !important;height:36px !important;line-height:36px !important;font-size:13px;font-weight:500;color:var(--bk-text);border:none !important;background:transparent !important;margin:2px auto !important;transition:all .15s;max-width:36px !important;min-width:0 !important}
    .flatpickr-calendar .flatpickr-day:hover:not(.disabled):not(.selected){background:var(--bk-primary-light) !important;color:var(--bk-primary) !important}
    .flatpickr-calendar .flatpickr-day.today{border:1.5px solid var(--bk-primary) !important;color:var(--bk-primary) !important;font-weight:700}
    .flatpickr-calendar .flatpickr-day.today:hover{background:var(--bk-primary) !important;color:#fff !important}
    .flatpickr-calendar .flatpickr-day.selected,
    .flatpickr-calendar .flatpickr-day.selected:hover{background:var(--bk-primary) !important;color:#fff !important;border-color:var(--bk-primary) !important;font-weight:700;box-shadow:0 2px 8px rgba(255,107,44,0.3)}
    .flatpickr-calendar .flatpickr-day.disabled,
    .flatpickr-calendar .flatpickr-day.prevMonthDay,
    .flatpickr-calendar .flatpickr-day.nextMonthDay{color:#cbd5e0 !important;cursor:not-allowed;font-weight:400}
    .flatpickr-calendar .flatpickr-day.disabled:hover{background:transparent !important;color:#cbd5e0 !important}

    /* Schedule options column */
    .schedule_radioInput{margin-top:0 !important}
    .show-schedule{display:flex;flex-wrap:wrap;gap:8px;margin-top:6px}
    .show-schedule .get-schedule,
    .show-schedule label.custom_radio__single,
    .schedule_radioInput label.custom_radio__single{padding:10px 16px !important;background:#fafbfd !important;border:1.5px solid var(--bk-border) !important;border-radius:10px !important;cursor:pointer;font-size:13px !important;font-weight:600 !important;color:var(--bk-text) !important;transition:all .15s;display:inline-flex !important;align-items:center;gap:8px;margin:0 !important}
    .show-schedule .get-schedule:hover,
    .show-schedule label.custom_radio__single:hover{background:var(--bk-primary-light) !important;border-color:var(--bk-primary) !important;color:var(--bk-primary) !important}
    .show-schedule .get-schedule input[type="radio"],
    .show-schedule input[type="radio"]{width:16px;height:16px;accent-color:var(--bk-primary);margin:0;cursor:pointer;flex-shrink:0}
    .show-schedule .get-schedule:has(input:checked),
    .show-schedule label.custom_radio__single:has(input:checked),
    .schedule_radioInput label:has(input:checked){background:var(--bk-primary) !important;color:#fff !important;border-color:var(--bk-primary) !important;box-shadow:0 2px 8px rgba(255,107,44,0.25)}
    .show-schedule .get-schedule:has(input:checked) label,
    .show-schedule .get-schedule:has(input:checked) input[type="radio"]+label{color:#fff !important}
    .schedule_loader{margin-top:10px;color:var(--bk-muted);font-size:13px}
    .schedule_loader:empty{display:none}
    .show-schedule .alert{margin-top:0 !important}

    /* Step navigation buttons */
    .stepForm_btn,
    .stepForm_btn__previous,
    input.stepForm_btn,
    input.stepForm_btn__previous,
    input.next.stepForm_btn,
    input.stepPrevious.stepForm_btn__previous,
    input[type="submit"].stepForm_btn{padding:11px 30px !important;border-radius:10px !important;font-weight:700 !important;font-size:14px !important;border:none !important;cursor:pointer !important;transition:all .15s !important;display:inline-block !important}
    .stepForm_btn,
    input.next.stepForm_btn,
    input[type="submit"].stepForm_btn{background:var(--bk-primary) !important;color:#fff !important}
    .stepForm_btn:hover,
    input.next.stepForm_btn:hover,
    input[type="submit"].stepForm_btn:hover{background:var(--bk-primary-dark) !important;transform:translateY(-1px);box-shadow:0 4px 12px rgba(255,107,44,0.25)}
    .stepForm_btn__previous,
    input.stepPrevious.stepForm_btn__previous{background:#fff !important;color:var(--bk-text) !important;border:1.5px solid var(--bk-border) !important}
    .stepForm_btn__previous:hover,
    input.stepPrevious.stepForm_btn__previous:hover{background:#fafbfd !important;border-color:var(--bk-soft) !important}
    .action-button{padding:11px 30px;border-radius:10px;background:var(--bk-primary);color:#fff !important;font-weight:700;font-size:14px;text-decoration:none;display:inline-block;transition:all .15s}
    .action-button:hover{background:var(--bk-primary-dark);color:#fff !important}

    /* Sidebar — Booking Summary */
    .new_serviceDetails__side{position:sticky;top:20px}
    .new_serviceDetails__side__item{background:#fff;border:1px solid var(--bk-border);border-radius:14px;padding:22px;box-shadow:0 1px 3px rgba(0,0,0,0.04)}
    .new_serviceBooking__summary__title{font-size:18px;font-weight:700;color:var(--bk-text);margin:0;padding-bottom:14px;border-bottom:2px solid var(--bk-border)}
    .new_serviceBooking__summary__contents__inner{padding-top:14px}
    .new_serviceBooking__summary__sub_title{font-size:14px;font-weight:700;color:var(--bk-text);margin:0;padding-top:14px;line-height:1.4}
    .border_top{border-top:1px solid var(--bk-border) !important;padding-top:14px;margin-top:14px}
    .new_serviceBooking__summary__list{margin:0;padding:0;list-style:none}
    .new_serviceBooking__summary__list__item{display:flex;justify-content:space-between;align-items:center;gap:10px;font-size:14px;color:var(--bk-text);padding:3px 0;line-height:1.4}
    .new_serviceBooking__summary__list__item .item__title{color:var(--bk-muted);font-weight:500}
    .new_serviceBooking__summary__list__item .item__title strong{color:var(--bk-text);font-weight:700}
    .new_serviceBooking__summary__list__item .value_count,
    .new_serviceBooking__summary__list__item .value-count{color:var(--bk-text);font-weight:700;text-align:right}

    /* Summary list (extras / includes) */
    .summery_list{margin:0;padding:0;list-style:none}
    .summery_list .list{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:6px 0;font-size:13px;color:var(--bk-text);flex-wrap:wrap}
    .summery_list .list .item__title{flex:1;color:var(--bk-text);font-weight:500;min-width:0}
    .summery_list .list .item_count{color:var(--bk-primary);font-weight:700;background:var(--bk-primary-light);padding:2px 10px;border-radius:10px;font-size:12px;display:inline-flex;align-items:center;gap:4px}
    .summery_list .list .value_count{color:var(--bk-text);font-weight:700;font-size:13px}
    .onlilne-special-list{margin:0;padding:0;list-style:none}
    .onlilne-special-list li{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--bk-muted);padding:4px 0}
    .onlilne-special-list li i{color:var(--bk-primary)}

    /* Total emphasised */
    .total-amount,
    .total_amount_for_coupon{font-size:18px !important;color:var(--bk-primary) !important;font-weight:800 !important}

    /* Coupon input */
    .coupon_input_field{margin-top:16px;padding-top:16px}
    .coupon_input_field .result-list{display:flex;gap:0}
    .coupon_input_field input.coupon_code,
    input.coupon_code{flex:1 !important;background:#fafbfd !important;border:1px solid var(--bk-border) !important;border-right:none !important;border-radius:10px 0 0 10px !important;padding:10px 12px !important;font-size:13px !important;color:var(--bk-text) !important;outline:none !important;height:auto !important}
    .coupon_input_field input.coupon_code:focus,
    input.coupon_code:focus{border-color:var(--bk-primary) !important;background:#fff !important}
    .coupon_input_field button.apply-coupon,
    button.apply-coupon{padding:10px 18px !important;background:var(--bk-primary) !important;border:1px solid var(--bk-primary) !important;color:#fff !important;font-weight:700 !important;font-size:13px !important;border-radius:0 10px 10px 0 !important;cursor:pointer;transition:all .15s !important;white-space:nowrap}
    .coupon_input_field button.apply-coupon:hover,
    button.apply-coupon:hover{background:var(--bk-primary-dark) !important;border-color:var(--bk-primary-dark) !important}

    /* Coupon discount line */
    .coupon_amount_for_apply_code{font-size:13px !important;color:var(--bk-success) !important;font-weight:600}
    .coupon_amount_for_apply_code:not(:empty){background:var(--bk-success-light);padding:8px 12px;border-radius:8px;margin-top:4px}

    /* Terms and conditions */
    .terms-and-conditions{display:flex;align-items:center;gap:8px;background:#fafbfd;border:1px solid var(--bk-border);border-radius:10px;padding:12px 16px;margin-top:14px}
    .terms-and-conditions input.check-input{margin:0}
    .terms-and-conditions .checkbox-label{margin:0 !important;color:var(--bk-text);font-size:13px;font-weight:500;cursor:pointer}
    .terms-and-conditions .checkbox-label a{color:var(--bk-primary);text-decoration:none;font-weight:600}
    .terms-and-conditions .checkbox-label a:hover{text-decoration:underline}
    .bottom-checkbox{justify-content:flex-end}

    /* Payment gateway radios */
    .paymentGateway_add{display:flex;flex-wrap:wrap;gap:12px}
    .paymentGateway_add__item{background:#fff !important;border:2px solid var(--bk-border);border-radius:12px;padding:12px;transition:all .15s;cursor:pointer}
    .paymentGateway_add__item:hover{border-color:var(--bk-primary);box-shadow:0 4px 12px rgba(255,107,44,0.12)}
    .paymentGateway_add__item.active,
    .paymentGateway_add__item:has(input:checked){border-color:var(--bk-primary);background:var(--bk-primary-light) !important}
    .paymentGateway_add__item img{max-width:100%;height:auto}

    /* Wallet payment block */
    .wallet-payment-gateway-wrapper{background:#fafbfd;border:1px solid var(--bk-border);border-radius:12px;padding:14px 16px;margin-bottom:14px;display:flex;align-items:center;gap:12px}
    .wallet-payment-gateway-wrapper label{font-weight:600 !important;color:var(--bk-text);padding:0 !important;margin:0}
    .wallet-payment-gateway-wrapper input{transform:scale(1.2);accent-color:var(--bk-primary)}

    /* FAQ section */
    .faq-area{margin-top:24px}
    .faq-item{background:#fafbfd;border:1px solid var(--bk-border);border-radius:10px;padding:14px 16px;margin-bottom:10px}
    .faq-title{font-size:14px;font-weight:700;color:var(--bk-text);margin-bottom:6px}
    .faq-para{font-size:13px;color:var(--bk-muted);margin:0;line-height:1.5}

    /* Overview / benefit list */
    .overview-single{padding-top:24px !important}
    .overview-single .title{font-size:16px;font-weight:700;color:var(--bk-text);margin-bottom:12px}
    .list_show{padding:8px 12px;background:#fafbfd;border:1px solid var(--bk-border);border-radius:8px;margin-bottom:6px;font-size:13px;color:var(--bk-text);list-style:none}
    .new_packageBook__list .list_show{background:transparent;border:none;padding:0;margin-bottom:4px;font-weight:600;color:var(--bk-text)}

    /* Service includes display row inside step 2 */
    .service_include_edit_show_hide{margin-top:8px !important}

    /* Page meta / breadcrumb area */
    .breadcrumb-area{background:#fff !important;padding:20px 0 !important;border-bottom:1px solid var(--bk-border)}

    /* Mobile responsive */
    @media(max-width:991px){
        .new_serviceDetails__side{position:static;margin-top:24px}
        fieldset.confirm-location,
        fieldset.edit_style_service_info,
        fieldset.confirm-information,
        fieldset.confirm-date-time,
        fieldset.edit_style_payment_option{padding:18px !important}
        .new_serviceDetails{padding:14px}
        .new_serviceDetails__author__title{font-size:17px}
        .new_packageBook__addFeature__flex{flex-direction:column;align-items:flex-start;gap:10px}
        .package_quantity{align-self:flex-start}
        .show-date,.show-schedule{gap:6px}
        .show-date li,.show-schedule .get-schedule{padding:8px 12px;font-size:12px}
        .stepForm_btn,.stepForm_btn__previous{padding:10px 22px;font-size:13px}
    }

    @media(max-width:575px){
        .new_serviceDetails__author__flex{gap:10px}
        .new_serviceDetails__author__thumb{width:60px;height:60px}
        .new_stepForm_list__item__click__icon{width:36px;height:36px;font-size:15px}
        .new_stepForm_list__item__click__title{font-size:14px}
        .new_stepForm_list__item__btn{display:none}
        .new_stepForm_list__item.active .new_stepForm_list__item__btn{display:block}
        .coupon_input_field .result-list{flex-direction:column;gap:8px}
        .coupon_input_field input.coupon_code{border-radius:10px !important;border-right:1px solid var(--bk-border) !important}
        .coupon_input_field button.apply-coupon{border-radius:10px !important;width:100%}
    }

    /* ============ Mobile-only inline coupon (rendered near Pay button) ============ */
    .mobile-coupon-inline{
        background:#fff8f3;
        border:1.5px solid #ffe9dd;
        border-radius:14px;
        padding:14px 16px;
    }
    .mobile-coupon-inline__label{
        display:block;
        font-size:13px;
        font-weight:700;
        color:var(--bk-text, #1f2433);
        margin-bottom:8px;
        letter-spacing:.2px;
    }
    .mobile-coupon-inline__row{
        display:flex;
        gap:8px;
        align-items:stretch;
    }
    /* Highest-specificity to defeat any theme/site-wide input hider */
    .mobile-coupon-inline .mobile-coupon-inline__row input.mobile-coupon-input,
    .mobile-coupon-inline .mobile-coupon-inline__row input.coupon_code{
        display:block !important;
        visibility:visible !important;
        opacity:1 !important;
        position:static !important;
        flex:1 1 auto !important;
        min-width:0 !important;
        width:100% !important;
        max-width:none !important;
        height:44px !important;
        background:#fff !important;
        border:1px solid #ececf3 !important;
        border-radius:10px !important;
        padding:10px 14px !important;
        font-size:13.5px !important;
        color:#1f2433 !important;
        outline:none !important;
        margin:0 !important;
        line-height:1.4 !important;
        box-shadow:none !important;
        pointer-events:auto !important;
    }
    .mobile-coupon-inline__row input.coupon_code:focus{
        border-color:#ff6b2c !important;
        box-shadow:0 0 0 3px rgba(255,107,44,.15) !important;
    }
    .mobile-coupon-inline__row button.apply-coupon{
        flex:0 0 auto;
        padding:10px 22px !important;
        background:linear-gradient(135deg,#ff6b2c,#e55621) !important;
        border:none !important;
        color:#fff !important;
        font-weight:700 !important;
        font-size:13.5px !important;
        border-radius:10px !important;
        cursor:pointer;
        white-space:nowrap;
        box-shadow:0 4px 12px rgba(255,107,44,.28);
    }
    .mobile-coupon-inline__row button.apply-coupon:hover{
        transform:translateY(-1px);
        box-shadow:0 6px 16px rgba(255,107,44,.36);
    }

    /* On very narrow screens, stack input above the button so both get full width */
    @media(max-width:600px){
        .mobile-coupon-inline .mobile-coupon-inline__row{
            flex-direction:column !important;
            gap:8px !important;
        }
        .mobile-coupon-inline .mobile-coupon-inline__row input.mobile-coupon-input,
        .mobile-coupon-inline .mobile-coupon-inline__row input.coupon_code{
            width:100% !important;
            display:block !important;
        }
        .mobile-coupon-inline .mobile-coupon-inline__row button.apply-coupon{
            width:100% !important;
            display:block !important;
        }
    }

    /* ============ MOBILE: hide the sidebar coupon (replaced by inline mobile coupon) ============ */
    @media(max-width:991px){
        /* Sidebar summary remains visible, but its coupon input is redundant on mobile
           because we render the same coupon inline in the form via .mobile-coupon-inline */
        .new_serviceDetails__side .coupon_input_field{
            display: none !important;
        }
    }
</style>
