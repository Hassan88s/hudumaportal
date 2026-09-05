@if(!empty(get_static_option('site_gdpr_cookie_enabled')))
    <script src="{{ asset('assets/frontend/js/jquery.ihavecookies.min.js') }}"></script>
    @php
        $gdpr_cookie_link = str_replace('{url}', url('/'), get_static_option('site_gdpr_cookie_more_info_link'));
    @endphp

    <script>
        $(document).ready(function () {
            var delayTime = "{{ get_static_option('site_gdpr_cookie_delay') }}";
            delayTime = delayTime ? delayTime : 4000;

            @php
                $all_title_fields = get_static_option('site_gdpr_cookie_manage_item_title');
                $all_title_fields = !empty($all_title_fields) ? unserialize($all_title_fields, ['class' => false]) : [''];

                $all_description_fields = get_static_option('site_gdpr_cookie_manage_item_description');
                $all_description_fields = !empty($all_description_fields) ? unserialize($all_description_fields, ['class' => false]) : [];

                $cookie_mange_data = [];
            @endphp

            @foreach($all_title_fields as $index => $title)
                @php
                    $cookie_mange_data[] = [
                        'type' => $title,
                        'value' => $title,
                        'description' => $all_description_fields[$index] ?? '',
                    ];
                @endphp
            @endforeach

            $('body').ihavecookies({
    title: "{{ __(get_static_option('site_gdpr_cookie_title')) }}",
    message: `{{ __(get_static_option('site_gdpr_cookie_message')) }}`,
    expires: "{{ get_static_option('site_gdpr_cookie_expire') }}",
    link: "{{ $gdpr_cookie_link }}",
    delay: delayTime,
    moreInfoLabel: "{{ __(get_static_option('site_gdpr_cookie_more_info_label')) }}",
    acceptBtnLabel: "{{ __(get_static_option('site_gdpr_cookie_accept_button_label')) }}",
    advancedBtnLabel: "{{ __(get_static_option('site_gdpr_cookie_decline_button_label')) }}",
    cookieTypes: {!! json_encode(array_map(function($item){
        $item['type'] = __($item['type']);
        $item['value'] = __($item['value']);
        $item['description'] = __($item['description']);
        return $item;
    }, $cookie_mange_data)) !!},
    moreBtnLabel: "{{ __(get_static_option('site_gdpr_cookie_manage_button_label', 'Manage')) }}",
    cookieTypesTitle: "{{ __(get_static_option('site_gdpr_cookie_manage_title', 'Manage Cookies')) }}",
});

            $('body').on('click', '#gdpr-cookie-close', function (e) {
                e.preventDefault();
                $(this).parent().remove();
            });
        });
    </script>
@endif