<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Icons_Manager;

class Simple_Offcanvas_Widget extends Widget_Base {

    public function get_name() {
        return 'simple_offcanvas';
    }

    public function get_title() {
        return __( 'Simple Offcanvas', 'simple-offcanvas' );
    }

    public function get_icon() {
        return 'eicon-sidebar';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_style_depends() {
        return [ 'simple-offcanvas-css' ];
    }

    public function get_script_depends() {
        return [ 'simple-offcanvas-js' ];
    }

    /**
     * Get Elementor templates for dropdown.
     *
     * @return array
     */
    protected function get_elementor_templates_options() {
        $options = [ '' => __( '— Select template —', 'simple-offcanvas' ) ];

        $templates = get_posts( [
            'post_type'      => 'elementor_library',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ] );

        if ( ! empty( $templates ) && ! is_wp_error( $templates ) ) {
            foreach ( $templates as $template ) {
                $options[ $template->ID ] = $template->post_title;
            }
        }

        return $options;
    }

    protected function register_controls() {

        /*
         * =====================
         * Content
         * =====================
         */
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Content', 'simple-offcanvas' ),
            ]
        );

        $this->add_control(
            'trigger_text',
            [
                'label'   => __( 'Trigger Button Text', 'simple-offcanvas' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Open Panel', 'simple-offcanvas' ),
            ]
        );

        $this->add_control(
            'show_text',
            [
                'label'        => __( 'Show Text', 'simple-offcanvas' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'simple-offcanvas' ),
                'label_off'    => __( 'No', 'simple-offcanvas' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        // فقط آیکن: اگر اینجا آیکن انتخاب بشه، نمایش داده می‌شود
        $this->add_control(
            'trigger_icon',
            [
                'label' => __( 'Icon', 'simple-offcanvas' ),
                'type'  => Controls_Manager::ICONS,
            ]
        );

        $this->add_control(
            'side',
            [
                'label'   => __( 'Side', 'simple-offcanvas' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'right',
                'options' => [
                    'left'  => __( 'Left', 'simple-offcanvas' ),
                    'right' => __( 'Right', 'simple-offcanvas' ),
                ],
            ]
        );

        $this->add_control(
            'template_id',
            [
                'label'       => __( 'Content Template', 'simple-offcanvas' ),
                'type'        => Controls_Manager::SELECT,
                'options'     => $this->get_elementor_templates_options(),
                'default'     => '',
                'description' => __( 'Select an Elementor template to display inside the offcanvas panel.', 'simple-offcanvas' ),
            ]
        );

        $this->add_control(
            'prevent_scroll',
            [
                'label'        => __( 'Prevent Body Scroll', 'simple-offcanvas' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'simple-offcanvas' ),
                'label_off'    => __( 'No', 'simple-offcanvas' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->end_controls_section();

        /*
         * =====================
         * Style: Trigger Button
         * =====================
         */
        $this->start_controls_section(
            'section_style_trigger',
            [
                'label' => __( 'Trigger Button', 'simple-offcanvas' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'trigger_alignment',
            [
                'label'   => __( 'Alignment', 'simple-offcanvas' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __( 'Left', 'simple-offcanvas' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'     => [
                        'title' => __( 'Center', 'simple-offcanvas' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end'   => [
                        'title' => __( 'Right', 'simple-offcanvas' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default'  => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .sle-offcanvas-wrapper' => 'display:flex; justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'trigger_text_color',
            [
                'label'     => __( 'Text Color', 'simple-offcanvas' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sle-offcanvas__trigger, {{WRAPPER}} .sle-offcanvas__trigger-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'trigger_bg_color',
            [
                'label'     => __( 'Background Color', 'simple-offcanvas' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sle-offcanvas__trigger' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'trigger_border',
                'selector' => '{{WRAPPER}} .sle-offcanvas__trigger',
            ]
        );

        $this->add_control(
            'trigger_border_radius',
            [
                'label'      => __( 'Border Radius', 'simple-offcanvas' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .sle-offcanvas__trigger' =>
                        'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'trigger_padding',
            [
                'label'      => __( 'Padding', 'simple-offcanvas' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .sle-offcanvas__trigger' =>
                        'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'trigger_typography',
                'selector' => '{{WRAPPER}} .sle-offcanvas__trigger-text',
            ]
        );

        $this->add_responsive_control(
    'trigger_icon_size',
    [
        'label' => __( 'Icon Size', 'simple-offcanvas' ),
        'type'  => Controls_Manager::SLIDER,
        'size_units' => [ 'px' ],
        'range' => [
            'px' => [
                'min' => 8,
                'max' => 64,
            ],
        ],
        'selectors' => [
            '{{WRAPPER}} .sle-offcanvas__trigger-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
        ],
    ]
);


        $this->end_controls_section();

        /*
         * =====================
         * Style: Panel
         * =====================
         */
        $this->start_controls_section(
            'section_style_panel',
            [
                'label' => __( 'Panel', 'simple-offcanvas' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'panel_width',
            [
                'label'      => __( 'Width', 'simple-offcanvas' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [
                        'min' => 200,
                        'max' => 800,
                    ],
                    '%'  => [
                        'min' => 20,
                        'max' => 100,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 400,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .sle-offcanvas__panel' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'panel_background',
                'label'    => __( 'Background', 'simple-offcanvas' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .sle-offcanvas__panel',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'panel_border',
                'selector' => '{{WRAPPER}} .sle-offcanvas__panel',
            ]
        );

        $this->add_control(
            'panel_border_radius',
            [
                'label'      => __( 'Border Radius', 'simple-offcanvas' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .sle-offcanvas__panel' =>
                        'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'panel_inner_padding',
            [
                'label'      => __( 'Inner Padding', 'simple-offcanvas' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .sle-offcanvas__inner' =>
                        'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'panel_inner_margin',
            [
                'label'      => __( 'Inner Margin', 'simple-offcanvas' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .sle-offcanvas__inner' =>
                        'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'panel_typography',
                'selector' => '{{WRAPPER}} .sle-offcanvas__panel',
            ]
        );

        $this->end_controls_section();

        /*
         * =====================
         * Style: Overlay
         * =====================
         */
        $this->start_controls_section(
            'section_style_overlay',
            [
                'label' => __( 'Overlay', 'simple-offcanvas' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'overlay_color',
            [
                'label'     => __( 'Overlay Color', 'simple-offcanvas' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => 'rgba(0,0,0,0.5)',
                'selectors' => [
                    '{{WRAPPER}} .sle-offcanvas__overlay' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        /*
         * =====================
         * Style: Close Button
         * =====================
         */
        $this->start_controls_section(
            'section_style_close',
            [
                'label' => __( 'Close Button', 'simple-offcanvas' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'close_text_color',
            [
                'label'     => __( 'Color', 'simple-offcanvas' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sle-offcanvas__close' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'close_bg_color',
            [
                'label'     => __( 'Background Color', 'simple-offcanvas' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sle-offcanvas__close' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'close_border',
                'selector' => '{{WRAPPER}} .sle-offcanvas__close',
            ]
        );

        $this->add_control(
            'close_border_radius',
            [
                'label'      => __( 'Border Radius', 'simple-offcanvas' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .sle-offcanvas__close' =>
                        'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'close_size',
            [
                'label'      => __( 'Size', 'simple-offcanvas' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [
                        'min' => 16,
                        'max' => 48,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .sle-offcanvas__close' =>
                        'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: calc({{SIZE}}{{UNIT}} - 8px);',
                ],
            ]
        );

        $this->add_responsive_control(
            'close_padding',
            [
                'label'      => __( 'Padding', 'simple-offcanvas' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .sle-offcanvas__close' =>
                        'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings        = $this->get_settings_for_display();
        $id              = $this->get_id();
        $side            = $settings['side'];
        $prevent_scroll  = $settings['prevent_scroll'] === 'yes' ? 'true' : 'false';
        $template_id     = ! empty( $settings['template_id'] ) ? (int) $settings['template_id'] : 0;

        $show_text       = ( isset( $settings['show_text'] ) && 'yes' === $settings['show_text'] && ! empty( $settings['trigger_text'] ) );
        $has_icon        = ! empty( $settings['trigger_icon']['value'] );

        $this->add_render_attribute(
            'wrapper',
            [
                'class'               => [ 'sle-offcanvas-wrapper', 'sle-offcanvas-side-' . $side ],
                'data-prevent-scroll' => $prevent_scroll,
                'data-offcanvas-id'   => $id,
            ]
        );

        $this->add_render_attribute(
            'panel',
            [
                'class'       => 'sle-offcanvas__panel',
                'role'        => 'dialog',
                'aria-hidden' => 'true',
                'tabindex'    => '-1',
                'id'          => 'sle-offcanvas-' . esc_attr( $id ),
            ]
        );

        ?>
        <div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
            <button
                type="button"
                class="sle-offcanvas__trigger"
                data-sle-offcanvas-open="<?php echo esc_attr( $id ); ?>"
                aria-controls="sle-offcanvas-<?php echo esc_attr( $id ); ?>"
                aria-expanded="false"
            >
                <?php if ( $has_icon ) : ?>
                    <span class="sle-offcanvas__trigger-icon" aria-hidden="true">
                        <?php Icons_Manager::render_icon( $settings['trigger_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                    </span>
                <?php endif; ?>

                <?php if ( $show_text ) : ?>
                    <span class="sle-offcanvas__trigger-text">
                        <?php echo esc_html( $settings['trigger_text'] ); ?>
                    </span>
                <?php endif; ?>
            </button>

            <div class="sle-offcanvas" data-sle-offcanvas-container="<?php echo esc_attr( $id ); ?>">
                <div
                    class="sle-offcanvas__overlay"
                    data-sle-offcanvas-close="<?php echo esc_attr( $id ); ?>"
                ></div>

                <div <?php echo $this->get_render_attribute_string( 'panel' ); ?>>
                    <div class="sle-offcanvas__inner">
                        <?php
                        if ( $template_id ) {
                            if ( class_exists( '\Elementor\Plugin' ) ) {
                                echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id );
                            }
                        } else {
                            echo '<p style="margin:0;">' . esc_html__( 'Select a template in the widget settings to display here.', 'simple-offcanvas' ) . '</p>';
                        }
                        ?>
                    </div>
                    <button
                        type="button"
                        class="sle-offcanvas__close"
                        data-sle-offcanvas-close="<?php echo esc_attr( $id ); ?>"
                        aria-label="<?php esc_attr_e( 'Close', 'simple-offcanvas' ); ?>"
                    >
                        ×
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}
