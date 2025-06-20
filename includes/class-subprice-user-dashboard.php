<?php
if (!defined('ABSPATH')) {
    exit;
}

class Subprice_User_Dashboard {
    public function __construct() {
        add_action('show_user_profile', [$this, 'subprice_display_subscription_status']);
        add_action('edit_user_profile', [$this, 'subprice_display_subscription_status']);
        add_filter('manage_users_columns', [$this, 'subprice_add_subscription_column']);
        add_action('manage_users_custom_column', [$this, 'subprice_display_subscription_column'], 10, 3);
        add_action('wp_ajax_subprice_cancel_subscription', [$this, 'subprice_cancel_subscription']);
        add_action('subprice_handle_cancel_subscription', [$this, 'subprice_handle_cancel_subscription']);
        add_action('admin_enqueue_scripts', [$this, 'subprice_enqueue_dashboard_js']);
    }

public function subprice_display_subscription_status($user) {
    $active_subscriptions = get_user_meta($user->ID, 'subprice_active_subscriptions', true);

    echo '<h2>' . esc_html__('Статус подписки', 'subscription-price') . '</h2>';

    if (empty($active_subscriptions)) {
        echo '<p>' . esc_html__('Подписка не активна.', 'subscription-price') . '</p>';
    } else {
        echo '<ul>';
        foreach ($active_subscriptions as $subscription) {
            echo '<li>' . esc_html($subscription['name']) . '</li>';
            if (!empty($subscription['start_date']) && !empty($subscription['end_date'])) {
                echo '<p>' . sprintf(
                    /* translators: 1: start date, 2: end date */
                    esc_html__('С: %1$s по %2$s', 'subscription-price'),
                    esc_html(date_i18n(get_option('date_format'), $subscription['start_date'])),
                    esc_html(date_i18n(get_option('date_format'), $subscription['end_date']))
                ) . '</p>';
    }
}
echo '</ul>';
$nonce = wp_create_nonce('subprice_cancel_subscription_' . $user->ID);
echo '<button id="subprice-cancel-subscription-button" class="button button-secondary" data-user-id="' . esc_attr($user->ID) . '" data-nonce="' . esc_attr($nonce) . '">' . esc_html__('Отменить подписку', 'subscription-price') . '</button>';
        }
    }

    public function subprice_add_subscription_column($columns) {
        $columns['subprice_subscription_status'] = esc_html__('Уровень подписки', 'subscription-price');
        return $columns;
    }

    public function subprice_display_subscription_column($value, $column_name, $user_id) {
        if ('subprice_subscription_status' === $column_name) {
            $active_subscriptions = get_user_meta($user_id, 'subprice_active_subscriptions', true);
            if (!empty($active_subscriptions) && isset($active_subscriptions[0]['name'])) {
                $name = $active_subscriptions[0]['name'];
                return esc_html(mb_strlen($name) > 30 ? mb_substr($name, 0, 30) . '...' : $name);
            }
            return '';
        }
        return $value;
    }

    public function subprice_cancel_subscription() {
        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        $nonce   = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';

        if (empty($nonce) || empty($user_id) || !wp_verify_nonce($nonce, 'subprice_cancel_subscription_' . $user_id)) {
            wp_send_json_error(esc_html__('Ошибка безопасности (nonce verification failed).', 'subscription-price'));
        }
        if (!current_user_can('edit_users')) {
            wp_send_json_error(esc_html__('Нет прав для выполнения операции.', 'subscription-price'));
        }
        if (!$user_id) {
            wp_send_json_error(esc_html__('Некорректный ID пользователя.', 'subscription-price'));
        }
        as_schedule_single_action(time(), 'subprice_handle_cancel_subscription', ['user_id' => $user_id]);
        wp_send_json_success(esc_html__('Задача на отмену подписки добавлена в очередь.', 'subscription-price'));
    }

    public function subprice_handle_cancel_subscription($user_id) {
        $user_id = absint($user_id);
        if (!$user_id) {
            return;
        }
        $active_role = get_option('subprice_subscription_role_active', '');
        $expired_role = get_option('subprice_subscription_role_expired', '');
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
            delete_user_meta($user_id, 'subprice_active_subscriptions');
        }
    }

    public function subprice_enqueue_dashboard_js($hook) {
        if ('user-edit.php' !== $hook && 'profile.php' !== $hook) {
            return;
        }
        wp_enqueue_script(
            'subprice-user-dashboard',
            plugin_dir_url(__FILE__) . '../assets/js/subprice-user-dashboard.js',
            ['jquery'],
            '1.0.0',
            true
        );
        wp_localize_script('subprice-user-dashboard', 'subpriceUserDashboardI18n', [
            'confirm_cancel'  => esc_html__('Вы уверены, что хотите отменить подписку?', 'subscription-price'),
            'canceling'       => esc_html__('Отмена...', 'subscription-price'),
            'success'         => esc_html__('Задача на отмену подписки добавлена в очередь. Подписка отменится в течении 1–2 минут.', 'subscription-price'),
            'error'           => esc_html__('Произошла ошибка. Попробуйте позже.', 'subscription-price'),
            'button_default'  => esc_html__('Отменить подписку', 'subscription-price'),
            'ajaxurl'         => admin_url('admin-ajax.php'),
        ]);
    }
}
