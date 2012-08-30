<?php
/**
 * This is a basic class for connecting to the Compete API
 * @author stephenreid
 * @desc A connecting class to the compete api
 * 
 */
class CompeteConnector
{
	//A flag for echoing debug output
	private $debug 		= false;
	private $apiKey		= null;
	private $outputMode	= 'simple'; // choose between 'simple','full','mobile'

	//Tthese are the basic free metrics
	const TREND_UNIQUE_VISITORS = 'uv';
	const TREND_UNIQUE_VISITS = 'vis';
	const TREND_RANK = 'rank';

	/**
	 * __construct PardotConnector()
	 * Dummy Constructor, Run authenticate() to be able to do anything
	 */
	public function __construct()
	{
	}
	public function setApiKey($key)
	{
		$this->apiKey = $key;
	}
	public function query($site,$metric)
	{
		return $this->send($site,$metric);
	}

	/**
	 * Send
	 * @desc Sends a web request to the api
	 * @param string $module
	 * @param string $action
	 * @param array $parameters A Key Value Store of Parameters
	 * @return Json Response from Server
	 * @throws Exception
	 */
	private function send($site,$metric){
		// http://apps.compete.com/sites/facebook.com/trended/uv/?apikey=
		$baseUrl = 'http://apps.compete.com/sites/';
	
		$url = $baseUrl.$site.'/trended/'.$metric.'/'.'?apikey='.$this->apiKey;
		$parameters = array();
		$context = stream_context_create(array(
			'http'	=> array(
				'method'	=> 'POST',//never want to send credentials over GET
				'header'	=> 'Content-type: application/x-www-form-urlencoded',
				'content'	=> http_build_query($parameters),
				'timeout'	=> 30.0, //in seconds
				'user_agent'=> 'PardotPHPClient',
		//'proxy'		=> '',
		//'ignore_errors'	=> false,
		)
		));

		$res = file_get_contents($url,false,$context);
		$ret = json_decode($res);
		if ($ret->status!='OK'){
			throw new CompeteConnectorException($ret->status_message.' url: '.$url, '1');
		}
		return $ret->data->trends->$metric;
	}
}
class CompeteConnectorException extends Exception{

	public function __construct($message='',$code=1){
		parent::__construct($message, $code);
	}
}?>
