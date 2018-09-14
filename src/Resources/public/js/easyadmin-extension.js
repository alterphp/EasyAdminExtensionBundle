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
    var autocompleteCreateFields = $('[data-easyadmin-autocomplete-add-url]');

    autocompleteCreateFields.each(function () {
        var $this = $(this),
            url = $this.data('easyadmin-autocomplete-add-url'),
            url_action = $this.data('easyadmin-autocomplete-action-url')
            field_name = $this.data('easyadmin-autocomplete-field-name');
            
        var select_id = $this.attr('id');

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
            language: {
                noResults: function () {
                    return noResultsCreate(url_action, select_id, field_name);
                }
            },
            escapeMarkup: function (markup) {
                return markup;
            }
        });
    });
}

function ajaxModalEntityAction(url_action, select_id, field_name) {
    $('#'+select_id).select2('close');
    $.ajax({
        url : url_action,
        type: 'GET',
        success: function(data) {
            showModalEntityForm(data, url_action, field_name, select_id);
            $('#modal-entity-form').modal({ backdrop: true, keyboard: true });
        }
    });
}
function showModalEntityForm(data, url_action, field_name, select_id) {
    var content = $('#modal-entity-form p.modal-body-content');
    content.html(data.template);
    $('form[name="'+field_name+'"]').attr('action', url_action);
    initAjaxForm(field_name, select_id);
}
function initAjaxForm(field_name, select_id) {
    $('form[name="'+field_name+'"]').submit(function( event ) {
        event.preventDefault();
        var url_action = $(this).attr('action');
        $.ajax({
            url: url_action,
            type: $(this).attr('method'),
            data: $(this).serializefiles(),
            cache: false,
            contentType: false,
            processData: false,
            success: function(data) {
                if (data.hasOwnProperty('option')) {
                    $('#modal-entity-form').modal('hide');
                    var newOption = new Option(data.option.text, data.option.id, true, true);
                    $('#'+select_id).append(newOption).trigger('change');
                    // manually trigger the `select2:select` event
                    $('#'+select_id).trigger({
                        type: 'select2:select',
                        params: {
                            data: data.option
                        }
                    });
                }
                if (data.hasOwnProperty('template')) {
                    showModalEntityForm(data, url_action, field_name, select_id);
                }
            },
            error: function(error){
                console.log(error);
            }
        });
    });
}
//USAGE: $("#form").serializefiles();
(function($) {
$.fn.serializefiles = function() {
    CKupdate();
    var obj = $(this);
    /* ADD FILE TO PARAM AJAX */
    var formData = new FormData();
    $.each($(obj).find("input[type='file']"), function(i, tag) {
        $.each($(tag)[0].files, function(i, file) {
            formData.append(tag.name, file);
        });
    });
    var params = $(obj).serializeArray();
    $.each(params, function (i, val) {
        formData.append(val.name, val.value);
    });

    return formData;
};
})(jQuery);
function CKupdate() {
    try {
        for ( instance in CKEDITOR.instances )
            CKEDITOR.instances[instance].updateElement();
    }
    catch(err) { }
}
$(function () {
    createAutoCompleteCreateFields();
});