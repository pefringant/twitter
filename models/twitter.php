<?php
/**
 * Twitter model class
 * Uses the Twitter DataSource by Alex Ciobanu
 * http://github.com/ics/twitter_datasource/tree/master
 */
class Twitter extends TwitterAppModel
{
	var $name = 'Twitter';
	
	var $useDbConfig = 'twitter';
	
	function __construct()
	{
		// Datasource config
		App::import(array(
			'type' => 'File',
			'name' => 'Twitter.TWITTER_CONFIG',
			'file' => 'config'.DS.'twitter.php'
		));
		
		// Datasource
		App::import(array(
			'type' => 'File',
			'name' => 'Twitter.TwitterSource',
			'file' => 'models'.DS.'datasources'.DS.'twitter_source.php'
		));
		
		$config =& new TWITTER_CONFIG();

		ConnectionManager::create('twitter', $config->twitter);
	}
	
	/**
	 * Posts a new status on Twitter
	 *
	 * @param string $status
	 */
	function postStatus($status)
	{
		$ds = ConnectionManager::getDataSource($this->useDbConfig);
		
		$ds->status_update($status);
	}
	
	/**
	 * Calls a URL shortening service
	 * http://is.gd
	 *
	 * @param string $url
	 * @return string Shortened URL
	 */
	function shorten($url)
	{
		if(!class_exists('HttpSocket'))
		{
			App::import('Core', 'HttpSocket');
		}
		
		$socket =& new HttpSocket();
		
		$service = sprintf('http://is.gd/api.php?longurl=%s', rawurlencode($url));
		
		return $socket->get($service);
	}
	
	
	/**
	 * Formats a Twitter status
	 *
	 * @param string $message
	 * @param string $url Optionnal URL to read the full story
	 * @param string $ending If the status is over 140 chars, it will be cut and followed by the $ending string.
	 * @return string Twitter compatible status
	 */
	function formatStatus($message, $url = null, $ending = '...')
	{
		if(!class_exists('Multibyte'))
		{
			App::import('Core', 'Multibyte');
		}
		
		$max = 140;
		
		if($url)
		{
			$url = $this->shorten($url);
			
			$max -= mb_strlen($url) + 1;
		}
		
		if(mb_strlen($message) > $max)
		{
			$message  = mb_substr($message, 0, $max - mb_strlen($ending));
			$message .= $ending;
		}
		
		if(!$url)
		{
			return $message;
		}
		
		return sprintf('%s %s', $message, $url);
	}
}
?>