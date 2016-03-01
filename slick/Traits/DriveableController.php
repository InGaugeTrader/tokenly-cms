<?php
namespace Traits;
use Util\Driver, Exception;
trait DriveableController
{
	public function init()
	{
		if(!isset($this->driver_key)){
			throw new Exception('No driver key set for this module');
		}
		$driver = new Driver($this->driver_key, $this);		
		$this->driver = $driver->load();
		if(isset($this->container)){
			$this->container = $this->driver;
		}		
		return $this->driver->init();
	}	
}
