<script>
    $(document).ready(function () {
        "use strict";

        function allPerms() { return $('.checkbox-wrapper input.group-perm'); }

        function updateCount() {
            $('#selCount').text(allPerms().filter(':checked').length);
        }

        function syncGroupHead(gid) {
            var items = $('.group-perm.' + gid);
            $('.group-check-all[data-group="' + gid + '"]').prop('checked', items.length > 0 && items.length === items.filter(':checked').length);
        }

        // initialise (covers the edit page where some are pre-checked)
        $('.group-check-all').each(function () { syncGroupHead($(this).data('group')); });
        updateCount();

        // global select / clear all
        $(document).on('click', '.checked_all', function () {
            var boxes = allPerms();
            var makeChecked = boxes.filter(':checked').length < boxes.length;
            boxes.prop('checked', makeChecked);
            $('.group-check-all').prop('checked', makeChecked);
            updateCount();
        });

        // per-group select all
        $(document).on('change', '.group-check-all', function () {
            $('.group-perm.' + $(this).data('group')).prop('checked', $(this).is(':checked'));
            updateCount();
        });

        // single permission toggled
        $(document).on('change', '.group-perm', function () {
            var gid = ($(this).attr('class').match(/\bg\d+\b/) || [])[0];
            if (gid) syncGroupHead(gid);
            updateCount();
        });

        // live search / filter
        $(document).on('keyup', '#permSearch', function () {
            var q = $(this).val().toLowerCase().trim();
            $('.perm-group').each(function () {
                var group = $(this), visible = 0;
                group.find('.perm-item').each(function () {
                    var match = !q || $(this).data('name').indexOf(q) !== -1;
                    $(this).closest('.perm-col').toggle(match);
                    if (match) visible++;
                });
                group.toggle(visible > 0);
            });
        });
    });
</script>
