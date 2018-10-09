function createAutoCompleteCreateFields() {
    var autocompleteCreateFields = $('[data-easyadmin-autocomplete-add-url]');

    autocompleteCreateFields.each(function () {
        var url = $(this).data('easyadmin-autocomplete-add-url'),
            url_action = $(this).data('easyadmin-autocomplete-action-url'),
            field_name = $(this).data('easyadmin-autocomplete-field-name'),
            button_text = $(this).data('easyadmin-autocomplete-button-text'),
            select_id = $(this).attr('id');

        $(this).select2({
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
                    return '<a href="#" class="btn btn-info" onclick="ajaxModalEntityAction(\''+url_action+'\', \''+select_id+'\', \''+field_name+'\');return false;">'+button_text+' '+field_name+'</a>';
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
    $('#modal-entity-form .modal-body').html(data.html);
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
                        params: { data: data.option }
                    });
                }
                if (data.hasOwnProperty('html')) {
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
$(function () {
    createAutoCompleteCreateFields();
});
