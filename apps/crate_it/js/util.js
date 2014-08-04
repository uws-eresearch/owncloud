var CrateIt = CrateIt || {};

CrateIt.Util = {

  FieldValidator: function($field, formValidator) {

    var $message = $field.next().find('label');
    var validators = [];
    var _self = this;
    var invalidMessage = '';

    this.validate = function() {
      var result = false;
      var value = $.trim($field.val());
      for(var i = 0; i < validators.length; i++){
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
      $field.keyup(checkValidity);
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

  },


  FormValidator: function($form) {

    var validators = [];
    var _self = this;
    var $submit = $form.find('.btn-primary');


    this.addValidator = function($field, fieldValidator) {
      var validator = new CrateIt.Util.FieldValidator($field, _self);
      validator.addValidator(fieldValidator);
      validators.push(validator);
    }


    this.validate = function() {
      console.log('putin');
      var valid = false;
      for(var i = 0; i < validators.length; i++){
        var validator = validators[i];
        valid = validator.validate();
        if(!valid) {
          break;
        }
      }
      if(valid) {
        $submit.removeAttr('disabled');
      } else {
        $submit.attr('disabled', 'disabled');
      }
    }

    $form.keyup(_self.validate);

  },

  YearValidator: function() {
    this.invalidMessage = 'Must be a valid submit year';

    var regex = /^\d{4}$/;

    this.isValid = function(value) {
      return regex.test(value);
    }

  },

  MaxLengthValidator: function(fieldName, maxLength) {
    this.invalidMessage = fieldName + 'must be less than ' + maxLength + ' characters';

    this.isValid = function(value) {
      return value.length <= maxLength;
    }

  }


  // function validateEmail($input, $error, $confirm) {
  //   validateTextLength($input, $error, $confirm, 128);
  //   var email = $input.val();
  //   var isEmail = function() {
  //     var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  //     return regex.test(email);
  //   }
  //   if(!isEmail()) {
  //     $confirm.prop('disabled', true);
  //     $error.text('Not recognised as a valid email address');
  //     $error.show();
  //   }
  // }

  // function validateYear($input, $error, $confirm) {
  //   var inputYear = $.trim($input.val());
  //   var isYear = function() {
  //     var regex = /^\d{4}$/;
  //     return regex.test(inputYear);
  //   }
  //   var emptyYear = function() {
  //     return (!inputYear || /^\s*$/.test(inputYear));
  //   };
  //   if(emptyYear()) {
  //     $confirm.prop('disabled', true);
  //     $error.text('Year can not be blank');
  //     $error.show();
  //   } else if(!isYear()) {
  //     $confirm.prop('disabled', true);
  //     $error.text('Must be a valid submit year');
  //     $error.show();
  //   } else {
  //     $confirm.prop('disabled', false);
  //     $error.hide();
  //   }
  // }

  // function validateTextLength($input, $error, $confirm, maxLength) {
  //   if (typeof(maxLength) === 'undefined') {
  //     maxLength = 256;
  //   }
  //   var inputText = $input.val();
  //   var emptyText = function() {
  //     return (!inputText || /^\s*$/.test(inputText));
  //   };
  //   if(emptyText()) {
  //     $confirm.prop('disabled', true);
  //     $error.text('Field cannot be blank');
  //     $error.show();
  //   } else if (inputText.length > maxLength) {
  //       $error.text('Field has reached the limit of ' + maxLength + ' characters');
  //       $input.val(inputText.substr(0, maxLength));
  //       $error.show();
  //       $confirm.prop('disabled', false);
  //    } else {
  //     $confirm.prop('disabled', false);
  //     $error.hide();
  //   }
  // }


  // function validateCrateName($input, $error, $confirm) {
  //   var inputName = $input.val();
  //   var crates = $.map($('#crates > option'), function(el, i) {
  //     return $(el).attr('id');
  //   });
  //   var emptyName = function() {
  //     return (!inputName || /^\s*$/.test(inputName));
  //   };
  //   var existingName = function() {
  //     return crates.indexOf(inputName) > -1;
  //   };
  //   if(existingName() || emptyName()) {
  //     $confirm.prop('disabled', true);
  //     if (emptyName()) {
  //       $error.text('Crate name cannot be blank');
  //     } else {
  //       $error.text('Crate with name "' + inputName + '" already exists');
  //     }
  //     $error.show();
  //   } else if (inputName.length > 128) {
  //       $error.text('Crate name has reached the limit of 128 characters');
  //       $input.val(inputName.substr(0, 128));
  //       $error.show();
  //       $confirm.prop('disabled', false);
  //    } else {
  //     $confirm.prop('disabled', false);
  //     $error.hide();
  //   }
  // }



};
