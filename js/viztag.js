var lastTag = function(query) {
    var ret = /([^,]+)$/.exec(query);
    if(ret && ret[1])
        return ret[1].trim();
    return '';
}

jQuery.getJSON('/viztag/tags', function(data) {

  var viztagInput = $('input#viztag-tagging');
  var selects = $('select.tag');
  var ns=null, tn=null, bits=null;

  viztagInput.typeahead({
    source: data,
    matcher: function(item) {
      tquery = lastTag(this.query);
      return ~item.toLowerCase().indexOf(tquery.toLowerCase());
    },
    updater: function(item) {
      bits = item.split(':');
      ns = bits[0], tn = bits[1];
      selects.find('option').filter(function() {
        return tn === $(this).text();
      }).prop('selected', 'selected');
      return this.$element.val().replace(/[^,]*$/,item+',');
    }
  });

  viztagInput.focus();

});

