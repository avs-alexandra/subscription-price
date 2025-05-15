<?php
if (!defined('ABSPATH')) exit; // Запрет прямого доступа

class SubscriptionNotifications {
    public function __construct() {
        add_action('subscription_notification_reminder', [$this, 'send_reminder_email']);
        add_action('subscription_notification_expired', [$this, 'send_expired_email']);
    }

    public function send_reminder_email($user_id) {
        $user = get_userdata($user_id);

        if (!$user || empty($user->user_email)) {
            return;
        }

        $active_subscriptions = get_user_meta($user_id, 'active_subscriptions', true);

        if (empty($active_subscriptions)) {
            return;
        }

        $subscription = reset($active_subscriptions);

        if (empty($subscription['end_date']) || $subscription['end_date'] < time()) {
            return;
        }

        $email_subject = get_option('reminder_email_subject', 'Ваша подписка скоро завершится');
        $email_body_template = get_option('reminder_email_body', 'Здравствуйте, [user]! Напоминаем, что ваша подписка закончится [end_date].');
        $email_font = get_option('email_font', 'Arial, sans-serif');
        $email_body = $this->generate_email_content($user, $subscription, $email_body_template, $email_font);

        $this->send_woocommerce_email($user->user_email, $email_subject, $email_body, $email_font);
    }

    public function send_expired_email($user_id) {
        $user = get_userdata($user_id);

        if (!$user || empty($user->user_email)) {
            return;
        }

        $email_subject = get_option('expired_email_subject', 'Ваша подписка завершилась');
        $email_body_template = get_option('expired_email_body', 'Здравствуйте, [user]! Ваша подписка завершилась.');
        $email_font = get_option('email_font', 'Arial, sans-serif');
        $email_body = $this->generate_email_content($user, null, $email_body_template, $email_font);

        $this->send_woocommerce_email($user->user_email, $email_subject, $email_body, $email_font);
    }

    private function generate_email_content($user, $subscription, $body_template, $font) {
        $placeholders = [
            '[user]' => esc_html($user->display_name),
            '[end_date]' => $subscription ? esc_html(date_i18n('d.m.y', $subscription['end_date'])) : '',
        ];

        $email_body = strtr($body_template, $placeholders);

        return '<tr><td style="font-family: ' . esc_attr($font) . '; padding: 40px; font-size: 15px;">' . wpautop($email_body) . '</td></tr>';
    }

    private function send_woocommerce_email($recipient, $subject, $message, $font) {
        $header_template = plugin_dir_path(__FILE__) . '../templates/email_header.php';
        $footer_template = plugin_dir_path(__FILE__) . '../templates/email_footer.php';

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

        $wrapped_message = '
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f5f5f5;">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:#ffffff;border:1px solid #dedede;border-radius:3px;">
                            ' . $header . '
                            ' . $message . '
                            ' . $footer . '
                        </table>
                    </td>
                </tr>
            </table>
        ';

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($recipient, $subject, $wrapped_message, $headers);
    }
}
