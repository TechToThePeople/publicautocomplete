cj(function($) {
  $('#current_employer')
    .autocomplete({
      source: function(request, response) {
        CRM.api3('contact', 'getpublic', {'term': request.term}).done(function(result) {
          var ret = [];
          console.log (result);
          if (result.values) {
            $.each(result.values, function(k, v) {
              ret.push({value: v.organization_name});
            })
          }
          response(ret);
        })
      },
      focus: function (event, ui){
        return false;
      }, 
      select: function (event, ui) {
        console.log(ui.item);
      }
    })
});
