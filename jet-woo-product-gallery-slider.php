<?php
/**
 * Class: Jet_Woo_Product_Gallery_Slider
 * Name: Gallery Slider
 * Slug: jet-woo-product-gallery-slider
 */

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Woo_Product_Gallery_Slider extends Jet_Gallery_Widget_Base {

	public function get_name() {
		return 'jet-woo-product-gallery-slider';
	}

	public function get_title() {
		return esc_html__( 'Gallery Slider', 'jet-woo-product-gallery' );
	}

	public function get_script_depends() {
		return [ 'jet-swiper', 'zoom', 'wc-single-product', 'mediaelement', 'photoswipe-ui-default', 'photoswipe' ];
	}

	/**
	 * Get style dependencies.
	 *
	 * Retrieve the list of style dependencies the widget requires.
	 *
	 * @since 1.0.0
	 * @since 2.1.19 Added widget styles.
	 *
	 * @return array Widget style dependencies.
	 */
	public function get_style_depends(): array {
		return [ 'jet-gallery-widget-gallery-slider' ];
	}

	public function get_icon() {
		return 'jet-gallery-icon-slider';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/how-to-create-a-horizontal-product-images-slider-with-jetproductgallery/';
	}

	public function get_categories() {
		return array( 'jet-woo-product-gallery' );
	}

	public function register_gallery_content_controls() {

		$this->start_controls_section(
			'section_product_images',
			[
				'tab'   => Controls_Manager::TAB_CONTENT,
				'label' => __( 'Images', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'image_size',
			[
				'type'    => Controls_Manager::SELECT,
				'label'   => __( 'Image Size', 'jet-woo-product-gallery' ),
				'options' => jet_woo_product_gallery_tools()->get_image_sizes(),
			]
		);

		$this->add_control(
			'thumbs_image_size',
			[
				'type'    => Controls_Manager::SELECT,
				'label'   => __( 'Thumbnails Size', 'jet-woo-product-gallery' ),
				'options' => jet_woo_product_gallery_tools()->get_image_sizes(),
			]
		);

		$this->end_controls_section();

		$css_scheme = apply_filters(
			'jet-woo-product-gallery-slider/css-scheme',
			[
				'slider'             => '.jet-woo-product-gallery-slider',
				'slider-item'        => '.jet-woo-product-gallery-slider .jet-woo-product-gallery__image-item',
				'images'             => '.jet-woo-product-gallery-slider .jet-woo-product-gallery__image',
				'images-arrows'      => '.jet-woo-product-gallery-slider .jet-swiper-nav',
				'pagination'         => '.swiper-pagination',
				'bullet'             => '.swiper-pagination .swiper-pagination-bullet',
				'bullet-active'      => '.swiper-pagination .swiper-pagination-bullet-active',
				'thumbnails-wrapper' => '.jet-woo-swiper-gallery-thumbs',
				'thumbnails-list'    => '.jet-woo-swiper-gallery-thumbs .swiper-wrapper',
				'thumbnails-slide'   => '.jet-woo-swiper-gallery-thumbs .swiper-slide',
				'thumbnails'         => '.jet-woo-swiper-control-thumbs__item',
				'thumbnails-arrows'  => '.jet-woo-swiper-gallery-thumbs .jet-swiper-nav',
			]
		);

		$this->register_controls_slider( $css_scheme );

		$this->register_controls_images_styles( $css_scheme );

		$this->register_controls_thumbnails_styles( $css_scheme );

		$this->register_controls_pagination_styles( $css_scheme );

	}

	public function register_controls_slider( $css_scheme ) {

		$this->start_controls_section(
			'section_slider_style',
			[
				'tab'   => Controls_Manager::TAB_CONTENT,
				'label' => __( 'Slider', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'slider_pagination_direction',
			[
				'type'    => Controls_Manager::SELECT,
				'label'   => __( 'Direction:', 'jet-woo-product-gallery' ),
				'default' => 'horizontal',
				'options' => [
					'vertical'   => __( 'Vertical', 'jet-woo-product-gallery' ),
					'horizontal' => __( 'Horizontal', 'jet-woo-product-gallery' ),
				],
			]
		);

		$this->add_responsive_control(
			'slider_vertical_height',
			[
				'type'        => Controls_Manager::SLIDER,
				'label'       => __( 'Height', 'jet-woo-product-gallery' ),
				'render_type' => 'template',
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min' => 100,
						'max' => 2000,
					],
				],
				'default'     => [
					'size' => 400,
					'unit' => 'px',
				],
				'selectors'   => [
					'{{WRAPPER}} ' . $css_scheme['slider'] . '.swiper-container-vertical' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'   => [
					'slider_pagination_direction' => 'vertical',
				],
			]
		);

		$this->add_control(
			'slider_enable_infinite_loop',
			[
				'type'  => Controls_Manager::SWITCHER,
				'label' => __( 'Enable Loop', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'slider_equal_slides_height',
			[
				'type'      => Controls_Manager::SWITCHER,
				'label'     => __( 'Enable Equal Slides Height', 'jet-woo-product-gallery' ),
				'condition' => [
					'slider_pagination_direction!' => 'vertical',
				],
			]
		);

		$this->add_control(
			'slider_sensitivity',
			[
				'type'    => Controls_Manager::NUMBER,
				'label'   => __( 'Swipes Sensitivity', 'jet-woo-product-gallery' ),
				'min'     => 0,
				'max'     => 1,
				'step'    => 0.1,
				'default' => 0.8,
			]
		);

		$this->add_control(
			'slider_enable_center_mode',
			[
				'type'      => Controls_Manager::SWITCHER,
				'label'     => __( 'Enable Centered Slides', 'jet-woo-product-gallery' ),
				'condition' => [
					'slider_pagination_direction!' => 'vertical',
				],
			]
		);

		$this->add_responsive_control(
			'slider_center_mode_slides',
			[
				'type'               => Controls_Manager::SELECT,
				'label'              => __( 'Slides Per View', 'jet-woo-product-gallery' ),
				'options'            => jet_woo_product_gallery_tools()->get_select_range(),
				'default'            => 4,
				'frontend_available' => true,
				'condition'          => [
					'slider_enable_center_mode!'   => '',
					'slider_pagination_direction!' => 'vertical',
				],
			]
		);

		$this->add_responsive_control(
			'slider_center_mode_space_between',
			[
				'type'               => Controls_Manager::NUMBER,
				'label'              => __( 'Space Between', 'jet-woo-product-gallery' ),
				'min'                => 0,
				'default'            => 10,
				'frontend_available' => true,
				'condition'          => [
					'slider_enable_center_mode!'   => '',
					'slider_pagination_direction!' => 'vertical',
				],
			]
		);

		$this->add_control(
			'slider_transition_effect',
			[
				'type'      => Controls_Manager::SELECT,
				'label'     => __( 'Transition Effect', 'jet-woo-product-gallery' ),
				'options'   => [
					'slide'     => __( 'Slide', 'jet-woo-product-gallery' ),
					'fade'      => __( 'Fade', 'jet-woo-product-gallery' ),
					'cube'      => __( 'Cube', 'jet-woo-product-gallery' ),
					'coverflow' => __( 'Coverflow', 'jet-woo-product-gallery' ),
					'flip'      => __( 'Flip', 'jet-woo-product-gallery' ),
				],
				'default'   => 'slide',
				'condition' => [
					'slider_enable_center_mode' => '',
				],
			]
		);

		$this->add_control(
			'slider_nav_heading',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => __( 'Navigation', 'jet-woo-product-gallery' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'slider_show_nav',
			[
				'type'    => Controls_Manager::SWITCHER,
				'label'   => __( 'Enable', 'jet-woo-product-gallery' ),
				'default' => 'yes',
			]
		);

		$this->__add_advanced_icon_control(
			'slider_nav_arrow_prev',
			[
				'type'        => Controls_Manager::ICON,
				'label'       => __( 'Arrow Previous', 'jet-woo-product-gallery' ),
				'label_block' => true,
				'file'        => '',
				'default'     => ! is_rtl() ? 'fa fa-angle-left' : 'fa fa-angle-right',
				'fa5_default' => [
					'value'   => ! is_rtl() ? 'fas fa-angle-left' : 'fas fa-angle-right',
					'library' => 'fa-solid',
				],
				'condition'   => [
					'slider_show_nav!' => '',
				],
			]
		);

		$this->__add_advanced_icon_control(
			'slider_nav_arrow_next',
			[
				'type'        => Controls_Manager::ICON,
				'label'       => __( 'Arrow Next', 'jet-woo-product-gallery' ),
				'label_block' => true,
				'file'        => '',
				'default'     => ! is_rtl() ? 'fa fa-angle-right' : 'fa fa-angle-left',
				'fa5_default' => [
					'value'   => ! is_rtl() ? 'fas fa-angle-right' : 'fas fa-angle-left',
					'library' => 'fa-solid',
				],
				'condition'   => [
					'slider_show_nav!' => '',
				],
			]
		);

		$this->add_control(
			'slider_pagination_heading',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => __( 'Pagination', 'jet-woo-product-gallery' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'slider_show_pagination',
			[
				'type'    => Controls_Manager::SWITCHER,
				'label'   => __( 'Enable', 'jet-woo-product-gallery' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'slider_pagination_type',
			[
				'type'      => Controls_Manager::CHOOSE,
				'label'     => __( 'Pagination Type', 'jet-woo-product-gallery' ),
				'options'   => [
					'bullets'    => [
						'title' => __( 'Pagination', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-navigation-horizontal',
					],
					'thumbnails' => [
						'title' => __( 'Thumbnails', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-image',
					],
				],
				'default'   => 'bullets',
				'condition' => [
					'slider_show_pagination!' => '',
				],
			]
		);

		$this->add_control(
			'slider_pagination_controller_type',
			[
				'type'      => Controls_Manager::SELECT,
				'label'     => __( 'Controller Type', 'jet-woo-product-gallery' ),
				'options'   => [
					'bullets'     => __( 'Bullets', 'jet-woo-product-gallery' ),
					'fraction'    => __( 'Fraction', 'jet-woo-product-gallery' ),
					'progressbar' => __( 'Progressbar', 'jet-woo-product-gallery' ),
				],
				'default'   => 'bullets',
				'condition' => [
					'slider_show_pagination!' => '',
					'slider_pagination_type'  => 'bullets',
				],
			]
		);

		$this->add_control(
			'slider_pagination_bullets_dynamic',
			[
				'type'      => Controls_Manager::SWITCHER,
				'label'     => __( 'Dynamic Bullets', 'jet-woo-product-gallery' ),
				'condition' => [
					'slider_show_pagination!'           => '',
					'slider_pagination_type'            => 'bullets',
					'slider_pagination_controller_type' => 'bullets',
				],
			]
		);

		$this->add_responsive_control(
			'slider_pagination_v_height',
			[
				'type'        => Controls_Manager::SLIDER,
				'label'       => __( 'Pagination Height', 'jet-woo-product-gallery' ),
				'render_type' => 'template',
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min' => 100,
						'max' => 2000,
					],
				],
				'default'     => [
					'size' => 400,
					'unit' => 'px',
				],
				'selectors'   => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-wrapper'] . '.swiper-container-vertical' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'   => [
					'slider_show_pagination!'     => '',
					'slider_pagination_type'      => 'thumbnails',
					'slider_pagination_direction' => 'vertical',
				],
			]
		);

		$this->add_control(
			'slider_pagination_v_position',
			[
				'label'     => __( 'Position', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'start',
				'options'   => [
					'start' => [
						'title' => __( 'Start', 'jet-woo-product-gallery' ),
						'icon'  => ! is_rtl() ? 'eicon-align-start-h' : 'eicon-align-end-h',
					],
					'end'   => [
						'title' => __( 'End', 'jet-woo-product-gallery' ),
						'icon'  => ! is_rtl() ? 'eicon-align-end-h' : 'eicon-align-start-h',
					],
				],
				'condition' => [
					'slider_show_pagination'      => 'yes',
					'slider_pagination_direction' => 'vertical',
				],
			]
		);

		$this->add_control(
			'slider_pagination_h_position',
			[
				'label'     => __( 'Position', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'bottom',
				'options'   => [
					'top'    => [
						'title' => __( 'Top', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-align-start-v',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-align-end-v',
					],
				],
				'condition' => [
					'slider_show_pagination'      => 'yes',
					'slider_pagination_direction' => 'horizontal',
				],
			]
		);

		$this->add_control(
			'slider_pagination_thumbnails_heading',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => __( 'Thumbnails', 'jet-woo-product-gallery' ),
				'separator' => 'before',
				'condition' => [
					'slider_show_pagination!' => '',
					'slider_pagination_type'  => 'thumbnails',
				],
			]
		);

		$this->add_control(
			'slider_show_thumb_nav',
			[
				'type'      => Controls_Manager::SWITCHER,
				'label'     => __( 'Show Navigation', 'jet-woo-product-gallery' ),
				'condition' => [
					'slider_show_pagination!' => '',
					'slider_pagination_type'  => 'thumbnails',
				],
			]
		);

		$this->__add_advanced_icon_control(
			'pagination_thumbnails_slider_arrow_prev',
			array(
				'label'       => esc_html__( 'Arrow Previous', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => ! is_rtl() ? 'fa fa-angle-left' : 'fa fa-angle-right',
				'fa5_default' => array(
					'value'   => ! is_rtl() ? 'fas fa-angle-left' : 'fas fa-angle-right',
					'library' => 'fa-solid',
				),
				'condition'   => array(
					'slider_show_pagination' => 'yes',
					'slider_show_thumb_nav'  => 'yes',
					'slider_pagination_type' => 'thumbnails',
				),
			)
		);

		$this->__add_advanced_icon_control(
			'pagination_thumbnails_slider_arrow_next',
			[
				'type'        => Controls_Manager::ICON,
				'label'       => __( 'Arrow Next', 'jet-woo-product-gallery' ),
				'label_block' => true,
				'file'        => '',
				'default'     => ! is_rtl() ? 'fa fa-angle-right' : 'fa fa-angle-left',
				'fa5_default' => [
					'value'   => ! is_rtl() ? 'fas fa-angle-right' : 'fas fa-angle-left',
					'library' => 'fa-solid',
				],
				'condition'   => [
					'slider_show_pagination!' => '',
					'slider_show_thumb_nav!'  => '',
					'slider_pagination_type'  => 'thumbnails',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_thumbnails_columns',
			[
				'type'               => Controls_Manager::SELECT,
				'label'              => __( 'Slides Per View', 'jet-woo-product-gallery' ),
				'default'            => 4,
				'frontend_available' => true,
				'options'            => jet_woo_product_gallery_tools()->get_select_range( 12 ),
				'selectors'          => [
					'{{WRAPPER}} .jet-gallery-swiper-thumb .swiper-slide' => '--columns: {{VALUE}}',
				],
				'condition'          => [
					'slider_show_pagination!' => '',
					'slider_pagination_type'  => 'thumbnails',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_thumbnails_space_between',
			[
				'type'               => Controls_Manager::NUMBER,
				'label'              => __( 'Space Between', 'jet-woo-product-gallery' ),
				'min'                => 0,
				'default'            => 10,
				'frontend_available' => true,
				'selectors'          => [
					'{{WRAPPER}} .jet-gallery-swiper-thumb .swiper-slide' => '--space-between: {{VALUE}}px',
				],
				'condition'          => [
					'slider_show_pagination!' => '',
					'slider_pagination_type'  => 'thumbnails',
				],
			]
		);

		$this->end_controls_section();

	}

	public function register_controls_images_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_images_style',
			[
				'label' => __( 'Images', 'jet-woo-product-gallery' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'images_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['images'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'images_border',
				'selector' => '{{WRAPPER}} ' . $css_scheme['images'] . ' img',
			]
		);

		$this->add_responsive_control(
			'images_border_radius',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images'] . ' img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'images_alignment',
			[
				'label'     => __( 'Image Alignment', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => __( 'Left', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['images'] => 'text-align: {{VALUE}};',
				],
				'classes'   => 'elementor-control-align',
			]
		);

		$this->add_control(
			'images_arrows_style_heading',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => __( 'Prev/Next Arrows', 'jet-woo-product-gallery' ),
				'separator' => 'before',
				'condition' => [
					'slider_show_nav' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'images_arrows_size',
			array(
				'label'     => esc_html__( 'Size', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 6,
						'max' => 80,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'slider_show_nav' => 'yes',
				),
			)
		);

		$this->start_controls_tabs(
			'tabs_images_arrows_style',
			[
				'condition' => [
					'slider_show_nav' => 'yes',
				],
			]
		);

		$this->start_controls_tab(
			'images_arrows_normal',
			array(
				'label' => esc_html__( 'Normal', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'images_arrows_normal_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'images_arrows_normal_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'images_arrows_hover',
			array(
				'label' => esc_html__( 'Hover', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'images_arrows_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . ':hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'images_arrows_hover_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . ':hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'images_arrows_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . ':hover' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'images_arrows_border_border!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'images_arrows_disabled',
			array(
				'label' => esc_html__( 'Disabled', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'images_arrows_disabled_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.swiper-button-disabled' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'images_arrows_disabled_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.swiper-button-disabled' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'images_arrows_disabled_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.swiper-button-disabled' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'images_arrows_border_border!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'images_arrows_border',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} ' . $css_scheme['images-arrows'],
				'condition' => [
					'slider_show_nav' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'images_arrows_border_radius',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'slider_show_nav' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'images_arrows_padding',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'slider_show_nav' => 'yes',
				],
			]
		);

		$this->add_control(
			'images_prev_arrow_heading',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => __( 'Prev Arrow', 'jet-woo-product-gallery' ),
				'separator' => 'before',
				'condition' => [
					'slider_show_nav' => 'yes',
				],
			]
		);

		$this->add_control(
			'images_prev_arrow_v_position',
			[
				'type'                 => Controls_Manager::SELECT,
				'label'                => __( 'Vertical Position by: ', 'jet-woo-product-gallery' ),
				'default'              => 'center',
				'options'              => [
					'top'    => __( 'Top', 'jet-woo-product-gallery' ),
					'center' => __( 'Center', 'jet-woo-product-gallery' ),
					'bottom' => __( 'Bottom', 'jet-woo-product-gallery' ),
				],
				'selectors_dictionary' => [
					'top'    => 'top: 0; bottom: auto; transform: translate(0,0);',
					'center' => 'top: 50%; bottom: auto; transform: translate(0,-50%);',
					'bottom' => 'top: auto; bottom: 0; transform: translate(0,0);',
				],
				'selectors'            => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-prev' => '{{VALUE}}',
				],
				'condition'            => [
					'slider_show_nav' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'images_prev_arrow_top_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Top Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-prev' => 'top: {{SIZE}}{{UNIT}}; bottom: auto;',
				],
				'condition'  => [
					'slider_show_nav'              => 'yes',
					'images_prev_arrow_v_position' => 'top',
				],
			]
		);

		$this->add_responsive_control(
			'images_prev_arrow_bottom_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Bottom Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-prev' => 'bottom: {{SIZE}}{{UNIT}}; top: auto;',
				],
				'condition'  => [
					'slider_show_nav'              => 'yes',
					'images_prev_arrow_v_position' => 'bottom',
				],
			]
		);

		$this->add_control(
			'images_prev_arrow_h_position',
			[
				'type'                 => Controls_Manager::SELECT,
				'label'                => __( 'Horizontal Position by: ', 'jet-woo-product-gallery' ),
				'default'              => ! is_rtl() ? 'left' : 'right',
				'options'              => [
					'left'   => __( 'Left', 'jet-woo-product-gallery' ),
					'center' => __( 'Center', 'jet-woo-product-gallery' ),
					'right'  => __( 'Right', 'jet-woo-product-gallery' ),
				],
				'selectors_dictionary' => [
					'left'   => 'right: auto;',
					'center' => 'left: 50%; right: auto; transform: translate(-50%, 0);',
					'right'  => 'left: auto;',
				],
				'selectors'            => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-prev' => '{{VALUE}}',
				],
				'condition'            => [
					'slider_show_nav' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'images_prev_arrow_left_position',
			[
				'label'      => __( 'Left Indent', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-prev' => 'left: {{SIZE}}{{UNIT}}; right: auto;',
				],
				'condition'  => [
					'slider_show_nav'              => 'yes',
					'images_prev_arrow_h_position' => 'left',
				],
			]
		);

		$this->add_responsive_control(
			'images_prev_arrow_right_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Right Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-prev' => 'right: {{SIZE}}{{UNIT}}; left: auto;',
				],
				'condition'  => [
					'slider_show_nav'              => 'yes',
					'images_prev_arrow_h_position' => 'right',
				],
			]
		);

		$this->add_control(
			'images_next_arrow_heading',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => __( 'Next Arrow', 'jet-woo-product-gallery' ),
				'separator' => 'before',
				'condition' => [
					'slider_show_nav' => 'yes',
				],
			]
		);

		$this->add_control(
			'images_next_arrow_v_position',
			[
				'type'                 => Controls_Manager::SELECT,
				'label'                => __( 'Vertical Position by: ', 'jet-woo-product-gallery' ),
				'default'              => 'center',
				'options'              => [
					'top'    => __( 'Top', 'jet-woo-product-gallery' ),
					'center' => __( 'Center', 'jet-woo-product-gallery' ),
					'bottom' => __( 'Bottom', 'jet-woo-product-gallery' ),
				],
				'selectors_dictionary' => [
					'top'    => 'top: 0; bottom: auto; transform: translate(0,0);',
					'center' => 'top: 50%; bottom: auto; transform: translate(0,-50%);',
					'bottom' => 'top: auto; bottom: 0; transform: translate(0,0);',
				],
				'selectors'            => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-next' => '{{VALUE}}',
				],
				'condition'            => [
					'slider_show_nav' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'images_next_arrow_top_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Top Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-next' => 'top: {{SIZE}}{{UNIT}}; bottom: auto;',
				],
				'condition'  => [
					'slider_show_nav'              => 'yes',
					'images_next_arrow_v_position' => 'top',
				],
			]
		);

		$this->add_responsive_control(
			'images_next_arrow_bottom_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Bottom Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-next' => 'bottom: {{SIZE}}{{UNIT}}; top: auto;',
				],
				'condition'  => [
					'slider_show_nav'              => 'yes',
					'images_next_arrow_v_position' => 'bottom',
				],
			]
		);

		$this->add_control(
			'images_next_arrow_h_position',
			[
				'type'                 => Controls_Manager::SELECT,
				'label'                => __( 'Horizontal Position by: ', 'jet-woo-product-gallery' ),
				'default'              => ! is_rtl() ? 'right' : 'left',
				'options'              => [
					'left'   => __( 'Left', 'jet-woo-product-gallery' ),
					'center' => __( 'Center', 'jet-woo-product-gallery' ),
					'right'  => __( 'Right', 'jet-woo-product-gallery' ),
				],
				'selectors_dictionary' => [
					'left'   => 'right: auto;',
					'center' => 'right: 50%; left: auto; transform: translate(50%, 0);',
					'right'  => 'left: auto;',
				],
				'selectors'            => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-next' => '{{VALUE}}',
				],
				'condition'            => [
					'slider_show_nav' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'images_next_arrow_left_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Left Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-next' => 'left: {{SIZE}}{{UNIT}}; right: auto;',
				],
				'condition'  => [
					'slider_show_nav'              => 'yes',
					'images_next_arrow_h_position' => 'left',
				],
			]
		);

		$this->add_responsive_control(
			'images_next_arrow_right_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Right Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images-arrows'] . '.jet-swiper-button-next' => 'right: {{SIZE}}{{UNIT}}; left: auto;',
				],
				'condition'  => [
					'slider_show_nav'              => 'yes',
					'images_next_arrow_h_position' => 'right',
				],
			]
		);

		$this->end_controls_section();
	}

	public function register_controls_pagination_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_pagination_style',
			[
				'label'     => __( 'Pagination', 'jet-woo-product-gallery' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'slider_show_pagination' => 'yes',
					'slider_pagination_type' => 'bullets',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_items_size',
			[
				'label'     => __( 'Bullet Size', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 40,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['bullet']                                                                     => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .jet-woo-swiper-vertical ' . $css_scheme['pagination'] . '.swiper-pagination-bullets-dynamic' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'slider_pagination_controller_type' => [ 'bullets', 'dynamic' ],
				],
			]
		);

		$this->start_controls_tabs(
			'pagination_items_style',
			[
				'condition' => [
					'slider_pagination_controller_type' => [ 'bullets', 'dynamic' ],
				],
			]
		);

		$this->start_controls_tab(
			'pagination_items_normal',
			[
				'label' => __( 'Normal', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'pagination_items_background',
			[
				'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['bullet'] => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'pagination_items_hover',
			[
				'label' => __( 'Hover', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'pagination_items_background_hover',
			[
				'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['bullet'] . ':hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'pagination_items_border_color_hover',
			[
				'label'     => __( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['bullet'] . ':hover' => 'border-color: {{VALUE}}',
				],
				'condition' => [
					'pagination_items_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'pagination_items_box_shadow_hover',
				'selector' => '{{WRAPPER}} ' . $css_scheme['bullet'] . ':hover',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'pagination_items_active',
			array(
				'label' => esc_html__( 'Active', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'pagination_items_background_active',
			[
				'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['bullet-active'] => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'pagination_items_border_color_active',
			[
				'label'     => __( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['bullet-active'] => 'border-color: {{VALUE}}',
				],
				'condition' => [
					'pagination_items_border_border!' => '',
				],
			]
		);


		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'pagination_items_box_shadow_active',
				'selector'  => '{{WRAPPER}} ' . $css_scheme['bullet-active'],
				'condition' => [
					'slider_pagination_controller_type' => 'bullets',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'pagination_items_border',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} ' . $css_scheme['bullet'],
				'condition' => [
					'slider_pagination_controller_type' => [ 'bullets', 'dynamic' ],
				],
			]
		);

		$this->add_responsive_control(
			'pagination_items_border_radius',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['bullet'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'slider_pagination_controller_type' => [ 'bullets', 'dynamic' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'pagination_items_box_shadow',
				'selector'  => '{{WRAPPER}} ' . $css_scheme['bullet'],
				'condition' => [
					'slider_pagination_controller_type' => 'bullets',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_items_margin',
			[
				'type'               => Controls_Manager::DIMENSIONS,
				'label'              => __( 'Bullet Space', 'jet-woo-product-gallery' ),
				'size_units'         => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'allowed_dimensions' => 'horizontal',
				'placeholder'        => [
					'top'    => 'auto',
					'right'  => '',
					'bottom' => 'auto',
					'left'   => '',
				],
				'selectors'          => [
					'{{WRAPPER}} ' . $css_scheme['bullet'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'          => [
					'slider_pagination_direction'       => 'horizontal',
					'slider_pagination_controller_type' => [ 'bullets', 'dynamic' ],
				],
			]
		);

		$this->add_responsive_control(
			'pagination_items_margin_vertical',
			[
				'type'               => Controls_Manager::DIMENSIONS,
				'label'              => __( 'Bullet Space', 'jet-woo-product-gallery' ),
				'size_units'         => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => [
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				],
				'selectors'          => [
					'{{WRAPPER}} ' . $css_scheme['bullet'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'          => [
					'slider_pagination_direction'       => 'vertical',
					'slider_pagination_controller_type' => [ 'bullets', 'dynamic' ],
				],
			]
		);

		$this->add_responsive_control(
			'pagination_progressbar_height',
			[
				'label'     => __( 'Progressbar Height', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 40,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-progressbar' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'slider_pagination_direction'       => 'horizontal',
					'slider_pagination_controller_type' => 'progressbar',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_progressbar_width',
			[
				'label'     => __( 'Progressbar Width', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 40,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-progressbar' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'slider_pagination_direction'       => 'vertical',
					'slider_pagination_controller_type' => 'progressbar',
				],
			]
		);

		$this->add_control(
			'pagination_progressbar_color',
			[
				'label'     => __( 'Progressbar Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-progressbar' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'slider_pagination_controller_type' => 'progressbar',
				],
			]
		);

		$this->add_control(
			'pagination_progressbar_fill_color',
			[
				'label'     => __( 'Progressbar Fill Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-progressbar-fill' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'slider_pagination_controller_type' => 'progressbar',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'pagination_fraction_typography',
				'label'     => __( 'Typography', 'jet-woo-product-gallery' ),
				'selector'  => '{{WRAPPER}} ' . $css_scheme['pagination'],
				'condition' => [
					'slider_pagination_controller_type' => 'fraction',
				],
			]
		);

		$this->add_control(
			'pagination_fraction_color',
			[
				'label'     => __( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['pagination'] => 'color: {{VALUE}}',
				],
				'condition' => [
					'slider_pagination_controller_type' => 'fraction',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_fraction_padding',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['pagination'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'slider_pagination_controller_type' => [ 'bullets', 'fraction' ],
				],
			]
		);

		$this->add_responsive_control(
			'pagination_top_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Top Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} .jet-woo-swiper-horizontal ' . $css_scheme['pagination'] => 'top: {{SIZE}}{{UNIT}}; bottom: auto;',
				],
				'condition'  => [
					'slider_pagination_h_position' => 'top',
					'slider_pagination_direction'  => 'horizontal',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_bottom_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Bottom Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} .jet-woo-swiper-horizontal ' . $css_scheme['pagination'] => 'bottom: {{SIZE}}{{UNIT}}; top: auto;',
				],
				'condition'  => [
					'slider_pagination_h_position' => 'bottom',
					'slider_pagination_direction'  => 'horizontal',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_right_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'End Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} .jet-woo-swiper-vertical ' . $css_scheme['pagination'] => ! is_rtl() ? 'right: {{SIZE}}{{UNIT}}; left: auto;' : 'left: {{SIZE}}{{UNIT}}; right: auto;',
				],
				'condition'  => [
					'slider_pagination_v_position' => 'end',
					'slider_pagination_direction'  => 'vertical',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_left_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Start Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} .jet-woo-swiper-vertical ' . $css_scheme['pagination'] => ! is_rtl() ? 'left: {{SIZE}}{{UNIT}}; right: auto;' : 'right: {{SIZE}}{{UNIT}}; left: auto;',
				],
				'condition'  => [
					'slider_pagination_v_position' => 'start',
					'slider_pagination_direction'  => 'vertical',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_horizontal_alignment',
			[
				'label'     => __( 'Alignment', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => __( 'Left', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['pagination'] => 'text-align: {{VALUE}};',
				],
				'condition' => [
					'slider_pagination_direction'       => 'horizontal',
					'slider_pagination_controller_type' => [ 'bullets', 'fraction' ],
					'slider_pagination_bullets_dynamic' => '',
				],
				'classes'   => 'elementor-control-align',
			]
		);

		$this->add_control(
			'pagination_fraction_vertical_alignment',
			[
				'label'                => __( 'Alignment', 'jet-woo-product-gallery' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => [
					'top'    => [
						'title' => __( 'Top', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-align-start-v',
					],
					'center' => [
						'title' => __( 'Center', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-align-center-v',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-align-end-v',
					],
				],
				'selectors_dictionary' => [
					'top'    => 'top: 0; bottom: auto; transform: translate(0,0);',
					'center' => 'top: 50%; bottom: auto; transform: translate(0,-50%);',
					'bottom' => 'top: auto; bottom: 0; transform: translate(0,0);',
				],
				'selectors'            => [
					'{{WRAPPER}} ' . $css_scheme['pagination'] => '{{VALUE}}',
				],
				'condition'            => [
					'slider_pagination_direction'       => 'vertical',
					'slider_pagination_controller_type' => 'fraction',
				],
			]
		);

		$this->end_controls_section();
	}

	public function register_controls_thumbnails_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_thumbnails_style',
			[
				'tab'       => Controls_Manager::TAB_STYLE,
				'label'     => __( 'Thumbnails', 'jet-woo-product-gallery' ),
				'condition' => [
					'slider_show_pagination' => 'yes',
					'slider_pagination_type' => 'thumbnails',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnails_vertical_width',
			[
				'type'        => Controls_Manager::SLIDER,
				'label'       => __( 'Width', 'jet-woo-product-gallery' ),
				'render_type' => 'template',
				'size_units'  => $this->set_custom_size_unit( [ 'px', '%' ] ),
				'range'       => [
					'px' => [
						'min' => 70,
						'max' => 500,
					],
					'%'  => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default'     => [
					'size' => 150,
					'unit' => 'px',
				],
				'selectors'   => [
					'{{WRAPPER}} .jet-woo-swiper-vertical .jet-gallery-swiper-thumb'  => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .jet-woo-swiper-vertical .jet-gallery-swiper-slider' => 'max-width: calc(100% - {{SIZE}}{{UNIT}});',
				],
				'condition'   => [
					'slider_pagination_direction' => 'vertical',
				],
			]
		);

		$this->add_control(
			'thumbnails_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'thumbnails_border',
				'selector' => '{{WRAPPER}} ' . $css_scheme['thumbnails'],
			]
		);

		$this->add_responsive_control(
			'thumbnails_border_radius',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnails_gutter_h',
			[
				'type'               => Controls_Manager::DIMENSIONS,
				'label'              => __( 'Gutter', 'jet-woo-product-gallery' ),
				'size_units'         => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'allowed_dimensions' => 'vertical',
				'render_type'        => 'template',
				'placeholder'        => [
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				],
				'selectors'          => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-wrapper'] => 'padding-top: {{TOP}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}};',
				],
				'condition'          => [
					'slider_pagination_direction' => 'horizontal',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnails_gutter_v',
			[
				'type'               => Controls_Manager::DIMENSIONS,
				'label'              => __( 'Gutter', 'jet-woo-product-gallery' ),
				'size_units'         => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'allowed_dimensions' => 'horizontal',
				'render_type'        => 'template',
				'placeholder'        => [
					'top'    => 'auto',
					'right'  => '',
					'bottom' => 'auto',
					'left'   => '',
				],
				'selectors'          => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-wrapper'] => 'padding-left: {{LEFT}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}};',
				],
				'condition'          => [
					'slider_pagination_direction' => 'vertical',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnails_horizontal_alignment',
			[
				'label'     => __( 'Horizontal Alignment', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => [
					'left'   => [
						'title' => __( 'Left', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-wrapper'] . '.swiper-container-horizontal' => 'text-align: {{VALUE}};',
				],
				'classes'   => 'elementor-control-align',
				'condition' => [
					'slider_pagination_direction' => 'horizontal',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_thumbnails_style' );

		$this->start_controls_tab(
			'tabs_thumbnails_normal',
			[
				'label' => __( 'Normal', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'thumbnails_opacity',
			[
				'type'      => Controls_Manager::SLIDER,
				'label'     => __( 'Opacity', 'jet-woo-product-gallery' ),
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-slide'] => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tabs_thumbnails_hover',
			[
				'label' => __( 'Hover', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'thumbnails_opacity_hover',
			[
				'type'      => Controls_Manager::SLIDER,
				'label'     => __( 'Opacity', 'jet-woo-product-gallery' ),
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-slide'] . ':hover' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tabs_thumbnails_active',
			[
				'label' => __( 'Active', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'thumbnails_opacity_active',
			[
				'type'      => Controls_Manager::SLIDER,
				'label'     => __( 'Opacity', 'jet-woo-product-gallery' ),
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-slide'] . '.swiper-slide-thumb-active' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'thumbnails_arrows_style_heading',
			array(
				'label'     => esc_html__( 'Prev/Next Arrows', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'slider_show_thumb_nav' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_arrows_size',
			array(
				'label'     => esc_html__( 'Size', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 6,
						'max' => 80,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'slider_show_thumb_nav' => 'yes',
				),
			)
		);

		$this->start_controls_tabs(
			'tabs_thumbnails_arrows_style',
			[
				'condition' => [
					'slider_show_thumb_nav!' => '',
				],
			]
		);

		$this->start_controls_tab(
			'thumbnails_arrows_normal',
			[
				'label' => __( 'Normal', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'thumbnails_arrows_normal_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'thumbnails_arrows_normal_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'thumbnails_arrows_hover',
			[
				'label' => __( 'Hover', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'thumbnails_arrows_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . ':hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'thumbnails_arrows_hover_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . ':hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'thumbnails_arrows_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . ':hover' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'thumbnails_arrows_border_border!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'thumbnails_arrows_disabled',
			[
				'label' => __( 'Disabled', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'thumbnails_arrows_disabled_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.swiper-button-disabled' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'thumbnails_arrows_disabled_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.swiper-button-disabled' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'thumbnails_arrows_disabled_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.swiper-button-disabled' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'thumbnails_arrows_border_border!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'thumbnails_arrows_border',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'],
				'condition' => [
					'slider_show_thumb_nav' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnails_arrows_border_radius',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'slider_show_thumb_nav' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnails_arrows_padding',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'slider_show_thumb_nav' => 'yes',
				],
			]
		);

		$this->add_control(
			'thumbnails_prev_arrow_heading',
			array(
				'label'     => esc_html__( 'Prev Arrow', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'slider_show_thumb_nav' => 'yes',
				),
			)
		);

		$this->add_control(
			'thumbnails_prev_arrow_v_position',
			array(
				'label'                => esc_html__( 'Vertical Position by: ', 'jet-woo-product-gallery' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'center',
				'options'              => array(
					'top'    => esc_html__( 'Top', 'jet-woo-product-gallery' ),
					'center' => esc_html__( 'Center', 'jet-woo-product-gallery' ),
					'bottom' => esc_html__( 'Bottom', 'jet-woo-product-gallery' ),
				),
				'selectors_dictionary' => array(
					'top'    => 'top: 0; bottom: auto; transform: translate(0,0);',
					'center' => 'top: 50%; bottom: auto; transform: translate(0,-50%);',
					'bottom' => 'top: auto; bottom: 0; transform: translate(0,0);',
				),
				'selectors'            => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' => '{{VALUE}}',
				),
				'condition'            => array(
					'slider_show_thumb_nav' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_prev_arrow_top_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Top Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' => 'top: {{SIZE}}{{UNIT}}; bottom: auto;',
				],
				'condition'  => [
					'thumbnails_prev_arrow_v_position' => 'top',
					'slider_show_thumb_nav'            => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnails_prev_arrow_bottom_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Bottom Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' => 'bottom: {{SIZE}}{{UNIT}}; top: auto;',
				],
				'condition'  => [
					'thumbnails_prev_arrow_v_position' => 'bottom',
					'slider_show_thumb_nav'            => 'yes',
				],
			]
		);

		$this->add_control(
			'thumbnails_prev_arrow_h_position',
			array(
				'label'                => esc_html__( 'Horizontal Position by: ', 'jet-woo-product-gallery' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => ! is_rtl() ? 'left' : 'right',
				'options'              => array(
					'left'   => esc_html__( 'Left', 'jet-woo-product-gallery' ),
					'center' => esc_html__( 'Center', 'jet-woo-product-gallery' ),
					'right'  => esc_html__( 'Right', 'jet-woo-product-gallery' ),
				),
				'selectors_dictionary' => array(
					'left'   => 'right: auto;',
					'center' => 'left: 50%; right: auto; transform: translate(-50%, 0);',
					'right'  => 'left: auto;',
				),
				'selectors'            => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' => '{{VALUE}}',
				),
				'condition'            => array(
					'slider_show_thumb_nav' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_prev_arrow_left_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Left Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' => 'left: {{SIZE}}{{UNIT}}; right: auto;',
				],
				'condition'  => [
					'thumbnails_prev_arrow_h_position' => 'left',
					'slider_show_thumb_nav'            => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnails_prev_arrow_right_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Right Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' => 'right: {{SIZE}}{{UNIT}}; left: auto;',
				],
				'condition'  => [
					'thumbnails_prev_arrow_h_position' => 'right',
					'slider_show_thumb_nav'            => 'yes',
				],
			]
		);

		$this->add_control(
			'thumbnails_next_arrow_heading',
			array(
				'label'     => esc_html__( 'Next Arrow', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'slider_show_thumb_nav' => 'yes',
				),
			)
		);

		$this->add_control(
			'thumbnails_next_arrow_v_position',
			array(
				'label'                => esc_html__( 'Vertical Position by: ', 'jet-woo-product-gallery' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'center',
				'options'              => array(
					'top'    => esc_html__( 'Top', 'jet-woo-product-gallery' ),
					'center' => esc_html__( 'Center', 'jet-woo-product-gallery' ),
					'bottom' => esc_html__( 'Bottom', 'jet-woo-product-gallery' ),
				),
				'selectors_dictionary' => array(
					'top'    => 'top: 0; bottom: auto; transform: translate(0,0);',
					'center' => 'top: 50%; bottom: auto; transform: translate(0,-50%);',
					'bottom' => 'top: auto; bottom: 0; transform: translate(0,0);',
				),
				'condition'            => array(
					'slider_show_thumb_nav' => 'yes',
				),
				'selectors'            => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' => '{{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_next_arrow_top_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Top Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' => 'top: {{SIZE}}{{UNIT}}; bottom: auto;',
				],
				'condition'  => [
					'thumbnails_next_arrow_v_position' => 'top',
					'slider_show_thumb_nav'            => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnails_next_arrow_bottom_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Bottom Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' => 'bottom: {{SIZE}}{{UNIT}}; top: auto;',
				],
				'condition'  => [
					'thumbnails_next_arrow_v_position' => 'bottom',
					'slider_show_thumb_nav'            => 'yes',
				],
			]
		);

		$this->add_control(
			'thumbnails_next_arrow_h_position',
			array(
				'label'                => esc_html__( 'Horizontal Position by: ', 'jet-woo-product-gallery' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => ! is_rtl() ? 'right' : 'left',
				'options'              => array(
					'left'   => esc_html__( 'Left', 'jet-woo-product-gallery' ),
					'center' => esc_html__( 'Center', 'jet-woo-product-gallery' ),
					'right'  => esc_html__( 'Right', 'jet-woo-product-gallery' ),
				),
				'selectors_dictionary' => array(
					'left'   => 'right: auto;',
					'center' => 'right: 50%; left: auto; transform: translate(50%, 0);',
					'right'  => 'left: auto;',
				),
				'condition'            => array(
					'slider_show_thumb_nav' => 'yes',
				),
				'selectors'            => array(
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' => '{{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnails_next_arrow_left_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Left Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' => 'left: {{SIZE}}{{UNIT}}; right: auto;',
				],
				'condition'  => [
					'thumbnails_next_arrow_h_position' => 'left',
					'slider_show_thumb_nav'            => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'thumbnails_next_arrow_right_position',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Right Indent', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'range'      => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges(),
				'condition'  => [
					'thumbnails_next_arrow_h_position' => 'right',
					'slider_show_thumb_nav'            => 'yes',
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' => 'right: {{SIZE}}{{UNIT}}; left: auto;',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		jet_woo_product_gallery()->base->render_gallery( 'gallery-slider', $this->get_settings_for_display(), 'elementor' );
	}

}
