<?php
if (!defined('ABSPATH')) exit; // Запрет прямого доступа

class SubscriptionSettingsPage {
    public function __construct() {
        // Добавляем страницу настроек в меню "Маркетинг"
        add_action('admin_menu', [$this, 'add_settings_page']);
        // Регистрируем настройки
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Добавляем страницу настроек в меню "Маркетинг"
     */
    public function add_settings_page() {
        add_submenu_page(
            'woocommerce-marketing', // Родительская страница (WooCommerce > Маркетинг)
            __('Настройки подписки', 'subscription-price'), // Заголовок страницы
            __('Подписка', 'subscription-price'), // Название в меню
            'manage_woocommerce', // Требуемые права
            'subscription-settings', // Слаг страницы
            [$this, 'render_settings_page'] // Callback для рендеринга страницы
        );
    }

    /**
     * Регистрируем настройки
     */
    public function register_settings() {
        add_settings_section(
            'subscription_settings_section',
            __('Подписки', 'subscription-price'),
            null,
            'subscription-settings'
        );

        for ($i = 1; $i <= 4; $i++) {
            register_setting('subscription_settings', "subscription_{$i}_product", [
                'sanitize_callback' => 'sanitize_text_field',
            ]);
            register_setting('subscription_settings', "subscription_{$i}_duration_years", [
                'sanitize_callback' => 'absint',
            ]);
            register_setting('subscription_settings', "subscription_{$i}_duration_months", [
                'sanitize_callback' => 'absint',
            ]);
            register_setting('subscription_settings', "subscription_{$i}_duration_days", [
                'sanitize_callback' => 'absint',
            ]);
            register_setting('subscription_settings', "subscription_{$i}_duration_hours", [
                'sanitize_callback' => 'absint',
            ]);
            register_setting('subscription_settings', "subscription_{$i}_duration_minutes", [
                'sanitize_callback' => 'absint',
            ]);
        }

        register_setting('subscription_settings', 'subscription_role_active', [
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        register_setting('subscription_settings', 'subscription_role_expired', [
            'sanitize_callback' => 'sanitize_text_field',
        ]);
    }

    /**
     * Рендеринг страницы настроек
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Настройки подписки', 'subscription-price'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('subscription_settings'); ?>
                <?php do_settings_sections('subscription-settings'); ?>
                <table class="form-table">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <tr valign="top">
                            <th scope="row"><?php printf(__('Подписка %d: Товар', 'subscription-price'), $i); ?></th>
                            <td>
                                <input type="text" name="subscription_<?php echo $i; ?>_product" value="<?php echo esc_attr(get_option("subscription_{$i}_product", '')); ?>" class="regular-text" />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php printf(__('Подписка %d: Длительность (Годы/Месяцы/Дни/Часы/Минуты)', 'subscription-price'), $i); ?></th>
                            <td>
                                <input type="number" name="subscription_<?php echo $i; ?>_duration_years" placeholder="<?php esc_attr_e('Годы', 'subscription-price'); ?>" min="0" style="width: 80px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_years", 0)); ?>"> 
                                <input type="number" name="subscription_<?php echo $i; ?>_duration_months" placeholder="<?php esc_attr_e('Месяцы', 'subscription-price'); ?>" min="0" style="width: 80px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_months", 0)); ?>"> 
                                <input type="number" name="subscription_<?php echo $i; ?>_duration_days" placeholder="<?php esc_attr_e('Дни', 'subscription-price'); ?>" min="0" style="width: 80px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_days", 0)); ?>"> 
                                <input type="number" name="subscription_<?php echo $i; ?>_duration_hours" placeholder="<?php esc_attr_e('Часы', 'subscription-price'); ?>" min="0" style="width: 80px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_hours", 0)); ?>"> 
                                <input type="number" name="subscription_<?php echo $i; ?>_duration_minutes" placeholder="<?php esc_attr_e('Минуты', 'subscription-price'); ?>" min="0" style="width: 80px;" value="<?php echo esc_attr(get_option("subscription_{$i}_duration_minutes", 0)); ?>">
                            </td>
                        </tr>
                    <?php endfor; ?>
                    <tr valign="top">
                        <th scope="row"><?php _e('Роль при активной подписке', 'subscription-price'); ?></th>
                        <td>
                            <input type="text" name="subscription_role_active" value="<?php echo esc_attr(get_option('subscription_role_active', '')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Роль после завершения подписки', 'subscription-price'); ?></th>
                        <td>
                            <input type="text" name="subscription_role_expired" value="<?php echo esc_attr(get_option('subscription_role_expired', '')); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
