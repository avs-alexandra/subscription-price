<?php
if (!defined('ABSPATH')) {
    exit; // Запрет прямого доступа
}

class Subprice_Shortcodes {
    public function __construct() {
        // Регистрация шорткода
        add_shortcode('subprice_status', [$this, 'subprice_render_status']);
    }

    /**
     * Рендеринг даты окончания подписки для текущего пользователя
     */
    public function subprice_render_status($atts) {
        // Проверяем, активен ли плагин WooCommerce (или ваш плагин, если есть специфическое условие)
        if (!class_exists('WooCommerce')) {
            return ''; // WooCommerce не активен, ничего не выводим
        }

        // Получаем текущего пользователя
        $current_user = wp_get_current_user();

        if (!$current_user->exists()) {
            return ''; // Пользователь не авторизован, ничего не выводим
        }

        // Получаем активные подписки пользователя
        $active_subscriptions = get_user_meta($current_user->ID, 'subprice_active_subscriptions', true);

        if (empty($active_subscriptions)) {
            return ''; // Подписок нет, ничего не выводим
        }

        // Берём первую подписку (если их несколько)
        $subscription = reset($active_subscriptions);

        // Проверяем, завершилась ли подписка
        if (empty($subscription['end_date']) || $subscription['end_date'] < time()) {
            return ''; // Подписка завершилась, ничего не выводим
        }

        // Форматируем дату окончания подписки в формате "15.05.25"
        $formatted_date = date_i18n('d.m.y', $subscription['end_date']);

        return esc_html($formatted_date);
    }
}
