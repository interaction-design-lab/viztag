var lastTag = function(query) {
    var ret = /([^,]+)$/.exec(query);
    if(ret && ret[1])
        return ret[1].trim();
    return '';
}

jQuery.getJSON('./tags', function(data) {

  var viztagInput = $('input#viztag-tagging');

  viztagInput.typeahead({
    source: data,
    matcher: function(item) {
      tquery = lastTag(this.query);
      return ~item.toLowerCase().indexOf(tquery.toLowerCase());
    },
    updater: function(item) {
      return this.$element.val().replace(/[^,]*$/,item+',');
    }
  });

  viztagInput.focus();

});

