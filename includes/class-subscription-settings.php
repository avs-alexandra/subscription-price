<?php
if (!defined('ABSPATH')) {
    exit; // Запрет прямого доступа
}

class Subscription_Settings extends WC_Settings_Page {
    public function __construct() {
        $this->id    = 'subscription'; // ID вкладки
        $this->label = __('Подписка', 'subscription-price'); // Заголовок вкладки

        // Добавляем вкладку в настройки WooCommerce
        add_filter('woocommerce_settings_tabs_array', [$this, 'add_settings_tab'], 50);
        // Вывод настроек
        add_action("woocommerce_settings_{$this->id}", [$this, 'output_settings']);
        // Сохранение настроек
        add_action("woocommerce_update_options_{$this->id}", [$this, 'save_settings']);
    }

    /**
     * Добавление вкладки в настройки WooCommerce
     */
    public function add_settings_tab($settings_tabs) {
        $settings_tabs[$this->id] = $this->label;
        return $settings_tabs;
    }

    /**
     * Вывод настроек
     */
    public function output_settings() {
        // Используем стандартный механизм WooCommerce для вывода полей
        woocommerce_admin_fields($this->get_settings());
    }

    /**
     * Сохранение настроек
     */
    public function save_settings() {
        // Сохраняем настройки с помощью WooCommerce API
        woocommerce_update_options($this->get_settings());
    }

    /**
     * Определение массива настроек
     */
    public function get_settings() {
        $settings = [];

        // Настройки для подписок
        for ($i = 1; $i <= 4; $i++) {
            $settings[] = [
                'title' => sprintf(__('Подписка %d', 'subscription-price'), $i),
                'type'  => 'title',
                'id'    => "subscription_{$i}_settings",
            ];

            // Поле выбора товара
            $settings[] = [
                'title'    => __('Выберите товар', 'subscription-price'),
                'id'       => "subscription_{$i}_product",
                'type'     => 'single_select_product',
                'desc'     => __('Товар, покупка которого активирует подписку.', 'subscription-price'),
                'default'  => '',
                'css'      => 'width: 50%;',
                'desc_tip' => true,
            ];

            // Поля длительности подписки
            $settings[] = [
                'title'    => __('Длительность подписки (годы)', 'subscription-price'),
                'id'       => "subscription_{$i}_duration_years",
                'type'     => 'number',
                'default'  => '0',
                'css'      => 'width: 60px;',
                'desc'     => __('Введите количество лет.', 'subscription-price'),
            ];

            $settings[] = [
                'title'    => __('Длительность подписки (месяцы)', 'subscription-price'),
                'id'       => "subscription_{$i}_duration_months",
                'type'     => 'number',
                'default'  => '0',
                'css'      => 'width: 60px;',
                'desc'     => __('Введите количество месяцев.', 'subscription-price'),
            ];

            $settings[] = [
                'title'    => __('Длительность подписки (дни)', 'subscription-price'),
                'id'       => "subscription_{$i}_duration_days",
                'type'     => 'number',
                'default'  => '0',
                'css'      => 'width: 60px;',
                'desc'     => __('Введите количество дней.', 'subscription-price'),
            ];

            $settings[] = [
                'type' => 'sectionend',
                'id'   => "subscription_{$i}_settings",
            ];
        }

        // Настройки ролей
        $settings[] = [
            'title' => __('Настройки ролей', 'subscription-price'),
            'type'  => 'title',
            'id'    => 'subscription_roles_settings',
        ];

        $settings[] = [
            'title'    => __('Роль при активной подписке', 'subscription-price'),
            'id'       => 'subscription_role_active',
            'type'     => 'select',
            'options'  => $this->get_roles(),
            'desc'     => __('Роль, которая будет назначена пользователю при активации подписки.', 'subscription-price'),
            'default'  => '',
            'desc_tip' => true,
        ];

        $settings[] = [
            'title'    => __('Роль после завершения подписки', 'subscription-price'),
            'id'       => 'subscription_role_expired',
            'type'     => 'select',
            'options'  => $this->get_roles(),
            'desc'     => __('Роль, которая будет назначена пользователю после завершения подписки.', 'subscription-price'),
            'default'  => '',
            'desc_tip' => true,
        ];

        $settings[] = [
            'type' => 'sectionend',
            'id'   => 'subscription_roles_settings',
        ];

        return $settings;
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
