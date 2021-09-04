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

		return Component.extend({
			validateForm: function (form) {
				return $(form).validation() && $(form).validation('isValid');
            },
			submitForm: function () {
				if (!this.validateForm('#buycredit-form')) {
					return;
				}
				body.loader('show');
				$.ajax({
					url: this.getActionUrl(),
					type: 'POST',
					dataType: "json",
					data: $('#buycredit-form').serialize(),
					complete: function (data) {
						var response = $.parseJSON(data.responseText);
						body.loader('hide');
						if (response.result == "success") {
							setTimeout(function () {
								window.location.href = url.build('checkout/cart');
							}, 2000);
						}
					},
				});
			},
			getActionUrl: function () {
				return url.build('walletrewardpoints/customer/purchasecredit');
			},
			getWithdrawCreditUrl: function () {
				window.location.href = url.build('walletrewardpoints/withdraw/form');
			},
			getInviteFriendUrl: function () {
				window.location.href = url.build('walletrewardpoints/customer/invitefriend');
			},
			getAllowToBuyCredit: function () {
				return window.allowToBuyCredit;
			},
			getAllowToWithdrawal: function () {
				return window.allowToWithdrawal;
			},
			getAllowToInvite: function () {
				return window.allowToInvite;
			},
			getTotalWalletCredit: function () {
				return window.totalWalletCredit;
			},
			getAllowToSendCredit: function () {
				return window.allowToSendCredit;
			},
			getSendCredittoFriendUrl: function () {
				window.location.href = window.sendCredittoFriendUrl;
			},
 		});
	}
);
