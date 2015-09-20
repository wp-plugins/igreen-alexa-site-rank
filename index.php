<?php
/*
Plugin Name: Igreen Alexa Site Rank
Plugin URI: https://wordpress.org/plugins/igreen-alexa-site-rank/
Description: Get your updated ALEXA RANK in widgets or integrate in theme using plugin API/ shortcode
Version: 4.0.0
Author: susheelhbti
Author URI: http://sakshamappinternational.com/
License: GPL2
*/

/* short code generating */

add_shortcode( "ALEXARANK" , "AlexaRank" );

 
 function  getAlexaRankbySiteName($url)
 {
  $AlexaRank = new AlexaRank($url );
  return   number_format($AlexaRank->get()) ;
 }
 
 
  function  AlexaRankbySiteName($url)
 {
 
  echo getAlexaRankbySiteName($url);
 }
 
 
 
function  getAlexaRank()
 {
	 
 $AlexaRank = new AlexaRank($_SERVER['HTTP_HOST'] );
 return   number_format($AlexaRank->get()) ;
}
				
 function  AlexaRank()
 {
 
  echo  getAlexaRank();

}
 
 /* /4 API IS HERE */
  
 
class igreen_alexa_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'igreen_alexa_widget', // Base ID
			'Igreen_Alexa_Widget', // Name
			array( 'description' => __( 'Igreen Alexa Site Rank Widget', 'sakshamapp' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		echo "Alexa Rank of ".$_SERVER['HTTP_HOST']." is " ;
		echo  getAlexaRank();
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'sakshamapp' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

} // class Foo_Widget

// register Foo_Widget widget
add_action( 'widgets_init', create_function( '', 'register_widget( "Igreen_Alexa_Widget" );' ) );
		 
	
class AlexaRank extends ParseXml{
	private $data;
	private $rank ;
	
	function __construct($domain) {
		$this->data = $this->fetchData($domain);
		$this->findValue();
	}
	
	/**
	 * @param domain name which you want to process.
	 * @return Array data for a domain
	 */
	private function fetchData($domain) {
		$url = "http://data.alexa.com/data?cli=10&dat=snbamz&url=http://".trim($domain);
		$this->LoadRemote($url, 5);
		$dataArray = $this->ToArray();
		return $dataArray;
	}
	
	/**
	 * @param domain name which you want to process.
	 * @return the rank 
	*/
	private function findValue() {
		 
     $this->rank= isset($this->data['SD'][1]['POPULARITY']['@attributes']['TEXT']) ? ($this->data['SD'][1]['POPULARITY']['@attributes']['TEXT']) : 0;
	}
	
	/**
	 * @param domain name which you want to process.
	 * @return the rank 
	*/
	
	public function get(){
	 return     $this->rank;
	}
	
} 
 class ParseXml {
	var $xmlStr;
	var $xmlFile;
	var $obj;
	var $aArray;
	var $timeOut;
	var $charsetOutput;
	
	function ParseXml() {

	}
	
	/**
	 * @param String xmlString xml string to parsing
	 */
	function LoadString($xmlString) {
		$this->xmlStr = $xmlString;
	}
	
	/**
	 * @param String Path and file name which you want to parsing, 
	 *	Also, if “fopen wrappers”  is activated, you can fetch a remote document, but timeout not be supported.
	 */
	function LoadFile ($file) {
		$this->xmlFile = $file;
		$this->xmlStr = @file_get_contents($file);
	}
	
	/**
	 * @todo Load remote xml document
	 * @param string $url URL of xml document.
	 * @param int $timeout timeout  default:5s
	 */
	function LoadRemote ($url, $timeout=5) {
		$this->xmlFile = $url;
		$p=parse_url($url);
		if($p['scheme']=='http'){
			$host = $p['host'];
			$pos = $p['path'];
			$pos .= isset($p['query']) ? sprintf("?%s",$p['query']) : '';
			$port = isset($p['port'])?$p['port']:80;
			$this->xmlStr = $this->Async_file_get_contents($host, $pos, $port, $timeout);
		}else{
			return false;
		}
		
	}
	
	/**
	 * @todo Set attributes.
	 * @param array $set array('attribute_name'=>'value')
	 */
	function Set (array $set) {
		foreach($set as $attribute=>$value) {
			if($attribute=='charsetOutput'){
				$value = strtoupper($value);
			}
			$this->$attribute = $value;
		}
	}
	
	/**
	 * @todo Convert charset&#65292;if you want to output data with a charset not "UTF-8",
	 *	this member function must be useful.
	 * @param string $string &#38656;&#36716;&#25442;&#30340;&#23383;&#31526;&#20018;
	 */
	function ConvertCharset ($string) {
		if('UTF-8'!=$this->charsetOutput) {
			if(function_exists("iconv")){
				$string = @iconv('UTF-8', $this->charsetOutput, $string);
			}elseif(function_exists("mb_convert_encoding")){
				$string = mb_convert_encoding($string, $this->charsetOutput, 'UTF-8');
			}else{
				die('Function "iconv" or "mb_convert_encoding" needed!');
			}
		}
		return $string;
	}
	
	/**
	 * &#35299;&#26512;xml
	 */
	function Parse () {
		$this->obj = simplexml_load_string($this->xmlStr);
	}
	
	/**
	 * @return Array Result of parsing.
	 */
	function ToArray(){
		if(empty($this->obj)){
			$this->Parse();
		}
		$this->aArray = $this->Object2array($this->obj);
		return $this->aArray;
	}
	
	/**
	 * @param Object object Objects you want convert to array.
	 * @return Array
	 */
	function Object2array($object) {
		$return = array();
		if(is_array($object)){
			foreach($object as $key => $value){
				$return[$key] = $this->Object2array($value);
			}
		}else{
			$var = @get_object_vars($object);
			if($var){
				foreach($var as $key => $value){
					$return[$key] = ($key && ($value==null)) ? null : $this->Object2array($value);
				}
			}else{
				return $this->ConvertCharset((string)$object);
			}
		}
		return $return;
	}
	
	/**
	 * @todo Fetch a remote document with HTTP protect.
	 * @param string $site Server's IP/Domain
	 * @param string $pos URI to be requested
	 * @param int $port Port default:80
	 * @param int $timeout Timeout  default:5s
	 * @return string/false Data or FALSE when timeout.
	 */
	function Async_file_get_contents($site,$pos,$port=80,$timeout=5) {
		$fp = fsockopen($site, $port, $errno, $errstr, 5);
		
		if (!$fp) {
			return false;		
		}else{
			fwrite($fp, "GET $pos HTTP/1.0\r\n");
			fwrite($fp, "Host: $site\r\n\r\n");
			stream_set_timeout($fp, $timeout);
			$res = stream_get_contents($fp);
			$info = stream_get_meta_data($fp);
			fclose($fp);
			
			if ($info['timed_out']) {
				return false;    	
			}else{
				return substr(strstr($res, "\r\n\r\n"),4);
			}
		}
	}
	
	/**
	 * @todo Get xmlStr of current object.
	 * @return string xmlStr
	 */
	function GetXmlStr () {
		return $this->xmlStr;
	}
}




/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function saksh_dashboard_widgets() {

	wp_add_dashboard_widget(
                 'saksh_dashboard_widget',         // Widget slug.
                 'Sakshamapp International Pvt. Ltd. Offer',         // Title.
                 'saksh_dashboard_widget_function' // Display function.
        );	
}
add_action( 'wp_dashboard_setup', 'saksh_dashboard_widgets' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function saksh_dashboard_widget_function() {

	// Display whatever it is you want to show.
	echo "<center><br><br><H1><A href='http://www.sakshamappinternational.com/free-cloud-server/?q=saksh-wp-smtp' target='_blank'>Get Free Cloud Hosting for 90 Days.</a></H1><br><br>No Credit Card required. This is free offer with full cpanel access by <b>Sakshamapp International Pvt. Ltd.</b> .Register and get start <a href='http://www.sakshamappinternational.com/free-cloud-server/?q=saksh-wp-smtp'  target='_blank'>http://www.sakshamappinternational.com</a>  </center>";
}
 
?>