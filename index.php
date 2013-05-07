<?php

/*
Plugin Name: Igreen Alexa Site Rank
Plugin URI: http://susheelonline.com
Description: Get your updated ALEXA RANK in widgets or integrate in theme using plugin API/ shortcode
Version: 1.0
Author: Susheel Kumar ,Ritu Kushwaha
Author URI: http://susheelonline.com
License: GPL2
*/

/* short code generating */

add_shortcode( "ALEXARANK" , "getAlexaRank" );

 
 function  getAlexaRankbySiteName($url)
 {
  $AlexaRank = new AlexaRank($url );
  return   number_format($AlexaRank->get('rank')) ;
 }
  function  AlexaRankbySiteName($url)
 {
  $AlexaRank = new AlexaRank($url );
  echo  number_format($AlexaRank->get('rank')) ;
 }
 
 
 
function  getAlexaRank()
 {
 $AlexaRank = new AlexaRank($_SERVER['HTTP_HOST'] );
 return   number_format($AlexaRank->get('rank')) ;
}
				
 function  AlexaRank()
 {
 $AlexaRank = new AlexaRank($_SERVER['HTTP_HOST'] );
  echo  getAlexaRank();
}
 
 /* /4 API IS HERE */
 
 /* widget code start */
class IGREENALEXA extends WP_Widget {
 
	function MyNewWidget() {
		// Instantiate the parent object
		parent::__construct( false, 'IGREEN Alexa Rank Widget' );
		 
	}

	function widget( $args, $instance ) {
		// Widget output
		echo "<BR>Alexa Rank of this site susheelonline.com IS ";
		  AlexaRank();
		//echo getAlexaRank();

		echo "<BR>Alexa Rank of site BLOGENTRY.IN IS ";
		echo getAlexaRankbySiteName("blogentry.in");
				
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
	}

	function form( $instance ) {
		// Output admin widget options form
	}
}

function IGREENALEXA_register_widgets() {
	register_widget( 'IGREENALEXA' );
}

add_action( 'widgets_init', 'IGREENALEXA_register_widgets' );






		 
	
class AlexaRank extends ParseXml{
	private $data;
	private $values = array();
	
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
		$this->values = array(
			'rank' => (isset($this->data['SD'][1]['POPULARITY']['@attributes']['TEXT']) ? ($this->data['SD'][1]['POPULARITY']['@attributes']['TEXT']) : NULL),
		 );
	}
	
	/**
	 * @param domain name which you want to process.
	 * @return the rank 
	*/
	
	public function get($value = NULL){
		if($value === NULL) {
			return $this->values; //Return the total Array
		} else {
			return (isset($this->values[$value]) ? $this->values[$value] : '"'.$value.'" does not exist.');
		}	
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





?>