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
            $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
            if (!$product_id) {
                continue; // Пропускаем, если товар не найден
            }

            // Логируем проверку текущего product_id
            error_log("Checking subscription plan for product ID: $product_id");

            foreach ($subscription_plans as $plan) {
                if (intval($plan['product_id']) === intval($product_id)) {
                    $this->activate_subscription($user_id, $plan, $item->get_name());
                    break; // Прекращаем поиск, если найдено совпадение
                }
            }
        }
    }

    /**
     * Активация подписки
     */
    private function activate_subscription($user_id, $plan, $product_name) {
        if (!$user_id || empty($plan['role_active']) || empty($plan['role_expired'])) {
            error_log("Invalid user ID or roles for subscription activation.");
            return; // Если пользователь или роли не указаны, выходим
        }

        $user = get_userdata($user_id);
        if ($user) {
            // Проверяем, есть ли у пользователя роль Customer
            if (in_array($plan['role_expired'], $user->roles, true)) {
                // Меняем роль с Customer на Member
                $user->remove_role($plan['role_expired']);
                $user->add_role($plan['role_active']);
                error_log("Role '{$plan['role_active']}' added, '{$plan['role_expired']}' removed for user ID $user_id.");
            } else {
                error_log("User ID $user_id does not have role '{$plan['role_expired']}', skipping role change.");
            }

            // Удаляем текущие активные подписки
            delete_user_meta($user_id, 'active_subscriptions');

            $current_time = time();
            $duration = $this->calculate_duration($plan['duration']);

            // Добавляем новую подписку в мета-данные пользователя
            $new_subscription = [
                'id' => $current_time, // Уникальный ID подписки
                'name' => "{$product_name} - {$plan['duration']['months']} месяц(ев)",
                'start_date' => $current_time, // Текущая дата как дата начала
                'end_date' => $current_time + $duration, // Дата окончания
                'expiration' => $current_time + $duration, // Для совместимости с текущей логикой
            ];

            update_user_meta($user_id, 'active_subscriptions', [$new_subscription]);

            // Планировщик завершения подписки
            if ($duration) {
                if (!wp_next_scheduled('subscription_end_event', ['user_id' => $user_id, 'expired_role' => $plan['role_expired']])) {
                    wp_schedule_single_event(
                        $current_time + $duration,
                        'subscription_end_event',
                        ['user_id' => $user_id, 'expired_role' => $plan['role_expired']]
                    );
                    error_log("Subscription expiration event scheduled for user ID $user_id after $duration seconds.");
                }
            }
        } else {
            error_log("User not found with ID: $user_id");
        }
    }

    /**
     * Обработка завершения подписки
     */
   /**
 * Обработка завершения подписки
 */
public function handle_subscription_expiration($user_id, $expired_role) {
    $active_role = get_option('subscription_role_active', '');

    if (!$user_id || empty($expired_role) || empty($active_role)) {
        error_log("Invalid user ID or roles for subscription expiration.");
        return; // Если данные отсутствуют, выходим
    }

    error_log("Subscription expiration event triggered for user ID $user_id.");

    $user = get_userdata($user_id);
    if ($user) {
        // Проверяем, есть ли у пользователя роль Member (активная роль подписки)
        if (in_array($active_role, $user->roles, true)) {
            // Меняем роль с Member на Customer
            $user->remove_role($active_role);
            $user->add_role($expired_role);
            error_log("Role '{$expired_role}' added, '{$active_role}' removed for user ID $user_id.");
        } else {
            error_log("User ID $user_id does not have role '{$active_role}', skipping role change.");
        }

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
