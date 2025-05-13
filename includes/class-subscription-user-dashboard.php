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

        // Подключение скрипта для обработки кнопки
        $this->enqueue_scripts();
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

        $user_id = absint($_POST['user_id'] ?? 0);

        if (!$user_id) {
            wp_send_json_error(__('Некорректный ID пользователя.', 'subscription-price'));
        }

        // Удаляем все активные подписки пользователя
        delete_user_meta($user_id, 'active_subscriptions');

        // Меняем роль пользователя на "customer" (или другую роль)
        $user = get_userdata($user_id);
        if ($user) {
            $user->set_role('customer');
        }

        wp_send_json_success(__('Подписка успешно отменена. Роль пользователя изменена.', 'subscription-price'));
    }

    /**
     * Подключение скриптов для обработки кнопки
     */
    private function enqueue_scripts() {
        wp_enqueue_script(
            'subscription-user-dashboard',
            plugin_dir_url(__FILE__) . '../assets/js/subscription-user-dashboard.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('subscription-user-dashboard', 'SubscriptionDashboard', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }
}
