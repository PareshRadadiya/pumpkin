jQuery(document).ready(function ($) {
  $(document).on('change', '.pumkin-get-post-types', function (event) {

    event.preventDefault();

    var post_types_name = $(this).data('id');
    var $select =  $('.multiselect-posts-' + post_types_name + ' select');

    if (this.checked) {
      $select.removeClass('pumpkin-multiselect-posts');
      $select.addClass('pumpkin-multiselect-posts-selected');
    } else {
      $select.addClass('pumpkin-multiselect-posts');
      $select.removeClass('pumpkin-multiselect-posts-selected');
    }
    
    if (this.checked && $select.has('option').length === 0) {
      $.ajax({
        type: 'GET',
        url: ajaxurl,
        data: {
          action:'pumkin_get_all_posts',
          post_types_name: post_types_name,
          _ajax_nonce: ajaxVars.get_all_posts_nonce,
        },
        success: function (response) {
          var $select = $('.multiselect-posts-' + post_types_name + ' select');
          $.each(response, function (key, data) {
            $select.append(
              "<option value=" +
                key +
                " name='multiselected_post' >" +
                data +
                "</option>"
            );
          });
        },
      });
     }
  });
});
