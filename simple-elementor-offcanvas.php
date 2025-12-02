<?php
/**
 * Plugin Name: Simple Offcanvas for Elementor (Sherman Core)
 * Description: Offcanvas widget for Elementor (no Pro) + PS Core dynamic tags + Sherman Core admin panel.
 * Version:     1.2.0
 * Author:      Plus
 * Text Domain: simple-offcanvas
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Sherman_Core_Plugin {

    const VERSION = '1.2.0';

    // گزینه‌ها
    const OPTION_ENABLE_OFFCANVAS     = 'sherman_core_enable_offcanvas';
    const OPTION_ENABLE_DYNAMIC_TAGS  = 'sherman_core_enable_dynamic_tags';

    public function __construct() {

        // Elementor integration
        add_action( 'plugins_loaded', [ $this, 'init_elementor_features' ] );

        // Admin panel (Sherman Core)
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /**
     * تعریف داینامیک‌تگ‌ها (قابل گسترش برای آینده)
     */
    protected function get_dynamic_tags_definitions() {
        return [
            'ps_site_url' => [
                'option_key' => 'sherman_core_enable_tag_ps_site_url',
                'file'       => __DIR__ . '/tags/class-ps-tag-site-url.php',
                'class'      => '\PS_Core\Dynamic_Tags\Tag_Site_URL',
                'title'      => __( 'PS Site URL', 'simple-offcanvas' ),
                'description'=> __( 'Outputs the site URL as a dynamic URL tag.', 'simple-offcanvas' ),
            ],
        ];
    }

    /**
     * Init Elementor integration: Dynamic Tags + optional Offcanvas widget.
     */
    public function init_elementor_features() {
        // Elementor active?
        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        // Dynamic Tags: فقط اگر از پنل فعال شده باشد
        $dynamic_enabled = get_option( self::OPTION_ENABLE_DYNAMIC_TAGS, 'yes' );
        if ( 'yes' === $dynamic_enabled ) {
            add_action( 'elementor/dynamic_tags/register', [ $this, 'register_dynamic_tags' ] );
            add_action( 'elementor/dynamic_tags/register_tags', [ $this, 'register_dynamic_tags' ] );
        }

        // Offcanvas widget فقط اگر از پنل فعال شده باشد
        $offcanvas_enabled = get_option( self::OPTION_ENABLE_OFFCANVAS, 'yes' );
        if ( 'yes' !== $offcanvas_enabled ) {
            return;
        }

        add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_scripts' ] );
        add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_styles' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widget' ] );
    }

    /**
     * Register Dynamic Tags (PS Core group).
     */
    public function register_dynamic_tags( $dynamic_tags_manager ) {

        if ( ! class_exists( '\Elementor\Core\DynamicTags\Tag' ) ) {
            return;
        }

        $defs = $this->get_dynamic_tags_definitions();
        if ( empty( $defs ) ) {
            return;
        }

        // حداقل یک تگ فعال است؟
        $has_enabled = false;
        foreach ( $defs as $slug => $def ) {
            $opt_key   = $def['option_key'];
            $tag_on    = get_option( $opt_key, 'yes' );
            if ( 'yes' === $tag_on ) {
                $has_enabled = true;
                break;
            }
        }

        if ( ! $has_enabled ) {
            // هیچ تگی فعال نیست، پس گروه هم لازم نیست
            return;
        }

        // گروه PS Core
        $dynamic_tags_manager->register_group(
            'ps-core',
            [
                'title' => __( 'PS Core', 'simple-offcanvas' ),
            ]
        );

        // ثبت هر تگ فعال
        foreach ( $defs as $slug => $def ) {
            $opt_key   = $def['option_key'];
            $tag_on    = get_option( $opt_key, 'yes' );
            if ( 'yes' !== $tag_on ) {
                continue;
            }

            if ( ! empty( $def['file'] ) && file_exists( $def['file'] ) ) {
                require_once $def['file'];
            }

            if ( ! empty( $def['class'] ) && class_exists( $def['class'] ) ) {
                $dynamic_tags_manager->register( new $def['class']() );
            }
        }
    }

    /**
     * Register frontend JS (Offcanvas).
     */
    public function register_scripts() {
        wp_register_script(
            'simple-offcanvas-js',
            plugins_url( 'assets/js/offcanvas.js', __FILE__ ),
            [ 'elementor-frontend' ],
            self::VERSION,
            true
        );
    }

    /**
     * Register frontend CSS (Offcanvas).
     */
    public function register_styles() {
        wp_register_style(
            'simple-offcanvas-css',
            plugins_url( 'assets/css/offcanvas.css', __FILE__ ),
            [ 'elementor-frontend' ],
            self::VERSION
        );
    }

    /**
     * Register Elementor Offcanvas widget.
     */
    public function register_widget( $widgets_manager ) {
        require_once __DIR__ . '/widget-simple-offcanvas.php';
        $widgets_manager->register( new \Simple_Offcanvas_Widget() );
    }

    /**
     * Add "Sherman Core" menu + زیرمنو Dynamic Tags
     */
    public function register_admin_menu() {
        add_menu_page(
            __( 'Sherman Core', 'simple-offcanvas' ),
            __( 'Sherman Core', 'simple-offcanvas' ),
            'manage_options',
            'sherman-core',
            [ $this, 'render_admin_page' ],
            'dashicons-admin-generic',
            59
        );

        add_submenu_page(
            'sherman-core',
            __( 'Dynamic Tags', 'simple-offcanvas' ),
            __( 'Dynamic Tags', 'simple-offcanvas' ),
            'manage_options',
            'sherman-core-dynamic-tags',
            [ $this, 'render_dynamic_tags_page' ]
        );
    }

    /**
     * Register settings for Sherman Core.
     */
    public function register_settings() {

        // تنظیمات صفحه اصلی (Offcanvas + Global Dynamic Tags)
        register_setting(
            'sherman_core_settings',
            self::OPTION_ENABLE_OFFCANVAS,
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_toggle' ],
                'default'           => 'yes',
            ]
        );

        register_setting(
            'sherman_core_settings',
            self::OPTION_ENABLE_DYNAMIC_TAGS,
            [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize_toggle' ],
                'default'           => 'yes',
            ]
        );

        // تنظیمات صفحه داینامیک تگ‌ها (per-tag)
        $defs = $this->get_dynamic_tags_definitions();

        foreach ( $defs as $slug => $def ) {
            if ( empty( $def['option_key'] ) ) {
                continue;
            }

            register_setting(
                'sherman_core_dynamic_tags_settings',
                $def['option_key'],
                [
                    'type'              => 'string',
                    'sanitize_callback' => [ $this, 'sanitize_toggle' ],
                    'default'           => 'yes',
                ]
            );
        }
    }

    /**
     * Sanitize toggle value ('yes' or 'no').
     */
    public function sanitize_toggle( $value ) {
        return ( 'yes' === $value ) ? 'yes' : 'no';
    }

    /**
     * صفحه اصلی Sherman Core
     * URL: admin.php?page=sherman-core
     */
    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $enabled_offcanvas     = get_option( self::OPTION_ENABLE_OFFCANVAS, 'yes' );
        $is_offcanvas_enabled  = ( 'yes' === $enabled_offcanvas );

        $enabled_dynamic       = get_option( self::OPTION_ENABLE_DYNAMIC_TAGS, 'yes' );
        $is_dynamic_enabled    = ( 'yes' === $enabled_dynamic );

        $dynamic_tags_url = admin_url( 'admin.php?page=sherman-core-dynamic-tags' );
        ?>
        <div class="wrap sherman-core-wrap">
            <style>
                .sherman-core-wrap {
                    max-width: 900px;
                }

                .sherman-core-header {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    margin-bottom: 12px;
                }

                .sherman-core-header-icon {
                    width: 40px;
                    height: 40px;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, #4f46e5, #6366f1);
                    color: #fff;
                    font-size: 20px;
                }

                .sherman-core-header-title {
                    font-size: 24px;
                    font-weight: 600;
                    margin: 0;
                }

                .sherman-core-header-subtitle {
                    margin: 0;
                    color: #666;
                    font-size: 13px;
                }

                .sherman-core-card {
                    background: #fff;
                    border-radius: 12px;
                    padding: 20px 22px;
                    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
                    border: 1px solid #e5e7eb;
                    margin-top: 14px;
                }

                .sherman-core-card-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 14px;
                }

                .sherman-core-card-title {
                    font-size: 16px;
                    font-weight: 600;
                    margin: 0;
                }

                .sherman-core-status-badge {
                    padding: 3px 10px;
                    border-radius: 999px;
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.04em;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                }

                .sherman-core-status-badge--enabled {
                    background: rgba(22, 163, 74, 0.1);
                    color: #15803d;
                    border: 1px solid rgba(22, 163, 74, 0.3);
                }

                .sherman-core-status-badge--disabled {
                    background: rgba(239, 68, 68, 0.06);
                    color: #b91c1c;
                    border: 1px solid rgba(248, 113, 113, 0.5);
                }

                .sherman-core-setting-row {
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                }

                .sherman-core-setting-main {
                    flex: 1;
                }

                .sherman-core-setting-label {
                    font-weight: 500;
                    margin-bottom: 4px;
                }

                .sherman-core-setting-description {
                    font-size: 12px;
                    color: #6b7280;
                    margin-top: 4px;
                }

                .sherman-core-toggle input[type="checkbox"] {
                    transform: scale(1.1);
                    margin-right: 6px;
                }

                .sherman-core-small-note {
                    margin-top: 16px;
                    font-size: 11px;
                    color: #9ca3af;
                }

                .sherman-core-link-inline {
                    font-size: 12px;
                    margin-top: 8px;
                }

                .sherman-core-link-inline a {
                    text-decoration: none;
                }

                @media (max-width: 782px) {
                    .sherman-core-card {
                        padding: 16px 14px;
                    }
                    .sherman-core-setting-row {
                        flex-direction: column;
                    }
                }
            </style>

            <div class="sherman-core-header">
                <div class="sherman-core-header-icon">
                    <span class="dashicons dashicons-admin-generic"></span>
                </div>
                <div>
                    <h1 class="sherman-core-header-title">
                        <?php esc_html_e( 'Sherman Core', 'simple-offcanvas' ); ?>
                    </h1>
                    <p class="sherman-core-header-subtitle">
                        <?php esc_html_e( 'Global controls for Sherman / PS features: Offcanvas widget & dynamic tags.', 'simple-offcanvas' ); ?>
                    </p>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'sherman_core_settings' ); ?>

                <!-- کارت Offcanvas -->
                <div class="sherman-core-card">
                    <div class="sherman-core-card-header">
                        <h2 class="sherman-core-card-title">
                            <?php esc_html_e( 'Offcanvas Widget for Elementor', 'simple-offcanvas' ); ?>
                        </h2>

                        <span class="sherman-core-status-badge <?php echo $is_offcanvas_enabled ? 'sherman-core-status-badge--enabled' : 'sherman-core-status-badge--disabled'; ?>">
                            <?php if ( $is_offcanvas_enabled ) : ?>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e( 'Enabled', 'simple-offcanvas' ); ?>
                            <?php else : ?>
                                <span class="dashicons dashicons-dismiss"></span>
                                <?php esc_html_e( 'Disabled', 'simple-offcanvas' ); ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="sherman-core-setting-row">
                        <div class="sherman-core-setting-main">
                            <div class="sherman-core-setting-label">
                                <?php esc_html_e( 'Load Offcanvas widget & assets', 'simple-offcanvas' ); ?>
                            </div>
                            <div class="sherman-core-toggle">
                                <label>
                                    <input type="checkbox"
                                           name="<?php echo esc_attr( self::OPTION_ENABLE_OFFCANVAS ); ?>"
                                           value="yes"
                                        <?php checked( $enabled_offcanvas, 'yes' ); ?>
                                    />
                                    <?php esc_html_e( 'Enable Offcanvas widget and load its CSS/JS on the frontend.', 'simple-offcanvas' ); ?>
                                </label>

                                <p class="sherman-core-setting-description">
                                    <?php esc_html_e( 'Disable this on sites where you don’t need the Offcanvas widget to keep things lighter.', 'simple-offcanvas' ); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- کارت Dynamic Tags -->
                <div class="sherman-core-card">
                    <div class="sherman-core-card-header">
                        <h2 class="sherman-core-card-title">
                            <?php esc_html_e( 'Dynamic Tags (PS Core)', 'simple-offcanvas' ); ?>
                        </h2>

                        <span class="sherman-core-status-badge <?php echo $is_dynamic_enabled ? 'sherman-core-status-badge--enabled' : 'sherman-core-status-badge--disabled'; ?>">
                            <?php if ( $is_dynamic_enabled ) : ?>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e( 'Enabled', 'simple-offcanvas' ); ?>
                            <?php else : ?>
                                <span class="dashicons dashicons-dismiss"></span>
                                <?php esc_html_e( 'Disabled', 'simple-offcanvas' ); ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="sherman-core-setting-row">
                        <div class="sherman-core-setting-main">
                            <div class="sherman-core-setting-label">
                                <?php esc_html_e( 'Enable PS Core dynamic tags for Elementor', 'simple-offcanvas' ); ?>
                            </div>
                            <div class="sherman-core-toggle">
                                <label>
                                    <input type="checkbox"
                                           name="<?php echo esc_attr( self::OPTION_ENABLE_DYNAMIC_TAGS ); ?>"
                                           value="yes"
                                        <?php checked( $enabled_dynamic, 'yes' ); ?>
                                    />
                                    <?php esc_html_e( 'Enable dynamic tags group "PS Core" in Elementor.', 'simple-offcanvas' ); ?>
                                </label>

                                <p class="sherman-core-setting-description">
                                    <?php esc_html_e( 'When enabled, you can configure each dynamic tag (like “PS Site URL”) from the dedicated Dynamic Tags settings page.', 'simple-offcanvas' ); ?>
                                </p>

                                <p class="sherman-core-link-inline">
                                    <a href="<?php echo esc_url( $dynamic_tags_url ); ?>">
                                        <?php esc_html_e( 'Open Dynamic Tags settings →', 'simple-offcanvas' ); ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <p class="sherman-core-small-note">
                        <?php esc_html_e( 'Tip: After changing dynamic tags settings, you may need to reload the Elementor editor to see the updated list of tags.', 'simple-offcanvas' ); ?>
                    </p>
                </div>

                <?php submit_button( __( 'Save Sherman Settings', 'simple-offcanvas' ) ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * صفحه مدیریت داینامیک تگ‌ها
     * URL: admin.php?page=sherman-core-dynamic-tags
     */
    public function render_dynamic_tags_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $global_dynamic_enabled = get_option( self::OPTION_ENABLE_DYNAMIC_TAGS, 'yes' );
        $defs = $this->get_dynamic_tags_definitions();
        ?>
        <div class="wrap sherman-core-wrap">
            <style>
                .sherman-core-wrap {
                    max-width: 900px;
                }

                .sherman-core-card {
                    background: #fff;
                    border-radius: 12px;
                    padding: 20px 22px;
                    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
                    border: 1px solid #e5e7eb;
                    margin-top: 14px;
                }

                .sherman-core-card-title {
                    font-size: 18px;
                    font-weight: 600;
                    margin: 0 0 10px 0;
                }

                .sherman-core-dtag-row {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 12px;
                    padding: 10px 0;
                    border-bottom: 1px solid #e5e7eb;
                }

                .sherman-core-dtag-main {
                    flex: 1;
                }

                .sherman-core-dtag-name {
                    font-weight: 500;
                    margin-bottom: 2px;
                }

                .sherman-core-dtag-slug {
                    font-size: 11px;
                    color: #9ca3af;
                }

                .sherman-core-dtag-description {
                    font-size: 12px;
                    color: #6b7280;
                    margin-top: 4px;
                }

                .sherman-core-dtag-toggle {
                    min-width: 120px;
                    text-align: right;
                }

                .sherman-core-dtag-toggle input[type="checkbox"] {
                    transform: scale(1.1);
                    margin-right: 4px;
                }

                .sherman-core-alert {
                    margin-top: 10px;
                    padding: 10px 12px;
                    border-radius: 8px;
                    background: #fef3c7;
                    border: 1px solid #facc15;
                    color: #92400e;
                    font-size: 12px;
                }
            </style>

            <h1><?php esc_html_e( 'Sherman Core - Dynamic Tags', 'simple-offcanvas' ); ?></h1>

            <?php if ( 'yes' !== $global_dynamic_enabled ) : ?>
                <div class="sherman-core-alert">
                    <?php esc_html_e( 'Global dynamic tags are currently disabled. Enable them from the main Sherman Core page to have these tags available inside Elementor.', 'simple-offcanvas' ); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'sherman_core_dynamic_tags_settings' ); ?>

                <div class="sherman-core-card">
                    <h2 class="sherman-core-card-title">
                        <?php esc_html_e( 'Available Dynamic Tags', 'simple-offcanvas' ); ?>
                    </h2>

                    <?php if ( empty( $defs ) ) : ?>
                        <p><?php esc_html_e( 'No dynamic tags are defined yet.', 'simple-offcanvas' ); ?></p>
                    <?php else : ?>
                        <?php foreach ( $defs as $slug => $def ) : 
                            $opt_key = $def['option_key'];
                            $on      = get_option( $opt_key, 'yes' );
                        ?>
                            <div class="sherman-core-dtag-row">
                                <div class="sherman-core-dtag-main">
                                    <div class="sherman-core-dtag-name">
                                        <?php echo esc_html( $def['title'] ); ?>
                                    </div>
                                    <div class="sherman-core-dtag-slug">
                                        <?php echo esc_html( $slug ); ?>
                                    </div>
                                    <?php if ( ! empty( $def['description'] ) ) : ?>
                                        <div class="sherman-core-dtag-description">
                                            <?php echo esc_html( $def['description'] ); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="sherman-core-dtag-toggle">
                                    <label>
                                        <input type="checkbox"
                                               name="<?php echo esc_attr( $opt_key ); ?>"
                                               value="yes"
                                            <?php checked( $on, 'yes' ); ?>
                                        />
                                        <?php esc_html_e( 'Enabled', 'simple-offcanvas' ); ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php submit_button( __( 'Save Dynamic Tags Settings', 'simple-offcanvas' ) ); ?>
            </form>
        </div>
        <?php
    }
}

new Sherman_Core_Plugin();
