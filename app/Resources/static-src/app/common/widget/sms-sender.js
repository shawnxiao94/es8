import notify from 'common/notify';

export default class SmsSender {
  constructor(option) {
    this.$element = $(option.element);
    this.validator = 0;
    this.url = option.url ? option.url : '';
    this.smsType = option.smsType ? option.smsType : '';
    this.captchaNum = option.captchaNum ? option.captchaNum : 'captcha_num';
    this.captcha = option.captcha ? option.captcha : false;
    this.captchaValidated = option.captchaValidated ? option.captchaValidated : false;
    this.dataTo = option.dataTo ? option.dataTo :  'mobile';
    this.setup();
  }

  preSmsSend() {
    return true;
  }

  setup() {
    this.smsSend();
    console.log('smsSend');
  }
  postData(url, data) {
    var self = this;
    console.log(this.$element);
    var refreshTimeLeft = function () {
      var leftTime = $('#js-time-left').html();
      $('#js-time-left').html(leftTime - 1);
      if (leftTime - 1 > 0) {
        self.$element.removeClass('disabled');
        self.$element.addClass('disabled');
        setTimeout(refreshTimeLeft, 1000);
      } else {
        $('#js-time-left').html('');
        $('#js-fetch-btn-text').html(Translator.trans('site.data.get_sms_code_btn'));
        self.$element.removeClass('disabled');
      }
    };
    self.$element.addClass('disabled');
    $.post(url, data, function (response) {
      if (("undefined" != typeof response['ACK']) && (response['ACK'] == 'ok')) {
        $('#js-time-left').html('120');
        $('#js-fetch-btn-text').html(Translator.trans('site.data.get_sms_code_again_btn'));
        if (response.allowance) {
          notify('success', Translator.trans('site.data.get_sms_code_allowance_success_hint', {'allowance':response.allowance}));
        } else {
          notify('success', Translator.trans('site.data.get_sms_code_success_hint'));
        }

        refreshTimeLeft();
      } else {
        if ("undefined" != typeof response['error']) {
          notify('danger', response['error']);
        } else {
          notify('danger', Translator.trans('site.data.get_sms_code_failure_hint'));
        }
      }
    });
    return this;
  }

  smsSend() {
    console.log('smsSend...');
    var leftTime = $('#js-time-left').html();
    if (leftTime.length > 0) {
      return false;
    }
    var url = this.url;
    var data = {};
    data.to = $('[name="' + this.dataTo + '"]').val();
    data.sms_type = this.smsType;
    if (this.captcha) {
      data.captcha_num = $('[name="' + this.captchaNum + '"]').val();
      if (!this.captchaValidated) {
        return false;
      }
    }
    data = $.extend(data, data);
    if (this.preSmsSend()) {
      this.postData(url, data);
    }
    return this;
  }
}