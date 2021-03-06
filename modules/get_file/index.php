<?php

/**
 * get file size
 *
 * @param string $file_name
 * @param string $unit
 */

function get_file_size($file_name = '', $unit = '')
{
	$output = filesize($file_name);

	/* calculate output */

	if ($unit == 'kb' || $unit == 'mb')
	{
		$output = $output / 1024;
	}
	if ($unit == 'mb')
	{
		$output = $output / 1024;
	}
	$output = ceil($output);
	echo $output;
}

/**
 * get file date
 *
 * @param string $file_name
 */

function get_file_date($file_name = '')
{
	$output = date(s('date'), filectime($file_name));
	echo $output;
}
?>