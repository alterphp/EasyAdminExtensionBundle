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
  $('.action-confirm').on('click', function(e) {
    e.preventDefault();

    var message = $(this).data('confirm');
    if (typeof message === "string") {
      $('#modal-confirm .modal-body p#modal-body-content').html(message);
    }

    var icon = $(this).find('i').attr('class');
    var confirmButton = $('#modal-confirm #modal-confirm-button');
    confirmButton.find('i').remove();
    if (icon) {
      confirmButton.prepend('<i class=\"' + icon + '\"></i>');
    }

    var href = $(this).data('href');
    $('#modal-confirm #confirm-form').attr('action', href);

    $('#modal-confirm').modal({ backdrop: true, keyboard: true });
  });
});
