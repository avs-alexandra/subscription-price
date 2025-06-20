<?php
if (!defined('ABSPATH')) exit; // Запрет прямого доступа

class Subprice_Settings {
    public function __construct() {
        // Добавляем настройки в раздел "Маркетинг"
        add_action('admin_menu', [$this, 'subprice_add_marketing_submenu']);
        add_action("admin_post_save_subprice_subscription_settings", [$this, 'subprice_save_settings']);
        add_action('admin_enqueue_scripts', [$this, 'subprice_enqueue_wc_scripts']);
        add_action('admin_notices', [$this, 'subprice_show_admin_notices']); // Для уведомлений
    }

    /**
     * Подключение скриптов WooCommerce для работы wc-product-search
     */
    public function subprice_enqueue_wc_scripts() {
        if (isset($_GET['page']) && $_GET['page'] === 'subprice-subscription-settings') {
            wp_enqueue_script('woocommerce_admin'); 
            wp_enqueue_script('wc-enhanced-select');
            wp_enqueue_style('woocommerce_admin_styles');
        }
    }

    /**
     * Добавить пункт меню в раздел "Маркетинг"
     */
    public function subprice_add_marketing_submenu() {
        add_submenu_page(
            'woocommerce-marketing',
            esc_html__('Настройки Подписки', 'subscription-price'),
            esc_html__('Подписка', 'subscription-price'),
            'manage_options',
            'subprice-subscription-settings',
            [$this, 'subprice_output_settings']
        );
    }

    /**
     * Показ уведомлений в админке
     */
    public function subprice_show_admin_notices() {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html__('Настройки сохранены.', 'subscription-price') . '</p>';
            echo '</div>';
        }

        if (!empty($_GET['message']) && $_GET['message'] === 'error_roles') {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . esc_html__('Роли активной и истекшей подписки не могут совпадать!', 'subscription-price') . '</p>';
            echo '</div>';
        }
    }

    /**
     * Вывод настроек
     */
    public function subprice_output_settings() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Настройки Подписки', 'subscription-price'); ?></h1>
            <p style="font-size: 14px; margin-bottom: 16px;">
            <?php esc_html_e('Шорткод для отображения даты окончания подписки пользователя [subprice_status]', 'subscription-price'); ?>
            </p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('save_subprice_subscription_settings', 'subprice_woocommerce_subscription_nonce'); ?>
                <input type="hidden" name="action" value="save_subprice_subscription_settings">
                <!-- Поля для настройки подписок -->
                <h2><?php esc_html_e('Настройки подписок', 'subscription-price'); ?></h2>
                <table class="form-table">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <tr>
                            <th scope="row">
                            <?php
                                /* translators: %d: subscription number */
                                printf('<label>%s</label>', esc_html(sprintf(__('Подписка %d', 'subscription-price'), $i)));
                            ?>
                            </th>
                            <td>
                            <label><?php esc_html_e('Выберите товар:', 'subscription-price'); ?></label>
                            <select id="product_<?php echo esc_attr($i); ?>" name="product_<?php echo esc_attr($i); ?>" class="wc-product-search" style="width: 50%;" data-placeholder="<?php esc_attr_e('Выберите товар', 'subscription-price'); ?>" data-action="woocommerce_json_search_products_and_variations">
                                <option value=""><?php esc_html_e('Не привязан', 'subscription-price'); ?></option>
                                <?php
                                $product_id = get_option("subprice_subscription_{$i}_product", '');
                                if ($product_id) {
                                    $product = wc_get_product($product_id);
                                    if ($product) {
                                        echo '<option value="' . esc_attr($product_id) . '" selected="selected">' . esc_html($product->get_name()) . '</option>';
                                    }
                                }
                                ?>
                                </select>
                                <br><br>
                                <label><?php esc_html_e('Длительность подписки:', 'subscription-price'); ?></label><br>
                                <label>
                                    <?php esc_html_e('Годы:', 'subscription-price'); ?>
                                    <input type="number" name="duration_<?php echo esc_attr($i); ?>_years" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subprice_subscription_{$i}_duration_years", 0)); ?>">
                                </label>
                                <label>
                                    <?php esc_html_e('Месяцы:', 'subscription-price'); ?>
                                    <input type="number" name="duration_<?php echo esc_attr($i); ?>_months" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subprice_subscription_{$i}_duration_months", 0)); ?>">
                                </label>
                                <label>
                                    <?php esc_html_e('Дни:', 'subscription-price'); ?>
                                    <input type="number" name="duration_<?php echo esc_attr($i); ?>_days" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subprice_subscription_{$i}_duration_days", 0)); ?>">
                                </label>
                                <label>
                                    <?php esc_html_e('Часы:', 'subscription-price'); ?>
                                    <input type="number" name="duration_<?php echo esc_attr($i); ?>_hours" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subprice_subscription_{$i}_duration_hours", 0)); ?>">
                                </label>
                                <label>
                                    <?php esc_html_e('Минуты:', 'subscription-price'); ?>
                                   <input type="number" name="duration_<?php echo esc_attr($i); ?>_minutes" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subprice_subscription_{$i}_duration_minutes", 0)); ?>">
                                </label>
                                <br><br>
                                 <!-- Кнопка для отвязки товара -->
                            <button type="submit" name="detach_subscription_<?php echo esc_attr($i); ?>" class="button-secondary" value="1"><?php esc_html_e('Отвязать товар', 'subscription-price'); ?></button>
                            </td>
                        </tr>
                    <?php endfor; ?>
                    <tr>
                        <th scope="row">
                            <label for="role_active"><?php esc_html_e('Роль при активной подписке', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <select id="role_active" name="role_active" required>
                                <?php foreach ($this->subprice_get_roles() as $role_key => $role_name): ?>
                                    <option value="<?php echo esc_attr($role_key); ?>" <?php selected(get_option('subprice_subscription_role_active'), $role_key); ?>>
                                        <?php echo esc_html($role_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="role_expired"><?php esc_html_e('Роль после завершения подписки', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <select id="role_expired" name="role_expired" required>
                                <?php foreach ($this->subprice_get_roles() as $role_key => $role_name): ?>
                                    <option value="<?php echo esc_attr($role_key); ?>" <?php selected(get_option('subprice_subscription_role_expired'), $role_key); ?>>
                                        <?php echo esc_html($role_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                 <!-- Поля для настройки писем -->
                <h2><?php esc_html_e('Настройки писем', 'subscription-price'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="subprice_reminder_email_subject"><?php esc_html_e('Тема письма (напоминание)', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="subprice_reminder_email_subject" name="subprice_reminder_email_subject" style="width: 70%;" value="<?php echo esc_attr(get_option('subprice_reminder_email_subject', esc_html__('Ваша подписка скоро завершится', 'subscription-price'))); ?>">
                            <p class="description"><?php esc_html_e('Письмо-напоминание отправляется за 3 дня до окончания подписки.', 'subscription-price'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="subprice_reminder_email_body"><?php esc_html_e('Текст письма (напоминание)', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <?php
                            $reminder_email_body = get_option('subprice_reminder_email_body', esc_html__('Здравствуйте, [user]! Ваша подписка закончится [end_date].', 'subscription-price'));
                            wp_editor($reminder_email_body, 'subprice_reminder_email_body', [
                                'textarea_name' => 'subprice_reminder_email_body',
                                'textarea_rows' => 2,
                                'media_buttons' => true,
                            ]);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="subprice_expired_email_subject"><?php esc_html_e('Тема письма (завершение)', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="subprice_expired_email_subject" name="subprice_expired_email_subject" style="width: 70%;" value="<?php echo esc_attr(get_option('subprice_expired_email_subject', esc_html__('Ваша подписка завершилась', 'subscription-price'))); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="subprice_expired_email_body"><?php esc_html_e('Текст письма (завершение)', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <?php
                            $expired_email_body = get_option('subprice_expired_email_body', esc_html__('Здравствуйте, [user]! Ваша подписка завершилась.', 'subscription-price'));
                            wp_editor($expired_email_body, 'subprice_expired_email_body', [
                                'textarea_name' => 'subprice_expired_email_body',
                                'textarea_rows' => 2,
                                'media_buttons' => true,
                            ]);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="subprice_email_font"><?php esc_html_e('Шрифт письма', 'subscription-price'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="subprice_email_font" name="subprice_email_font" style="width: 50%;" value="<?php echo esc_attr(get_option('subprice_email_font', 'Arial, sans-serif')); ?>">
                            <p class="description"><?php esc_html_e('Укажите шрифт для письма (например, Arial, sans-serif).', 'subscription-price'); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button-primary"><?php esc_html_e('Сохранить изменения', 'subscription-price'); ?></button>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Сохранение настроек
     */
   public function subprice_save_settings() {
    if (
        !isset($_POST['subprice_woocommerce_subscription_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['subprice_woocommerce_subscription_nonce'])),
            'save_subprice_subscription_settings'
        )
    ) {
        return; // Проверяем nonce для безопасности
    }

    // Сохраняем настройки подписок
    for ($i = 1; $i <= 4; $i++) {
    if (
        isset($_POST["detach_subscription_{$i}"]) &&
        absint(wp_unslash($_POST["detach_subscription_{$i}"])) === 1
    ) {
        delete_option("subprice_subscription_{$i}_product");
        delete_option("subprice_subscription_{$i}_duration_years");
        delete_option("subprice_subscription_{$i}_duration_months");
        delete_option("subprice_subscription_{$i}_duration_days");
        delete_option("subprice_subscription_{$i}_duration_hours");
        delete_option("subprice_subscription_{$i}_duration_minutes");
        continue;
    }

        $product_id = isset($_POST["product_{$i}"]) ? absint(wp_unslash($_POST["product_{$i}"])) : 0;

        // Если выбран конкретный товар, сохраняем его параметры
        if ($product_id > 0) {
            update_option("subprice_subscription_{$i}_product", $product_id);
            update_option("subprice_subscription_{$i}_duration_years", absint(wp_unslash($_POST["duration_{$i}_years"] ?? 0)));
            update_option("subprice_subscription_{$i}_duration_months", absint(wp_unslash($_POST["duration_{$i}_months"] ?? 0)));
            update_option("subprice_subscription_{$i}_duration_days", absint(wp_unslash($_POST["duration_{$i}_days"] ?? 0)));
            update_option("subprice_subscription_{$i}_duration_hours", absint(wp_unslash($_POST["duration_{$i}_hours"] ?? 0)));
            update_option("subprice_subscription_{$i}_duration_minutes", absint(wp_unslash($_POST["duration_{$i}_minutes"] ?? 0)));
        }
    }

    // Собираем subscription_plans прямо из $_POST, а не через get_option!
    $subscription_plans = [];
    for ($i = 1; $i <= 4; $i++) {
        $product_id = isset($_POST["product_{$i}"]) ? absint(wp_unslash($_POST["product_{$i}"])) : 0;
        if ($product_id > 0) {
            $subscription_plans[] = [
                'product_id' => $product_id,
                'duration' => [
                    'years'   => absint(wp_unslash($_POST["duration_{$i}_years"] ?? 0)),
                    'months'  => absint(wp_unslash($_POST["duration_{$i}_months"] ?? 0)),
                    'days'    => absint(wp_unslash($_POST["duration_{$i}_days"] ?? 0)),
                    'hours'   => absint(wp_unslash($_POST["duration_{$i}_hours"] ?? 0)),
                    'minutes' => absint(wp_unslash($_POST["duration_{$i}_minutes"] ?? 0)),
                ],
                'role_active'  => sanitize_text_field(wp_unslash($_POST['role_active'] ?? '')),
                'role_expired' => sanitize_text_field(wp_unslash($_POST['role_expired'] ?? '')),
            ];
        }
    }
    update_option('subprice_subscription_plans', $subscription_plans);

    // Сохраняем другие параметры
update_option('subprice_reminder_email_subject', sanitize_text_field(wp_unslash($_POST['subprice_reminder_email_subject'] ?? '')));
update_option('subprice_reminder_email_body', wp_kses_post(wp_unslash($_POST['subprice_reminder_email_body'] ?? '')));
update_option('subprice_expired_email_subject', sanitize_text_field(wp_unslash($_POST['subprice_expired_email_subject'] ?? '')));
update_option('subprice_expired_email_body', wp_kses_post(wp_unslash($_POST['subprice_expired_email_body'] ?? '')));
update_option('subprice_email_font', sanitize_text_field(wp_unslash($_POST['subprice_email_font'] ?? '')));

    // Сохраняем роли для активной и истекшей подписки
    $role_active = sanitize_text_field(wp_unslash($_POST['role_active'] ?? ''));
    $role_expired = sanitize_text_field(wp_unslash($_POST['role_expired'] ?? ''));

    // Проверяем, чтобы роли активной и истекшей подписки не совпадали
    if ($role_active === $role_expired) {
        wp_redirect(admin_url('admin.php?page=subprice-subscription-settings&message=error_roles'));
        exit;
    }

    update_option('subprice_subscription_role_active', $role_active);
    update_option('subprice_subscription_role_expired', $role_expired);

    // Перенаправляем пользователя обратно на страницу настроек с уведомлением об успешном сохранении
    wp_redirect(admin_url('admin.php?page=subprice-subscription-settings&settings-updated=true'));
    exit;
}

    /**
     * Получить список ролей
     */
    private function subprice_get_roles() {
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
