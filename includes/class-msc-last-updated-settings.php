<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MSC_Last_Updated_Settings {
    /** @var MSC_Last_Updated */
    private $plugin;

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
        add_action( 'admin_menu', array( $this, 'register_menu' ), 5 );
        add_action( 'admin_init', array( $this, 'handle_save' ) );
        if ( ! has_action( 'admin_menu', 'msc_site_care_reorder_priority_items' ) ) {
            add_action( 'admin_menu', 'msc_site_care_reorder_priority_items', 999 );
        }
        if ( ! has_action( 'admin_head', 'msc_site_care_menu_highlight_styles' ) ) {
            add_action( 'admin_head', 'msc_site_care_menu_highlight_styles' );
        }
    }

    public function register_menu() {
        global $admin_page_hooks;

        if ( ! isset( $admin_page_hooks['msc-site-care'] ) ) {
            add_menu_page(
                __( 'Site Care', 'msc-last-updated' ),
                __( 'Site Care', 'msc-last-updated' ),
                'manage_options',
                'msc-site-care',
                array( __CLASS__, 'render_landing_page' ),
                'dashicons-shield-alt',
                65
            );
        }

        if ( $this->plugin->is_pro_active() ) {
            return;
        }

        add_submenu_page(
            'msc-site-care',
            __( 'Last Updated', 'msc-last-updated' ),
            __( 'Last Updated', 'msc-last-updated' ),
            'manage_options',
            'msclu-settings',
            array( $this, 'render_page' )
        );

        add_filter( 'msc_upgrade_sections', array( $this, 'add_upgrade_section' ) );

        global $submenu;
        $upgrade_registered = false;
        if ( ! empty( $submenu['msc-site-care'] ) ) {
            foreach ( $submenu['msc-site-care'] as $item ) {
                if ( isset( $item[2] ) && 'msc-site-care-upgrade' === $item[2] ) {
                    $upgrade_registered = true;
                    break;
                }
            }
        }
        if ( ! $upgrade_registered ) {
            add_submenu_page(
                'msc-site-care',
                __( 'Upgrade to Pro', 'msc-last-updated' ),
                __( 'Upgrade to Pro', 'msc-last-updated' ),
                'manage_options',
                'msc-site-care-upgrade',
                'msc_render_combined_upgrade_page'
            );
        }
    }

    public static function render_landing_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Site Care', 'msc-last-updated' ) . '</h1>';
        echo '<p>' . esc_html__( 'Welcome to Site Care by Anomalous Developers. Use the submenu items to manage each installed module.', 'msc-last-updated' ) . '</p>';
        echo '</div>';
    }

    public function add_upgrade_section( $sections ) {
        $sections[] = array(
            'title'    => __( 'Last Updated Pro', 'msc-last-updated' ),
            'features' => __( 'Relative dates, style presets, and per-post override controls.', 'msc-last-updated' ),
            'url'      => 'https://anomalous.co.za',
        );
        return $sections;
    }

    public function handle_save() {
        if ( ! is_admin() || ! isset( $_POST['msclu_settings_submit'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_admin_referer( 'msclu_save_settings', 'msclu_nonce' );

        $defaults = MSC_Last_Updated::default_options();
        $incoming = isset( $_POST['msclu'] ) ? (array) wp_unslash( $_POST['msclu'] ) : array();
        $clean    = $defaults;

        $clean['module_enabled'] = isset( $incoming['module_enabled'] ) ? 1 : 0;
        $clean['post_types']     = $this->sanitize_post_types( $incoming, 'post_types' );
        $clean['days_threshold'] = isset( $incoming['days_threshold'] ) ? max( 0, absint( $incoming['days_threshold'] ) ) : 0;

        $positions        = array( 'above_title', 'below_title', 'end_content' );
        $position         = isset( $incoming['position'] ) ? sanitize_key( $incoming['position'] ) : 'end_content';
        $clean['position'] = in_array( $position, $positions, true ) ? $position : 'end_content';

        $template         = isset( $incoming['template'] ) ? sanitize_text_field( $incoming['template'] ) : '';
        $clean['template'] = '' !== trim( $template ) ? $template : $defaults['template'];

        $this->plugin->update_options( $clean );

        wp_safe_redirect(
            add_query_arg(
                array( 'page' => 'msclu-settings', 'updated' => 1 ),
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    private function sanitize_post_types( $incoming, $key ) {
        $types = isset( $incoming[ $key ] ) ? (array) $incoming[ $key ] : array();
        $types = array_map( 'sanitize_key', $types );
        return array_values( array_filter( $types ) );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $options    = $this->plugin->get_options();
        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Last Updated', 'msc-last-updated' ); ?></h1>

            <?php if ( isset( $_GET['updated'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'msc-last-updated' ); ?></p></div>
            <?php endif; ?>

            <form method="post" action="">
                <?php wp_nonce_field( 'msclu_save_settings', 'msclu_nonce' ); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable module', 'msc-last-updated' ); ?></th>
                        <td><label><input type="checkbox" name="msclu[module_enabled]" value="1" <?php checked( 1, (int) $options['module_enabled'] ); ?> /> <?php esc_html_e( 'Enabled', 'msc-last-updated' ); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Apply to post types', 'msc-last-updated' ); ?></th>
                        <td>
                            <?php foreach ( $post_types as $pt ) : ?>
                                <label style="display:block;"><input type="checkbox" name="msclu[post_types][]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, (array) $options['post_types'], true ) ); ?> /> <?php echo esc_html( $pt->labels->singular_name ); ?></label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Only show after N days since publish', 'msc-last-updated' ); ?></th>
                        <td>
                            <input type="number" min="0" name="msclu[days_threshold]" value="<?php echo esc_attr( $options['days_threshold'] ); ?>" />
                            <p class="description"><?php esc_html_e( 'Set to 0 to always show.', 'msc-last-updated' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Position', 'msc-last-updated' ); ?></th>
                        <td>
                            <select name="msclu[position]">
                                <option value="above_title" <?php selected( 'above_title', $options['position'] ); ?>><?php esc_html_e( 'Above title', 'msc-last-updated' ); ?></option>
                                <option value="below_title" <?php selected( 'below_title', $options['position'] ); ?>><?php esc_html_e( 'Below title (start of content)', 'msc-last-updated' ); ?></option>
                                <option value="end_content" <?php selected( 'end_content', $options['position'] ); ?>><?php esc_html_e( 'End of content', 'msc-last-updated' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Label template', 'msc-last-updated' ); ?></th>
                        <td>
                            <input type="text" class="regular-text" name="msclu[template]" value="<?php echo esc_attr( $options['template'] ); ?>" />
                            <p class="description"><?php esc_html_e( 'Use {date} token for the formatted date.', 'msc-last-updated' ); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button( __( 'Save Settings', 'msc-last-updated' ), 'primary', 'msclu_settings_submit' ); ?>
            </form>
        </div>
        <?php
    }
}

if ( ! function_exists( 'msc_render_combined_upgrade_page' ) ) {
    function msc_render_combined_upgrade_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $sections = apply_filters( 'msc_upgrade_sections', array() );
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Upgrade to Pro', 'msc-last-updated' ) . '</h1>';
        echo '<p>' . esc_html__( 'Upgrade individual modules to unlock more features for each plugin.', 'msc-last-updated' ) . '</p>';
        if ( empty( $sections ) ) {
            echo '<p>' . esc_html__( 'No upgrades available.', 'msc-last-updated' ) . '</p>';
        } else {
            echo '<div style="display:flex;flex-wrap:wrap;gap:20px;margin-top:20px;">';
            foreach ( $sections as $section ) {
                echo '<div style="background:#fff;border:1px solid #ccd0d4;border-radius:4px;padding:20px 24px;flex:1;min-width:240px;max-width:340px;">';
                echo '<h2 style="margin-top:0;">' . esc_html( $section['title'] ) . '</h2>';
                echo '<p>' . esc_html( $section['features'] ) . '</p>';
                echo '<a href="' . esc_url( $section['url'] ) . '" target="_blank" rel="noopener noreferrer" class="button button-primary">';
                echo esc_html__( 'Learn more', 'msc-last-updated' );
                echo '</a>';
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
}
