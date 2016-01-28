var publicautocomplete = {
  'matchedValues': [],
  'isValid': function() {
    value = cj('#current_employer').val()
    return (value.length == 0 || (this.matchedValues.hasOwnProperty(value) && this.matchedValues[value]));
  }
};

cj(function($) {
  $('#current_employer').autocomplete({
    source: function(request, response) {
      CRM.api3('contact', 'getpublic', {'term': request.term}).done(function(result) {
        ret = [];
        if (result.values.length > 0) {
          $.each(result.values, function(k, v) {
            publicautocomplete.matchedValues[v.organization_name] = true;
            ret.push({value: v.organization_name});
          })
        }
        response(ret);
      })
    },
    focus: function (event, ui){
      return false;
    }
  });

  if (CRM.vars['eu.tttp.publicautocomplete'].require_match === true) {
    var form = $('#current_employer').get(0).form
    $(form).submit(function (e) {
      if(! publicautocomplete.isValid()) {
        e.preventDefault();
        alert(CRM.vars['eu.tttp.publicautocomplete'].required_error);
        $('#current_employer').focus().select().css({'border-color':'red', 'outline': 'none'});
      }
    });
  }
});

