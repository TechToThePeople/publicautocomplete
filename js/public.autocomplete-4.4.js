var publicautocomplete = {
  // Array of all matched values that have been returned as autocomplete options
  'matchedValues': {},

  /**
   * Test whether the value of current_employer field is valid.
   * True if the current value is blank or appears in this.matchedValues; otherwise
   * false.
   */
  'isValid': function() {
    value = cj('#current_employer').val();
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
  }
};

cj(function($) {
  // Apply jQuery autocomplete to the current_employer field.
  $('#current_employer').autocomplete('/civicrm/ajax/rest?entity=contact&action=getpublic&json=1', {
    dataType: "json",
    extraParams: {term:function () { return $("#current_employer").val();} },
    parse: function(data) {
      if ("is_error" in data && data.is_error !== 0) {
        return {};
      }

      // Array to hold autocomplete option objects.
      var parsed = [];
      // Loop through reeturned values and add them to parsed.
      var values = data.values;
      for (var i in values) {
        var v = values[i];
        // value is the displayed label in autocomplete options list.
        var value = publicautocomplete.buildLabel(v, CRM.vars['eu.tttp.publicautocomplete'].return_properties);
        // result is the value that gets inserted into the text input upon selection.
        var result = v[CRM.vars['eu.tttp.publicautocomplete'].return_properties[0]];
        parsed.push({ data:v, value:value, result:result });
        
        // Add result to publicautocomplete.matchedValues so we can validate it
        // upon submission if require_match is true.
        publicautocomplete.matchedValues[result] = true;
      }
      return parsed;
    },
    formatItem: function(data, i, max, value, term){
      return value;
    },
    // Don't automatically select the first autocomplete option (encourage the
    // user to be explicit in their selection). 
    selectFirst: false
  });

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
        $('#current_employer').focus().select().css({'border-color':'red', 'outline': 'none'});
      }
    });

    // If there's already a value in the current_employer field, peform a search
    // on that value and add any matching values to autocomplete.matchedValues
    // so we can use it for validation in isValid().
    var initialValue = $('#current_employer').val();
    if (initialValue.length) {
      CRM.api3('contact', 'getpublic', {'term': initialValue}).done(function(result) {
        if (result.values.length > 0) {
          // Loop through the values returned by the AJAX call.
          $.each(result.values, function(k, v) {
            var value = v[CRM.vars['eu.tttp.publicautocomplete'].return_properties[0]];
            publicautocomplete.matchedValues[value] = true;
          });
        }
      });
    }


  }
});

