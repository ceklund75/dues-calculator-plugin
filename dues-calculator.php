<?php
/**
 * Plugin Name: Dues Calculator
 * Description: Calculates projected dues from an hourly rate.
 * Version:     1.3.0
 * Author:      Chris Eklund
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
                'factor'       => '2.5',
                'parent_class' => 'elementor-kit-7',
                'title'        => "HOW MUCH WILL YOU PAY IN UNION DUES?",
                'subtitle'     => 'Enter your hourly rate:',
                'default_hourly_rate'=> '0',
                'disclaimer'   => '',
                'parent_bg_color'     => 'transparent',
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
        'dc_default_hourly_rate',
        'Default Hourly Rate',
        'dc_field_default_hourly_rate',
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

     add_settings_field(
        'dc_disclaimer',
        'Disclaimer Text',
        'dc_field_disclaimer',
        'dc_settings_page',
        'dc_main_section'
    );

    add_settings_field(
        'dc_parent_bg_color',
        'Parent Background Color',
        'dc_field_parent_bg_color',
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

    $output['factor']               = isset( $input['factor'] ) ? floatval( $input['factor'] ) : 2.5;
    $output['parent_class']         = isset( $input['parent_class'] ) ? sanitize_html_class( $input['parent_class'] ) : 'elementor-kit-7';
    $output['title']                = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : "HOW MUCH WILL YOU PAY IN UNION DUES?";
    $output['subtitle']             = isset( $input['subtitle'] ) ? sanitize_text_field( $input['subtitle'] ) : 'Enter your hourly rate:';    
    $output['default_hourly_rate']  = isset( $input['default_hourly_rate'] )
    ? floatval( $input['default_hourly_rate'] )
    : 0;
     $output['disclaimer']          = isset( $input['disclaimer'] ) ? wp_kses_post( $input['disclaimer'] ) : '';
    $raw_bg = isset( $input['parent_bg_color'] ) ? $input['parent_bg_color'] : 'transparent';
    if ( strtolower( $raw_bg ) === 'transparent' ) {
        $output['parent_bg_color'] = 'transparent';
    } else {
        $sanitized = sanitize_hex_color( $raw_bg );
        $output['parent_bg_color'] = $sanitized ? $sanitized : 'transparent';
    }
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

        <p>
            Use the shortcode
            <code>[hourly_calculator]</code>
            in any post, page, or Elementor Shortcode widget to display the dues calculator form on the front end.
        </p>
        <p>
            The defaults configured below (factor, default hourly rate, parent CSS class, title, subtitle, disclaimer,
    and parent background color) will be used
            whenever you insert the shortcode without overriding attributes.
        </p>

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
        'factor'       => '2.5',
        'parent_class' => 'elementor-kit-7',
        'title'        => "HOW MUCH WILL YOU PAY IN UNION DUES?",
        'subtitle'     => 'Enter your hourly rate:',
        'default_hourly_rate' => '0',
        'disclaimer'          => '',
        'parent_bg_color'     => 'transparent',
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

function dc_field_default_hourly_rate() {
    $options = dc_get_options();
    ?>
    <input type="number" step="0.01" name="dc_options[default_hourly_rate]" value="<?php echo esc_attr( $options['default_hourly_rate'] ); ?>" />
    <p class="description">Default hourly rate to pre-fill the form (default 0).</p>
    <?php
}

function dc_field_disclaimer() {
    $options    = dc_get_options();
    $disclaimer = isset( $options['disclaimer'] ) ? $options['disclaimer'] : '';
    ?>
    <textarea
        name="dc_options[disclaimer]"
        rows="4"
        cols="60"
        class="large-text"
    ><?php echo esc_textarea( $disclaimer ); ?></textarea>
    <p class="description">Optional disclaimer shown below the calculator. Basic HTML is allowed.</p>
    <?php
}

function dc_field_parent_bg_color() {
    $options = dc_get_options();
    $value   = isset( $options['parent_bg_color'] ) ? $options['parent_bg_color'] : 'transparent';
    ?>
    <input
        type="text"
        name="dc_options[parent_bg_color]"
        value="<?php echo esc_attr( $value ); ?>"
        class="regular-text"
        placeholder="transparent or #123456"
    />
    <p class="description">
        Background color for the shortcode parent wrapper (e.g. <code>transparent</code> or <code>#123B6B</code>).
    </p>
    <?php
}
function dc_enqueue_assets() {
    static $enqueued = false;
    if ( $enqueued ) {
        return;
    }
    $enqueued = true;

    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style(
        'dc-dues-calculator',
        $plugin_url . 'assets/css/dues-calculator.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'dc-dues-calculator',
        $plugin_url . 'assets/js/dues-calculator.js',
        array(),
        '1.0.0',
        true
    );

    // Pass factor and any global data to JS
    $options = dc_get_options();
    wp_localize_script(
        'dc-dues-calculator',
        'DuesCalculatorSettings',
        array(
            'factor' => isset( $options['factor'] ) ? $options['factor'] : 2.5,
        )
    );
}


/**
 * Shortcode output.
 */
function dc_hourly_projection_calculator_shortcode( $atts ) {
    dc_enqueue_assets();
    
    $options = dc_get_options();

    $atts = shortcode_atts(
        array(
            'parent_class'      => $options['parent_class'],
            'title'             => $options['title'],
            'subtitle'          => $options['subtitle'],
            'default_hourly'    => $options['default_hourly_rate'],
            'disclaimer'        => $options['disclaimer'],
            'parent_bg_color'   => $options['parent_bg_color'],
        ),
        $atts,
        'hourly_calculator'
    );
    
    //$factor              = floatval( $atts['factor'] );
    $parent_class        = sanitize_html_class( $atts['parent_class'] );
    $title               = sanitize_text_field( $atts['title'] );
    $subtitle            = sanitize_text_field( $atts['subtitle'] );
    $disclaimer          = $atts['disclaimer'] !== '' ? $atts['disclaimer'] : $options['disclaimer'];
    $parent_bg_color     = $atts['parent_bg_color'] !== '' ? $atts['parent_bg_color'] : $options['parent_bg_color'];
    $default_hourly_rate = $atts['default_hourly'] !== ''
        ? floatval( $atts['default_hourly'] )
        : ( isset( $options['default_hourly_rate'] ) ? floatval( $options['default_hourly_rate'] ) : 0 );
    $hourly_rate = $default_hourly_rate > 0 ? $default_hourly_rate : '';

    ob_start();
    ?>
    <style>
        .<?php echo esc_attr( $parent_class ); ?> {
            background-color: <?php echo esc_html( $parent_bg_color ); ?>;
        }
    </style>    

    <div class="<?php echo esc_attr( $parent_class ); ?>">
        <div class="hpc-wrapper">
            <div class="hpc-inner">
                <h2><?php echo esc_html( $title ); ?></h2>
                <p><?php echo esc_html( $subtitle ); ?></p>
                <div class="hpc-error">
                        
                </div>
                <form method="post" class="hpc-form">
                    <p>
                        <label for="hpc_hourly_rate">Hourly rate</label>
                        <input
                            type="number"
                            size="6"
                            step="0.01"
                            min="0"
                            name="hpc_hourly_rate"
                            id="hpc_hourly_rate"
                            value="<?php echo esc_attr( $hourly_rate ); ?>"
                        />
                    </p>
                    <p>
                        <button type="button" class="hpc-submit-btn" name="hpc_submit">Calculate</button>
                    </p>
                </form>

                <div class="hpc-results">
                    <div class="hpc-result-item">
                        <div class="hpc-result-label">Your dues per month</div>
                        <div class="hpc-result-value" data-hpc-total="month">$0.00</div>
                    </div>
                    <div class="hpc-result-item">
                        <div class="hpc-result-label">Your dues per year</div>
                        <div class="hpc-result-value" data-hpc-total="year">$0.00</div></div>
                    <div class="hpc-result-item">
                        <div class="hpc-result-label">Your dues for a 5-year contract</div>
                        <div class="hpc-result-value" data-hpc-total="five">$0.00</div>
                    </div>
                </div>
                 <?php if ( ! empty( $disclaimer ) ) : ?>
                    <div class="hpc-disclaimer">
                        <?php echo wp_kses_post( $disclaimer ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode( 'hourly_calculator', 'dc_hourly_projection_calculator_shortcode' );
