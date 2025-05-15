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

        // Регистрация обработчика для Action Scheduler
        add_action('handle_cancel_subscription', [$this, 'handle_cancel_subscription']);
    }

    public function display_subscription_status($user) {
        $active_subscriptions = get_user_meta($user->ID, 'active_subscriptions', true);

        echo '<h2>' . __('Статус подписки', 'subscription-price') . '</h2>';

        if (empty($active_subscriptions)) {
            echo '<p>' . __('Подписка не активна.', 'subscription-price') . '</p>';
        } else {
            echo '<ul>';
            foreach ($active_subscriptions as $subscription) {
                echo '<li>' . esc_html($subscription['name']) . '</li>';
                if (!empty($subscription['start_date']) && !empty($subscription['end_date'])) {
                    echo '<p>' . sprintf(__('С: %s по %s', 'subscription-price'),
                        esc_html(date_i18n(get_option('date_format'), $subscription['start_date'])),
                        esc_html(date_i18n(get_option('date_format'), $subscription['end_date']))
                    ) . '</p>';
                }
            }
            echo '</ul>';
            echo '<button id="cancel-subscription-button" class="button button-secondary" data-user-id="' . esc_attr($user->ID) . '">' . __('Отменить подписку', 'subscription-price') . '</button>';
        }

        $this->embed_inline_script();
    }

    public function add_subscription_column($columns) {
        $columns['subscription_status'] = __('Уровень подписки', 'subscription-price');
        return $columns;
    }

    public function display_subscription_column($value, $column_name, $user_id) {
        if ('subscription_status' === $column_name) {
            $active_subscriptions = get_user_meta($user_id, 'active_subscriptions', true);
            if (!empty($active_subscriptions) && isset($active_subscriptions[0]['name'])) {
                $name = $active_subscriptions[0]['name'];
                return esc_html(mb_strlen($name) > 30 ? mb_substr($name, 0, 30) . '...' : $name);
            }
            return __('');
        }
        return $value;
    }

    public function cancel_subscription() {
        if (!current_user_can('edit_users')) {
            wp_send_json_error(__('Нет прав для выполнения операции.', 'subscription-price'));
        }
        $user_id = absint($_POST['user_id'] ?? 0);
        if (!$user_id) {
            wp_send_json_error(__('Некорректный ID пользователя.', 'subscription-price'));
        }
        as_schedule_single_action(time(), 'handle_cancel_subscription', ['user_id' => $user_id]);
        wp_send_json_success(__('Задача на отмену подписки добавлена в очередь.', 'subscription-price'));
    }

    public function handle_cancel_subscription($user_id) {
        $active_role = get_option('subscription_role_active', '');
        $expired_role = get_option('subscription_role_expired', '');
        if (empty($active_role) || empty($expired_role)) {
            return;
        }
        $user = get_userdata($user_id);
        if ($user) {
            if (in_array($active_role, $user->roles, true)) {
                $user->remove_role($active_role);
                if (!in_array($expired_role, $user->roles, true)) {
                    $user->add_role($expired_role);
                }
                clean_user_cache($user_id);
            }
            delete_user_meta($user_id, 'active_subscriptions');
        }
    }

    private function embed_inline_script() {
        echo '<script>
            jQuery(document).ready(function ($) {
                $("#cancel-subscription-button").on("click", function () {
                    if (!confirm("Вы уверены, что хотите отменить подписку?")) {
                        return;
                    }
                    var button = $(this);
                    var userId = button.data("user-id");
                    button.prop("disabled", true).text("Отмена...");
                    $.ajax({
                        url: ajaxurl,
                        method: "POST",
                        data: { action: "cancel_subscription", user_id: userId },
                        success: function (response) {
                            if (response.success) {
                                alert("Подписка успешно отменена.");
                                button.text("Подписка отменена, перезагрузите страницу");
                            } else {
                                alert(response.data);
                                button.prop("disabled", false).text("Отменить подписку");
                            }
                        },
                        error: function () {
                            alert("Произошла ошибка. Попробуйте позже.");
                            button.prop("disabled", false).text("Отменить подписку");
                        }
                    });
                });
            });
        </script>';
    }
}
