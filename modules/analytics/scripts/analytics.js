/**
 * @tableofcontents
 *
 * 1. analytics
 * 2. startup
 */

(function ($)
{
	'use strict';

	/* @section 1. analytics */

	$.fn.analytics = function (options)
	{
		/* extend options */

		if (r.module.analytics.options !== options)
		{
			options = $.extend({}, r.module.analytics.options, options || {});
		}

		/* create tracker */

		if (options.id && options.url)
		{
			r.module.analytics.tracker = _gat._createTracker(options.id);
			r.module.analytics.tracker._setDomainName(options.url);
			r.module.analytics.tracker._initData();
			r.module.analytics.tracker._trackPageview();
		}

		/* return this */

		return this.each(function ()
		{
			/* listen for click */

			$(this).one('click', function ()
			{
				var trigger = $(this),
					category = trigger.data('category'),
					action = trigger.data('action'),
					label = trigger.data('label');

				/* track event */

				if (category && action)
				{
					r.module.analytics.tracker._trackEvent(String(category), String(action), String(label));
				}
			});
		});
	};

	/* @section 2. startup */

	$(function ()
	{
		if (r.module.analytics.startup && r.constant.LOGGED_IN !== r.constant.TOKEN && typeof _gat === 'object')
		{
			$(r.module.analytics.selector).analytics(r.module.analytics.options);
		}
	});
})(jQuery);