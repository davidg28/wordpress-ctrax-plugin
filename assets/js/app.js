/**
 * @summary     Application
 * @description Application global
 * @requires    jQuery library, Bootstrap
 * @author      Keith Andrews
 */
if(!com) var com = {};
if(!com.c_trax_integration) com.c_trax_integration = {};
if(!com.c_trax_integration.app) com.c_trax_integration.app = {

	_ajaxProcesses: [],

	/**
	 * Method for calling generic ajax method
	 * @param action   - action to process
	 * @param data     - array of data to be sent
	 * @param element  - element to place the loading class on
	 * @param hasFile  - boolean if passing a file
	 * @param callback - callback method to happen on complete
	 * @param args     - extra data to send to the callback
	 */
	runAjax: function(action, data, callback, element, hasFile, args)
	{
		com.c_trax_integration.app.enableLoading(element);
		if(data == null)
			data = {};
		data._ajax_nonce = c_trax_integration.nonce;
		data.action = action;

		var _raw = (data.hasOwnProperty('_raw')) ? data._raw : false;
		var extraArgs = [];
		jQuery.ajaxSetup(
			{
				headers: {
					'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
				}
			}
		);

		var responseJson = jQuery.ajax(
			{
				url        : c_trax_integration.ajax_url,
				method     : 'POST',
				data       : data,
				processData: (hasFile == true) ? false : true,
				contentType: (hasFile == true) ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
				dataType   : 'json',
				fail       : function(jqXHR, textStatus, errorThrown)
				{
					com.c_trax_integration.app.disableLoading(element);
					com.c_trax_integration.app.ajaxError(jqXHR, textStatus, errorThrown);
				},
				complete   : function(jqXHR, textStatus)
				{
					try
					{
						var data = null;
						// If the response doesn't need to be parsed in Json
						if(!_raw && jqXHR.responseText)
							data = jQuery.parseJSON(jqXHR.responseText);
						else
						{
							if(jqXHR.responseText)
								data = jqXHR.responseText;
						}

						if(data != null && data.hasOwnProperty('page_contents'))
							com.c_trax_integration.app.displayPageContents(data.page_contents, element);

						// For each extra argument after the callback, insert into the args array
						if(typeof (args) == 'array')
						{
							for(var i = 0; i < args.length; ++i)
							{
								extraArgs.push(args[i]);
							}
						} else
							extraArgs = args;

						com.c_trax_integration.app.ajaxComplete(data, callback, extraArgs, element);
					}
					catch(e)
					{
						com.c_trax_integration.app.ajaxError(jqXHR, e, 'Exception', element);
					}
					finally
					{
						com.c_trax_integration.app.disableLoading(element);
					}
				},
				done       : function(data, textStatus, jqXHR)
				{
					com.c_trax_integration.app.disableLoading(element);
				}
			}
		);
	},

	/**
	 * Method when ajax returns an error
	 * @param jqXHR
	 * @param textStatus
	 * @param errorThrown
	 * @param element
	 */
	ajaxError: function(jqXHR, textStatus, errorThrown, element)
	{
		console.log('Failure to process ajax request', textStatus, errorThrown);
		console.log(jqXHR);
		com.c_trax_integration.app.systemMessage('An error has occurred while trying to parse the request.', 'error', element);
	},

	/**
	 * Method when ajax completes, return json data
	 * @param response
	 * @param callback
	 * @param args
	 * @param element
	 */
	ajaxComplete: function(response, callback, args, element)
	{
		if(response != null && response.hasOwnProperty('status') && response.status == 'error' && !response.hasOwnProperty('page_contents'))
		{
			var exception = (response.hasOwnProperty('exception') && response.exception.hasOwnProperty('message')) ? ' ' + response.exception.message : '';
			com.c_trax_integration.app.systemMessage(response.message + exception, 'error', element);
		} else
		{
			if(response != null && response.hasOwnProperty('message') && response.message && response.hasOwnProperty('status') && !response.hasOwnProperty('page_contents'))
			{
				com.c_trax_integration.app.systemMessage(response.message, response.status, element);
			}

			var data;
			if(response != null && response.hasOwnProperty('status') && response.status == 'success' && response.hasOwnProperty('data'))
			{
				data = response.data;
			}
			// Pass to the callback
			if(typeof callback == 'string')
				com.c_trax_integration.app.executeFunctionByName(callback, window, data, args, response);
			else if(typeof callback == 'function')
			{
				callback(data, args, response);
			}
		}
	},

	/**
	 * Modify the element and it's buttons when loading
	 * @param element
	 */
	enableLoading: function(element)
	{
		if(element === false)
			return;

		// Add to processes
		com.c_trax_integration.app._ajaxProcesses.push(Date());

		if(!jQuery('.loader').is(':visible'))
			jQuery('.loader').show();
		if(element && typeof element == 'object')
		{
			jQuery(element).addClass('loading');
			jQuery(element).find('input[type=button], input[type=submit], button').attr('disabled', true);
		} else if(element)
		{
			jQuery(element).addClass('loading');
			jQuery(element + 'input[type=button], ' + element + ' input[type=button], ' + element + 'input[type=submit],' + element + ' input[type=submit], ' +
				       element + ' select')
				.attr('disabled', true);
		}
	},

	/**
	 * Modify the element and it's buttons when finished loading
	 * @param element
	 */
	disableLoading: function(element)
	{
		if(element === false)
			return;

		// Pop a process off and check to make sure others are not still there in order to remove the loading display
		com.c_trax_integration.app._ajaxProcesses.pop();
		if(com.c_trax_integration.app._ajaxProcesses.length === 0)
		{
			if(jQuery('.loader').is(':visible'))
				jQuery('.loader').hide();
			if(element && typeof element == 'object')
			{
				jQuery(element).removeClass('loading');
				jQuery(element).find('input[type=button], input[type=submit]').removeAttr('disabled');
			} else if(element)
			{
				jQuery(element).removeClass('loading');
				jQuery(element + 'input[type=button], ' + element + ' input[type=button], ' + element + 'input[type=submit],' + element + ' input[type=submit], ' +
					       element + ' select')
					.removeAttr('disabled');
			}
		}
	},

	/**
	 * Display the page contents at the top of the page or closest div
	 * @param contents
	 * @param element
	 */
	displayPageContents: function(contents, element)
	{
		if(element == '' || element == null || jQuery(element).length == -1)
		{
			jQuery('#wpbody .wrap').after(contents);
			jQuery('html, body').animate(
				{
					scrollTop: jQuery('body').offset().top - 20
				}, 'slow');
		} else
		{
			if(jQuery(element).is('div'))
				jQuery(element).prepend(contents);
			else
				jQuery(element).closest('div').before(contents);
		}
	},

	/**
	 * Set a system message for better UI
	 * Is based on the PHP method 'systemMessage'
	 * @param message
	 * @param type | error, info, success
	 * @param selector
	 */
	systemMessage: function(message, type, selector)
	{
		if(selector === false)
			return;

		var divClass = 'info';
		var html = '';
		var fa;

		switch(type)
		{
			case 'error':
				divClass = 'danger';
				break;
			case 'success':
				divClass = 'success';
				break;
			case 'info':
				divClass = 'info';
				break;
			case 'warning':
				divClass = 'warning';
				break;
			default:
				divClass = 'info';
				break;
		}

		// Set the pre message string
		switch(divClass)
		{
			case 'success':
				type = 'Success';
				fa = 'fa-check';
				break;
			case 'error':
			case 'danger':
				type = 'Error';
				fa = 'fa-warning';
				break;
			case 'warning':
				type = 'Warning';
				fa = 'fa-warning';
				break;
			default:
				type = 'Info';
				fa = 'fa-info';
				break;
		}

		// Display if there is something to display
		if(message != '')
		{
			html = '<div class="system-message alert alert-' + divClass + ' alert-dismissible fade show" role="alert">';
			html += '<strong class="alert-title"><i class="fa ' + fa + '"></i> ' + type + '</strong> ' + message;
			html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			html += '</div>';
		}

		com.c_trax_integration.app.displayPageContents(html, selector);
	},

	/**
	 * Execute a namespace method that is a string
	 * @param functionName
	 * @param context
	 * @returns {*}
	 */
	executeFunctionByName: function(functionName, context /*, args */)
	{
		var args = [].slice.call(arguments).splice(2);
		var namespaces = functionName.split('.');
		var func = namespaces.pop();

		for(var i = 0; i < namespaces.length; i++)
		{
			context = context[namespaces[i]];
		}

		return context[func].apply(context, args);
	},
};