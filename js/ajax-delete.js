jQuery(document).ready(function($) {
    $('.delete-product').on('click', function() {
        var product_id = $(this).data('id');
        var row = $(this).closest('tr');

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'delete_product',
                product_id: product_id
            },
            success: function(response) {
                if (response.trim() === 'success') {
                    row.fadeOut(500, function() { $(this).remove(); });
                } else {
                    alert('Error deleting product.');
                }
            }
        });
    });
});
