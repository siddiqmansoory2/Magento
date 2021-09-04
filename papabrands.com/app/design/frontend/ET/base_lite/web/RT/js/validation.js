var RequireText = function (me) {
    var text = $.trim($(me).val());
    if (text == '') {
        $(me).addClass("vErrorRed");
        return false;
    } else {
        $(me).removeClass("vErrorRed");
        return true;
    }
}

var RequireSpecialText = function (me) {
    var text = $.trim($(me).val());
    if (text == '') {
        $(me).parent().addClass("userhas-error");
        return false;
    } else {
        $(me).parent().removeClass("userhas-error");
        return true;
    }
}



var RequireSelect = function (me) {
    var SelectedText = $(me).find('option:selected').text();
    var SelectedVal = $(me).find('option:selected').val();
    var FirstText = $(me).find('option').first().text();
    var FirstVal = $(me).find('option').first().val();

    if (FirstText == SelectedText && FirstVal == SelectedVal) {
        $(me).addClass("vErrorRed");
        return false;
    } else {
        $(me).removeClass("vErrorRed");
        return true;
    }
}

var EmailValidate = function (me) {
    var pattern = /^[a-zA-Z0-9\-_]+(\.[a-zA-Z0-9\-_]+)*@[a-z0-9]+(\-[a-z0-9]+)*(\.[a-z0-9]+(\-[a-z0-9]+)*)*\.[a-z]{2,4}$/;

    var emailText = $.trim($(me).val());
    if (pattern.test(emailText)) {
        $(me).removeClass("vErrorRed");
        return true;
    } else {
        $(me).addClass("vErrorRed");
        return false;
    }
}

var UrlValidate = function (me) {
    var pattern = /^(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?$/i;

    var urlText = $.trim($(me).val());
    if (urlText == "") {
        $(me).removeClass("vErrorRed");
        return true;
    }

    if (pattern.test(urlText)) {
        $(me).removeClass("vErrorRed");
        return true;
    } else {
        $(me).addClass("vErrorRed");
        return false;
    }
}

var RequireMatchPassword = function (me1, me2) {

    var value1 = $.trim($(me1).val());
    var value2 = $.trim($(me2).val());
   
    if (value1 == value2) {
        $(me2).removeClass("vErrorRed");
        return true;
    } else {
        $(me2).addClass("vErrorRed");
        return false;
    }
}

var PageValidation = function (SubmitButtonID) {
    var IsValid = true;
    
    $('.vRequiredText').each(function () {
        if ($(this).attr("data-validate") == SubmitButtonID) {
            if (!Boolean(RequireText($(this)))) {
                IsValid = false;
            }
        }
    });

    $('.vSpecialRequiredText').each(function () {
        if ($(this).attr("data-validate") == SubmitButtonID) {
            if (!Boolean(RequireSpecialText($(this)))) {
                IsValid = false;
            }
        }
    });

    $('.vRequiredDropdown').each(function () {
        if ($(this).attr("data-validate") == SubmitButtonID) {
            if (!Boolean(RequireSelect($(this)))) {
                IsValid = false;
            }
        }
    });

    $('.vEmailAddress').each(function () {
        if ($(this).attr("data-validate") == SubmitButtonID) {
            if (!Boolean(EmailValidate($(this)))) {
                IsValid = false;
            }
        }
    });

    $('.vIntegerOnly').each(function () {
        var txt = $(this).val();
        var me = $(this);
        if (txt != '' && ($(this).attr("data-validate") == SubmitButtonID)) {
            var num = Number(txt);
            if (isNaN(num)) {
                IsValid = false;

                if ($(me).hasClass("vSpecialRequiredText")) {
                    $(me).parent().addClass("userhas-error");
                } else {
                    $(me).addClass("vErrorRed");
                }

            } else {

                if ($(me).hasClass("vSpecialRequiredText")) {
                    $(me).parent().removeClass("userhas-error");
                } else {
                    $(me).removeClass("vErrorRed");
                }
            }
        }
    });

    $('.vNumberOnly').each(function () {
        var txt = $(this).val();
        var me = $(this);
        if (txt != '' && ($(this).attr("data-validate") == SubmitButtonID)) {
            var num = Number(txt);
            if (isNaN(num)) {
                IsValid = false;

                if ($(me).hasClass("vSpecialRequiredText")) {
                    $(me).parent().addClass("userhas-error");
                } else {
                    $(me).addClass("vErrorRed");
                }
                
            } else {

                if ($(me).hasClass("vSpecialRequiredText")) {
                    $(me).parent().removeClass("userhas-error");
                } else {
                    $(me).removeClass("vErrorRed");
                }
            }
        }
    });

    return IsValid;
}

$(document).ready(function () {
    $('.vRequiredText').focusout(function () {
        RequireText($(this));
    });

    // for bootstrap controls
    $('.vSpecialRequiredText').focusout(function () {
        RequireSpecialText($(this));
    });

    $('.vRequiredDropdown').focusout(function () {
        RequireSelect($(this));
    });

    $('.vIntegerOnly').keypress(function (e) {
        var specialKeys = new Array();
        specialKeys.push(8);
        var keyCode = e.which ? e.which : e.keyCode
        var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
        return ret;
    });

    $('.vNumberOnly').keypress(function (evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode;
        return !(charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57));
    });

    $('.vAlphabetOnly').keypress(function (e) {
        var specialKeys = new Array();
        specialKeys.push(8);
        specialKeys.push(32);
        var keyCode = e.which ? e.which : e.keyCode
        var ret = ((keyCode >= 65 && keyCode <= 90) || (keyCode >= 97 && keyCode <= 122) || specialKeys.indexOf(keyCode) != -1);
        return ret;
    });

    $('.vAlphabetAndNumberOnly').keypress(function (e) {
        var specialKeys = new Array();
        specialKeys.push(8);
        specialKeys.push(32);
        var keyCode = e.which ? e.which : e.keyCode
        var ret = ((keyCode >= 48 && keyCode <= 57) || (keyCode >= 65 && keyCode <= 90) || (keyCode >= 97 && keyCode <= 122) || specialKeys.indexOf(keyCode) != -1);
        return ret;
    });

    $('.vEmailAddress').focusout(function () {
        EmailValidate($(this));
    });

    $('.vUrlValidation').focusout(function () {
        UrlValidate($(this));
    });
  
   
    
});