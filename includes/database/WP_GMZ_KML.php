<?php
/**
 * Created by WTC.
 * User: Rob Wilde
 * Date: 12/04/2015
 * Time: 6:11 PM
 */
require_once ( plugin_dir_path( __FILE__ ).'Base_Custom_Data.php' );

class WP_GMZ_KML extends Base_Custom_Data
{
	public function __construct($tableName)
	{
		parent::__construct($tableName);
	}

}

