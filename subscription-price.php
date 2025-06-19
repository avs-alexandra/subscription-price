<?php
/**
 * Plugin Name: Subscription price
 * Plugin URI: https://github.com/avs-alexandra/subscription-price
 * Description: Subscription functionality for WooCommerce based on user role switching.
 * Version: 1.0.0
 * Author: avs-alexandra
 * Author URI: https://github.com/avs-alexandra
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: subscription-price
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Запрет прямого доступа
}

// Подключаем файлы классов
require_once plugin_dir_path(__FILE__) . 'includes/class-subscription-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-subscription-user-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-subscription-shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-subscription-notifications.php';

class SubscriptionPrice {
    public function __construct() {
        // Инициализация настроек
        new Subscription_Settings();

        // Инициализация пользовательского интерфейса
        new Subscription_User_Dashboard();
        
        // Инициализация шорткодов
        new Subscription_Shortcodes();
        
        // Отправка уведомлений о завершении подписки
        new SubscriptionNotifications();

        // Обработка активации подписки при покупке
        add_action('woocommerce_order_status_completed', [$this, 'handle_subscription_activation']);

        // Регистрация обработчика завершения подписки (через Action Scheduler)
        add_action('handle_subscription_expiration', [$this, 'handle_subscription_expiration']);
    }

    /**
     * Обработка завершения заказа для активации подписки
     */
    public function handle_subscription_activation($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $user_id = $order->get_user_id();
        if (!$user_id) {
            return;
        }

        $subscription_plans = get_option('subscription_plans', []);

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
            if (!$product_id) {
                continue;
            }

            foreach ($subscription_plans as $plan) {
                if (intval($plan['product_id']) === intval($product_id)) {
                    $this->activate_subscription($user_id, $plan, $item->get_name());
                    break;
                }
            }
        }
    }

    /**
     * Активация подписки
     */
    private function activate_subscription($user_id, $plan, $product_name) {
        if (!$user_id || empty($plan['role_active']) || empty($plan['role_expired'])) {
            return;
        }

        $user = get_userdata($user_id);
        if ($user) {
            if (in_array($plan['role_expired'], $user->roles, true)) {
                $user->remove_role($plan['role_expired']);
                $user->add_role($plan['role_active']);
            } else {
                if (!in_array($plan['role_active'], $user->roles, true)) {
                    $user->add_role($plan['role_active']);
                }
            }

            delete_user_meta($user_id, 'active_subscriptions');

            $current_time = time();
            $end_date = $this->calculate_end_date($current_time, $plan['duration']);

            $new_subscription = [
                'id' => $current_time,
                'name' => "{$product_name} - {$plan['duration']['months']} месяц(ев)",
                'start_date' => $current_time,
                'end_date' => $end_date,
            ];

            update_user_meta($user_id, 'active_subscriptions', [$new_subscription]);

            if ($end_date) {
                as_schedule_single_action(
                    $end_date - 3 * DAY_IN_SECONDS,
                    'subscription_notification_reminder',
                    ['user_id' => $user_id]
                );
                as_schedule_single_action(
                    $end_date,
                    'subscription_notification_expired',
                    ['user_id' => $user_id]
                );
                as_schedule_single_action(
                    $end_date,
                    'handle_subscription_expiration',
                    ['user_id' => $user_id, 'expired_role' => $plan['role_expired']]
                );
            }
        }
    }

    /**
     * Обработка завершения подписки
     */
    public function handle_subscription_expiration($user_id, $expired_role = '') {
        if (empty($expired_role)) {
            $expired_role = get_option('subscription_role_expired', '');
        }

        $active_role = get_option('subscription_role_active', '');

        if (!$user_id || empty($expired_role) || empty($active_role)) {
            return;
        }

        $user = get_userdata($user_id);
        if ($user) {
            if (in_array($active_role, $user->roles, true)) {
                $user->remove_role($active_role);
                $user->add_role($expired_role);
            }
            delete_user_meta($user_id, 'active_subscriptions');
        }
    }

    /**
     * Рассчитать дату окончания подписки (универсально)
     */
    private function calculate_end_date($start_time, $duration) {
        $date = new DateTime();
        $date->setTimestamp($start_time);

        if (isset($duration['years']) && $duration['years'] > 0) {
            $date->modify('+' . intval($duration['years']) . ' years');
        }
        if (isset($duration['months']) && $duration['months'] > 0) {
            $date->modify('+' . intval($duration['months']) . ' months');
        }

        $seconds = 0;
        if (isset($duration['days']) && $duration['days'] > 0) {
            $seconds += intval($duration['days']) * DAY_IN_SECONDS;
        }
        if (isset($duration['hours']) && $duration['hours'] > 0) {
            $seconds += intval($duration['hours']) * HOUR_IN_SECONDS;
        }
        if (isset($duration['minutes']) && $duration['minutes'] > 0) {
            $seconds += intval($duration['minutes']) * MINUTE_IN_SECONDS;
        }
        if ($seconds > 0) {
            $date->modify("+$seconds seconds");
        }

        return $date->getTimestamp();
    }
}

// Инициализируем плагин
new SubscriptionPrice();
