/**
 * @tableofcontents
 *
 * 1. editor
 * 2. startup
 */

(function ($)
{
	'use strict';

	/* @section 1. editor */

	$.fn.editor = function (options)
	{
		/* extend options */

		if (r.module.editor.options !== options)
		{
			options = $.extend({}, r.module.editor.options, options || {});
		}

		/* return this */

		return this.each(function ()
		{
			/* detect needed mode */

			if (r.constant.FIRST_PARAMETER === 'admin')
			{
				options.toolbar = options.toolbar.backend;
				options.xhtml = options.newline.backend;
				options.newline = options.newline.backend;
			}
			else
			{
				options.toolbar = options.toolbar.frontend;
				options.xhtml = options.toolbar.frontend;
				options.newline = options.newline.frontend;
			}

			var editor = this;
			editor.textarea = $(this);

			/* prematurely terminate editor */

			if (editor.textarea.length < 1)
			{
				return false;
			}

			/* build editor elements */

			editor.textarea.hide();
			editor.container = $('<div class="' + options.classString.editor + '"></div>').insertBefore(editor.textarea);
			editor.toolbar = $('<div class="' + options.classString.editorToolbar + '" unselectable="on"></div>').appendTo(editor.container);

			/* create toolbar */

			editor.createToolbar = function ()
			{
				var name, data, control, i;

				for (i = 0; i < options.toolbar.length; i++)
				{
					name = options.toolbar[i];
					data = r.module.editor.controls[name];

					/* append divider */

					if (name === 'divider')
					{
						$('<div class="' + options.classString.editorDivider + '"></div>').appendTo(editor.toolbar);
					}

					/* append newline */

					else if (name === 'newline')
					{
						$('<div class="' + options.classString.editorNewline + '"></div>').appendTo(editor.toolbar);
					}

					/* append toggle */

					else if (name === 'toggle')
					{
						editor.toggler = control = $('<div class="' + options.classString.editorControl + ' ' + options.classString.editorSourceCode + '" title="' + data.title + '"></div>').appendTo(editor.toolbar);
					}

					/* append serveral controls */

					else if (data)
					{
						control = $('<div class="' + options.classString.editorControl + ' ' + name + '" title="' + data.title + '"></div>').appendTo(editor.toolbar);
					}

					/* store control data */

					if (data)
					{
						control.data('data', data);
					}
				}
			}();

			/* setup control events */

			editor.toolbar.find('div.js_editor_control').mousedown(function ()
			{
				var data = $(this).data('data');

				/* call methode */

				editor[data.methode](data.command, data.message, data.value);
				editor.post();
			});

			/* general action call */

			editor.action = function (command)
			{
				if (editor.checkSelection())
				{
					try
					{
						document.execCommand(command, 0, 0);

						/* fix mozilla styles from preview */

						if (r.constant.MY_BROWSER === 'firefox')
						{
							editor.preview.removeAttr('style');
						}
					}

					/* alert dialog if no support */

					catch (exception)
					{
						$.fn.dialog(
						{
							message: l.editor_browser_support_no + l.point
						});
					}
				}
			};

			/* general insert */

			editor.insert = function (command, message, value)
			{
				/* prompt dialog */

				$.fn.dialog(
				{
					type: 'prompt',
					message: message + l.colon,
					value: value,
					callback: function (input)
					{
						/* create link without selection */

						if (command === 'createLink')
						{
							editor.insertHTML('<a href="' + input + '">' + input + '</a>');
						}

						/* insert function */

						else if (command === 'insertFunction')
						{
							editor.insertHTML('&lt;function&gt;' + input + '&lt;/function&gt;');
						}

						/* else default behavior */

						else
						{
							editor.preview.focus();
							document.execCommand(command, 0, input);
						}
						editor.post();
					}
				});
			};

			/* insert html */

			editor.insertHTML = function (text)
			{
				editor.preview.focus();
				if (r.constant.MY_BROWSER === 'msie')
				{
					document.selection.createRange().pasteHTML(text);
				}
				else
				{
					document.execCommand('insertHTML', 0, text);
				}
			};

			/* insert code quote */

			editor.insertCode = function ()
			{
				if (editor.checkSelection())
				{
					editor.insertHTML('&lt;code&gt;' + editor.select() + '&lt;/code&gt;');
				}
			};

			/* insert document break */

			editor.insertBreak = function ()
			{
				editor.insertHTML('&lt;break&gt;');
			};

			/* alternate format */

			editor.format = function (tag)
			{
				if (tag && editor.checkSelection())
				{
					editor.insertHTML('<' + tag + '>' + editor.select() + '</' + tag + '>');
				}
			};

			/* get selection */

			editor.select = function ()
			{
				var output = '';

				if (r.constant.MY_BROWSER === 'msie')
				{
					output = document.selection.createRange().text;
				}
				else
				{
					output = window.getSelection().toString();
				}
				return output;
			};

			/* check for selected text */

			editor.checkSelection = function ()
			{
				if (editor.select())
				{
					return true;
				}
				else
				{
					/* alert dialog if no selection */

					$.fn.dialog(
					{
						message: l.editor_select_text_first + l.point
					});
					return false;
				}
			};

			/* toggle between source code and wysiwyg */

			editor.toggle = function ()
			{
				if (editor.mode)
				{
					editor.mode = 0;
					editor.preview.html(editor.convertToEntity()).focus();
					editor.toggler.attr('title', l.editor_source_code);
				}
				else
				{
					editor.mode = 1;
					editor.textarea.val(editor.convertToHTML()).focus();
					editor.toggler.attr('title', l.editor_wysiwyg);
				}
				editor.toggler.toggleClass(options.classString.editorSourceCode + ' ' + options.classString.editorWysiwyg).nextAll(options.element.editorControl + ', ' + options.element.editorDivider).toggle();
				editor.textarea.add(editor.preview).toggle();
				editor.validate();
			};

			/* convert to html */

			editor.convertToHTML = function ()
			{
				var output = editor.preview.html();

				/* pseudo tags */

				output = output.replace(/-&gt;/gi, '->');
				output = output.replace(/&lt;(break|code|function)&gt;/gi, '<$1>');
				output = output.replace(/&lt;\/(code|function)&gt;/gi, '</$1>');
				output = output.replace(/[\r\n]/gi, '');

				/* xhtml cleanup */

				if (options.xhtml)
				{
					output = output.replace(/ class="(apple-style-span|msonormal)"/gi, '');
					output = output.replace(/ class=""/gi, '');
					output = output.replace(/ style="(.*?)"/gi, '');
					output = output.replace(/<(\w+)>(\s)*<\/\1>/gi, '');
					output = output.replace(/<b>(.*?)<\/b>/gi, '<strong>$1</strong>');
					output = output.replace(/<i>(.*?)<\/i>/gi, '<em>$1</em>');
					output = output.replace(/<(s|strike)>(.*?)<\/(s|strike)>/gi, '<del>$2</del>');
					output = output.replace(/<br>/gi, '<br />');
					output = output.replace(/(<img [^>]+[^\/])>/gi, '$1 />');
				}

				/* add newlines */

				if (options.newline)
				{
					output = output.replace(/<br \/>/gi, '<br \/>\n');
					output = output.replace(/<\/h([1-6])>/gi, '<\/h$1>\n');
					output = output.replace(/<\/(div|li|ol|p|span|ul)>/gi, '<\/$1>\n');
					output = output.replace(/<(ol|ul)>/gi, '<$1>\n');
				}
				return output;
			};

			/* convert to entity */

			editor.convertToEntity = function ()
			{
				var output = editor.textarea.val();

				output = output.replace(/->/gi, '-&gt;');
				output = output.replace(/<(break|code|function)>/gi, '&lt;$1&gt;');
				output = output.replace(/<\/(code|function)>/gi, '&lt;/$1&gt;');
				return output;
			};

			/* post html to textarea */

			editor.post = function ()
			{
				var html = editor.convertToHTML();

				if (html)
				{
					editor.textarea.val(html);
				}
			};

			/* validate textarea and preview */

			editor.validate = function ()
			{
				editor.textarea.add(editor.preview).attr('data-related', 'editor').trigger('related');
			};

			/* append preview */

			editor.preview = $('<div class="' + options.classString.editorPreview + '" contenteditable="true">' + editor.convertToEntity() + '</div>').appendTo(editor.container);

			/* insert break on enter */

			editor.preview.on('keydown', function (event)
			{
				if (event.which === 13)
				{
					var output = '<br />';

					if (r.constant.MY_BROWSER === 'firefox')
					{
						output += '<div></div>';
					}
					else if (r.constant.MY_BROWSER === 'msie')
					{
						output += '<span></span>';
					}
					else if (r.constant.MY_ENGINE === 'webkit')
					{
						output += '<br />';
					}
					editor.insertHTML(output);
					event.preventDefault();
				}
			});

			/* post and validate on keyup */

			editor.preview.on('keyup', function ()
			{
				editor.post();
				editor.validate();
			});

			/* force xhtml */

			if (options.xhtml)
			{
				try
				{
					document.execCommand('styleWithCSS', 0, false);
				}
				catch (exception)
				{
					try
					{
						document.execCommand('useCSS', 0, true);
					}
					catch (exception)
					{
						return false;
					}
				}
			}
		});
	};

	/* @section 2. startup */

	$(function ()
	{
		if (r.module.editor.startup && (r.constant.LAST_TABLE === 'articles' || (r.constant.ADMIN_PARAMETER === 'new' || r.constant.ADMIN_PARAMETER === 'edit') && (r.constant.TABLE_PARAMETER === 'articles' || r.constant.TABLE_PARAMETER === 'extras' || r.constant.TABLE_PARAMETER === 'comments')))
		{
			$(r.module.editor.selector).editor(r.module.editor.options);
		}
	});
})(jQuery);