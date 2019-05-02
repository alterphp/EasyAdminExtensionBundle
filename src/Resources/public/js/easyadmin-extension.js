function reloadEmbeddedList(identifier, toggleBaseUrl) {
  var containerPrefix = '.embedded-list[for="'+identifier+'"]';

  // Reload sorted/paginated list in the embedded-list container
  $(containerPrefix)
    .on('click', 'th a', function (e) {
      e.preventDefault();

      // Prevent from "disbaled" links
      if ($(e.currentTarget).hasClass('disabled')) {
        return false;
      }

      $.ajax({
        url: e.currentTarget.href,
        dataType: 'html',
        success: function (data, textStatus, jqXHR) {
          $(containerPrefix).replaceWith(data);
        }
      });
    })
    .on('click', '.list-pagination a', function (e) {
      e.preventDefault();

      // Prevent from out-of-bounds pagination
      if ($(e.currentTarget).hasClass('disabled')) {
        return false;
      }

      $.ajax({
        url: e.currentTarget.href,
        dataType: 'html',
        success: function (data, textStatus, jqXHR) {
          $(containerPrefix).replaceWith(data);
        }
      });
    })
  ;

  const toggles = $(containerPrefix).find('table .checkbox-switch input[type="checkbox"]');
  for (i = 0; i < toggles.length; i++) {
      toggles[i].addEventListener('change', function () {
          const toggle = this;
          const newValue = this.checked;
          const oldValue = !newValue;
          const propertyName = this.closest('.checkbox-switch').dataset.propertyname;

          const toggleUrl = toggleBaseUrl
              + "&id=" + this.closest('tr').dataset.id
              + "&property=" + propertyName
              + "&newValue=" + newValue.toString();

          let toggleRequest = $.ajax({ type: "GET", url: toggleUrl, data: {} });

          toggleRequest.done(function(result) {});

          toggleRequest.fail(function() {
              // in case of error, restore the original value and disable the toggle
              toggle.checked = oldValue;
              toggle.disabled = true;
              toggle.closest('.checkbox-switch').classList.add('disabled');
          });
      });
  }
}

window.addEventListener('load', function() {
  $(function() {
    $(document).on('click', '[data-confirm]', function(e) {
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
});

function serializeForm(form) {
  var formData = new FormData();
  var params = form.serializeArray();
  $.each(params, function (i, val) {
    formData.append(val.name, val.value);
  });
  return formData;
};
