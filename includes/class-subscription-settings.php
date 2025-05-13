<?php
if (!defined('ABSPATH')) {
    exit; // Запрет прямого доступа
}

class Subscription_Settings extends WC_Settings_Page {
    public function __construct() {
        $this->id    = 'subscription'; // ID вкладки
        $this->label = __('Подписка', 'subscription-price'); // Заголовок вкладки

        add_filter('woocommerce_settings_tabs_array', [$this, 'add_settings_tab'], 50);
        add_action("woocommerce_settings_{$this->id}", [$this, 'output_settings']);
        add_action("woocommerce_update_options_{$this->id}", [$this, 'save_settings']);
    }

    /**
     * Добавить вкладку в настройки WooCommerce
     */
    public function add_settings_tab($settings_tabs) {
        $settings_tabs[$this->id] = $this->label;
        return $settings_tabs;
    }

    /**
     * Вывод настроек
     */
    public function output_settings() {
        ?>
        <h2><?php _e('Настройки подписки', 'subscription-price'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('save_subscription_settings', 'woocommerce_subscription_nonce'); ?>
            <table class="form-table">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <tr>
                        <th scope="row">
                            <label><?php printf(__('Подписка %d', 'subscription-price'), $i); ?></label>
                        </th>
                        <td>
                            <label><?php _e('Выберите товар:', 'subscription-price'); ?></label>
                            <select id="product_<?php echo $i; ?>" name="product_<?php echo $i; ?>" class="wc-product-search" style="width: 50%;" data-placeholder="<?php esc_attr_e('Введите название товара...', 'subscription-price'); ?>" data-action="woocommerce_json_search_products_and_variations">
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
                            <input type="number" name="duration_<?php echo $i; ?>_years" placeholder="<?php esc_attr_e('Годы', 'subscription-price'); ?>" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_years", 0)); ?>"> <?php _e('год', 'subscription-price'); ?>
                            <input type="number" name="duration_<?php echo $i; ?>_months" placeholder="<?php esc_attr_e('Месяцы', 'subscription-price'); ?>" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_months", 0)); ?>"> <?php _e('мес.', 'subscription-price'); ?>
                            <input type="number" name="duration_<?php echo $i; ?>_days" placeholder="<?php esc_attr_e('Дни', 'subscription-price'); ?>" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_days", 0)); ?>"> <?php _e('дни', 'subscription-price'); ?>
                            <input type="number" name="duration_<?php echo $i; ?>_hours" placeholder="<?php esc_attr_e('Часы', 'subscription-price'); ?>" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_hours", 0)); ?>"> <?php _e('часы', 'subscription-price'); ?>
                            <input type="number" name="duration_<?php echo $i; ?>_minutes" placeholder="<?php esc_attr_e('Минуты', 'subscription-price'); ?>" min="0" style="width: 60px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_minutes", 0)); ?>"> <?php _e('мин.', 'subscription-price'); ?>
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
            <p class="submit">
                <button type="submit" class="button-primary"><?php _e('Сохранить изменения', 'subscription-price'); ?></button>
            </p>
        </form>
        <?php
    }

    /**
     * Сохранение настроек
     */
    public function save_settings() {
        if (!isset($_POST['woocommerce_subscription_nonce']) || !wp_verify_nonce($_POST['woocommerce_subscription_nonce'], 'save_subscription_settings')) {
            error_log('Nonce verification failed.'); // Логирование ошибки
            return;
        }

        error_log('Saving settings...'); // Логирование начала сохранения

        for ($i = 1; $i <= 4; $i++) {
            if (isset($_POST["product_{$i}"])) {
                update_option("subscription_{$i}_product", absint($_POST["product_{$i}"]));
            }
            update_option("subscription_{$i}_duration_years", absint($_POST["duration_{$i}_years"] ?? 0));
            update_option("subscription_{$i}_duration_months", absint($_POST["duration_{$i}_months"] ?? 0));
            update_option("subscription_{$i}_duration_days", absint($_POST["duration_{$i}_days"] ?? 0));
            update_option("subscription_{$i}_duration_hours", absint($_POST["duration_{$i}_hours"] ?? 0));
            update_option("subscription_{$i}_duration_minutes", absint($_POST["duration_{$i}_minutes"] ?? 0));
        }

        // Сохраняем роли
        if (isset($_POST['role_active'])) {
            update_option('subscription_role_active', sanitize_text_field($_POST['role_active']));
        }

        if (isset($_POST['role_expired'])) {
            update_option('subscription_role_expired', sanitize_text_field($_POST['role_expired']));
        }

        error_log('Settings saved successfully.'); // Логирование успешного сохранения
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
