$(document).ready(function () {
    $('.tag_input_wrapper_input').on('input', inputWrapper);
    $('.global_blacklist_type').on('click', function () {
        $('.global_blacklist_type').siblings('input').prop('checked', false);
        $(this).siblings('input').prop('checked', true);
    });
    $('.global_comparison_type').on('click', function () {
        $('.global_comparison_type').siblings('input').prop('checked', false);
        $(this).siblings('input').prop('checked', true);
    });
    $(document).on('click', '.remove_global_blacklist_item', function () {
        $(this).parent().remove();
    });
});

function inputWrapper(e) {
    let blacklistValue = $(this).val();
    let blacklistItems = blacklistValue.split(';');
    let blacklistDivId = $(this).data('div-id');

    blacklistItems.forEach((item, index) => {
        let trimmedItem = item.trim();
        if (trimmedItem !== '' && index < blacklistItems.length - 1) {
            $('#' + blacklistDivId).append(
                `<div class="item"><span>`
                + trimmedItem +
                `</span><span class="remove_global_blacklist_item"><i class="fa-solid fa-xmark"></i></span>
                <input type="hidden" name="global_blacklist_item[]" value="`
                + trimmedItem +
                `"></div>`);
        } else {
            $(this).val(item);
        }
    });
}