jQuery(document).ready(function ($) {
    $(document).on("click", "#subprice-cancel-subscription-button", function () {
        var button = $(this);
        var userId = button.data("user-id");
        var ajaxNonce = button.data("nonce");
        var i18n = window.subpriceUserDashboardI18n || {};

        // Проверка, что nonce не пустой
        if (!ajaxNonce) {
            alert('Ошибка: nonce не найден в data-атрибуте кнопки. Попробуйте обновить страницу или обратитесь к администратору.');
            button.prop("disabled", false).text(i18n.button_default || 'Отменить подписку');
            return;
        }

        if (!confirm(i18n.confirm_cancel || 'Вы уверены, что хотите отменить подписку?')) {
            return;
        }
        button.prop("disabled", true).text(i18n.canceling || 'Отмена...');

        $.ajax({
            url: typeof ajaxurl !== 'undefined' ? ajaxurl : (window.subpriceUserDashboardI18n ? window.subpriceUserDashboardI18n.ajaxurl : ''),
            method: "POST",
            data: {
                action: "subprice_cancel_subscription",
                user_id: userId,
                _wpnonce: ajaxNonce
            },
            success: function (response) {
                if (response.success) {
                    alert(i18n.success || 'Задача на отмену подписки добавлена в очередь. Подписка отменится в течении 1–2 минут.');
                    button.text(i18n.success || 'Отменено');
                } else {
                    alert(response.data || 'Ошибка безопасности (nonce verification failed).');
                    button.prop("disabled", false).text(i18n.button_default || 'Отменить подписку');
                }
            },
            error: function () {
                alert(i18n.error || 'Произошла ошибка. Попробуйте позже.');
                button.prop("disabled", false).text(i18n.button_default || 'Отменить подписку');
            }
        });
    });
});
