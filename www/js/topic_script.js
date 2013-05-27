var storageLocal = window.localStorage;

function initCommentators() {
    if (storageLocal) {
        if (storageLocal.nickname) {
            $('#comment_form #name').val(storageLocal.nickname);
        }
        if (storageLocal.email) {
            $('#comment_form #mail').val(storageLocal.email);
        }
        if (storageLocal.website) {
            $('#comment_form #website').val(storageLocal.website);
        }
    }
}

$(document).ready(function() {

    $('#comments #noscript').hide();

    if (!auth_comment) {
        initCommentators();
    }

    $('#comments').on('click', '.comment-reply span', function() {
        var parent_id = $(this).attr('data-comment_id');

        $('#form_bottom_'+parent_id).append($('#comment_add'));
        $('#parentId').val(parent_id);
    });

    $('#comments').on('click', '#topic-reply span', function() {
        $('#comment-form-wrapper').append($('#comment_add'));
        $('#parentId').val(0);
    });

    $('#comments').on('submit', '#comment_form', function() {
        if (storageLocal && !auth_comment) {
            storageLocal.setItem('nickname', $('#comment_form #name').val());
            storageLocal.setItem('email', $('#comment_form #mail').val());
            storageLocal.setItem('website', $('#comment_form #website').val());

            $('#comment_form #cookie').val(0);
        }

        $('.ajax-loader').show();

        var formData = $('#comment_form').serialize();

        $.ajax({
            url: addCommentLink,
            data: formData,
            dataType: 'json',
            type: 'POST',
            success: function(data) {
                if (data.valid) {
                    $('#comments').append($('#comment_add'));
                    $('ul.errors', $('#comment_add')).remove();
                    $('#comment_text').val('');
                    $('#parentId').val(0);
                    $('#all-comments').load(window.location.pathname + ' #all-comments > *');
                } else {
                    $('#comment_add').html(data.form_html);
                }
                $('.ajax-loader').hide();
            }
        });

        return false;
    })
});