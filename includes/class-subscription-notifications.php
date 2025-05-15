<?php
if (!defined('ABSPATH')) exit; // Запрет прямого доступа

class SubscriptionNotifications {
    /**
     * Конструктор для инициализации хуков и добавления функционала
     */
    public function __construct() {
        // Хук для отправки уведомлений
        add_action('subscription_notification_reminder', [$this, 'send_reminder_email']);
        add_action('subscription_notification_expired', [$this, 'send_expired_email']);
    }

    /**
     * Отправка уведомления о скором завершении подписки
     *
     * @param int $user_id ID пользователя
     */
    public function send_reminder_email($user_id) {
        $user = get_userdata($user_id);

        if (!$user || empty($user->user_email)) {
            return; // Если пользователь не найден или email отсутствует, выходим
        }

        $active_subscriptions = get_user_meta($user_id, 'active_subscriptions', true);

        if (empty($active_subscriptions)) {
            return; // Если подписок нет, выходим
        }

        // Берём первую подписку
        $subscription = reset($active_subscriptions);

        if (empty($subscription['end_date']) || $subscription['end_date'] < time()) {
            return; // Если дата окончания подписки уже прошла, выходим
        }

        // Загрузка настроек из опций
        $email_subject = get_option('reminder_email_subject', 'Ваша подписка скоро завершится'); // Тема письма
        $email_body_template = get_option('reminder_email_body', 'Здравствуйте, [user]! Напоминаем, что ваша подписка закончится [end_date].');
        $email_font = get_option('email_font', 'Arial, sans-serif');
        $email_body = $this->generate_email_content($user, $subscription, $email_body_template, $email_font);

        $this->send_woocommerce_email($user->user_email, $email_subject, $email_body, $email_font);
    }

    /**
     * Отправка уведомления о завершении подписки
     *
     * @param int $user_id ID пользователя
     */
    public function send_expired_email($user_id) {
        $user = get_userdata($user_id);

        if (!$user || empty($user->user_email)) {
            return; // Если пользователь не найден или email отсутствует, выходим
        }

        // Загрузка настроек из опций
        $email_subject = get_option('expired_email_subject', 'Ваша подписка завершилась'); // Тема письма
        $email_body_template = get_option('expired_email_body', 'Здравствуйте, [user]! Ваша подписка завершилась.');
        $email_font = get_option('email_font', 'Arial, sans-serif');
        $email_body = $this->generate_email_content($user, null, $email_body_template, $email_font);

        $this->send_woocommerce_email($user->user_email, $email_subject, $email_body, $email_font);
    }

    /**
     * Генерация содержимого письма
     *
     * @param WP_User $user Данные пользователя
     * @param array|null $subscription Данные подписки
     * @param string $body_template Шаблон тела письма
     * @param string $font Шрифт письма
     * @return string Содержимое письма
     */
    private function generate_email_content($user, $subscription, $body_template, $font) {
        $placeholders = [
            '[user]' => esc_html($user->display_name),
            '[end_date]' => $subscription ? esc_html(date_i18n('d.m.y', $subscription['end_date'])) : '',
        ];

        $email_body = strtr($body_template, $placeholders);

        // Возвращаем содержимое письма с применением шрифта
        return '<div style="font-family: ' . esc_attr($font) . '; padding: 40px; font-size: 15px;">' . wpautop($email_body) . '</div>';
    }

    /**
     * Отправка письма через WooCommerce с кастомным шаблоном
     *
     * @param string $recipient Email получателя
     * @param string $subject Тема письма
     * @param string $message Сообщение письма
     * @param string $font Шрифт письма
     */
    private function send_woocommerce_email($recipient, $subject, $message, $font) {
        // Пути к шаблонам
        $header_template = plugin_dir_path(__FILE__) . '../templates/email_header.php';
        $footer_template = plugin_dir_path(__FILE__) . '../templates/email_footer.php';

        // Генерация шапки
        ob_start();
        if (file_exists($header_template)) {
            include $header_template;
        }
        $header = ob_get_clean();
        $header = strtr($header, [
            '{background_color}' => get_option("woocommerce_email_background_color", "#f5f5f5"),
            '{woocommerce_email_base_color}' => get_option("woocommerce_email_base_color", "#007cba"),
            '{title}' => esc_html($subject),
            '{font_family}' => esc_attr($font),
        ]);

        // Генерация подвала
        ob_start();
        if (file_exists($footer_template)) {
            include $footer_template;
        }
        $footer = ob_get_clean();
        $footer = strtr($footer, [
            '{text_color}' => get_option("woocommerce_email_text_color", "#444444"),
            '{description}' => get_option('woocommerce_email_footer_text', __("Спасибо за использование нашего сервиса!", "subscription-price")),
            '{site_title}' => get_bloginfo('name'),
            '{site_url}' => home_url(),
            '{year}' => date('Y'),
            '{font_family}' => esc_attr($font),
        ]);

        // Объединяем шапку, тело и подвал
        $wrapped_message = $header . $message . $footer;

        // Заголовки
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        // Отправляем письмо
        wp_mail($recipient, $subject, $wrapped_message, $headers);
    }
}
