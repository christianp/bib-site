function add_field() {
    var n = document.querySelectorAll('#extra-fields .field').length;
    var field = document.createElement('div');
    field.classList.add('field');
    field.innerHTML = '<input type="text" name="extra_fields-'+n+'-name" class="label-input form-control"> \
                      <input type="text" name="extra_fields-'+n+'-value" class="form-control" >\
                      <button type="button" class="btn delete-field">x</button>';
    document.querySelector('#extra-fields').appendChild(field);
}
document.getElementById('add-field').addEventListener('click',add_field);

function delete_field(e) {
    if(!e.target.classList.contains('delete-field')) {
        return;
    }
    var field = e.target.parentElement;
    field.parentElement.removeChild(field);
}
document.getElementById('extra-fields').addEventListener('click',delete_field);


