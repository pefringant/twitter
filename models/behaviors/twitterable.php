<?php
/**
 * Twitterable Behavior class
 * Defines afterCreate callback for associated model, that posts a new status on Twitter on create, update or both.
 */
class TwitterableBehavior extends ModelBehavior
{
	/**
	 * Settings array
	 *
	 * @var array
	 */
	var $__settings = array();

	/**
	 * Twitter Model handler
	 *
	 * @var object
	 */
	var $Twitter = null;
	
	/**
	 * Setup method
	 *
	 * @param object $model
	 * @param array $settings The array key 'on' can take 3 values : 'create', 'update' or 'both'
	 */
	function setup(&$model, $settings = array())
	{
		$default = array('on' => 'create');

		if(!isset($this->__settings[$model->alias]))
		{
			$this->__settings[$model->alias] = $default;
		}

		$this->__settings[$model->alias] = am($this->__settings[$model->alias], ife(is_array($settings), $settings, array()));
		
		// Twitter model
		$this->Twitter = ClassRegistry::init('Twitter.Twitter');
	}

	/**
	 * After saving a record, posts a new status on Twitter.
	 * This method awaits a model method named 'twitterStatus' that returns the new status to be posted.
	 *
	 * @param object $model
	 * @param boolean $created True if record was created, false if record was updated
	 */
	function afterSave(&$model, $created)
	{
		if(!method_exists($model, 'twitterStatus'))
		{
			return;
		}
		
		$do = false;
		
		switch($this->__settings[$model->alias]['on'])
		{
			case 'create':
				if($created) $do = true;
				break;
				
			case 'update':
				if(!$created) $do = true;
				break;
				
			case 'both':
				$do = true;
				break;
		}
		
		if($do)
		{
			$this->Twitter->postStatus($model->twitterStatus());
		}
	}

	/**
	 * Formats a Twitter status
	 *
	 * @param object $model
	 * @param string $message
	 * @param string $url Optionnal URL to read the full page
	 * @param string $ending If the status is over 140 chars, it will be cut and followed by the $ending string.
	 * @return string Twitter status
	 */
	function twitterFormatStatus(&$model, $message, $url = null, $ending = '...')
	{
		return $this->Twitter->formatStatus($message, $url, $ending);
	}
}
?>