<?php

/**
 * seo tube loader start
 */

function seo_tube_loader_start()
{
	global $loader_modules_styles, $loader_modules_scripts;
	$loader_modules_styles[] = 'modules/seo_tube/styles/seo_tube.css';
	$loader_modules_scripts[] = 'modules/seo_tube/scripts/startup.js';
	$loader_modules_scripts[] = 'modules/seo_tube/scripts/seo_tube.js';
}

/**
 * seo tube scripts end
 */

function seo_tube_scripts_end()
{
	global $video_content;
	if ($video_content)
	{
		$last = end(array_keys($video_content));

		/* add seo tube object */

		$output = '<script>' . PHP_EOL;
		$output .= 'r.module.seoTube.video = ' . json_encode($video_content) . ';';

		/* add constant object */

		$output .= 'r.module.seoTube.constant = ' . PHP_EOL . '{' . PHP_EOL;
		$output .= 'SEO_TUBE_DESCRIPTION_PARAGRAPH: \'' . SEO_TUBE_DESCRIPTION_PARAGRAPH . '\',' . PHP_EOL;
		$output .= 'SEO_TUBE_GDATA_URL: \'' . SEO_TUBE_GDATA_URL . '\',' . PHP_EOL;
		$output .= 'SEO_TUBE_COMMENT_FEED: \'' . SEO_TUBE_COMMENT_FEED . '\',' . PHP_EOL;
		$output .= 'SEO_TUBE_COMMENT_LIMIT: ' . SEO_TUBE_COMMENT_LIMIT . PHP_EOL;
		$output .= '};' . PHP_EOL . '</script>' . PHP_EOL;
	}
	echo $output;
}

/**
 * seo tube center start
 */

function seo_tube_center_start()
{
	if ((ADMIN_PARAMETER == 'new' || ADMIN_PARAMETER == 'edit') && TABLE_PARAMETER == 'articles' && $_POST['seo_tube_post'])
	{
		seo_tube_post();
	}
}

/**
 * seo tube admin contents form start
 */

function seo_tube_admin_contents_form_start()
{
	if ((ADMIN_PARAMETER == 'new' || ADMIN_PARAMETER == 'edit') && TABLE_PARAMETER == 'articles')
	{
		seo_tube_form();
	}
}

/**
 * seo tube get id
 *
 * @param string $video_url
 * @return string
 */

function seo_tube_get_id($video_url = '')
{
	$video_url_delimiter = '?v=';
	if (strlen($video_url) == 11)
	{
		$output = $video_url;
	}
	else
	{
		$output = explode($video_url_delimiter, $video_url);
		$output = substr($output[1], 0, 11);
	}
	return $output;
}

/**
 * seo tube parser
 *
 * @param string $video_id
 * @return string
 */

function seo_tube_parser($video_id = '')
{
	$video_url = SEO_TUBE_GDATA_URL . '/'. $video_id;
	$video_data = simplexml_load_file($video_url);

	/* collect output */

	if ($video_url == $video_data->id)
	{
		$output = array(
			'id' => $video_id,
			'title' => (string)$video_data->title,
			'description' => (string)$video_data->content,
			'date' => (string)$video_data->published,
			'status' => 200
		);
	}
	else
	{
		$output = array(
			'status' => 400
		);
	}
	return $output;
}

/* seo tube player
 *
 * @param string $video_id
 * @return string
 */

function seo_tube_player($video_id = '')
{
	$output = object_element('application/x-shockwave-flash', '', 'player player_default', '', $video_id, 'http://www.youtube.com/v/' . $video_id);
	return $output;
}

/**
 * seo tube form
 */

function seo_tube_form()
{
	global $video_id;
	if ($video_id)
	{
		$url = $video_id;
	}
	else
	{
		$url = l('seo_tube_enter_youtube_video');
	}
	$output .= form_element('form', '', 'form_seo_tube', '', '', '', 'method="post"');
	$output .= form_element('text', 'url', 'js_clear_focus field_search', 'url', $url, '', 'maxlength="50"');

	/* collect hidden and button output */

	$output .= form_element('hidden', '', '', 'token', TOKEN);
	$output .= form_element('button', '', 'field_button_search', 'seo_tube_post', l('seo_tube_load'));
	$output .= '</form>';
	echo $output;
}

/**
 * seo tube post
 */

function seo_tube_post()
{
	global $video_id, $video_content;
	$url = clean($_POST['url'], 5);
	if ($url)
	{
		$video_id = seo_tube_get_id($url);
		$video_content = seo_tube_parser($video_id);

		/* collect output */

		if ($video_content['status'] == 200)
		{
			$output = '<div class="box_note note_success">' . l('seo_tube_status_200') . l('point') . '</div>';
		}
		else if ($video_content['status'] == 400)
		{
			$output = '<div class="box_note note_error">' . l('seo_tube_status_400') . l('point') . '</div>';
		}
	}
	else
	{
		$output = '<div class="box_note note_error">' . l('input_incorrect') . l('point') . '</div>';
	}
	echo $output;
}
?>