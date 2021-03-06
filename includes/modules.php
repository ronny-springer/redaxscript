<?php

/**
 * modules include
 *
 * @return array
 */

function modules_include()
{
	static $modules_installed, $modules_directory;

	/* query installed modules */

	if ($modules_installed == '')
	{
		$query = 'SELECT alias, access FROM ' . PREFIX . 'modules WHERE status = 1';
		$result = mysql_query($query);
		if ($result)
		{
			while ($r = mysql_fetch_assoc($result))
			{
				$alias = $r['alias'];
				$access = $r['access'];
				$check_access = check_access($access, MY_GROUPS);

				/* if access granted */

				if ($check_access == 1)
				{
					$modules_installed[] = $alias;
				}
			}
		}
	}

	/* read modules directory */

	if ($modules_directory == '')
	{
		$modules_directory = read_directory('modules');
	}

	/* intersect modules diretory and modules installed */

	if ($modules_directory && $modules_installed)
	{
		$output = array_intersect($modules_directory, $modules_installed);
	}
	return $output;
}

/**
 * hook
 *
 * @param string $input
 * @return string
 */

function hook($input = '')
{
	global $hook;
	static $modules_include;

	/* get modules include */

	if ($modules_include == '')
	{
		$modules_include = modules_include();
	}

	/* call hook functions */

	foreach ($modules_include as $value)
	{
		$function = $value . '_' . $input;
		if (function_exists($function))
		{
			$hook[] = $function;
			$output .= call_user_func($function);
		}
	}
	return $output;
}
?>