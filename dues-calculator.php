<?php
/**
 * Plugin Name: Dues Calculator
 * Description: Calculates projected dues from an hourly rate.
 * Version:     1.1.0
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
}
add_action( 'admin_init', 'dc_register_settings' );

/**
 * Sanitize options.
 */
function dc_sanitize_options( $input ) {
    $output = array();

    $output['factor']       = isset( $input['factor'] ) ? floatval( $input['factor'] ) : 2.5;
    $output['parent_class'] = isset( $input['parent_class'] ) ? sanitize_html_class( $input['parent_class'] ) : 'elementor-kit-7';
    $output['title']        = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : "HOW MUCH WILL YOU PAY IN UNION DUES?";
    $output['subtitle']     = isset( $input['subtitle'] ) ? sanitize_text_field( $input['subtitle'] ) : 'Enter your hourly rate:';
    $output['default_hourly_rate'] = isset( $input['default_hourly_rate'] )
    ? floatval( $input['default_hourly_rate'] )
    : 0;

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
            The defaults configured below (factor, default hourly rate, parent CSS class, title, and subtitle) will be used
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

/**
 * Shortcode output.
 */
function dc_hourly_projection_calculator_shortcode( $atts ) {
    $options = dc_get_options();

    $atts = shortcode_atts(
        array(
            'factor'       => $options['factor'],
            'parent_class' => $options['parent_class'],
            'title'        => $options['title'],
            'subtitle'     => $options['subtitle'],
        ),
        $atts,
        'hourly_calculator'
    );

    $factor       = floatval( $atts['factor'] ); // direct multiplier, e.g. 2.5
    $default_hourly_rate = isset( $options['default_hourly_rate'] )  //default hourly rate, e.g. 35
    ? floatval( $options['default_hourly_rate'] )
    : 0;
    $parent_class = sanitize_html_class( $atts['parent_class'] );
    $title        = sanitize_text_field( $atts['title'] );
    $subtitle     = sanitize_text_field( $atts['subtitle'] );

    $hourly_rate     = $default_hourly_rate > 0 ? $default_hourly_rate : '';

    ob_start();
    ?>
    <style>
        .hpc-wrapper {
            background-color: #123B6B; /* Dark Blue band */
            padding: 1.5rem 1.25rem;
            border-radius: 12px;
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.3);
        }

        .hpc-inner {
            background-color: #F8F8F8; /* Off White card */
            color: #101010;            /* Near Black text */
            border-radius: 10px;
            padding: 2rem 2rem 2.25rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
        }

        .hpc-inner h2 {
            margin-top: 0;
            margin-bottom: 0.5rem;
            font-size: 2rem;
            line-height: 1.2;
            color: #123B6B; /* Dark Blue heading */
        }

        .hpc-inner p {
            margin-bottom: 1rem;
            font-size: 1rem;
            color: #494949; /* Dark Gray for body */
        }

        .hpc-inner label {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: #336793; /* Medium Blue */
        }

        .hpc-inner input[type="number"] {
            padding: 0.35rem 0.5rem;
            border-radius: 4px;
            border: 1px solid #A6C8E4; /* Light Blue */
            background-color: #FFFFFF;
            color: #101010;
            width: 120px;
            max-width: 100%;
            box-sizing: border-box;
            font-size: 0.9rem;
        }

        .hpc-inner input[type="number"]:focus {
            outline: none;
            border-color: #336793;
            box-shadow: 0 0 0 1px #336793;
        }

        .hpc-inner input[type="submit"] {
            padding: 0.5rem 1.25rem;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, #5F5DB3, #336793); /* Purple → Medium Blue */
            color: #F8F8F8;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.02em;
            margin-top: 0.25rem;
        }

        .hpc-inner input[type="submit"]:hover {
            background: linear-gradient(135deg, #44437D, #123B6B); /* Dark Purple → Dark Blue */
        }

        /* Results: 3-column stat layout */
        .hpc-results {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.5rem;
        }

        .hpc-result-item {
            background-color: #D7E9F7; /* Pale Blue */
            border-radius: 10px;
            padding: 1rem 1.25rem;
            border: 1px solid #A6C8E4; /* Light Blue border */
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }

        .hpc-result-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #336793; /* Medium Blue */
            margin-bottom: 0.5rem;
        }

        .hpc-result-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #123B6B; /* Dark Blue number */
        }

        .hpc-result-value::after {
            content: '';
            display: block;
            margin-top: 0.4rem;
            width: 40%;
            height: 3px;
            background: linear-gradient(90deg, #A6C8E4, #5F5DB3); /* Light Blue → Purple */
            border-radius: 999px;
        }
        .hpc-error {
            margin-bottom: 1rem;
            padding: 0.6rem 0.8rem;
            border-radius: 6px;
            background-color: #FCE8E8;
            color: #8A1F1F;
            font-size: 0.9rem;
            border: 1px solid #F5C2C2;
            display: none;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .hpc-wrapper {
                padding: 1.25rem 1rem;
            }
            .hpc-inner {
                padding: 1.5rem 1.5rem 1.75rem;
            }
            .hpc-results {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        (function() {
            function animateValue(el, end, duration) {
                if (!el) return;

                const start = 0;
                const startTime = performance.now();
                const formatter = new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                function frame(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const value = start + (end - start) * progress;

                    el.textContent = '$' + formatter.format(value);

                    if (progress < 1) {
                        requestAnimationFrame(frame);
                    }
                }

                requestAnimationFrame(frame);
            }

            document.addEventListener('DOMContentLoaded', function() {
                const wrappers = document.querySelectorAll('.hpc-wrapper');
                wrappers.forEach(function(wrapper) {
                    const form      = wrapper.querySelector('.hpc-form');
                    const inputRate = wrapper.querySelector('#hpc_hourly_rate');
                    const btn       = wrapper.querySelector('.hpc-submit-btn');
                    const errorBox  = wrapper.querySelector('.hpc-error');
                    const monthEl   = wrapper.querySelector('[data-hpc-total="month"]');
                    const yearEl    = wrapper.querySelector('[data-hpc-total="year"]');
                    const fiveEl    = wrapper.querySelector('[data-hpc-total="five"]');

                    if (!inputRate || !btn || !monthEl || !yearEl || !fiveEl) return;

                    const factor = <?php echo json_encode( $factor ); ?>;

                    if (form) {
                        form.addEventListener('submit', function(ev) {
                            ev.preventDefault();
                        });
                    }

                    btn.addEventListener('click', function() {
                        if (errorBox) {
                            errorBox.style.display = 'none';
                            errorBox.textContent = '';
                        }

                        const rate = parseFloat(inputRate.value);
                        if (!rate || rate <= 0) {
                            if (errorBox) {
                                errorBox.textContent = 'Please enter an hourly rate greater than 0 to see your dues.';
                                errorBox.style.display = 'block';
                            }
                            monthEl.textContent = '$0.00';
                            yearEl.textContent  = '$0.00';
                            fiveEl.textContent  = '$0.00';
                            return;
                        }

                        const monthly = rate * factor;
                        const yearly  = monthly * 12;
                        const five    = yearly * 5;

                        animateValue(monthEl, monthly, 900);
                        animateValue(yearEl,  yearly,  900);
                        animateValue(fiveEl,  five,    900);
                    });
                });
            });

        })();
        </script>


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
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode( 'hourly_calculator', 'dc_hourly_projection_calculator_shortcode' );
