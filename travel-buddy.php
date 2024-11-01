<?php
/**
 * @wordpress-plugin
 * Plugin Name:		Travel Buddy
 * Plugin URI:      https://wordpress.org/plugins/travel-buddy/
 * Description: 	This plugin adds a powerful widget to your WordPress site, allowing users to check visa requirements for 199 different passports. It delivers real-time updates on visa policies, ensuring your audience has access to the latest travel information.
 * Author: 			travelbuddy
 * Author URI: 		https://travel-buddy.ai/
 * Version: 		1.2.0
 * Requires at least: 6.0
 * Requires PHP:    5.6
 * Text Domain:     travel-buddy
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function trbdai_enqueue_styles() {
    wp_enqueue_style('travel-buddy-css', plugin_dir_url(__FILE__) . 'assets/travel-buddy-css.min.css', null, '1.2.0');
}
add_action('wp_enqueue_scripts', 'trbdai_enqueue_styles');


function trbdai_plugin_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('travel-buddy-script', plugin_dir_url(__FILE__) . 'assets/travel-buddy.min.js', array('jquery'), '1.2.0', true);

    // Localize script to pass AJAX URL
    wp_localize_script('travel-buddy-script', 'travelbuddyAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'trbdai_plugin_scripts');

// Widget
if ( ! class_exists( 'travelbuddyWidget' ) ) {
    class travelbuddyWidget extends WP_Widget {

        public function __construct() {
            parent::__construct(
                'travel-buddy-widget', 
                __( 'Visa Requirement Widget', "travel-buddy" ),
                array( 'description' => __( 'Displays a visa requirement form.', 'travel-buddy' ), )
            );
        }

        public function widget( $args, $instance ) {

            $title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) : '';
            $content = isset( $instance['content'] ) ? wp_strip_all_tags( $instance['content'] ) : '[travel-buddy]';

            // outputs the content of the widget
            echo wp_kses_post($args['before_widget']);

            if ( ! empty( $title ) ) {
                echo wp_kses_post($args['before_title']) . esc_html( $title ) . wp_kses_post($args['after_title']);
            }
            $options = get_option('trbdai_settings');
            $power_by = isset($options['trbdai_show_powered_by']) ? $options['trbdai_show_powered_by'] : false;
            $header_logo = isset($options['trbdai_show_header_logo']) ? $options['trbdai_show_header_logo'] : false;
        
            
            $logo = plugin_dir_url(__FILE__) . 'images/travelbuddy-logo.jpg';
            $nonce = wp_create_nonce( 'travel-buddy-nonce' );
            $dropdown_p = $this->country_dropdown("passport");
            $dropdown_d = $this->country_dropdown("destination");
            ?>
            <? 
            if ($header_logo) {
            ?>
            <img src="<?php echo esc_url($logo) ?>" alt="Travel Buddy logo" width="350" height="80">
            <?
                }
            ?>
            <form id="travel-buddy-form">
                <div class="travel-buddy-dd">
                    <?php echo esc_html__("Passport of:", "travel-buddy") ?>
                    <select name="country" id="country-select">
                        <?php echo $dropdown_p ?>
                    </select>
                    <?php echo esc_html__("Travel to:", "travel-buddy") ?>
                    <select name="destination" id="destination-select">
                        <?php echo $dropdown_d ?>
                    </select>
                </div>
                <div class="travel-buddy-bt"><button type="submit"><?php echo esc_html__('Check visa requirement', "travel-buddy") ?></button></div>
                <input type="hidden" name="wpnonc" value="<?php echo esc_html($nonce) ?>" id="wpnonc">
            </form>
            <div id="travel-buddy-result"></div> 
            <?php
            if ($power_by) {
                ?>
                <div class="travel-buddy-footer"><?php echo esc_html__("Powered by", "travel-buddy") ?> <a href="<?php echo esc_url("https://travel-buddy.ai") ?>" target="_blank"><?php echo esc_html__('travel-buddy.ai', "travel-buddy") ?></a></div>
                <?php
            } else if ($header_logo) {
                ?>
                <div class="travel-buddy-footer"></div>
                <?php
            } 
            
            echo wp_kses_post($args['after_widget']);
        }

        public function form( $instance ) {

            $title = isset( $instance['title'] ) ? $instance['title'] : '';
            $content = isset ( $instance['content'] ) ? wp_strip_all_tags( $instance['content'] ) : '[travel-buddy]';
            
            $logo = plugin_dir_url(__FILE__) . 'images/travelbuddy-logo.jpg';
            $dropdown_p = $this->country_dropdown("passport");
            $dropdown_d = $this->country_dropdown("destination");
            ?>
            <img src="<?php echo esc_url($logo) ?>" alt="Travel Buddy logo" width="350" height="80">
            <form id="travel-buddy-form">
                <div class="travel-buddy-dd">
                    <?php echo esc_html__("Passport of:", "travel-buddy") ?>
                    <select name="country" id="country-select">
                        <?php echo $dropdown_p ?>
                    </select>
                    <?php echo esc_html__("Travel to:", "travel-buddy") ?>
                    <select name="destination" id="destination-select">
                        <?php echo $dropdown_d ?>
                    </select>
                </div>
                <div class="travel-buddy-bt"><button type="submit"><?php echo esc_html__('Check visa requirement', "travel-buddy") ?></button></div>
                <input type="hidden" name="wpnonc" value="<?php echo esc_html($nonce) ?>" id="wpnonc">
            </form>
            <div id="travel-buddy-result"></div>
            <div class="travel-buddy-footer"><?php echo esc_html__("Powered by", "travel-buddy") ?> <a href="<?php echo esc_url("https://travel-buddy.ai") ?>" target="_blank"><?php echo esc_html__('travel-buddy.ai', "travel-buddy") ?></a></div>
            <?php
        }

        public function update( $new_instance, $old_instance ) {
            $instance = array();
            $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
            return $instance;
        }
        public function countryList() {
            return array(
                "AF"=>"Afghanistan","AL"=>"Albania","DZ"=>"Algeria","AD"=>"Andorra","AO"=>"Angola","AI"=>"Anguilla","AG"=>"Antigua and Barbuda","AR"=>"Argentina","AM"=>"Armenia","AW"=>"Aruba","AU"=>"Australia","AT"=>"Austria","AZ"=>"Azerbaijan","BS"=>"Bahamas","BH"=>"Bahrain","BD"=>"Bangladesh","BB"=>"Barbados","BY"=>"Belarus","BE"=>"Belgium","BZ"=>"Belize","BJ"=>"Benin","BM"=>"Bermuda","BT"=>"Bhutan","BO"=>"Bolivia","BA"=>"Bosnia and Herzegovina","BW"=>"Botswana","BR"=>"Brazil","BN"=>"Brunei","BG"=>"Bulgaria","BF"=>"Burkina Faso","BI"=>"Burundi","KH"=>"Cambodia","CM"=>"Cameroon","CA"=>"Canada","CV"=>"Cape Verde","KY"=>"Cayman Islands","CF"=>"Central African Republic","TD"=>"Chad","CL"=>"Chile","CN"=>"China","CO"=>"Colombia","KM"=>"Comoros","CG"=>"Congo","CD"=>"Congo (Dem. Rep.)","CR"=>"Costa Rica","CI"=>"Cote d'Ivoire","HR"=>"Croatia","CU"=>"Cuba","CW"=>"Curacao","CY"=>"Cyprus","CZ"=>"Czech Republic","DK"=>"Denmark","DJ"=>"Djibouti","DM"=>"Dominica","DO"=>"Dominican Republic","EC"=>"Ecuador","EG"=>"Egypt","SV"=>"El Salvador","GQ"=>"Equatorial Guinea","ER"=>"Eritrea","EE"=>"Estonia","SZ"=>"Eswatini","ET"=>"Ethiopia","FJ"=>"Fiji","FI"=>"Finland","FR"=>"France","GA"=>"Gabon","GM"=>"Gambia","GE"=>"Georgia","DE"=>"Germany","GH"=>"Ghana","GR"=>"Greece","GD"=>"Grenada","GP"=>"Guadeloupe","GT"=>"Guatemala","GN"=>"Guinea","GW"=>"Guinea-Bissau","GY"=>"Guyana","HT"=>"Haiti","HN"=>"Honduras","HK"=>"Hong Kong","HU"=>"Hungary","IS"=>"Iceland","IN"=>"India","ID"=>"Indonesia","IR"=>"Iran","IQ"=>"Iraq","IE"=>"Ireland","IL"=>"Israel","IT"=>"Italy","JM"=>"Jamaica","JP"=>"Japan","JO"=>"Jordan","KZ"=>"Kazakhstan","KE"=>"Kenya","KI"=>"Kiribati","XK"=>"Kosovo","KW"=>"Kuwait","KG"=>"Kyrgyzstan","LA"=>"Laos","LV"=>"Latvia","LB"=>"Lebanon","LS"=>"Lesotho","LR"=>"Liberia","LY"=>"Libya","LI"=>"Liechtenstein","LT"=>"Lithuania","LU"=>"Luxembourg","MO"=>"Macau","MG"=>"Madagascar","MW"=>"Malawi","MY"=>"Malaysia","MV"=>"Maldives","ML"=>"Mali","MT"=>"Malta","MH"=>"Marshall Islands","MQ"=>"Martinique","MR"=>"Mauritania","MU"=>"Mauritius","MX"=>"Mexico","FM"=>"Micronesia","MD"=>"Moldova","MC"=>"Monaco","MN"=>"Mongolia","ME"=>"Montenegro","MS"=>"Montserrat","MA"=>"Morocco","MZ"=>"Mozambique","MM"=>"Myanmar","NA"=>"Namibia","NR"=>"Nauru","NP"=>"Nepal","NL"=>"Netherlands","NZ"=>"New Zealand","NI"=>"Nicaragua","NE"=>"Niger","NG"=>"Nigeria","KP"=>"North Korea","MK"=>"North Macedonia","NO"=>"Norway","OM"=>"Oman","PK"=>"Pakistan","PW"=>"Palau","PS"=>"Palestinian Territories","PA"=>"Panama","PG"=>"Papua New Guinea","PY"=>"Paraguay","PE"=>"Peru","PH"=>"Philippines","PL"=>"Poland","PT"=>"Portugal","QA"=>"Qatar","RO"=>"Romania","RU"=>"Russian Federation","RW"=>"Rwanda","KN"=>"Saint Kitts and Nevis","LC"=>"Saint Lucia","MF"=>"Saint Martin","VC"=>"Saint Vincent and the Grenadines","WS"=>"Samoa","SM"=>"San Marino","ST"=>"Sao Tome and Principe","SA"=>"Saudi Arabia","SN"=>"Senegal","RS"=>"Serbia","SC"=>"Seychelles","SL"=>"Sierra Leone","SG"=>"Singapore","SX"=>"Sint Maarten","SK"=>"Slovakia","SI"=>"Slovenia","SB"=>"Solomon Islands","SO"=>"Somalia","ZA"=>"South Africa","KR"=>"South Korea","SS"=>"South Sudan","ES"=>"Spain","LK"=>"Sri Lanka","SD"=>"Sudan","SR"=>"Suriname","SE"=>"Sweden","CH"=>"Switzerland","SY"=>"Syria","TW"=>"Taiwan","TJ"=>"Tajikistan","TZ"=>"Tanzania","TH"=>"Thailand","TL"=>"Timor-Leste","TG"=>"Togo","TO"=>"Tonga","TT"=>"Trinidad and Tobago","TN"=>"Tunisia","TR"=>"Türkiye","TM"=>"Turkmenistan","TC"=>"Turks and Caicos","TV"=>"Tuvalu","UG"=>"Uganda","UA"=>"Ukraine","AE"=>"United Arab Emirates","GB"=>"United Kingdom","US"=>"United States of America","UY"=>"Uruguay","UZ"=>"Uzbekistan","VU"=>"Vanuatu","VA"=>"Vatican City","VE"=>"Venezuela","VN"=>"Viet Nam","VG"=>"Virgin Islands (British)","YE"=>"Yemen","ZM"=>"Zambia","ZW"=>"Zimbabwe"
            );
        }
        private function country_dropdown($dropdown) {
            $countryCodes = $this->countryList();
            $missing_passport = array("AI","AW","BM","KY","CW","GP","MQ","MS","MF","SX","TC","VG");
            $ret = '<option value=""></option>';
            foreach($countryCodes as $key=>$value) {
                if ($dropdown == "passport" && in_array($key,$missing_passport)) continue;
                $ret .= '<option value="' . esc_attr($key) . '">' . esc_html($value) . '</option>';
            }
            return $ret;
        }
        
    }
}

// register teh Widget
function trbdai_widget() {
    register_widget( 'travelbuddyWidget' );
}
add_action( 'widgets_init', 'trbdai_widget' );

// allow shortcode in widgets
add_filter( 'widget_text', 'do_shortcode' );


// Add a new menu under Settings
function trbdai_add_admin_menu() {
    add_options_page('Travel Buddy Settings', 'Travel Buddy', 'manage_options', 'travel-buddy', 'trbdai_options_page');
}
add_action('admin_menu', 'trbdai_add_admin_menu');

// Admin settings
function trbdai_settings_init() {
    register_setting('travelbuddyPlugin', 'trbdai_settings');

    add_settings_section(
        'trbdai_plugin_section', 
        __('API Configuration', "travel-buddy"), 
        'trbdai_settings_section_callback', 
        'travelbuddyPlugin'
    );
    // Add API key box
    add_settings_field(
        'trbdai_api_key', 
        __('X-RapidAPI-Key', "travel-buddy"), 
        'trbdai_api_key_render', 
        'travelbuddyPlugin', 
        'trbdai_plugin_section'
    );

    // Add a checkbox for "Show Powered by in the footer"
    add_settings_field(
        'trbdai_show_powered_by', 
        __('Show "Powered by" in the footer', "travel-buddy"),
        'trbdai_show_powered_by_render', 
        'travelbuddyPlugin', 
        'trbdai_plugin_section' 
    );

    // Add a checkbox for "Show header image logo"
    add_settings_field(
        'trbdai_show_header_logo', 
        __('Show "Travel Buddy logo" in the header', "travel-buddy"),
        'trbdai_show_header_logo_render', 
        'travelbuddyPlugin', 
        'trbdai_plugin_section' 
    );
}
add_action('admin_init', 'trbdai_settings_init');

// Render the API key box
function trbdai_api_key_render() {
    $options = get_option('trbdai_settings');
    ?>
    <input type='text' name='trbdai_settings[trbdai_api_key]' value='<?php echo esc_attr($options['trbdai_api_key']); ?>'>
    <?php
}

// Function to render the checkbox for "Show Powered by in the footer"
function trbdai_show_powered_by_render() {
    $options = get_option('trbdai_settings');
    
    ?>
     <input type="checkbox" name="trbdai_settings[trbdai_show_powered_by]" <?php checked(isset($options["trbdai_show_powered_by"]) ? $options["trbdai_show_powered_by"] : 0) ?> value="1" >
     <?php
}

function trbdai_show_header_logo_render() {
    $options = get_option('trbdai_settings');
    ?>
     <input type="checkbox" name="trbdai_settings[trbdai_show_header_logo]" <?php checked(isset($options["trbdai_show_header_logo"]) ? $options["trbdai_show_header_logo"] : 0) ?> value="1" >
     <?php
}


function trbdai_settings_section_callback() {
    $return = '<p>Get an API key: <a href="https://rapidapi.com/TravelBuddyAI/api/visa-requirement" target="_blank">Sign up for free here.</a><p>';
    $return .= '<p>Enjoy our service with a free plan that includes up to 600 requests per month.</p>';
    echo wp_kses_post($return);
    
}

function trbdai_options_page() {
    ?>
    <form action='options.php' method='post'>

        <?php
        echo wp_kses_post("<h2>Travel Buddy Widget Settings</h2>");
        settings_fields('travelbuddyPlugin');
        do_settings_sections('travelbuddyPlugin');
        submit_button();
        ?>

    </form>
    
    <h3><?php esc_html_e("How to Use the Plugin:", "travel-buddy")?></h3>
    <ul>
        <ol><?php esc_html_e("Navigate to your WordPress dashboard.", "travel-buddy")?></ol>
        <ol><?php esc_html_e('Go to "Appearance" and then select "Widgets".', "travel-buddy")?></ol>
        <ol><?php esc_html_e('In the Widgets area, search for the "Visa Requirement Widget".', "travel-buddy")?></ol>
        <ol><?php esc_html_e('Add it to your desired widget area or customize it as needed.', "travel-buddy")?></ol>
    </ul>
    <p>
        <?php esc_html_e('You can display the widget on every page using the shortcode [travel_buddy].', "travel-buddy")?>
    </p>
    <p>
        <?php esc_html_e('For any comments or inquiries, please don\'t hesitate to reach out to us at ', "travel-buddy")?>
        <a href="mailto:info@travel-buddy.ai" target="_blank">info@travel-buddy.ai</a>.
        <?php esc_html_e('We\'re here to help!', "travel-buddy")?>
    </p>
    <?php
}

// Ajax handler Call the API
function trbdai_ajax_handler() {
    $nonce = sanitize_text_field($_REQUEST['wpnonc']);

    if ( ! wp_verify_nonce( $nonce, 'travel-buddy-nonce' ) && isset($_POST['passport']) && isset($_POST['destination'])) {
        die( 'Security check'); 
    }

    $return = "";
    $passport = sanitize_text_field($_POST['passport']);
    $destination = sanitize_text_field($_POST['destination']);
    $options = get_option('trbdai_settings');
    $api_key = sanitize_text_field($options['trbdai_api_key']);

    // Validate the input data
    if (trbdai_country_code("passport", $passport ) || trbdai_country_code("destination", $destination) ) {
        die( 'Wrong Passport or destination');
    }

    $boundary = '----011000010111000001101001';

    $body = "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"passport\"\r\n\r\n";
    $body .= "{$passport}\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"destination\"\r\n\r\n";
    $body .= "{$destination}\r\n";
    $body .= "--{$boundary}--\r\n";

    // Define the request arguments
    $args = array(
        'body'        => $body,
        'headers'     => array(
            'Content-Type' => "multipart/form-data; boundary={$boundary}",
            'x-rapidapi-host' => 'visa-requirement.p.rapidapi.com',
            'x-rapidapi-key'  => $api_key,
        ),
        'timeout'     => 30,
    );
    // Perform API request here and output the HTML structure with the data
    // For example, use wp_remote_post() to send the API request
    $response = wp_remote_post('https://visa-requirement.p.rapidapi.com', $args);

    if (is_wp_error($response)) {
        echo 'Error in API call';
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        $elink_style = "";
        if ($data["link"] != "") {
            $elink_style = "elink";
            $elink_new = '<span class="evisa-button">
                <a href="'.esc_url($data["link"]).'" target="_blank" rel="nofollow">
                
                    <img src="' . plugin_dir_url(__FILE__) . 'images/re.png" alt="Redirect icon" width="20" height="20">  
                </a></span>';
        }
        
        $stay = "";
        if ($data["stay_of"] != '') {
            $stay = $data["stay_of"];
        }
        if ($data["except_text"] != '') {
            $data["except_text"] = '*'.$data["except_text"];
            $data["visa"] = $data["visa"].'*';
            $except_dis = "";
        } else {
            $except_dis = "travel-buddy-dis";
        }
        
        
        if (!$data["error"]) {
            $return = '<div class="travel-buddy-vr-main">
                    <div class="travel-buddy-vr-right">
                        <div class="travel-buddy-vr-country"> 
                            <span class="country_name">'. esc_html($data["destination"]) .'</span>
                        </div>
                        <div class="travel-buddy-vr-info">
                            Continent: <span>' . esc_html($data["continent"]) . ' | </span>Capital: <span>'. esc_html($data["capital"]).' | </span>Timezone: <span>UTC'. esc_html($data["timezone"]) .' | </span>
                            Currency: <span>'. esc_html($data["currency"]) .' | </span> Dialing code: <span>'. esc_html($data["phone_code"]) .' | </span>Passport must be valid: <span>'. esc_html($data["pass_valid"]) .'</span>
                        </div>
                        <div class="travel-buddy-vr-text travel-buddy-vr-'. esc_attr($data["color"]) .'-bg '.$elink_style.'"><span class="vtext">' . esc_html($data["visa"]) . '<sup>' . esc_html($stay)  . '</sup></span>'. wp_kses_post($elink_new) .'</div>
                        <div class="travel-buddy-vr-except '.esc_attr($except_dis).'">'. esc_html($data["except_text"]) .'</div>
                    </div>
                </div>';
        } else {
            $return = "We are currently experiencing a temporary technical issue. Please try again later.";
        }

        echo wp_kses_post($return);
        // Format and output your response here based on $data
    }
    wp_die(); // End AJAX request
}

/** Checking if passport code and desctination are allowed 
* return: true if is not allowed
*/
function trbdai_country_code($check, $code) {
    $clist = array("AF","AL","DZ","AD","AO","AI","AG","AR","AM","AW","AU","AT","AZ","BS","BH","BD","BB","BY","BE","BZ","BJ","BM","BT","BO","BA","BW","BR","BN","BG","BF","BI","KH","CM","CA","CV","KY","CF","TD","CL","CN","CO","KM","CG","CD","CR","CI","HR","CU","CW","CY","CZ","DK","DJ","DM","DO","EC","EG","SV","GQ","ER","EE","SZ","ET","FJ","FI","FR","GA","GM","GE","DE","GH","GR","GD","GP","GT","GN","GW","GY","HT","HN","HK","HU","IS","IN","ID","IR","IQ","IE","IL","IT","JM","JP","JO","KZ","KE","KI","XK","KW","KG","LA","LV","LB","LS","LR","LY","LI","LT","LU","MO","MG","MW","MY","MV","ML","MT","MH","MQ","MR","MU","MX","FM","MD","MC","MN","ME","MS","MA","MZ","MM","NA","NR","NP","NL","NZ","NI","NE","NG","KP","MK","NO","OM","PK","PW","PS","PA","PG","PY","PE","PH","PL","PT","QA","RO","RU","RW","KN","LC","MF","VC","WS","SM","ST","SA","SN","RS","SC","SL","SG","SX","SK","SI","SB","SO","ZA","KR","SS","ES","LK","SD","SR","SE","CH","SY","TW","TJ","TZ","TH","TL","TG","TO","TT","TN","TR","TM","TC","TV","UG","UA","AE","GB","US","UY","UZ","VU","VA","VE","VN","VG","YE","ZM","ZW");
    $missing_passport = array("AI","AW","BM","KY","CW","GP","MQ","MS","MF","SX","TC","VG");

    if (strlen($code) != 2) return true;
    if ($check == "passport" && in_array($code, $clist) && !in_array($code, $missing_passport)) return false;
    if ($check == "destination" && in_array($code, $clist)) return false;

    return true;
}
add_action('wp_ajax_trbdai_action', 'trbdai_ajax_handler');
add_action('wp_ajax_nopriv_trbdai_action', 'trbdai_ajax_handler');

// Shortcode function to display the widget content
function trbdai_shortcode($atts) {
    ob_start();
    
    $widget_args = array(
        'before_widget' => '<div class="widget_travel-buddy-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    );

    // Check if class exists, and then create the widget instance
    if (class_exists('travelbuddyWidget')) {
        $instance = array(
            'title' => isset($atts['title']) ? $atts['title'] : '',
        );

        $widget = new travelbuddyWidget();
        $widget->widget($widget_args, $instance);
    }

    return ob_get_clean();
}

// Register the shortcode
add_shortcode('travel_buddy', 'trbdai_shortcode');
