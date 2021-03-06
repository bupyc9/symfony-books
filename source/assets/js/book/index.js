$(function () {
    $('.book_delete-js').on('click', function (e) {
        e.preventDefault();

        const result = confirm('Вы уверены что хотите удалить элемент?');

        if (!result) {
            return;
        }

        const $this = $(this);

        if ($this.data('ajaxSending')) {
            return;
        }

        $this.data('ajaxSending', true);
        $.ajax({
            url: $this.attr('href'),
            type: 'DELETE',
            success: function () {
                $this.data('ajaxSending', false);
                location.reload();
            },
            error: function () {
                $this.data('ajaxSending', false);
                location.reload();
            }
        });
    })
});