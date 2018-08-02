<?php
 
/**
 * Plugin Name: Dylans Shipping
 * Plugin URI: https://www.Dylans.com
 * Description: Dylans Rating Integration for Woocommerce
 * Version: 1.0.0
 * Author: Dylan Jackson
 * Author URI: http://www.theavidexperience.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: Dylans
 */
 
if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function Dylans_shipping_method() {
        if ( ! class_exists( 'Dylans_Shipping_Method' ) ) {
            class Dylans_Shipping_Method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = 'Dylans'; 
                    $this->method_title       = __( 'Dylans Shipping', 'Dylans' );  
                    $this->method_description = __( 'Custom Shipping Method for Dylans', 'Dylans' ); 
 
                    // Availability & Countries
                    $this->availability = 'including';
                    $this->countries = array(
                        'US', // Unites States of America
                        'CA'
                        );
 
                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Dylans Shipping', 'Dylans' );
                }
 
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings(); 
 
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }
 
                /**
                 * Define settings field for this shipping
                 * @return void 
                 */
                function init_form_fields() { 
 
                    $this->form_fields = array(
 
                     'enabled' => array(
                          'title' => __( 'Enable', 'Dylans' ),
                          'type' => 'checkbox',
                          'description' => __( 'Enable this shipping.', 'Dylans' ),
                          'default' => 'yes'
                          ),
 
                     'title' => array(
                        'title' => __( 'Title', 'Dylans' ),
                          'type' => 'text',
                          'description' => __( 'Title to be display on site', 'Dylans' ),
                          'default' => __( 'Dylans Shipping', 'Dylans' )
                          ),
 
                     'weight' => array(
                        'title' => __( 'Weight (lbs)', 'Dylans' ),
                          'type' => 'number',
                          'description' => __( 'Maximum allowed weight', 'Dylans' ),
                          'default' => 100
                          ),
 
                     );
 
                }
 
                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package ) {
                    
                    $weight = 0;
                    $cost = 0;
                    $country = $package["destination"]["country"];
 
                    foreach ( $package['contents'] as $item_id => $values ) 
                    { 
                        $_product = $values['data']; 
                        $weight = $weight + $_product->get_weight() * $values['quantity'];
                        $quantity = $values['quantity'];
                        $width =  $_product->get_width() * $values['quantity'];
                        $length =  $_product->get_length() * $values['quantity'];
                        $height =  $_product->get_height() * $values['quantity'];


                    }


                    // wc_add_notice(print_r($package['contents'], true), "error");

 
                    $countryZones = array(
                        'HR' => 0,
                        'US' => 3,
                        'GB' => 2,
                        'CA' => 3,
                        'ES' => 2,
                        'DE' => 1,
                        'IT' => 1
                        );
 
                    $zonePrices = array(
                        0 => 10,
                        1 => 30,
                        2 => 50,
                        3 => 70
                        );
 
                    $zoneFromCountry = $countryZones[ $country ];
                    $priceFromZone = $zonePrices[ $zoneFromCountry ];
 
                    $cost += $priceFromZone;
                    $origin = $package;
                    $destination = $package["destination"];

                    $d_Country = $destination["country"];
                    $d_State = $destination["state"];
                    $d_City = $destination["city"];
                    $d_zip = $destination["postcode"];
                    $d_address = $destination["address"];

                    $o_Country = "USA";
                    $o_State = "AZ";
                    $o_City = "Phoenix";
                    $o_zip = "85281";
                    $o_address ="7350 N Dobson Rd";

                    # Logging data to page for development purposes 

                    # wc_add_notice(print_r($origin, true), "error");
                    // wc_add_notice('===============================================================', "error");
                    // wc_add_notice(print_r($destination, true), "error");
                    // wc_add_notice('===============================================================', "error");

                    // wc_add_notice(print_r($d_Country, true), "error");
                    // wc_add_notice(print_r($d_State, true), "error");
                    // wc_add_notice(print_r($d_City, true), "error");
                    // wc_add_notice(print_r($d_zip, true), "error");
                    // wc_add_notice(print_r($d_address, true), "error");
                    // wc_add_notice("============= DATE HERE =================", "error");

                    # wc_add_notice(date("m/d/Y"), "error");
                    $currentDate = (string)date("m/d/Y") ;
                    $currentMonth = date("m");
                    $currentYear = date("Y");
                    
                    // Api call to get custom rates
                    $curl = curl_init();
                    $data = json_encode(array(
                        "PickupDate"=>htmlspecialchars($currentDate), // htmlspecialchars is to format the /'s in the date
                        "ExtremeLength"=> null,
                        "ExtremeLengthBundleCount"=> null,
                        "Stackable"=> false,
                        "TerminalPickup"=> false,
                        "ValueOfGoods"=> 0,
                        "ShipmentNew"=> false,
                        "Origin" =>array( // Woocommerce doesn't define origin address, Might have to customize integration for stores needs
                        "Street"=> "",
                        "City"=> "Columbus",
                        "State"=> "OH",
                        "Zip"=> "43201",
                        "Country"=> "USA"
                        ),
                        "Destination"=> array(
                        "Street"=> $d_address,
                        "City"=> $d_Country,
                        "State"=> $d_State,
                        "Zip"=> $d_zip,
                        "Country"=> "USA" // Any customers shipping in Mexico / Canada etc?
                        ),
                        "Items" => array(array(
                        "PieceCount"=> $quantity,
                        "PalletCount"=> "1", // Need to place logic for how many pieces = 1 pallet
                        "Length"=> $length,
                        "Width"=> $width,
                        "Height"=> $height,
                        "Weight"=> $weight,
                        "WeightType"=> 1,
                        "ProductClass"=> "70",
                        "LinearFeet"=> null,
                        "NmfcNumber"=> "Test12345",
                        "Description"=> "Woocommerce Automated Shipping Quote",
                        "PackageType"=> 0,
                        "Hazmat"=> false,
                        "HazmatClass"=> "",
                        "PackingGroupNumber"=> "",
                        "UnPoNumber"=> "",
                        "Stackable"=> false
                        )),
                        "Accessorials" => array()
                        ));
                                        
                    curl_setopt_array($curl, array(
                    CURLOPT_URL => "http://api.theavidexperience.com/Integrate/LTL/1.0/RateRequest?apiKey={Insert_API_KEY_HERE}",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $data,
                    CURLOPT_HTTPHEADER => array(
                        "Authorization: Basic cslijdcoiewnlncwehoewifhiowef=", // Invalid Auth token don't bother decoding ;)
                        "Cache-Control: no-cache",
                        "Content-Type: application/json",
                        "Postman-Token: 0ee1d398-641a-424b-84b0-651c33c2ce43"
                    ),
                    ));

                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    # print_r($json['LowestCostRate']);
                    curl_close($curl);



                    if ($err) {
                    # If error, show error notice on screen 
                    wc_add_notice(print_r($err, true), "error");
                    
                    } else {
                        $json = json_decode($response, true);
                        $OnlyOne = False;

                        $lowPrice = $json['LowestCostRate']['LtlAmount'];
                        $fastPrice = $json['QuickestTransitRate']['LtlAmount'];

                        $lowDays = $json['LowestCostRate']['LtlServiceDays'];
                        $fastDays = $json['QuickestTransitRate']['LtlServiceDays'];

                        if( $lowPrice && $fastPrice){

                        } else {
                        $onePrice = $json['LtlAmount'];
                        $OnlyOne = True;
                        $days = $json['LtlServiceDays'];
                        }

                        }

                    if ($OnlyOne == True){
                            $oneRate = array (   
                                'id' =>'BestRate',
                            'label' => 'Best Rate Possible (Speed + Rate)',
                            'cost' => $onePrice);

                            $this->add_rate( $oneRate );
                        } else {
                            $label1 = 'Lowest Rate';
                            $rate = array(
                                'id' =>'LowRate',
                                'label' =>$label1 . " - "  . $lowDays . " Days" ,
                                'cost' => $lowPrice
                            );
                            
                            $label2 = 'Fastest Rate';
                            $rate2 = array(
                                'id' =>'FastRate',
                                'label' => $label2 . " - " . $fastDays . " Days",
                                'cost' => $fastPrice
                            );

                            $this->add_rate( $rate );
                            $this->add_rate( $rate2 );
                        }

// Print response to screen for development purposes 

// wc_add_notice("=========== RESPONSE ================", "error");

// wc_add_notice(print_r($response, true), "error");
// wc_add_notice("========== Rate 1 and Rate 2 ==========", "error");

// wc_add_notice($lowPrice, "error");
#wc_add_notice(print_r($rate2->{'cost'}, true), "error");


                }
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'Dylans_shipping_method' );
 
    function add_Dylans_shipping_method( $methods ) {
        $methods[] = 'Dylans_Shipping_Method';
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'add_Dylans_shipping_method' );
 
    function Dylans_validate_order( $posted )   {
        
        $packages = WC()->shipping->get_packages();
        
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
         
        if( is_array( $chosen_methods ) && in_array( 'Dylans', $chosen_methods ) ) {
             
            foreach ( $packages as $i => $package ) {
                
                if ( $chosen_methods[ $i ] != "Dylans" ) {
                            
                    continue;
                             
                }
                
                $Dylans_Shipping_Method = new Dylans_Shipping_Method();
                $weightLimit = (int) $Dylans_Shipping_Method->settings['weight'];
                $weight = 0;
 
                foreach ( $package['contents'] as $item_id => $values ) 
                { 
                    $_product = $values['data']; 
                    $weight = $weight + $_product->get_weight() * $values['quantity']; 
                }
 
                $weight = wc_get_weight( $weight, 'kg' );
                
                if( $weight > $weightLimit ) {
 
                        $message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'Dylans' ), $weight, $weightLimit, $Dylans_Shipping_Method->title );
                             
                        $messageType = "error";
 
                        if( ! wc_has_notice( $message, $messageType ) ) {
                         
                            wc_add_notice( $message, $messageType );
                      
                        }
                }
            }       
        } 
    }
 
    add_action( 'woocommerce_review_order_before_cart_contents', 'Dylans_validate_order' , 10 );
    add_action( 'woocommerce_after_checkout_validation', 'Dylans_validate_order' , 10 );
}