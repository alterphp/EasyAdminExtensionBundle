function noResultsCreate(url_action, select_id, field_name) {
  return '<a href="#" class="btn btn-info" onclick="ajaxModalEntityAction(\''+url_action+'\', \''+select_id+'\', \''+field_name+'\');return false;">Agregar '+field_name+'</a>';
}