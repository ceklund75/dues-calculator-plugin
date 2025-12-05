<?php
/**
 * Plugin Name: Dues Calculator
 * Description: Calculates projected dues from an hourly rate.
 * Version:     1.0.0
 * Author:      Your Name
 * Text Domain: dues-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register plugin settings.
 */
function dc_register_settings() {
    register_setting(
        'dc_options_group',
        'dc_options',
        array(
            'type'              => 'array',
            'sanitize_callback' => 'dc_sanitize_options',
            'default'           => array(
                'factor'          => '2.5',
                'hours_per_month' => '160',
                'parent_class'    => 'elementor-kit-7',
                'title'           => "Learn How Much You'll Pay in Dues?",
                'subtitle'        => 'Enter your hourly rate:',
            ),
        )
    );

    add_settings_section(
        'dc_main_section',
        'Dues Calculator Settings',
        '__return_false',
        'dc_settings_page'
    );

    add_settings_field(
        'dc_factor',
        'Multiplier Factor',
        'dc_field_factor',
        'dc_settings_page',
        'dc_main_section'
    );

    add_settings_field(
        'dc_hours_per_month',
        'Hours per Month',
        'dc_field_hours_per_month',
        'dc_settings_page',
        'dc_main_section'
    );

    add_settings_field(
        'dc_parent_class',
        'Parent CSS Class',
        'dc_field_parent_class',
        'dc_settings_page',
        'dc_main_section'
    );

    add_settings_field(
        'dc_title',
        'Section Title',
        'dc_field_title',
        'dc_settings_page',
        'dc_main_section'
    );

    add_settings_field(
        'dc_subtitle',
        'Section Subtitle',
        'dc_field_subtitle',
        'dc_settings_page',
        'dc_main_section'
    );
}
add_action( 'admin_init', 'dc_register_settings' );

/**
 * Sanitize options.
 */
function dc_sanitize_options( $input ) {
    $output = array();

    $output['factor']          = isset( $input['factor'] ) ? floatval( $input['factor'] ) : 2.5;
    $output['hours_per_month'] = isset( $input['hours_per_month'] ) ? floatval( $input['hours_per_month'] ) : 160;
    $output['parent_class']    = isset( $input['parent_class'] ) ? sanitize_html_class( $input['parent_class'] ) : 'elementor-kit-7';
    $output['title']           = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : "Learn How Much You'll Pay in Dues?";
    $output['subtitle']        = isset( $input['subtitle'] ) ? sanitize_text_field( $input['subtitle'] ) : 'Enter your hourly rate:';

    return $output;
}

/**
 * Add options page under Settings.
 */
function dc_add_options_page() {
    add_options_page(
        'Dues Calculator',
        'Dues Calculator',
        'manage_options',
        'dc_settings_page',
        'dc_render_settings_page'
    );
}
add_action( 'admin_menu', 'dc_add_options_page' );

/**
 * Render settings page.
 */
function dc_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    ?>
    <div class="wrap">
        <h1>Dues Calculator</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'dc_options_group' );
            do_settings_sections( 'dc_settings_page' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Settings helpers and fields.
 */
function dc_get_options() {
    $defaults = array(
        'factor'          => '2.5',
        'hours_per_month' => '160',
        'parent_class'    => 'elementor-kit-7',
        'title'           => "Learn How Much You'll Pay in Dues?",
        'subtitle'        => 'Enter your hourly rate:',
    );

    $opts = get_option( 'dc_options', array() );
    return wp_parse_args( $opts, $defaults );
}

function dc_field_factor() {
    $options = dc_get_options();
    ?>
    <input type="number" step="0.01" name="dc_options[factor]" value="<?php echo esc_attr( $options['factor'] ); ?>" />
    <p class="description">Multiplier applied to the hourly rate (default 2.5).</p>
    <?php
}

function dc_field_hours_per_month() {
    $options = dc_get_options();
    ?>
    <input type="number" step="1" name="dc_options[hours_per_month]" value="<?php echo esc_attr( $options['hours_per_month'] ); ?>" />
    <p class="description">Typical hours worked per month (default 160).</p>
    <?php
}

function dc_field_parent_class() {
    $options = dc_get_options();
    ?>
    <input type="text" name="dc_options[parent_class]" value="<?php echo esc_attr( $options['parent_class'] ); ?>" />
    <p class="description">Parent CSS class, e.g. <code>elementor-kit-7</code>.</p>
    <?php
}

function dc_field_title() {
    $options = dc_get_options();
    ?>
    <input type="text" class="regular-text" name="dc_options[title]" value="<?php echo esc_attr( $options['title'] ); ?>" />
    <?php
}

function dc_field_subtitle() {
    $options = dc_get_options();
    ?>
    <input type="text" class="regular-text" name="dc_options[subtitle]" value="<?php echo esc_attr( $options['subtitle'] ); ?>" />
    <?php
}

/**
 * Shortcode output.
 */
function dc_hourly_projection_calculator_shortcode( $atts ) {
    $options = dc_get_options();

    $atts = shortcode_atts(
        array(
            'factor'          => $options['factor'],
            'hours_per_month' => $options['hours_per_month'],
            'parent_class'    => $options['parent_class'],
            'title'           => $options['title'],
            'subtitle'        => $options['subtitle'],
        ),
        $atts,
        'hourly_calculator'
    );

    $factor          = floatval( $atts['factor'] );
    $hours_per_month = floatval( $atts['hours_per_month'] );
    $parent_class    = sanitize_html_class( $atts['parent_class'] );
    $title           = sanitize_text_field( $atts['title'] );
    $subtitle        = sanitize_text_field( $atts['subtitle'] );

    $hourly_rate     = '';
    $monthly_total   = '';
    $yearly_total    = '';
    $five_year_total = '';

    if ( isset( $_POST['hpc_submit'] ) ) {
        if ( ! isset( $_POST['hpc_nonce'] ) || ! wp_verify_nonce( $_POST['hpc_nonce'], 'hpc_calc' ) ) {
            return '<p>Security check failed.</p>';
        }

        $hourly_rate = isset( $_POST['hpc_hourly_rate'] ) ? floatval( $_POST['hpc_hourly_rate'] ) : 0;

        if ( $hourly_rate > 0 ) {
            $effective_hourly = $hourly_rate * $factor;
            $monthly_total    = $effective_hourly * $hours_per_month;
            $yearly_total     = $monthly_total * 12;
            $five_year_total  = $yearly_total * 5;
        }
    }

    ob_start();
    ?>
    <style>
        .hpc-wrapper {
            background-color: #2d2d2d;
            color: #f5f5f5;
            padding: 2.5rem;
            border-radius: 8px;
        }
        .hpc-wrapper h2 {
            margin-top: 0;
            margin-bottom: 0.5rem;
        }
        .hpc-wrapper p {
            margin-bottom: 0.75rem;
        }
        .hpc-wrapper label {
            display: block;
            margin-bottom: 0.25rem;
        }
        .hpc-wrapper input[type="number"],
        .hpc-wrapper input[type="submit"] {
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            border: 1px solid #444;
        }
        .hpc-wrapper input[type="submit"] {
            cursor: pointer;
            background-color: #444;
            color: #f5f5f5;
            border-color: #555;
        }
        .hpc-wrapper .hpc-results {
            margin-top: 1.5rem;
        }
    </style>

    <div class="<?php echo esc_attr( $parent_class ); ?>">
        <div class="hpc-wrapper">
            <h2><?php echo esc_html( $title ); ?></h2>
            <p><?php echo esc_html( $subtitle ); ?></p>

            <form method="post">
                <?php wp_nonce_field( 'hpc_calc', 'hpc_nonce' ); ?>
                <p>
                    <label for="hpc_hourly_rate">Hourly rate</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="hpc_hourly_rate"
                        id="hpc_hourly_rate"
                        value="<?php echo esc_attr( $hourly_rate ); ?>"
                    />
                </p>
                <p>
                    <input type="submit" name="hpc_submit" value="Calculate" />
                </p>
            </form>

            <?php if ( $monthly_total !== '' ) : ?>
                <div class="hpc-results">
                    <p>Factor used: <?php echo esc_html( $factor ); ?></p>
                    <p>Hours per month: <?php echo esc_html( $hours_per_month ); ?></p>
                    <p>Monthly total: <?php echo esc_html( number_format( $monthly_total, 2 ) ); ?></p>
                    <p>Yearly total: <?php echo esc_html( number_format( $yearly_total, 2 ) ); ?></p>
                    <p>5-year total: <?php echo esc_html( number_format( $five_year_total, 2 ) ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'hourly_calculator', 'dc_hourly_projection_calculator_shortcode' );
