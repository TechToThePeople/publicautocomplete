var publicautocomplete = {
  // Array of all matched values that have been returned as autocomplete options
  'matchedValues': {},

  /**
   * Test whether the value of current_employer field is valid.
   * True if the current value is blank or appears in this.matchedValues; otherwise
   * false.
   */
  'isValid': function() {
    var value = cj('#current_employer').val();
    return (value.length === 0 || (this.matchedValues.hasOwnProperty(value) && this.matchedValues[value]));
  },

  /**
   * Build the label for an option by concatenating the specified properties of the
   * given object.
   *
   * @param obj The object from which to take the values.
   * @param properties Array of obj properties to incluede in the label.
   *
   * @return String
   */
  'buildLabel': function(obj, properties) {
    // If there are multiple properties, string them together with a separator.
    if (properties.length > 1) {
      // Separator to use for concatenation.
      var separator = ' :: ';
      // Array to hold properties that will be concatenated.
      var text_values = [];
      for (var i in properties) {
        var component = obj[properties[i]];
        // Only include the property if it's not an empty value.
        if (! this.isEmpty(component)) {
          text_values.push(component);
        }
      }
      return text_values.join(separator);
    }
    // Otherwise, there's only one property to list, so just use that one.
    else {
      return obj[properties[0]];
    }
  },

  /**
   * Test if the given string is empty or null.
   */
  'isEmpty': function(str) {
    if (typeof str === 'undefined' || str === null) {
      return true;
    }
    return str.replace(/\s/g, '').length < 1;
  },

  'clearInvalidStyling': function(e) {
    console.log('clearInvalidStyling');
    cj('#current_employer').removeClass('publicautocomplete-invalid');
    return true;
  }
};

cj(function($) {
  // Apply jQuery autocomplete to the current_employer field.
  $('#current_employer').autocomplete({
    minLength: CRM.vars['eu.tttp.publicautocomplete'].min_length,
    source: function(request, response) {
      CRM.api3('contact', 'getpublic', {'term': request.term}).done(function(result) {
        // Initialize the list of autocomplete options.
        ret = [];
        if (result.count > 0) {
          // Loop through the values returned by the AJAX call.
          $.each(result.values, function(k, v) {            
            var label = publicautocomplete.buildLabel(v, CRM.vars['eu.tttp.publicautocomplete'].return_properties);
            var value = v[CRM.vars['eu.tttp.publicautocomplete'].return_properties[0]];
            // Store the value in the matchedValues array so we can use it for
            // validation in isValid().
            publicautocomplete.matchedValues[value] = true;
            // Add the value/label pair to the list of autocomplete options.
            ret.push({'value': value, 'label': label});
          });
        }
        // Return the list of autocomplete options.
        response(ret);
      });
    }
  });

  // prevent autofill of the browser
  // the tag contains the attribute autocomplete="off" but this is ignored by modern browsers
  // autocomplete="new-password" works better
  $('#current_employer').attr('autocomplete', 'new-password');

  // If we're configured to ensure that the current_employer field contains an
  // existing organization name, set that up now.
  if (CRM.vars['eu.tttp.publicautocomplete'].require_match === true) {
    var form = $('#current_employer').get(0).form;
    $(form).submit(function (e) {
      // If the current_employer value is invalid, cancel form submission and
      // alert the user.
      if(! publicautocomplete.isValid()) {
        e.preventDefault();
        alert(CRM.vars['eu.tttp.publicautocomplete'].required_error);
        $('#current_employer').focus().select().addClass('publicautocomplete-invalid');
        $(form).trigger('invalid-form');
      }
    });

    // If there's already a value in the current_employer field, peform a search
    // on that value and add any matching values to autocomplete.matchedValues
    // so we can use it for validation in isValid().
    var initialValue = $('#current_employer').val();
    if (initialValue.length) {
      CRM.api3('contact', 'getpublic', {'term': initialValue}).done(function(result) {
        if (result.count > 0) {
          // Loop through the values returned by the AJAX call.
          $.each(result.values, function(k, v) {
            var value = v[CRM.vars['eu.tttp.publicautocomplete'].return_properties[0]];
            publicautocomplete.matchedValues[value] = true;
          });
        }
      });
    }
  }

  // Clear any "invalid" styling upon change of value.
  $('#current_employer').change(publicautocomplete.clearInvalidStyling);
});

