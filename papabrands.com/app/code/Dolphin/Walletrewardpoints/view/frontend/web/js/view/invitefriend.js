// @codingStandardsIgnoreStart
define([
	'jquery',
	'ko',
	'uiComponent',
	'mage/url',
	'mage/validation'
	],
	function ($, ko, Component, url) {
		"use strict";
		var body = $('body').loader();

		return Component.extend({
			validateForm: function (form) {
				return $(form).validation() && $(form).validation('isValid');
            },
			submitForm: function () {
				if (!this.validateForm('#invite-friend-form')) {
					return;
				}
				body.loader('show');
				$.ajax({
					url: this.getActionUrl(),
					type: 'POST',
					dataType: "json",
					data: $('#invite-friend-form').serialize(),
					complete: function (data) {
						body.loader('hide');
						window.location.reload();
					},
				});
			},
			getActionUrl: function () {
				return url.build('walletrewardpoints/customer/invitefriendsubmit');
			},
			getBackUrl: function () {
				return url.build('walletrewardpoints/customer/transaction');
			},
			getOneRewardPointCost: function () {
				return window.oneCreditCost;
			},
			getInviteFriRewardPoints: function () {
				return window.ifRewardPoints;
			},
			getInviteFriEarnLimit: function () {
				return window.ifrLimit;
			},
			getInviteFriCOEnable: function () {
				return window.ifCreateOrderEnable;
			},
			getInviteFriCOMessage: function () {
				return window.ifMessage;
			},
			getInviteFriOrderLimit: function () {
				return window.ifoLimit;
			},
 		});
	}
);
// @codingStandardsIgnoreEnd
