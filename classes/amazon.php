<?php
namespace DevhatsAmazon;
new Amazon;

class Amazon
{
  public $options = array();

  public function __construct()
  {
    // setup database table
    add_action( 'plugins_loaded', array( $this, 'setup_table' ) );

    // set otpions variable
    $this->set_options();

    // test shortcode
    add_shortcode( 'devhats_test', array( $this, 'shortcode' ) );
  }

  // setup database table devhats_amazon
  // https://codex.wordpress.org/Creating_Tables_with_Plugins
  public function setup_table()
  {
    global $wpdb;

    if( 1 == get_option('devhats-amazon-installed') ) {
      return;
    }

    $table_name = $wpdb->prefix . 'devhats_amazon';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      asin tinytext NOT NULL,
      url text DEFAULT NULL,
      image mediumint(9) DEFAULT NULL,
      attributes text DEFAULT NULL,
      prices text DEFAULT NULL,
      time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta( $sql );

    update_option('devhats-amazon-installed', 1);
  }

  public function set_options()
  {
    $this->options['tag']      = get_option('devhats-amazon_tag', 'brows-21');
    $this->options['local']    = get_option('devhats-amazon_local', 'de');
    $this->options['version']  = get_option('devhats-amazon_version', '2013-08-01');
    $this->options['public']   = get_option('devhats-amazon_public', 'AKIAI6ZDRR653CR3RBUA');
    $this->options['private']  = get_option('devhats-amazon_private', '+OzA3kOMzxwkoVx8orqEBlF8t4boEmwRmuV5ZGAW');
  }

  public function select_product($asin)
  {
    global $wpdb;

    $table_name = $wpdb->prefix . 'devhats_amazon';

    $row = $wpdb->get_row( "SELECT * FROM $table_name WHERE asin = '$asin'", ARRAY_A );

    if( !$row['id'] ) {
      return false;
    }

    $row['attributes'] = unserialize($row['attributes']);
    $row['prices'] = unserialize($row['prices']);

    $row = array_filter($row);

    return $row;
  }

  public function update_product($asin)
  {
    global $wpdb;

    $values = $this->request($asin);

    $table_name = $wpdb->prefix . 'devhats_amazon';

    $row = $wpdb->get_row( "SELECT * FROM $table_name WHERE asin = '$asin'", ARRAY_A );

    if( false == $row ) {

      $image = $this->upload_media($values['image']);
      $values['image'] = (int) $image;

      $post = $wpdb->insert( $table_name, $values);

      return $post;
    }

    $where = array( 'asin' => $asin );

    $values['image'] = $row['image'];

    $post = $wpdb->update( $table_name, $values, $where );

    return $post;
  }

  public function upload_media($src)
  {
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    return media_sideload_image( $src, 0, null, 'id' );
  }

  public function format_xml($xml)
  {
    $item = $xml->Item;

    $item = json_decode(json_encode($item), true);

    $results = array();

    $results['asin'] = $item['ASIN'];
    $results['url'] = $item['DetailPageURL'];
    $results['image'] = $item['LargeImage']['URL'];

    $results['prices'] = array();

    $results['prices']['LowestUsedPrice'] = null;
    if( isset($item['OfferSummary']['LowestUsedPrice']) ) {
      $results['prices']['LowestUsedPrice'] = $item['OfferSummary']['LowestUsedPrice']['FormattedPrice'];
    }

    $results['prices']['LowestNewPrice'] = null;
    if( isset($item['OfferSummary']['LowestNewPrice']) ) {
      $results['prices']['LowestNewPrice'] = $item['OfferSummary']['LowestNewPrice']['FormattedPrice'];
    }

    $results['prices'] = serialize($results['prices']);
    $results['attributes'] = serialize($item['ItemAttributes']);

    $results['time'] = current_time( 'mysql' );

    return $results;
  }
  
  // https://www.kritzelblog.de/
  public function request($asin, $items = true)
  {
    $option = $this->options;

    $method = 'GET';
    $host = 'webservices.amazon.'.$option['local'];
    $uri = '/onca/xml';

    $params['ItemId'] = $asin;
    $params['ResponseGroup'] = 'Large';
    $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
    $params['Version'] = $option['version'];
    $params['Service'] = 'AWSECommerceService';
    $params['Operation'] = 'ItemLookup';
    $params['AWSAccessKeyId'] = $option['public'];

    if (null !== $option['tag']) {
      $params['AssociateTag'] = $option['tag'];
    }

    ksort($params);

    $canonicalized_query = array();

    foreach ($params as $param => $value) {
      $param = str_replace('%7E', '~', rawurlencode($param));
      $value = str_replace('%7E', '~', rawurlencode($value));
      $canonicalized_query[] = $param.'='.$value;
    }

    $canonicalized_query = implode('&', $canonicalized_query);
    $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
    $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $option['private'], TRUE));
    $signature = str_replace('%7E', '~', rawurlencode($signature));
    $request = 'http://'.$host.$uri.'?'.$canonicalized_query.'&Signature='.$signature;

    $response = @file_get_contents($request);

    if(false === $response) {
      return __('Der Request ist fehlgeschlagen.', DH_AMAZON_TEXTDOMAIN);
    }

    $xml = simplexml_load_string($response);

    if (false === $xml) {
      return __('Es ist ein Fehler beim Parsen aufgetreten.', DH_AMAZON_TEXTDOMAIN);
    }

    if( false === $items ) {
      return $this->format_xml($xml);
    }

    return $this->format_xml($xml->Items);
  }
}
