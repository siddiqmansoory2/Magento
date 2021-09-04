define([
	'jquery',
	'ko',
	'uiComponent',
	'Magento_Customer/js/customer-data',
	'mage/url',
	'mage/validation'
	],
	function ($, ko, Component, customerData, url) {
		"use strict";
		var body = $('body').loader();
		var status = ko.observable();

		return Component.extend({
			validateForm: function (form) {
				return $(form).validation() && $(form).validation('isValid');
            },
			submitForm: function () {
				if (!this.validateForm('#transaction-subscription-form')) {
					return;
				}
				body.loader('show');
				$.ajax({
					url: this.getActionUrl(),
					type: 'POST',
					dataType: "json",
					data: $('#transaction-subscription-form').serialize(),
					complete: function (data) {
						body.loader('hide');
					},
				});
			},
			getActionUrl: function () {
				return url.build('walletrewardpoints/transaction/save');
			},
			getIsSubscribed: function () {
				$.ajax({
					url: url.build('walletrewardpoints/transaction/status'),
					type: 'GET',
					dataType: "json",
					complete: function (data) {
						var response = $.parseJSON(data.responseText);
						status(response.status);
					},
				});
				return status;
			}
 		});
	}
);
