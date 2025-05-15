<?php
if (!defined('ABSPATH')) exit; // Запрет прямого доступа

class Subscription_Settings {
    public function __construct() {
        // Добавляем настройки в раздел "Маркетинг"
        add_action('admin_menu', [$this, 'add_marketing_submenu']);
        add_action("admin_post_save_subscription_settings", [$this, 'save_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_wc_scripts']);
        add_action('admin_notices', [$this, 'show_admin_notices']); // Для уведомлений
    }

    /**
     * Подключение скриптов WooCommerce для работы wc-product-search
     */
    public function enqueue_wc_scripts() {
        if (isset($_GET['page']) && $_GET['page'] === 'subscription-settings') {
            wp_enqueue_script('woocommerce_admin');
            wp_enqueue_script('wc-enhanced-select');
            wp_enqueue_style('woocommerce_admin_styles');
        }
    }

    /**
     * Добавить пункт меню в раздел "Маркетинг"
     */
    public function add_marketing_submenu() {
        add_submenu_page(
            'woocommerce-marketing',
            __('Настройки Подписки', 'subscription-price'),
            __('Подписка', 'subscription-price'),
            'manage_options',
            'subscription-settings',
            [$this, 'output_settings']
        );
    }

    /**
     * Показ уведомлений в админке
     */
    public function show_admin_notices() {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('Настройки сохранены.', 'subscription-price') . '</p>';
            echo '</div>';
        }

        if (!empty($_GET['message']) && $_GET['message'] === 'error_roles') {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . __('Роли активной и истекшей подписки не могут совпадать!', 'subscription-price') . '</p>';
            echo '</div>';
        }
    }

    /**
     * Вывод настроек
     */
    public function output_settings() {
        ?>
        <div class="wrap">
            <h1><?php _e('Настройки Подписки', 'subscription-price'); ?></h1>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('save_subscription_settings', 'woocommerce_subscription_nonce'); ?>
                <input type="hidden" name="action" value="save_subscription_settings">
                <!-- Поля для настройки подписок -->
                <h2><?php _e('Настройки подписок', 'subscription-price'); ?></h2>
                <table class="form-table">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <tr>
                            <th scope="row">
                                <label><?php printf(__('Подписка %d', 'subscription-price'), $i); ?></label>
                            </th>
                            <td>
                                <label><?php _e('Выберите товар:', 'subscription-price'); ?></label>
                                <select id="product_<?php echo $i; ?>" name="product_<?php echo $i; ?>" class="wc-product-search" style="width: 50%;" data-placeholder="<?php esc_attr_e('Выберите товар', 'subscription-price'); ?>" data-action="woocommerce_json_search_products_and_variations">
                                    <?php
                                    $product_id = get_option("subscription_{$i}_product", '');
                                    if ($product_id) {
                                        $product = wc_get_product($product_id);
                                        if ($product) {
                                            echo '<option value="' . esc_attr($product_id) . '" selected="selected">' . esc_html($product->get_name()) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <br><br>
                                <label><?php _e('Длительность подписки:', 'subscription-price'); ?></label><br>
                                <label>
                                    <?php _e('Годы:', 'subscription-price'); ?>
                                    <input type="number" name="duration_<?php echo $i; ?>_years" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_years", 0)); ?>">
                                </label>
                                <label>
                                    <?php _e('Месяцы:', 'subscription-price'); ?>
                                    <input type="number" name="duration_<?php echo $i; ?>_months" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_months", 0)); ?>">
                                </label>
                                <label>
                                    <?php _e('Дни:', 'subscription-price'); ?>
                                    <input type="number" name="duration_<?php echo $i; ?>_days" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_days", 0)); ?>">
                                </label>
                                <label>
                                    <?php _e('Часы:', 'subscription-price'); ?>
                                    <input type="number" name="duration_<?php echo $i; ?>_hours" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_hours", 0)); ?>">
                                </label>
                                <label>
                                    <?php _e('Минуты:', 'subscription-price'); ?>
                                    <input type="number" name="duration_<?php echo $i; ?>_minutes" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_minutes", 0)); ?>">
                                </label>
                            </td>
                        </tr>
                    <?php endfor; ?>
                    <tr>
                        <th scope="row">
                            <label for="role_active"><?php _e('Роль при активной подписке', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <select id="role_active" name="role_active" required>
                                <?php foreach ($this->get_roles() as $role_key => $role_name): ?>
                                    <option value="<?php echo esc_attr($role_key); ?>" <?php selected(get_option('subscription_role_active'), $role_key); ?>>
                                        <?php echo esc_html($role_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="role_expired"><?php _e('Роль после завершения подписки', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <select id="role_expired" name="role_expired" required>
                                <?php foreach ($this->get_roles() as $role_key => $role_name): ?>
                                    <option value="<?php echo esc_attr($role_key); ?>" <?php selected(get_option('subscription_role_expired'), $role_key); ?>>
                                        <?php echo esc_html($role_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                 <!-- Поля для настройки писем -->
                <h2><?php _e('Настройки писем', 'subscription-price'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="reminder_email_subject"><?php _e('Тема письма (напоминание)', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reminder_email_subject" name="reminder_email_subject" style="width: 70%;" value="<?php echo esc_attr(get_option('reminder_email_subject', __('Ваша подписка скоро завершится', 'subscription-price'))); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reminder_email_body"><?php _e('Текст письма (напоминание)', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <?php
                            $reminder_email_body = get_option('reminder_email_body', __('Здравствуйте, [user]! Ваша подписка закончится [end_date].', 'subscription-price'));
                            wp_editor($reminder_email_body, 'reminder_email_body', [
                                'textarea_name' => 'reminder_email_body',
                                'textarea_rows' => 2,
                                'media_buttons' => true,
                            ]);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="expired_email_subject"><?php _e('Тема письма (завершение)', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="expired_email_subject" name="expired_email_subject" style="width: 70%;" value="<?php echo esc_attr(get_option('expired_email_subject', __('Ваша подписка завершилась', 'subscription-price'))); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="expired_email_body"><?php _e('Текст письма (завершение)', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <?php
                            $expired_email_body = get_option('expired_email_body', __('Здравствуйте, [user]! Ваша подписка завершилась.', 'subscription-price'));
                            wp_editor($expired_email_body, 'expired_email_body', [
                                'textarea_name' => 'expired_email_body',
                                'textarea_rows' => 2,
                                'media_buttons' => true,
                            ]);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="email_font"><?php _e('Шрифт письма', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="email_font" name="email_font" style="width: 50%;" value="<?php echo esc_attr(get_option('email_font', 'Arial, sans-serif')); ?>">
                            <p class="description"><?php _e('Укажите шрифт для письма (например, Arial, sans-serif).', 'subscription-price'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button-primary"><?php _e('Сохранить изменения', 'subscription-price'); ?></button>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Сохранение настроек
     */
    public function save_settings() {
        if (!isset($_POST['woocommerce_subscription_nonce']) || !wp_verify_nonce($_POST['woocommerce_subscription_nonce'], 'save_subscription_settings')) {
            return; // Проверяем nonce для безопасности
        }
        // Сохраняем настройки писем
        update_option('reminder_email_subject', sanitize_text_field($_POST['reminder_email_subject']));
        update_option('reminder_email_body', wp_kses_post($_POST['reminder_email_body']));
        update_option('expired_email_subject', sanitize_text_field($_POST['expired_email_subject']));
        update_option('expired_email_body', wp_kses_post($_POST['expired_email_body']));
        update_option('email_font', sanitize_text_field($_POST['email_font']));

        // Сохраняем роли для активной и истекшей подписки
        $role_active = sanitize_text_field($_POST['role_active'] ?? '');
        $role_expired = sanitize_text_field($_POST['role_expired'] ?? '');

        // Проверяем, чтобы роли активной и истекшей подписки не совпадали
        if ($role_active === $role_expired) {
            wp_redirect(admin_url('admin.php?page=subscription-settings&message=error_roles'));
            exit;
        }

        update_option('subscription_role_active', $role_active);
        update_option('subscription_role_expired', $role_expired);

        // Формируем массив всех подписок
        $all_subscriptions = [];
        for ($i = 1; $i <= 4; $i++) {
            $product_id = absint($_POST["product_{$i}"] ?? 0);

            if ($product_id > 0) {
                $all_subscriptions[] = [
                    'product_id' => $product_id,
                    'duration' => [
                        'years' => absint($_POST["duration_{$i}_years"] ?? 0),
                        'months' => absint($_POST["duration_{$i}_months"] ?? 0),
                        'days' => absint($_POST["duration_{$i}_days"] ?? 0),
                        'hours' => absint($_POST["duration_{$i}_hours"] ?? 0),
                        'minutes' => absint($_POST["duration_{$i}_minutes"] ?? 0),
                    ],
                    'role_active' => $role_active,
                    'role_expired' => $role_expired,
                ];
            }
        }
        // Сохраняем массив подписок в опцию
        update_option('subscription_plans', $all_subscriptions);
        // Перенаправляем пользователя обратно на страницу настроек с уведомлением об успешном сохранении
        wp_redirect(admin_url('admin.php?page=subscription-settings&settings-updated=true'));
        exit;
    }

    /**
     * Получить список ролей
     */
    private function get_roles() {
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        $roles = $wp_roles->roles;
        $formatted_roles = [];

        foreach ($roles as $key => $role) {
            $formatted_roles[$key] = $role['name'];
        }

        return $formatted_roles;
    }
}
