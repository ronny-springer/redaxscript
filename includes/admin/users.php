<?php

/**
 * admin users list
 */

function admin_users_list()
{
	hook(__FUNCTION__ . '_start');

	/* query users */

	$query = 'SELECT id, name, user, language, first, last, status, groups FROM ' . PREFIX . 'users ORDER BY last DESC';
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);

	/* collect listing output */

	$output = '<h2 class="title_content">' . l('users') . '</h2>';
	$output .= '<div class="wrapper_button_admin">';
	if (USERS_NEW == 1)
	{
		$output .= '<a class="field_button_admin field_button_plus" href="' . REWRITE_ROUTE . 'admin/new/users"><span><span>' . l('user_new') . '</span></span></a>';
	}
	$output .= '</div><div class="wrapper_table_admin"><table class="table table_admin">';

	/* collect thead and tfoot */

	$output .= '<thead><tr><th class="s3o6 column_first">' . l('name') . '</th><th class="s1o6 column_second">' . l('user') . '</th><td class="s1o6 column_third">' . l('groups') . '</td><th class="s1o6 column_last">' . l('session') . '</th></tr></thead>';
	$output .= '<tfoot><tr><td class="column_first">' . l('name') . '</td><td class="column_second">' . l('user') . '</td><td class="column_third">' . l('groups') . '</td><td class="column_last">' . l('session') . '</td></tr></tfoot>';
	if ($result == '' || $num_rows == '')
	{
		$error = l('user_no') . l('point');
	}
	else if ($result)
	{
		$output .= '<tbody>';
		while ($r = mysql_fetch_assoc($result))
		{
			if ($r)
			{
				foreach ($r as $key => $value)
				{
					$$key = stripslashes($value);
				}
			}

			/* build class string */

			if ($status == 1)
			{
				$class_status = '';
			}
			else
			{
				$class_status = 'row_disabled';
			}

			/* collect table row */

			$output .= '<tr';
			if ($class_status)
			{
				$output .= ' class="' . $class_status . '"';
			}
			$output .= '><td class="column_first">';
			if ($language)
			{
				$output .= '<span class="icon_flag language_' . $language . '" title="' . l($language) . '">' . $language . '</span>';
			}
			$output .= $name;

			/* collect control output */

			if (USERS_EDIT == 1 || (USERS_DELETE == 1 && $id > 1))
			{
				$output .= '<ul class="list_control_admin">';
			}
			if (USERS_EDIT == 1)
			{
				if ($id > 1)
				{
					if ($status == 1)
					{
						$output .= '<li class="item_disable">' . anchor_element('internal', '', '', l('disable'), 'admin/disable/users/' . $id . '/' . TOKEN) . '</li>';
					}
					else if ($status == 0)
					{
						$output .= '<li class="item_enable">' . anchor_element('internal', '', '', l('enable'), 'admin/enable/users/' . $id . '/' . TOKEN) . '</li>';
					}
				}
				$output .= '<li class="item_edit">' . anchor_element('internal', '', '', l('edit'), 'admin/edit/users/' . $id) . '</li>';
			}
			if (USERS_DELETE == 1 && $id > 1)
			{
				$output .= '<li class="item_delete">' . anchor_element('internal', '', 'js_confirm', l('delete'), 'admin/delete/users/' . $id . '/' . TOKEN) . '</li>';
			}
			if (USERS_EDIT == 1 || (USERS_DELETE == 1 && $id > 1))
			{
				$output .= '</ul>';
			}

			/* collect user and parent output */

			$output .= '</td><td class="column_second">' . $user . '</td><td class="column_third">';
			if ($groups)
			{
				$groups_array = explode(', ', $groups);
				$groups_array_last = end(array_keys($groups_array));
				foreach ($groups_array as $key => $value)
				{
					$group_alias = retrieve('alias', 'groups', 'id', $value);
					if ($group_alias)
					{
						$output .= anchor_element('internal', '', 'link_parent', retrieve('name', 'groups', 'id', $value), 'admin/edit/groups/' . $value);
						if ($groups_array_last != $key)
						{
							$output .= ', ';
						}
					}
				}
			}
			else
			{
				$output .= l('none');
			}
			$output .= '</td><td class="column_last">';
			if ($first == $last)
			{
				$output .= l('none');
			}
			else
			{
				$minute_ago = date('Y-m-d H:i:s', strtotime('-1 minute'));
				$day_ago = date('Y-m-d H:i:s', strtotime('-1 day'));
				if ($last > $minute_ago)
				{
					$output .= l('online');
				}
				else if ($last > $day_ago)
				{
					$time = date(s('time'), strtotime($last));
					$output .= l('today') . ' ' . l('at') . ' ' . $time;
				}
				else
				{
					$date = date(s('date'), strtotime($last));
					$output .= $date;
				}
			}
			$output .= '</td></tr>';
		}
		$output .= '</tbody>';
	}

	/* handle error */

	if ($error)
	{
		$output .= '<tbody><tr><td colspan="3">' . $error . '</td></tr></tbody>';
	}
	$output .= '</table></div>';
	echo $output;
	hook(__FUNCTION__ . '_end');
}

/**
 * admin users form
 */

function admin_users_form()
{
	hook(__FUNCTION__ . '_start');

	/* define fields for existing user */

	if (ADMIN_PARAMETER == 'edit' && ID_PARAMETER)
	{
		/* query user */

		$query = 'SELECT * FROM ' . PREFIX . 'users WHERE id = ' . ID_PARAMETER;
		$result = mysql_query($query);
		$r = mysql_fetch_assoc($result);
		if ($r)
		{
			foreach ($r as $key => $value)
			{
				$$key = stripslashes($value);
			}
		}
		$wording_headline = $name;
		$wording_submit = l('save');
		$route = 'admin/process/users/' . $id;
	}

	/* else define fields for new user */

	else if (ADMIN_PARAMETER == 'new')
	{
		$status = 1;
		$groups = 0;
		$wording_headline = l('user_new');
		$wording_submit = l('create');
		$route = 'admin/process/users';
		$class_required = ' js_required field_note';
		$code_required = ' required="required"';
	}

	/* collect output */

	$output = '<h2 class="title_content">' . $wording_headline . '</h2>';

	/* collect tab output */

	$output .= '<ul class="js_list_tab list_tab list_tab_admin">';
	$output .= '<li class="js_item_active item_active item_first">' . anchor_element('internal', '', '', l('user'), FULL_ROUTE . '#tab-1') . '</li>';
	$output .= '<li class="item_second">' . anchor_element('internal', '', '', l('customize'), FULL_ROUTE . '#tab-2') . '</li></ul>';

	/* collect tab box output */

	$output .= form_element('form', 'form_admin', 'js_check_required js_note_required form_admin hidden_legend', '', '', '', 'action="' . REWRITE_ROUTE . $route . '" method="post"');
	$output .= '<div class="js_box_tab box_tab box_tab_admin">';

	/* collect user set */

	$output .= form_element('fieldset', 'tab-1', 'js_set_tab set_tab set_tab_admin', '', '', l('user')) . '<ul>';
	$output .= '<li>' . form_element('text', 'name', 'js_required field_text_admin field_note', 'name', $name, l('name'), 'maxlength="50" required="required" autofocus="autofocus"') . '</li>';
	if ($id == '')
	{
		$output .= '<li>' . form_element('text', 'user', 'js_required field_text_admin field_note', 'user', $user, l('user'), 'maxlength="50" required="required"') . '</li>';
	}
	$output .= '<li>' . form_element('password', 'password', 'field_text_admin js_unmask_password' . $class_required, 'password', '', l('password'), 'maxlength="50" autocomplete="off"' . $code_required) . '</li>';
	$output .= '<li>' . form_element('password', 'password_confirm', 'field_text_admin js_unmask_password' . $class_required, 'password_confirm', '', l('password_confirm'), 'maxlength="50" autocomplete="off"' . $code_required) . '</li>';
	$output .= '<li>' . form_element('email', 'email', 'js_required field_text_admin field_note', 'email', $email, l('email'), 'maxlength="50" required="required"') . '</li>';
	$output .= '<li>' . form_element('textarea', 'description', 'js_auto_resize field_textarea_small_admin', 'description', $description, l('description'), 'rows="1" cols="15"') . '</li>';
	$output .= '</ul></fieldset>';

	/* collect customize set */

	$output .= form_element('fieldset', 'tab-2', 'js_set_tab set_tab set_tab_admin', '', '', l('customize')) . '<ul>';

	/* build languages select */

	$language_array[l('select')] = '';
	$languages_directory = read_directory('languages', 'misc.php');
	foreach ($languages_directory as $value)
	{
		$value = substr($value, 0, 2);
		$language_array[l($value)] = $value;
	}
	$output .= '<li>' . select_element('language', 'field_select_admin', 'language', $language_array, $language, l('language')) . '</li>';
	if ($id == '' || $id > 1)
	{
		$output .= '<li>' . select_element('status', 'field_select_admin', 'status', array(
			l('enable') => 1,
			l('disable') => 0
		), $status, l('status')) . '</li>';

		/* build groups select */

		if (GROUPS_EDIT == 1 && USERS_EDIT == 1)
		{
			$groups_query = 'SELECT * FROM ' . PREFIX . 'groups ORDER BY name ASC';
			$groups_result = mysql_query($groups_query);
			if ($groups_result)
			{
				while ($g = mysql_fetch_assoc($groups_result))
				{
					$groups_array[$g['name']] = $g['id'];
				}
			}
			$output .= '<li>' . select_element('groups', 'field_select_admin field_multiple', 'groups', $groups_array, $groups, l('groups'), 'multiple="multiple"') . '</li>';
		}
	}
	$output .= '</ul></fieldset></div>';

	/* collect hidden output */

	if ($id)
	{
		$output .= form_element('hidden', '', '', 'user', $user);
	}
	$output .= form_element('hidden', '', '', 'token', TOKEN);

	/* collect button output */

	if (USERS_EDIT == 1 || USERS_DELETE == 1)
	{
		$cancel_route = 'admin/view/users';
	}
	else
	{
		$cancel_route = 'admin';
	}
	$output .= '<a class="js_cancel field_button_large_admin field_button_backward" href="' . REWRITE_ROUTE . $cancel_route . '"><span><span>' . l('cancel') . '</span></span></a>';

	/* delete button */

	if ((USERS_DELETE == 1 || USERS_EXCEPTION == 1) && $id > 1)
	{
		$output .= '<a class="js_delete js_confirm field_button_large_admin" href="' . REWRITE_ROUTE . 'admin/delete/users/' . $id . '/' . TOKEN . '"><span><span>' . l('delete') . '</span></span></a>';
	}

	/* submit button */

	if (USERS_NEW == 1 || USERS_EDIT == 1 || USERS_EXCEPTION == 1)
	{
		$output .= form_element('button', '', 'js_submit field_button_large_admin field_button_forward', ADMIN_PARAMETER, $wording_submit);
	}
	$output .= '</form>';
	echo $output;
	hook(__FUNCTION__ . '_end');
}
?>