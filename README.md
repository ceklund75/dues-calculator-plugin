Dues Calculator

A lightweight WordPress plugin that calculates projected union dues from an hourly rate and displays animated monthly, yearly, and 5‑year totals inside a styled CTA block. The plugin is shortcode-driven, works well inside Elementor, and exposes several configuration options in the WordPress admin.
Features

    Calculates dues based on a configurable multiplier (“factor”) and hourly rate.

    Shows animated results for:

        Dues per month

        Dues per year

        Dues for a 5‑year contract

    Settings page under Settings → Dues Calculator:

        Multiplier factor

        Default hourly rate

        Parent CSS class for outer wrapper

        Section title and subtitle

        Optional disclaimer text (supports basic HTML)

        Parent background color (e.g. transparent or #123B6B)

    Shortcode-based, so it can be used in:

        Posts and pages

        Elementor Shortcode widget

    Responsive, card-style layout using CSS Grid.

    Front‑end animation and validation implemented in vanilla JavaScript.

Installation

    Copy the plugin folder (e.g. dues-calculator) into your WordPress installation at:

    text
    wp-content/plugins/dues-calculator

    Ensure the folder contains:

        dues-calculator.php (main plugin file)

        assets/css/dues-calculator.css

        assets/js/dues-calculator.js

    In the WordPress admin, go to Plugins → Installed Plugins and activate Dues Calculator.

Usage
Shortcode

Insert the shortcode where you want the calculator to appear:

text
[hourly_calculator]

This will render the calculator using the defaults from the settings page.
Shortcode Attributes

You can override some defaults on a per-instance basis:

    parent_class – CSS class applied to the outer wrapper (div).

    title – Section title.

    subtitle – Section subtitle.

    default_hourly – Default hourly rate prefilled in the input.

    disclaimer – Optional disclaimer text.

    parent_bg_color – Background color for the parent wrapper (e.g. transparent or #123B6B).

Example:

text
[hourly_calculator
    title="Estimate Your Union Dues"
    subtitle="Enter your hourly rate below."
    default_hourly="40"
    parent_class="elementor-kit-7"
    parent_bg_color="transparent"
]

If an attribute is omitted, the plugin falls back to the global setting.
Settings

Navigate to Settings → Dues Calculator to configure defaults:

    Multiplier Factor
    The multiplier applied to the hourly rate to determine monthly dues. For example, with a factor of 2.5 and an hourly rate of 40, monthly dues are calculated as 40 × 2.5.

    Default Hourly Rate
    The hourly rate used to pre-fill the input field in the calculator. Set to 0 to leave it blank by default.

    Parent CSS Class
    A class applied to the outer wrapper around the calculator. This works well with theme/Elementor utility classes (e.g. elementor-kit-7) for consistent typography and spacing.

    Section Title
    Main heading displayed above the calculator.

    Section Subtitle
    Supporting text displayed below the title.

    Disclaimer Text
    Optional disclaimer rendered below the results. Supports basic HTML; content is sanitized for safety.

    Parent Background Color
    Background color applied to the parent wrapper class. Accepts transparent or a valid hex color (e.g. #123B6B).

Styling

The visual layout is defined in assets/css/dues-calculator.css. Key classes:

    .hpc-wrapper – Outer blue band / container, width-constrained and centered.

    .hpc-inner – Inner card with light background, padding, and shadow.

    .hpc-form – Wrapper for the input and button.

    .hpc-results – CSS Grid container for the three stat cards.

    .hpc-result-item – Individual result card.

    .hpc-result-label / .hpc-result-value – Label and number styling.

    .hpc-error – Inline validation error box.

    .hpc-disclaimer – Optional disclaimer text at the bottom.

You can override these styles in your theme or child theme as needed.
JavaScript Behavior

The front‑end script (assets/js/dues-calculator.js) handles:

    Binding click handlers to the Calculate button.

    Validating the hourly rate (must be greater than 0).

    Computing:

        Monthly dues: rate × factor

        Yearly dues: monthly × 12

        5‑year dues: yearly × 5

    Animating the counts from 0.00 up to the calculated values with a brief easing effect.

    Displaying error messages when the input is empty or invalid.

The script reads configuration (such as factor) from a global object that WordPress registers when enqueuing the script.
Development Notes

    Built using the WordPress Settings API for storing and sanitizing options.

    Uses WordPress sanitization functions (sanitize_text_field, sanitize_html_class, sanitize_hex_color, wp_kses_post) and output escaping (esc_attr, esc_html, esc_textarea) to keep admin and front‑end output safe.

    Designed so additional options (labels, currency symbol, etc.) can be added without changing the overall structure.

Roadmap Ideas

Potential future improvements:

    Configurable labels and currency symbol/position.

    Support for multiple calculator “profiles” (per-contract settings).

    A block editor (Gutenberg) block wrapper for the shortcode.

    Accessibility enhancements (ARIA attributes, reduced motion support).
