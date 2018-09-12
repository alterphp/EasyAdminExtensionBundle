function reloadEmbeddedList(identifier, toggleBaseUrl)
{
  var containerPrefix = '.embedded-list[for="'+identifier+'"]';

  $(containerPrefix).find('table .toggle input[type="checkbox"]').each(function (idx, el) {
    $(this).bootstrapToggle();
  })

  // Reload sorted/paginated list in the embedded-list container
  $(containerPrefix)
      .on('click', 'th[data-property-name] a', function (e) {
          e.preventDefault();
          $.ajax({
              url: e.target.href,
              dataType: 'html',
              success: function (data, textStatus, jqXHR) {
                  $(containerPrefix).replaceWith(data);
              }
          });
      })
      .on('click', '.list-pagination a', function (e) {
          e.preventDefault();
          $.ajax({
              url: e.target.href,
              dataType: 'html',
              success: function (data, textStatus, jqXHR) {
                  $(containerPrefix).replaceWith(data);
              }
          });
      })
  ;

  $(containerPrefix).find('table .toggle input[type="checkbox"]').change(function() {
      var toggle = $(this);
      var newValue = toggle.prop('checked');
      var oldValue = !newValue;

      var columnIndex = $(this).closest('td').index() + 1;
      var propertyName = $(containerPrefix + ' table th.toggle:nth-child(' + columnIndex + ')').data('property-name');

      var toggleUrl = toggleBaseUrl
                    + "&id=" + $(this).closest('tr').data('id')
                    + "&property=" + propertyName
                    + "&newValue=" + newValue.toString();

      var toggleRequest = $.ajax({ type: "GET", url: toggleUrl, data: {} });

      toggleRequest.done(function(result) {});

      toggleRequest.fail(function() {
          // in case of error, restore the original value and disable the toggle
          toggle.bootstrapToggle(oldValue == true ? 'on' : 'off');
          toggle.bootstrapToggle('disable');
      });
  });
}

$(function() {
  $('[data-confirm]').on('click', function(e) {
    e.preventDefault();

    var message = $(this).data('confirm');
    var content = $('#modal-confirm p.modal-body-content');
    content.html(message);

    var confirmButton = $('#modal-confirm #modal-confirm-button');
    if (!confirmButton.find('i').length) { confirmButton.prepend('<i></i>'); }
    confirmButton.find('i')
      .removeClass()
      .addClass($(this).find('i').attr('class'))
    ;

    var href = $(this).data('href');
    $('#modal-confirm #confirm-form').attr('action', href);

    $('#modal-confirm').modal({ backdrop: true, keyboard: true });
  });
});

function createAutoCompleteCreateFields() {
    var autocompleteCreateFields = $('[data-easyadmin-autocomplete-create-url]');

    autocompleteCreateFields.each(function () {
        var $this = $(this),
            url = $this.data('easyadmin-autocomplete-create-url');

        $this.select2({
            theme: 'bootstrap',
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { 'query': params.term, 'page': params.page };
                },
                // to indicate that infinite scrolling can be used
                processResults: function (data, params) {
                    console.log(data.results.length);
                    if (data.results.length <= 0) {
                        console.log(params);
                    }
                    return {
                        results: data.results,
                        pagination: {
                            more: data.has_next_page
                        }
                    };
                },
                cache: true
            },
            placeholder: '',
            allowClear: true,
            minimumInputLength: 1,
            language : {
                noResults : function(params) {
                    return '<a href="#">Agregar</a>';
                }
            },
            escapeMarkup: function (markup) {
                return markup;
            }
        });
    });
}

$(function () {
    createAutoCompleteCreateFields();
});