{{-- Copy-to-clipboard for a won discount code (buyer + seller). --}}
<script>
    (function ($) {
        "use strict";
        $(document).ready(function () {
            $(document).on('click', '.spin_reward__code__copy', function () {
                var btn  = $(this);
                var code = btn.data('code');
                var icon = btn.find('i');

                var done = function () {
                    icon.removeClass('far fa-copy').addClass('fas fa-check');
                    setTimeout(function () {
                        icon.removeClass('fas fa-check').addClass('far fa-copy');
                    }, 1500);
                };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(code).then(done);
                    return;
                }

                // Fallback for plain http (the dev server) where the async API is blocked.
                var field = $('<textarea>').val(code).css({position: 'fixed', top: '-1000px'}).appendTo('body');
                field.get(0).select();
                try { document.execCommand('copy'); done(); } catch (e) { /* nothing we can do */ }
                field.remove();
            });
        });
    })(jQuery);
</script>
