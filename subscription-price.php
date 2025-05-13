<?php
/**
 * Plugin Name: Subscription price
 * Plugin URI: https://example.com
 * Description: Плагин добавляет вкладку "Подписка" в настройки WooCommerce для управления ролями пользователей в зависимости от подписки.
 * Version: 1.0.0
 * Author: avs-alexandra
 * Author URI: https://example.com
 * Text Domain: subscription-price
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Запрет прямого доступа
}

// Подключаем файл класса настроек
require_once plugin_dir_path(__FILE__) . 'includes/class-subscription-settings.php';

class SubscriptionPrice {
    public function __construct() {
        // Инициализация настроек
        new Subscription_Settings();

        // Обработка активации подписки при покупке
        add_action('woocommerce_order_status_completed', [$this, 'handle_subscription_activation']);
        // Планировщик завершения подписки
        add_action('subscription_end_event', [$this, 'handle_subscription_expiration']);
    }

    /**
     * Обработка завершения заказа для активации подписки
     */
   public function handle_subscription_activation($order_id) {
    $order = wc_get_order($order_id);

    if (!$order) {
        error_log("Order not found: $order_id");
        return; // Если заказ не найден, выходим
    }

    $user_id = $order->get_user_id();
    if (!$user_id) {
        error_log("User ID not found for order: $order_id");
        return; // Если пользователь не найден, выходим
    }

    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        if (!$product_id) {
            continue; // Пропускаем, если товар не найден
        }

        // Загружаем настройки подписки
        $subscription_plans = get_option('subscription_plans', []);
        $matched = false;
        foreach ($subscription_plans as $plan) {
            if (isset($plan['product_id']) && intval($plan['product_id']) === intval($product_id)) {
                $this->activate_subscription($user_id, $plan);
                $matched = true;
            }
        }

        if (!$matched) {
            error_log("No subscription plan matched for product ID $product_id in order $order_id.");
        }
    }
}

    /**
     * Активация подписки
     */
    private function activate_subscription($user_id, $plan) {
        if (!$user_id || empty($plan['role_active'])) {
            error_log("Invalid user ID or active role for subscription activation.");
            return; // Если пользователь или роль не указаны, выходим
        }

        $user = get_userdata($user_id);
        if ($user) {
            // Устанавливаем роль для активной подписки
            $user->set_role($plan['role_active']);
            error_log("Role '{$plan['role_active']}' assigned to user ID $user_id.");

            // Рассчитываем длительность подписки
            $duration = $this->calculate_duration($plan['duration']);
            if ($duration) {
                $scheduled = wp_schedule_single_event(
                    time() + $duration,
                    'subscription_end_event',
                    ['user_id' => $user_id, 'expired_role' => $plan['role_expired']]
                );
                if ($scheduled) {
                    error_log("Subscription expiration event scheduled for user ID $user_id after $duration seconds.");
                } else {
                    error_log("Failed to schedule subscription expiration event for user ID $user_id.");
                }
            }
        } else {
            error_log("User not found with ID: $user_id");
        }
    }

    /**
     * Обработка завершения подписки
     */
    public function handle_subscription_expiration($user_id, $expired_role) {
        if (!$user_id || empty($expired_role)) {
            error_log("Invalid user ID or expired role for subscription expiration.");
            return; // Если данные отсутствуют, выходим
        }

        $user = get_userdata($user_id);
        if ($user) {
            $user->set_role($expired_role); // Устанавливаем роль после завершения подписки
            error_log("Role '{$expired_role}' assigned to user ID $user_id after subscription expiration.");
        } else {
            error_log("User not found with ID: $user_id");
        }
    }

    /**
     * Рассчитать длительность подписки в секундах
     */
    private function calculate_duration($duration) {
        $total_seconds = 0;

        if (!empty($duration['years'])) {
            $total_seconds += $duration['years'] * YEAR_IN_SECONDS;
        }
        if (!empty($duration['months'])) {
            $total_seconds += $duration['months'] * MONTH_IN_SECONDS;
        }
        if (!empty($duration['days'])) {
            $total_seconds += $duration['days'] * DAY_IN_SECONDS;
        }
        if (!empty($duration['hours'])) {
            $total_seconds += $duration['hours'] * HOUR_IN_SECONDS;
        }
        if (!empty($duration['minutes'])) {
            $total_seconds += $duration['minutes'] * MINUTE_IN_SECONDS;
        }

        return $total_seconds;
    }
}

// Инициализируем плагин
new SubscriptionPrice();
