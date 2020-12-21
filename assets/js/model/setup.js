/**
 * @summary     Setup
 * @description Setup model
 * @requires    jQuery library, Bootstrap
 * @author      Keith Andrews
 */
if(!com) var com = {};
if(!com.c_trax_integration) com.c_trax_integration = {};
if(!com.c_trax_integration.app) com.c_trax_integration.app = {};
if(!com.c_trax_integration.model) com.c_trax_integration.model = {};
if(!com.c_trax_integration.model.setup) com.c_trax_integration.model.setup = {

	/**
	 * Ready events for the setup page
	 */
	ready: function()
	{
		jQuery('#connect-account-contain #connect-account').on('click', com.c_trax_integration.model.setup.connectAccount);
	},

	/**
	 * Get the username and password and send to the api to store the account
	 */
	connectAccount: function()
	{
		// Get the fields and validate data first
		var fields = jQuery('#connect-account-contain :input:not(button)');
		var data = {};
		var valid = true;
		jQuery.each(fields, function(i, field)
		{
			field = jQuery(field);
			if(field.val())
			{
				field.removeClass('is-invalid');
				data[field.prop('id')] = field.val();
			} else {
				field.addClass('is-invalid');
				valid = false;
			}
		});

		if(valid)
		{
			com.c_trax_integration.app.runAjax('c_trax_connect_account', data, com.c_trax_integration.model.setup.connectionComplete, '#connect-account-contain');
		}
	},

	/**
	 * Account connection complete
	 * @param data
	 */
	connectionComplete: function(data)
	{
		if(data.hasOwnProperty('auth_token'))
		{
			jQuery('#setup-input-contain').addClass('hidden');
			jQuery('#setup-continue-contain').removeClass('hidden');
		}
	}
};