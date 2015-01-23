cj(function($) {
  $('#current_employer')
    .autocomplete({
      source: function(request, response) {
        CRM.api3('contact', 'getpublic').done(function(result) {
          var ret = [];
console.log (result);
          if (result.values) {
            $.each(result.values, function(k, v) {
              ret.push({value: v.sort_name});
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

//        document.location = CRM.url('civicrm/contact/view', {reset: 1, cid: ui.item.value});
//        return false;
      },
      create: function() {
        // Place menu in front
//        $(this).autocomplete('widget').css('z-index', $('#civicrm-menu').css('z-index'));
      }
    })
    .keydown(function() {
//      $.Menu.closeAll();
    });
});
