var CrateIt = CrateIt || {};

CrateIt.Validation = {

  FieldValidator: function($field) {

    var $message = $field.next().find('label');
    var validators = [];
    var _self = this;
    var invalidMessage = '';

    this.validate = function() {
      var result = false;
      var value = $.trim($field.val());
      for (var i = 0; i < validators.length; i++) {
        var validator = validators[i];
        result = validator.isValid(value);
        if (!result) {
          invalidMessage = validator.invalidMessage;
          break;
        }
      }
      return result;
    }

    this.addValidator = function(validator) {
      validators.push(validator);
    }

    var checkValidity = function() {
      var valid = _self.validate();
      if (valid) {
        hideInvalidMessage();
      } else {
        showInvalidMessage();
      }
    }

    var showInvalidMessage = function() {
      $message.text(invalidMessage);
      $message.show();
    }

    var hideInvalidMessage = function() {
      $message.hide();
    }

    $field.keyup(checkValidity);

  },


  FormValidator: function($form) {

    var validators = {};
    var _self = this;
    var $submit = $form.find('.btn-primary');


    this.addValidator = function($field, fieldValidator) {
      var key = $field.attr('id');
      if (key in validators) {
        var validator = validators[key];
      } else {
        var validator = new CrateIt.Validation.FieldValidator($field);
        validators[key] = validator;
      }
      validator.addValidator(fieldValidator);
    }


    this.validate = function() {
      var valid = false;
      for (var validator in validators) {
        valid = validators[validator].validate();
        if (!valid) {
          break;
        }
      }
      if (valid) {
        $submit.prop('disabled', false);
      } else {
        $submit.prop('disabled', true);
      }
    }

    $form.keyup(_self.validate);

  },

  YearValidator: function() {
    this.invalidMessage = 'Must be a valid year';

    var regex = /^\d{4}$/;

    this.isValid = function(value) {
      return regex.test(value);
    }

  },

  MaxLengthValidator: function(fieldName, maxLength) {
    this.invalidMessage = fieldName + ' must not be more than ' + maxLength + ' characters';

    this.isValid = function(value) {
      return value.length <= maxLength;
    }

  },

  RequiredValidator: function(fieldName) {
    this.invalidMessage = fieldName + ' is required';

    this.isValid = function(value) {
      return !(!value || /^\s*$/.test(value));
    }

  },

  EmailValidator: function() {
    this.invalidMessage = 'Must be a valid email address';

    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

    this.isValid = function(value) {
      return regex.test(value);
    }

  },

  UrlValidator: function() {
    this.invalidMessage = 'Must be a valid URL';

    var regex = /^https?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/;

    this.isValid = function(value) {
      return regex.test(value);
    }

  },


  OptionalValidator: function(validator) {
    this.invalidMessage = validator.invalidMessage;

    this.isValid = function(value) {
      return /^\s*$/.test(value) || validator.isValid(value);
    }

  }



};