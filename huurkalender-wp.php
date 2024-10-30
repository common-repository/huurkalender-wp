<?php

/**
 * Plugin Name: Huurkalender WP
 * Plugin URI: 
 * Description: Huurkalender wordpress plugin om uw kalender en/of boekingsmodule te integreren in uw eigen Wordpress website. De instellingen en de gebruiks aanwijzing kunt u vinden via uw Wordpress installatie: Instellingen > Huurkalender plugin
 * Version: 1.5.6
 * Author: Huurkalender
 * Author URI: https://www.huurkalender.nl
 * License: MIT license (MIT)
 * License URI: https://opensource.org/licenses/mit-license.php
 * Text Domain: Huurkalender.nl
 */
class TSHCAL
{

    public $version = '1.5.6';
    protected static $_instance = null;
    protected $plugin_name = 'Huurkalender WP';

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct()
    {
        $this->define_constants();
        $this->includes();

        add_action('init', array($this, 'ts_init'), 0);
        add_action('admin_init', array($this, 'ts_register_settings'));
    }

    public function ts_init()
    {
        add_action('admin_menu', array($this, 'ts_register_submenu_page'));
        add_shortcode('huurkalender', array($this, 'ts_shortcode_exec'));
    }

    public function plugin_url()
    {
        return untrailingslashit(plugins_url('/', __FILE__));
    }

    /**
     * Get the plugin path.
     * @return string
     */
    public function plugin_path()
    {
        return untrailingslashit(plugin_dir_path(__FILE__));
    }

    /**
     * Get the template path.
     * @return string
     */
    public function template_path()
    {
        return apply_filters('ts_template_path', 'huurkalender-wp/');
    }

    /**
     * Define TSSC Constants.
     */
    private function define_constants()
    {
        $this->define('TSHCAL_PLUGIN_FILE', __FILE__);
        $this->define('TSHCAL_ABSPATH', dirname(__FILE__) . '/');
        $this->define('TSHCAL_PLUGIN_BASENAME', plugin_basename(__FILE__));
        $this->define('TSHCAL_VERSION', $this->version);
        $this->define('TS_TEMPLATE_DEBUG_MODE', false);
    }

    public function ts_shortcode_exec($atts)
    {
        $def = array("color_bg_calendar" => 'none', "color_bg" => 'none', 'type' => 0, 'lang' => 0, 'id' => 0, 'start' => 0, 'month' => 0, 'width' => 800, 'height' => 800);
        $atts = shortcode_atts($def, preg_replace("/<.+>/sU", "", $atts));
        $url = 'https://www.huurkalender.nl/';

        // set type   
        $type = 'calendar';
        if (isset($atts['type'])) {
            if ($atts['type'] == 'boeking' || $atts['type'] == 'booking') {
                $type = 'booking';
            }
            if ($atts['type'] == 'kalender' || $atts['type'] == 'calendar') {
                $type = 'calendar';
            }
            if ($atts['type'] == 'combi') {
                $type = 'combi';
            }
        }
        // aantal maanden overwrite
        $month = '';
        if ($atts['month'] > 0) {
            $month = '&m=' . $atts['month'];
        }

        // start date overwrite
        $start = '';
        if ($atts['start'] > 0) {
            $month = '&start=' . $atts['start'];
        }

        // set lang 
        $lang = 'nl';

        // hardcode lang
        if (isset($atts['lang'])) {
            $lang = $atts['lang'];
        }

        // hardcode color 
        $color_bg = '';
        if ($atts['color_bg'] != 'none') {
            $color_bg = 'color_bg=' . $atts['color_bg'] . '&';
        }
        if ($atts['color_bg_calendar'] != 'none') {
            $color_bg = $color_bg . 'color_bg_calendar=' . $atts['color_bg_calendar'];
        }
        if (!empty($color_bg)) {
            $color_bg = '&' . $color_bg;
        }

        if ($lang == 'auto') {
            // locale lang
            $lang_locale = get_locale();
            if (!empty($lang_locale)) {
                $lang = substr(get_locale(), 0, 2);
            }

            // check lang var
            if (isset($_GET['lang'])) {
                $lang_get = strip_tags($_GET['lang']);
                if (!empty($lang_get)) {
                    $lang = $lang_get;
                }
            }

            // check polylang
            if (function_exists('pll_current_language')) {
                $polylang = pll_current_language();
                if (!empty($polylang)) {
                    $lang = $polylang;
                }
            } else {
                // check wmpml
                $wpml = apply_filters('wpml_current_language', null);
                if (!empty($wpml)) {
                    $lang = $wpml;
                }
            }
        }

        // set hmtl
        if ($type == 'calendar') {
            $iframe = 'iframe_huurkalender_';
            $path = 'vacancy';
        }
        if ($type == 'booking') {
            $iframe = 'iframe_huurkalender_booking3_';
            $path = 'vacancy/booking';
        }
        if ($type == 'combi') {
            $iframe = 'iframe_huurkalender_view2_';
            $path = 'vacancy/all';
        }
        if (is_numeric($atts['id'])) {
            // return
            if ($type == 'combi') {
                $html = '<link href="' . $url . 'online/embed/huurkalender.css" rel="stylesheet"><div class="huurkalender-embed huurkalender-embed-container_overview"><iframe id="' . $iframe . $atts['id'] . '" src="' . $url . $path . '/wordpress-wp-' . $atts['id'] . '.html?type=iframe&wordpress&lang=' . $lang . $month . $start . $color_bg . '" frameborder="0" width="100%" allowfullscreen></iframe><script type="text/javascript" src="' . $url . 'online/embed/huurkalender.js"></script></div>';
            } else {
                $html = '<link href="' . $url . 'online/embed/huurkalender.css" rel="stylesheet"><div class="huurkalender-embed huurkalender-embed-container"><iframe id="' . $iframe . $atts['id'] . '" src="' . $url . $path . '/wordpress-wp-' . $atts['id'] . '.html?type=iframe&wordpress&lang=' . $lang . $month .  $start .  $color_bg . '" frameborder="0" width="100%" allowfullscreen></iframe><script type="text/javascript" src="' . $url . 'online/embed/huurkalender.js"></script></div>';
            }
        } else {
            $html = '<style>.calendar_alert a{color:#0073aa}.calendar_alert{font-size:14px;line-height:20px;color:red;margin:0!important;height:250px;display:block;border:1px solid red;padding:20px;font-family:proxima-nova,"Helvetica Neue",Helvetica,Arial,sans-serif}</style><span class="calendar_alert"><b>ERROR onjuist ID</b><br>Voer een juist KALENDER-ID of ACCOUNT-ID in.<br>Uw ID kunt u vinden via: <a href="https://www.huurkalender.nl/account/widget/">https://www.huurkalender.nl/account/widget/</a><br><br>Kijk voor de handleiding in de kennisbank <a href="https://www.huurkalender.nl/kennisbank/handleidingen/" target="_blank">Huurkalender.nl/kennisbank</a>.<br>Vragen? Stuur een bericht via <a href="https://www.huurkalender.nl/#contact" target="_blank">Huurkalender.nl</a> wij helpen u graag.</span>';
        }
        return $html;
    }

    /**
     * Define constant if not already set.
     *
     * @param  string $name
     * @param  string|bool $value
     */
    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    public function includes()
    {
    }

    public function ts_register_submenu_page()
    {
        add_submenu_page('options-general.php', __('Huurkalender plugin', 'tshcal'), __('Huurkalender plugin', 'tshcal'), 'manage_options', 'huurkalender-wp', array($this, 'ts_render_submenu_page'));
    }

    /**
     * Register settings.
     *
     * @since  1.0
     *
     * @action admin_init
     */
    function ts_register_settings()
    {
        register_setting('tshcal_settings_group', 'tshcal_settings');
    }

    /**
     * Render Admin Menu page.
     *
     * @since 1.0
     */
    public function ts_render_submenu_page()
    {
        global $wp_version;
        $options = get_option('tshcal_settings');
        $content = '';
?>

        <style>
            ol>li,
            ol>li:before {
                border-top: 2px solid #666
            }

            .huurkal_h2 {
                font-size: 1.3em;
                margin: 20px 0 5px
            }

            .huurkal_set_code {
                margin: 15px 0 20px 5px;
                display: inline-block;
                font-size: 16px;
                background-color: #fff;
                padding: 10px;
                border-radius: 5px
            }

            ol {
                counter-reset: li;
                margin-left: 0;
                padding-left: 0;
                margin-bottom: 20px
            }

            ol>li {
                position: relative;
                margin: 0 0 12px 2em;
                padding: 4px 8px;
                list-style: none;
                background: #f9f9f9
            }

            ol>li:before {
                content: counter(li);
                counter-increment: li;
                position: absolute;
                top: -2px;
                left: -2em;
                -moz-box-sizing: border-box;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                width: 2em;
                margin-right: 8px;
                padding: 4px;
                color: #fff;
                background: #666;
                font-weight: 700;
                font-family: "Helvetica Neue", Arial, sans-serif;
                text-align: center
            }

            li ol,
            li ul {
                margin-top: 6px
            }

            ol ol li:last-child {
                margin-bottom: 0
            }
        </style>

        <div class="wrap">
            <h2 style="">Huurkalender Wordpress plugin</h2>
            <p>
                Binnen één minuut heeft u uw huurkalender in uw eigen website ge&iuml;mplementeerd en ontvang direct aanvragen via uw eigen kalender of de boekingsmodule.
            </p>

            <h2 class="huurkal_h2">Kalender plugin implementeren</h2>
            <p>Volg de eenvoudige stappen om de Huurkalender binnen uw website te activeren.</p>
            <ol>
                <li>Bewerk de pagina waar u de plugin wilt weergeven.</li>
                <li>Voeg uw gewenste attribute toe:<br><br>
                    <h3># Kalender plugin weergeven </h3>
                    Voeg de volgende tekst aan uw pagina toe:<br>
                    <span class="huurkal_set_code">[huurkalender id="KALENDER-ID" type="kalender" lang="auto"]</span><br />
                    Uw "KALENDER-ID" kunt u vinden op plugin pagina op Huurkalender.nl<br>
                    <a target="_blank" href="https://www.huurkalender.nl/account/widget.html?wordpress-calendar-id" target="_blank" title="Bekijk uw kalender-id">Klik hier voor uw "KALENDER-ID"</a><br>
                    <br>U kunt de instellingen van de getoonde kalender aanpassen via de instellingen op Huurkalender.nl.<br>
                    <a target="_blank" href="https://www.huurkalender.nl/account/widget/form_settings.html" target="_blank" title="Kalender instellingen">Klik hier voor de kalender instellingen</a><br>
                    <br>
                    Het is ook mogelijk om de instellingen van het aantal maanden te overschrijven. Dit door de <b>month=""</b> aan de code toe te voegen.<br>
                    Hiermee kunt u bijvoorbeeld op één pagina de kalender weergeven met alleen 3 maanden:<br>
                    <span class="huurkal_set_code">[huurkalender id="KALENDER-ID" type="kalender" <b>month="3"</b> lang="auto"]</span><br>
                    En op een andere pagina dezelfde kalender met 6 maanden:<br>
                    <span class="huurkal_set_code">[huurkalender id="KALENDER-ID" type="kalender" <b>month="6"</b> lang="auto"]</span>

                    <h3># Combi kalender plugin weergeven</h3>
                    Als u al uw kalenders in één plugin wilt weergeven gebruikt u <b>type="combi"</b> en uw <b>ACCOUNT-ID</b> ipv kalender-id:<br>
                    <span class="huurkal_set_code">[huurkalender id="ACCOUNT-ID" <b>type="combi"</b> lang="auto"]</span> <br>
                    Uw "ACCOUNT-ID" kunt u vinden op plugin pagina op Huurkalender.nl<br>
                    <a target="_blank" href="https://www.huurkalender.nl/account/widget.html?wordpress-calendar-id" target="_blank" title="Bekijk uw account-id">Klik hier voor uw "ACCOUNT-ID"</a><br>
                    <br>
                    <h3># Direct boeken plugin weergeven</h3>
                    Als u uw direct boeken plugin wilt weergeven gebruikt u <b>type="boeking"</b>:<br>
                    <span class="huurkal_set_code">[huurkalender id="KALENDER-ID" <b>type="boeking"</b> lang="auto"]</span> <br>

                    <h3># Opstarten in een gewenste taal </h3>
                    Als u de kalender of combi kalender automatisch de taal van uw pagina wilt laten overnemen gebruik dan <b>lang="auto"</b>. De Huurkalender plugin kijkt dan naar de geïnstalleerde taal van de huidige pagina:<br>
                    <span class="huurkal_set_code">[huurkalender id="KALENDER-ID" type="kalender" <b>lang="auto"</b>]</span><br>

                    <h3># Opstarten op een gewenste datum </h3>
                    Als u de kalender of combi kalender op een gewenste datum wilt laten starten gebruik dan <b>start="YYYY-MM"</b>. De Huurkalender plugin start dan op de gewenste datum:<br>
                    <span class="huurkal_set_code">[huurkalender id="KALENDER-ID" type="kalender" <b>start="<?= date('Y'); ?>-01"</b>]</span><br>


                    Als u de kalender in het Engels wilt opstarten gebruikt u <b>lang="en":</b><br>
                    <span class="huurkal_set_code">[huurkalender id="KALENDER-ID" type="kalender" <b>lang="en"</b>]</span><br>
                    En als u de kalender bijvoorbeeld in het Frans wilt opstarten dan gebruikt u <b>lang="fr":</b><br>
                    <span class="huurkal_set_code">[huurkalender id="KALENDER-ID" type="kalender" <b>lang="fr"</b>]</span><br>
                    Via Huurkalender.nl kunt u meerdere vertalingen aan uw kalender toevoegen.<br>
                    <a target="_blank" href="https://www.huurkalender.nl/account/widget/form_translate.html" title="Bekijk uw vertalingen">Klik hier voor uw vertalingen</a>
                    <br><br>
                    <h3># Plugin stijling </h3>
                    De algemene instellingen en kleuren van uw kalender plugin kunt u wijzigen via Huurkalender.nl.<br />
                    <a target="_blank" href="https://www.huurkalender.nl/account/widget/form_settings.html" title="Bekijk uw plugin instellingen">Klik hier voor uw instellingen</a><br>
                    <br>
                    <h3># Achtergrondkleur per plugin</h3>
                    Het is mogelijk om elke plugin een verschillende achtergrondkleur mee te geven met de tag "color_bg" en "color_bg_calendar". Bijvoorbeeld voor een paarse achtergrond <b>"color_bg=725ce6"</b>:<br>
                    <span class="huurkal_set_code">[huurkalender id="KALENDER-ID" type="kalender" <b>color_bg="725ce6"</b> lang="auto"]</span><br>
                    De achtergrondkleur in van de plugin instellingen wordt dan overschreven met HEX kleur 725ce6, wat gelijk staat voor de kleur paars. Zie voor meer kleuren de <a href="https://www.google.com/search?q=hex+color" target="_blank">Kleuren kiezer</a>.<br>

                </li>
                <li>Publiceer de pagina en u bent klaar.<br />
                    De plugin wordt nu weergegeven op uw geselecterde pagina.</li>
            </ol>

            <h2 class="huurkal_h2">Vragen of opmerkingen? </h2>

            <p> Zie <a href="https://www.huurkalender.nl/account/widget/">Huurkalender plugin pagina</a> voor meer informatie of bezoek de kennisbank via <a href="https://www.huurkalender.nl/kennisbank/handleidingen/" target="_blank">Huurkalender.nl/kennisbank</a>. Vragen? Stuur ons een bericht via <a target="_blank" href="https://www.huurkalender.nl/#contact" title="Bezoek Huurkalender.nl">Huurkalender.nl</a> of mails ons via service@huurkalender.nl. Wij helpen u graag verder.</p>
            <br><br>

        </div>
<?php

    }

    public function ts_maybe_print_css()
    {

        return '';
    }

    /**
     * Echo the CSS.
     *
     * @since 4.0
     */
    public function ts_the_css()
    {
        $options = get_option('tshcal_settings');
        $raw_content = '';
        $content = wp_kses($raw_content, array('\'', '\"'));
        $content = str_replace('&gt;', '>', $content);
        return $content; // WPCS: xss okay.
    }

    /**
     * Get Ajax URL.
     * @return string
     */
    public function ajax_url()
    {
        return admin_url('admin-ajax.php', 'relative');
    }
}

function TSHCAL()
{
    return TSHCAL::instance();
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'salcode_add_plugin_page_settings_link');

function salcode_add_plugin_page_settings_link($links)
{
    $links[] = '<a href="' .
        admin_url('options-general.php?page=huurkalender-wp') .
        '">' . __('Instellingen') . '</a>';
    return $links;
}

// Global for backwards compatibility.
$GLOBALS['tshcal'] = TSHCAL();
