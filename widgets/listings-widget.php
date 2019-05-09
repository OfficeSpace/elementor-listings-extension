<?php


class Listings_Widget extends \Elementor\Widget_Base {
/**
   * Get widget name.
   *
   * Retrieve Listings widget name.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string Widget name.
   */
  public function get_name() {
    return 'listings';
  }

  /**
   * Get widget title.
   *
   * Retrieve Listings widget title.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string Widget title.
   */
  public function get_title() {
    return __( 'Listings', 'plugin-name' );
  }

  /**
   * Get widget icon.
   *
   * Retrieve Listings widget icon.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string Widget icon.
   */
  public function get_icon() {
    return 'fa fa-code';
  }

  /**
   * Get widget categories.
   *
   * Retrieve the list of categories the Listings widget belongs to.
   *
   * @since 1.0.0
   * @access public
   *
   * @return array Widget categories.
   */
  public function get_categories() {
    return [ 'general' ];
  }

  /**
   * Register Listings widget controls.
   *
   * Adds different input fields to allow the user to change and customize the widget settings.
   *
   * @since 1.0.0
   * @access protected
   */
  protected function _register_controls() {

    $this->start_controls_section(
      'content_section',
      [
        'label' => __( 'Content', 'plugin-name' ),
        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
      ]
    );

    $this->add_control(
      'number',
      [
        'label' => __( 'Number of Listings', 'plugin-name' ),
        'type' => \Elementor\Controls_Manager::NUMBER,
        'input_type' => 'number'
      ]
    );

    $this->end_controls_section();

  }

  protected function get_listings($amount){
    $url = parse_url(get_site_url());

    $call = $url['scheme']."://".$url['host']."/api/listings/blog_widget?limit_amount=".$amount;

    $curl = curl_init();
    if (strpos($url['host'],'staging') !== FALSE || strpos($url['host'],'local') !== FALSE ){
      $user = $_SERVER['STAGING_USER'];
      $pass = $_SERVER["STAGING_PASS"];
      curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($curl, CURLOPT_USERPWD, $user.":".$pass);
    }
    curl_setopt($curl, CURLOPT_URL, $call);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);

    $listings = [];
    $default_path = 'wa/seattle';
    //make sure no error
    if( empty(curl_error($curl))){
      $result = JSON_decode($result);
      foreach( $result->buildings as $bld ){
        //default search path
        $default_path = (!empty($bld->region_id)) ? $bld->region_id : $default_path;

        //default market name
        $market_name = $bld->city.', '.$bld->state;
        if( !empty($bld->submarket_name)){
          $market_name = $bld->submarket_name;
        } else if (!empty($bld->market_name)){
          $market_name = $bld->market_name;
        }

        $img_url = (empty($bld->photo_source_url)) ? $bld->map_image : $bld->photo_source_url;

        $listings[] = '<div class="officespace-listing">'.
                        '<a id="building_'.$bld->id.'" class="building-item-link" href="'.$url["scheme"]."://".$url['host'].$bld->building_path.'">'.
                          '<div class="image td">'.
                            '<span class="helper"></span>'.
                            '<img src="'.$img_url.'" />'.
                          '</div>'.
                          '<div class="name td">'.
                            '<div class="sqft_size">Available Size: '.$bld->min_size.' - '.$bld->max_size.' SF</div>'.
                            '<div class="address">'.$bld->address.'</div>'.
                            '<div class="city-state">'.$market_name.'</div>'.
                          '</div>'.
                        '</a>'.
                      '</div>';
      }
    }
    return $listings;
  }

  /**
   * Render Listings widget output on the frontend.
   *
   * Written in PHP and used to generate the final HTML.
   *
   * @since 1.0.0
   * @access protected
   */
  protected function render() {

    $settings = $this->get_settings_for_display();

    $listings = $this-> get_listings(intval($settings['number']));
    echo '<div class="listings-elementor-widget">';
    foreach( $listings as $listing ) {
      echo $listing;
    }

    echo '</div>';

  }
}
