<?php
if (!defined('ABSPATH')) {
    exit; // Запрет прямого доступа
}

class Subscription_Settings {
    private $id;

    public function __construct() {
        $this->id = 'subscription'; // Устанавливаем ID настроек

        // Добавляем страницу в меню "Маркетинг"
        add_action('admin_menu', [$this, 'add_settings_page']);

        // Подключаем скрипты WooCommerce
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Обрабатываем сохранение данных
        add_action('admin_post_save_subscription_settings', [$this, 'save_settings']);
    }
    

    /**
     * Возвращает идентификатор настроек
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Добавить страницу настроек в меню "Маркетинг"
     */
    public function add_settings_page() {
        add_submenu_page(
            'woocommerce-marketing', // Родительское меню "Маркетинг"
            __('Настройки подписки', 'subscription-price'), // Заголовок страницы
            __('Подписки', 'subscription-price'), // Название в меню
            'manage_options', // Право доступа
            'subscription-settings', // Слаг страницы
            [$this, 'render_settings_page'] // Функция рендеринга
        );
    }

    /**
     * Подключение скриптов WooCommerce
     */
    public function enqueue_scripts($hook) {
        // Подключаем скрипты только на нашей странице
        if ($hook === 'woocommerce_page_subscription-settings') {
            if (function_exists('wc_enqueue_js')) {
                wp_enqueue_script('wc-enhanced-select'); // Скрипт для работы wc-product-search
                wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css'); // Стили WooCommerce
            }
        }
    }

    /**
     * Рендеринг страницы настроек
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Настройки подписки', 'subscription-price'); ?></h1>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('save_subscription_settings', 'subscription_nonce'); ?>
                <input type="hidden" name="action" value="save_subscription_settings">
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
                <?php submit_button(__('Сохранить изменения', 'subscription-price')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Сохранение настроек
     */
    public function save_settings() {
        if (!isset($_POST['subscription_nonce']) || !wp_verify_nonce($_POST['subscription_nonce'], 'save_subscription_settings')) {
            wp_die(__('Ошибка проверки данных.', 'subscription-price'));
        }

        for ($i = 1; $i <= 4; $i++) {
            if (isset($_POST["product_{$i}"])) {
                update_option("subscription_{$i}_product", absint($_POST["product_{$i}"]));
            }
            update_option("subscription_{$i}_duration_years", absint($_POST["duration_{$i}_years"] ?? 0));
            update_option("subscription_{$i}_duration_months", absint($_POST["duration_{$i}_months"] ?? 0));
            update_option("subscription_{$i}_duration_days", absint($_POST["duration_{$i}_days"] ?? 0));
        }

        if (isset($_POST['role_active'])) {
            update_option('subscription_role_active', sanitize_text_field($_POST['role_active']));
        }

        if (isset($_POST['role_expired'])) {
            update_option('subscription_role_expired', sanitize_text_field($_POST['role_expired']));
        }

        wp_redirect(add_query_arg('updated', 'true', wp_get_referer()));
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
