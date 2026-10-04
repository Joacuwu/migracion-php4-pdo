$(function () {
    // Carga nombre e id en el modal de baja.
    var modal = document.getElementById('modalBorrar');
    $('.js-borrar').on('click', function () {
        $('#borrarId').val($(this).data('id'));
        $('#borrarNombre').text($(this).data('nombre'));
        bootstrap.Modal.getOrCreateInstance(modal).show();
    });

    // Legajo siempre en mayúsculas y con el guion en su lugar (S0012 -> S-0012).
    $('#legajo').on('input', function () {
        var v = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (v.length > 1) {
            v = v.charAt(0) + '-' + v.substring(1, 5);
        }
        this.value = v;
    });
});
