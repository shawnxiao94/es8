export default class UserInfoFieldsItemValidate {
  constructor(options) {
    this.validator = null;
    this.$element = $(options.element);
    this.setup();
  }

  setup() {
    this.createValidator();
    this.initComponents();
  }

  initComponents() {
    $('.date').each(function () {
      $(this).datetimepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        minView: 2,
        language: document.documentElement.lang
      });
    });
  }

  createValidator() {
    this.validator = this.$element.validate({
      currentDom: '#form-submit-btn',
      rules: {
        email: {
          required: true,
          email: true,
          remote: {
            url: $('#email').data('url'),
            type: 'get',
            data: {
              'value': function () {
                return $('#email').val();
              }
            }
          }
        },
        mobile: {
          required: true,
          phone: true,
        },
        truename: {
          required: true,
          chinese_alphanumeric: true,
          minlength: 2,
          maxlength: 36,
        },
        qq: {
          required: true,
          qq: true,
        },
        idcard: {
          required: true,
          idcardNumber: true,
        },
        gender: {
          required: true,
        },
        company: {
          required: true,
        },
        job: {
          required: true,
        },
        weibo: {
          required: true,
          url: true,
        },
        weixin: {
          required: true,
        }
      },
      messages: {
        gender: {
          required: Translator.trans('site.choose_gender_hint'),
        },
        mobile: {
          phone: Translator.trans('validate.phone.message'),
        }
      }
      });
    this.getCustomFields();
  }

  getCustomFields() {
    for (var i = 1; i <= 5; i++) {
      $(`[name="intField${i}"]`).rules('add', {
        required: true,
        int: true,
      });
      $(`[name="floatField${i}"]`).rules('add', {
        required: true,
        float: true,
      });
      $(`[name="dateField${i}"]`).rules('add', {
        required: true,
        date: true,
      });
    }
    for (var i = 1; i <= 10; i++) {
      $(`[name="varcharField${i}"]`).rules('add', {
        required: true,
      });
      $(`[name="textField${i}"]`).rules('add', {
        required: true,
      });
    }
  }
}
