<?php
// A Curl Class for making requests written by Dick Munroe
require_once 'class.curl.php';

class feedburner {	
	/**
    * Feedburner Feed URI
    * @var string
	* @access private 
    */
	var $_feedburner_uri = '';
	
	/**
    * Feedburner Web Services Url
    * @var string
	* @access private 
    */
	var $_feedburner_api_url = 'http://api.feedburner.com/awareness/1.0/';
	
	/**
    * Gets set if there is an error; stores the code
    * @var string
	* @access private 
    */
	var $_error_code = '';
	
	/**
    * Gets set if there is an error; stores the message
    * @var string
	* @access private 
    */
	var $_error_msg = '';
	
	/**
	* Turns debugging on or off
	* @var bool
	* @access private
	*/
	var $_debug = false;
	
	/**
	* feedburner constructor
	*
	* @param string $api_key An API Key is required to make flickr requests
	*
	* @return void
	* @access public
	*/
	function feedburner($uri) {
		$this->_feedburner_uri = $uri;
	}
	
	/**
	* internal function that I use to make all the requests to flickr
	*
	* @param string $method The Flickr Method that is being requested
	* @param array $params An array of the various required and optional fields needed to make the mthod request
	*
	* @return array The xml turned into an array
	* @access public
	*/
	function makeRequest($method, $params = array()) {
		$this->_clearErrors();
		
		$xml 			= '';
		$useCURL 		= in_array('curl', get_loaded_extensions());
		$params['uri'] 	= $this->_feedburner_uri;
		
		$args = array();
		foreach($params as $k => $v){
			array_push($args, urlencode($k).'='.urlencode($v));
		}
		
		// build rest query string
		$query_str = implode('&', $args);
		
		// hit up the feedburner api with or without CURL
		if ($useCURL) {
			$c = &new curl($this->_feedburner_api_url . $method . '?' . $query_str );
			$c->setopt(CURLOPT_FOLLOWLOCATION, true);
			$xml = $c->exec();
			$error = $c->hasError();
			if ($error) {
				$this->_error_msg = $error;
				return false;
			}
			$c->close() ;
		} else {
			$url_parsed = parse_url($this->_feedburner_api_url . $method . '?' . $query_str);
			$host 		= $url_parsed["host"];
			$port 		= ($url_parsed['port'] == 0) ? 80 : $url_parsed['port'];
			$path 		= $url_parsed["path"] . (($url_parsed['query'] != '') ? $path .= "?{$url_parsed[query]}" : '');
			$headers	= "GET $path HTTP/1.0\r\n";
			$headers	.= "Host: $host\r\n\r\n";
			$fp 		= fsockopen($host, $port, $errno, $errstr, 30);
			if (!$fp) {
				$this->_error_msg 	= $errstr;
				$this->_error_code 	= $errno;
				return false;
			} else {
				fwrite($fp, $headers);
				while (!feof($fp)) {
					$xml .= fgets($fp, 1024);
				}
				fclose($fp);
				
				/* 	
					this seems stupid, but it removes the 
					headers from the response; if you know 
					a better way let me know
				*/
				$xml_start = strpos($xml, '<?xml');
				$xml = substr($xml, $xml_start, strlen($xml));
			}
		}
		
		if ($this->_debug) {
			echo '<h2>XML Response</h2>';
			echo '<pre class="xml">';
			echo htmlspecialchars($xml);
			echo '</pre>';
		}
		
		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $xml, $data);
		xml_parser_free($xml_parser);
		
		return $data;
	}
	
	/**
	* Gets the feed data for a uri
	*
	* @param array $param params to pass such as dates and uri
	*
	* @return array $ret_array Array full of feed data
	* @access private
	*/
	function getFeedData($params = array()) {
		$entry_count = 1;
		$ret_array = array();
		
		// make request to flickr
		$data = $this->makeRequest('GetFeedData', $params);
		
		// check if error
		if ($this->_checkForError($data)) {
			return false;
		}
		
		// build return array
		for ($i=0, $count=count($data); $i<$count; $i++) {
			$a = $data[$i];
			switch ($a['tag']) {
				case 'FEED':
					if (is_array($a['attributes'])) {
						$ret_array['id'] = $a['attributes']['ID'];
						$ret_array['uri'] = $a['attributes']['URI'];
					}
					break;
				case 'ENTRY':
					if (is_array($a['attributes'])) {
						$ret_array['entries'][$entry_count]['date'] = $a['attributes']['DATE'];
						$ret_array['entries'][$entry_count]['circulation'] = $a['attributes']['CIRCULATION'];
						$ret_array['entries'][$entry_count]['hits'] = $a['attributes']['HITS'];
						$entry_count++;
					}
					break;
			}
		}
		if ($this->_debug) {
			echo '<h2>Function Return</h2>';
			$this->_a($ret_array);
			echo '<hr />';
		}
		return $ret_array;
	}
	
	/**
	* Gets the item data for a uri
	*
	* @param array $params An array of params to make the item request with
	*
	* @return array $ret_array Array stuffed with item data
	* @access private
	*/
	function getItemData($params = array()) {
		$entry_count 	= 0;
		$item_count 	= 1;
		$ret_array 		= array();
		
		// make request to flickr
		$data = $this->makeRequest('GetItemData', $params);
		
		// check if error
		if ($this->_checkForError($data)) {
			return false;
		}
		
		// build return array
		for ($i=0, $count=count($data); $i<$count; $i++) {
			$a = $data[$i];
			switch ($a['tag']) {
				case 'FEED':
					if (is_array($a['attributes'])) {
						$ret_array['id'] = $a['attributes']['ID'];
						$ret_array['uri'] = $a['attributes']['URI'];
					}
					break;
				case 'ENTRY':
					if (is_array($a['attributes'])) {
						$entry_count++;
						$item_count = 1;
						$ret_array['entries'][$entry_count]['date'] = $a['attributes']['DATE'];
						$ret_array['entries'][$entry_count]['circulation'] = $a['attributes']['CIRCULATION'];
						$ret_array['entries'][$entry_count]['hits'] = $a['attributes']['HITS'];
					}
					break;
				case 'ITEM':
					if (is_array($a['attributes'])) {
						$ret_array['entries'][$entry_count]['items'][$item_count]['title'] = $a['attributes']['TITLE'];
						$ret_array['entries'][$entry_count]['items'][$item_count]['url'] = $a['attributes']['URL'];
						$ret_array['entries'][$entry_count]['items'][$item_count]['itemviews'] = $a['attributes']['ITEMVIEWS'];
						$ret_array['entries'][$entry_count]['items'][$item_count]['clickthroughs'] = $a['attributes']['CLICKTHROUGHS'];
						$item_count++;
					}
					break;
			}
		}
		if ($this->_debug) {
			echo '<h2>Function Return</h2>';
			$this->_a($ret_array);
			echo '<hr />';
		}
		return $ret_array;
	}
	
	/**
	* Checks an array that used to be xml for an error, if so it sets the error code and message
	*
	* @param array $data The array to check for flickr response errors
	*
	* @return bool True if error false if not
	* @access private
	*/
	function _checkForError($data) {
		if ($data[0]['attributes']['STAT'] == 'fail') {
			$this->_error_code = $data[1]['attributes']['CODE'];
			$this->_error_msg = $data[1]['attributes']['MSG'];
			return true;
		}
		return false;
	}
	
	/**
	* Checks if there is an error, if so it returns it
	*
	* @return string The error code and message
	* @access public
	*/
	function isError() {
		if  ($this->_error_msg != '') {
			return true;
		}
		return false;
	}
	
	/**
	* Returns error code and message if any
	*
	* @return string The error code and message
	* @access public
	*/
	function getErrorMsg() {
		return '<p>Error: (' . $this->_error_code . ') ' . $this->_error_msg . '</p>';
	}
	
	/**
	* Returns error code
	*
	* @return string The error code
	* @access public
	*/
	function getErrorCode() {
		return $this->_error_code;
	}
	
	/**
	* Clears the error variables
	*
	* @return void
	* @access private
	*/
	function _clearErrors() {
		$this->_error_code = '';
		$this->_error_msg = '';
	}
	
	/**
	* Sets debug to true or false
	*
	* @param bool $debug True or false
	*
	* @return void
	* @access public
	*/
	function setDebug($debug) {
		$this->_debug = $debug;
	}
	
	/**
	* Just for debugging; prints an array nicely
	*
	* @param array $array The array to print out
	*
	* @return void
	* @access private
	*/
	function _a($array) {
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	}


}
?>