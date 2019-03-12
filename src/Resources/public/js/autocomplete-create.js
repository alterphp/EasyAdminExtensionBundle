function createAutoCompleteCreateFields() {
  var autocompleteCreateFields = $('[data-easyadmin-autocomplete-create-action-url]');

  autocompleteCreateFields.each(function () {
    var $this = $(this),
      url = $this.data('easyadmin-autocomplete-url'),
      url_action = $this.data('easyadmin-autocomplete-create-action-url'),
      field_name = $this.data('easyadmin-autocomplete-create-field-name'),
      button_text = $this.data('easyadmin-autocomplete-create-button-text'),
      select_id = $this.attr('id');

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
          return '<a href="#" class="btn btn-info" onclick="switchToEntityCreation(\''+url_action+'\', \''+select_id+'\', \''+field_name+'\');return false;">'+button_text+' '+field_name+'</a>';
        }
      },
      escapeMarkup: function (markup) {
        return markup;
      }
    });
  });
}

function switchToEntityCreation(url_action, select_id, field_name) {
  $('#'+select_id).select2('close');
  $.ajax({
    url : url_action,
    type: 'GET',
    success: function(data) {
      openCreateEntityModal(data, url_action, field_name, select_id);
      $('#create-entity-modal').modal({ backdrop: true, keyboard: true });
    }
  });
}

function openCreateEntityModal(data, url_action, field_name, select_id) {
  var data_html = $(data.html);
  data_html.find('.form-actions > a[name=list]').remove();
  data_html.find('.form-actions > a[name=delete]').remove();
  $('#create-entity-modal .modal-body').html(data_html);
  $('form[name="'+field_name+'"]').attr('action', url_action);
  initCreateEntityAjaxForm(field_name, select_id);
}

function initCreateEntityAjaxForm(field_name, select_id) {
  $('form[name="'+field_name+'"]').submit(function( event ) {
    event.preventDefault();
    var url_action = $(this).attr('action');
    $.ajax({
      url: url_action,
      type: $(this).attr('method'),
      data: serializeForm($(this)),
      cache: false,
      contentType: false,
      processData: false,
      success: function(data) {
        if (data.hasOwnProperty('option')) {
          $('#create-entity-modal').modal('hide');
          var newOption = new Option(data.option.text, data.option.id, true, true);
          $('#'+select_id).append(newOption).trigger('change');
          // manually trigger the `select2:select` event
          $('#'+select_id).trigger({
            type: 'select2:select',
            params: { data: data.option }
          });
        }
        if (data.hasOwnProperty('html')) {
          openCreateEntityModal(data, url_action, field_name, select_id);
        }
      },
      error: function(error){
        console.log(error);
      }
    });
  });
}

window.addEventListener('load', function() {
  $(function () {
    createAutoCompleteCreateFields();
  });
});
