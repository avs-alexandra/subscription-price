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

// Подключаем файлы классов
require_once plugin_dir_path(__FILE__) . 'includes/class-subscription-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-subscription-user-dashboard.php';

class SubscriptionPrice {
    public function __construct() {
        // Инициализация настроек
        new Subscription_Settings();

        // Инициализация пользовательского интерфейса
        new Subscription_User_Dashboard();

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

        // Логируем содержимое подписочных планов
        $subscription_plans = get_option('subscription_plans', []);
        error_log("Subscription plans: " . print_r($subscription_plans, true));

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if (!$product_id) {
                continue; // Пропускаем, если товар не найден
            }

            // Проверяем родительский ID для вариативных товаров
            $parent_id = wp_get_post_parent_id($product_id);
            error_log("Processing product ID: $product_id, Parent ID: $parent_id");

            $matched = false;

            foreach ($subscription_plans as $plan) {
                if (
                    isset($plan['product_id']) &&
                    (intval($plan['product_id']) === intval($product_id) ||
                     intval($plan['parent_id']) === intval($product_id) || 
                     intval($plan['product_id']) === intval($parent_id) || 
                     intval($plan['parent_id']) === intval($parent_id))
                ) {
                    $this->activate_subscription($user_id, $plan, $item->get_name());
                    $matched = true;
                    break; // Прекращаем поиск, если найдено совпадение
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
    private function activate_subscription($user_id, $plan, $product_name) {
        if (!$user_id || empty($plan['role_active'])) {
            error_log("Invalid user ID or active role for subscription activation.");
            return; // Если пользователь или роль не указаны, выходим
        }

        $user = get_userdata($user_id);
        if ($user) {
            // Устанавливаем роль для активной подписки
            $user->set_role($plan['role_active']);
            error_log("Role '{$plan['role_active']}' assigned to user ID $user_id.");

            // Добавляем активную подписку в мета-данные пользователя
            $active_subscriptions = get_user_meta($user_id, 'active_subscriptions', true);
            if (empty($active_subscriptions)) {
                $active_subscriptions = [];
            }

            $current_time = time();
            $duration = $this->calculate_duration($plan['duration']);

            $active_subscriptions[] = [
                'id' => $current_time, // Уникальный ID подписки
                'name' => "{$product_name} - {$plan['duration']['months']} месяц(ев)",
                'start_date' => $current_time, // Текущая дата как дата начала
                'end_date' => $current_time + $duration, // Дата окончания
                'expiration' => $current_time + $duration, // Для совместимости с текущей логикой
            ];

            update_user_meta($user_id, 'active_subscriptions', $active_subscriptions);

            // Планировщик завершения подписки
            if ($duration) {
                $scheduled = wp_schedule_single_event(
                    $current_time + $duration,
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
            // Меняем роль пользователя на роль после завершения подписки
            $user->set_role($expired_role);
            error_log("Role '{$expired_role}' assigned to user ID $user_id after subscription expiration.");

            // Удаляем все подписки пользователя
            delete_user_meta($user_id, 'active_subscriptions');
        } else {
            error_log("User not found with ID: $user_id");
        }
    }

    /**
     * Рассчитать длительность подписки в секундах
     */
   private function calculate_duration($duration) {
    $total_seconds = 0;

    // Текущая дата
    $current_date = new DateTime();

    if (isset($duration['years']) && $duration['years'] > 0) {
        $current_date->modify('+' . $duration['years'] . ' years');
    }
    if (isset($duration['months']) && $duration['months'] > 0) {
        $current_date->modify('+' . $duration['months'] . ' months');
    }
    if (isset($duration['days']) && $duration['days'] > 0) {
        $total_seconds += $duration['days'] * DAY_IN_SECONDS;
    }
    if (isset($duration['hours']) && $duration['hours'] > 0) {
        $total_seconds += $duration['hours'] * HOUR_IN_SECONDS;
    }
    if (isset($duration['minutes']) && $duration['minutes'] > 0) {
        $total_seconds += $duration['minutes'] * MINUTE_IN_SECONDS;
    }

    // Вычисляем разницу между текущей датой и итоговой датой
    $end_date = $current_date->getTimestamp();
    $total_seconds += $end_date - time();

    return $total_seconds;
}
}

// Инициализируем плагин
new SubscriptionPrice();
