jQuery(document).ready(function ($) {
    $('#cancel-subscription-button').on('click', function () {
        if (!confirm('Вы уверены, что хотите отменить подписку?')) {
            return;
        }

        var userId = $(this).data('user-id');

        $.ajax({
            url: SubscriptionDashboard.ajax_url,
            method: 'POST',
            data: {
                action: 'cancel_subscription',
                user_id: userId,
            },
            success: function (response) {
                if (response.success) {
                    alert(response.data);
                    location.reload(); // Перезагружаем страницу
                } else {
                    alert(response.data);
                }
            },
            error: function () {
                alert('Произошла ошибка. Попробуйте позже.');
            },
        });
    });
});
