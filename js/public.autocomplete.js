var publicautocomplete = {
  // Array of all matched values that have been returned as autocomplete options
  'matchedValues': [],
  /**
   * Test whether the value of current_employer field is valid.
   * True if the current value is blank or appears in this.matchedValues; otherwise
   * false.
   */
  'isValid': function() {
    value = cj('#current_employer').val()
    return (value.length == 0 || (this.matchedValues.hasOwnProperty(value) && this.matchedValues[value]));
  }
};

cj(function($) {
  // Apply jQuery autocomplete to the current_employer field.
  $('#current_employer').autocomplete({
    source: function(request, response) {
      CRM.api3('contact', 'getpublic', {'term': request.term}).done(function(result) {
        // Initialize the list of autocomplete options.
        ret = [];
        if (result.values.length > 0) {
          // Loop through the values returned by the AJAX call.
          $.each(result.values, function(k, v) {
            var display_value = v.organization_name;
            // Store the value in the matchedValues array so we can use it for
            // validation in isValid().
            publicautocomplete.matchedValues[display_value] = true;
            // Add the value to the list of autocomplete options.
            ret.push({'value': display_value});
          })
        }
        // Return the list of autocomplete options.
        response(ret);
      })
    }
  });

  // If we're configured to ensure that the current_employer field contains an
  // existing organization name, make it so.
  if (CRM.vars['eu.tttp.publicautocomplete'].require_match === true) {
    var form = $('#current_employer').get(0).form
    $(form).submit(function (e) {
      // If the current_employer value is invalid, cancel form submission and
      // alert the user.
      if(! publicautocomplete.isValid()) {
        e.preventDefault();
        alert(CRM.vars['eu.tttp.publicautocomplete'].required_error);
        $('#current_employer').focus().select().css({'border-color':'red', 'outline': 'none'});
      }
    });
  }
});

