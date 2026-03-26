<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MSC_Last_Updated_Module {
    /** @var MSC_Last_Updated */
    private $plugin;

    public function __construct( $plugin ) {
        $this->plugin = $plugin;

        add_filter( 'the_title', array( $this, 'inject_above_title' ), 20, 2 );
        add_filter( 'the_content', array( $this, 'inject_in_content' ), 20 );
        add_shortcode( 'msclu_last_updated', array( $this, 'render_shortcode' ) );
    }

    public function inject_above_title( $title, $post_id ) {
        if ( ! $this->is_enabled() || 'above_title' !== $this->plugin->get_option( 'position', 'end_content' ) ) {
            return $title;
        }

        if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
            return $title;
        }

        $label = $this->build_label( $post_id );
        if ( '' === $label ) {
            return $title;
        }

        return $label . $title;
    }

    public function inject_in_content( $content ) {
        if ( ! $this->is_enabled() || ! is_singular() ) {
            return $content;
        }

        $post = get_post();
        if ( ! $post || ! $this->supports_post_type( $post->post_type ) ) {
            return $content;
        }

        $label = $this->build_label( $post->ID );
        if ( '' === $label ) {
            return $content;
        }

        $position = (string) $this->plugin->get_option( 'position', 'end_content' );

        if ( 'below_title' === $position ) {
            return $label . $content;
        }

        if ( 'end_content' === $position ) {
            return $content . $label;
        }

        return $content;
    }

    public function render_shortcode( $atts ) {
        $atts    = shortcode_atts( array( 'id' => get_the_ID() ), $atts, 'msclu_last_updated' );
        $post_id = absint( $atts['id'] );

        if ( ! $post_id ) {
            return '';
        }

        return $this->build_label( $post_id );
    }

    private function build_label( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post || ! $this->supports_post_type( $post->post_type ) ) {
            return '';
        }

        $published = get_post_time( 'U', true, $post );
        $modified  = get_post_modified_time( 'U', true, $post );

        if ( $modified <= $published ) {
            return '';
        }

        $threshold_days = absint( $this->plugin->get_option( 'days_threshold', 0 ) );
        if ( $threshold_days > 0 && ( $modified - $published ) < ( $threshold_days * DAY_IN_SECONDS ) ) {
            return '';
        }

        $template = (string) $this->plugin->get_option( 'template', 'Last updated on {date}' );
        $date     = wp_date( get_option( 'date_format' ), $modified );
        $label    = str_replace( '{date}', $date, $template );
        $output   = '<p class="msclu-last-updated">' . esc_html( $label ) . '</p>';

        return (string) apply_filters( 'msclu_last_updated_output', $output, $post_id, $label );
    }

    private function is_enabled() {
        return (bool) $this->plugin->get_option( 'module_enabled', 1 );
    }

    private function supports_post_type( $post_type ) {
        return in_array( $post_type, (array) $this->plugin->get_option( 'post_types', array( 'post', 'page' ) ), true );
    }
}
