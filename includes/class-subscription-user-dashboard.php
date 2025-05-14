<?php
if (!defined('ABSPATH')) {
    exit; // Запрет прямого доступа
}

class Subscription_User_Dashboard {
    public function __construct() {
        // Добавляем информацию о подписке в админке на странице профиля пользователя
        add_action('show_user_profile', [$this, 'display_subscription_status']);
        add_action('edit_user_profile', [$this, 'display_subscription_status']);

        // Добавляем колонку "Уровень подписки" в таблице пользователей
        add_filter('manage_users_columns', [$this, 'add_subscription_column']);
        add_action('manage_users_custom_column', [$this, 'display_subscription_column'], 10, 3);

        // Обработка AJAX для завершения подписки
        add_action('wp_ajax_cancel_subscription', [$this, 'cancel_subscription']);
    }

    /**
     * Отображение информации о подписке на странице редактирования пользователя
     */
    public function display_subscription_status($user) {
        // Получаем активные подписки пользователя
        $active_subscriptions = get_user_meta($user->ID, 'active_subscriptions', true);

        echo '<h2>' . __('Статус подписки', 'subscription-price') . '</h2>';

        if (empty($active_subscriptions)) {
            // Если подписок нет, выводим сообщение
            echo '<p>' . __('Подписка не активна.', 'subscription-price') . '</p>';
        } else {
            // Если подписки есть, выводим их список
            echo '<ul>';
            foreach ($active_subscriptions as $subscription) {
                echo '<li>';
                echo sprintf(
                    __('%s', 'subscription-price'),
                    esc_html($subscription['name'])
                );
                echo '</li>';

                // Выводим дату начала и окончания подписки
                if (!empty($subscription['start_date']) && !empty($subscription['end_date'])) {
                    echo '<p>';
                    echo sprintf(
                        __('Дата начала: %s', 'subscription-price'),
                        esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $subscription['start_date']))
                    );
                    echo '<br>';
                    echo sprintf(
                        __('Дата окончания: %s', 'subscription-price'),
                        esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $subscription['end_date']))
                    );
                    echo '</p>';
                }
            }
            echo '</ul>';

            // Кнопка для отмены подписки
            echo '<button id="cancel-subscription-button" class="button button-secondary" data-user-id="' . esc_attr($user->ID) . '">';
            echo __('Отменить подписку', 'subscription-price');
            echo '</button>';
        }

        // Встраиваем JavaScript код для обработки кнопки
        $this->embed_inline_script();
    }

    /**
     * Добавление новой колонки "Уровень подписки" в таблицу пользователей
     */
    public function add_subscription_column($columns) {
        $columns['subscription_status'] = __('Уровень подписки', 'subscription-price');
        return $columns;
    }

    /**
     * Вывод данных для колонки "Уровень подписки"
     */
    public function display_subscription_column($value, $column_name, $user_id) {
        if ('subscription_status' === $column_name) {
            // Получаем активные подписки пользователя
            $active_subscriptions = get_user_meta($user_id, 'active_subscriptions', true);

            if (!empty($active_subscriptions)) {
                // Берем только название товара из подписки
                $subscription_names = array_map(function ($subscription) {
                    $name = $subscription['name']; // Берем только 'name'
                    return mb_strlen($name) > 30 ? mb_substr($name, 0, 30) . '...' : $name; // Ограничиваем до 30 символов
                }, $active_subscriptions);

                // Возвращаем только первую подписку, если есть несколько
                return esc_html($subscription_names[0]);
            }

            // Если подписок нет, возвращаем пустую строку
            return '';
        }

        return $value;
    }

/**
 * Обработка завершения подписки через AJAX
 */
public function cancel_subscription() {
    // Проверяем права доступа
    if (!current_user_can('edit_users')) {
        wp_send_json_error(__('Нет прав для выполнения операции.', 'subscription-price'));
    }

    // Получаем ID пользователя
    $user_id = absint($_POST['user_id'] ?? 0);

    if (!$user_id) {
        wp_send_json_error(__('Некорректный ID пользователя.', 'subscription-price'));
    }

    // Получаем активную и истекшую роли из настроек
    $active_role = get_option('subscription_role_active', '');
    $expired_role = get_option('subscription_role_expired', '');

    if (empty($active_role) || empty($expired_role)) {
        wp_send_json_error(__('Роли для подписки не настроены.', 'subscription-price'));
    }

    // Обрабатываем пользователя
    $user = get_userdata($user_id);
    if ($user) {
        // Проверяем, есть ли активная роль у пользователя
        if (in_array($active_role, $user->roles, true)) {
            // Удаляем активную роль
            $user->remove_role($active_role);

            // Добавляем роль завершённой подписки
            if (!in_array($expired_role, $user->roles, true)) {
                $user->add_role($expired_role);
            }

            // Принудительное обновление объекта пользователя
            clean_user_cache($user_id);
            $user = get_userdata($user_id); // Повторно получаем объект пользователя
        }

        // Удаляем все активные подписки пользователя
        delete_user_meta($user_id, 'active_subscriptions');

        // Добавляем небольшую задержку
        usleep(100000); // Задержка в 100 мс
    } else {
        wp_send_json_error(__('Пользователь не найден.', 'subscription-price'));
    }

    // Завершаем AJAX-запрос успешно
    wp_send_json_success(__('Подписка успешно отменена.', 'subscription-price'));
}


   /**
 * Встраивание JavaScript кода для обработки кнопки
 */
private function embed_inline_script() {
    echo '<script>
        jQuery(document).ready(function ($) {
    $("#cancel-subscription-button").on("click", function () {
        if (!confirm("Вы уверены, что хотите отменить подписку?")) {
            return;
        }

        var button = $(this);
        var userId = button.data("user-id");

        // Отключаем кнопку сразу после нажатия
        button.prop("disabled", true);

        $.ajax({
            url: ajaxurl,
            method: "POST",
            data: {
                action: "cancel_subscription",
                user_id: userId,
            },
            success: function (response) {
                if (response.success) {
                    alert("Подписка успешно отменена.");
                    button.text("Подписка отменена, перезагрузите страницу");
                } else {
                    alert(response.data);
                    button.prop("disabled", false); // Включаем кнопку обратно при ошибке
                }
            },
            error: function () {
                alert("Произошла ошибка. Попробуйте позже.");
                button.prop("disabled", false); // Включаем кнопку обратно при ошибке
            },
        });
    });
});
    </script>';
}
}
