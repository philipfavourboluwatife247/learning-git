<?php 

Class Notification extends Automated_DB
{

	function get_notifications()
	{
		$result = $this->read("select * from users");
		print_r($result);
	}
}
