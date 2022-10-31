<?php
// Download Custom Post
function downloads_custom_post_type()
{

    $labels = array(
        'name'                => _x('Downloads', 'Post Type General Name', 'nextblocktheme'),
        'singular_name'       => _x('Downloads', 'Post Type Singular Name', 'nextblocktheme'),
        'menu_name'           => __('Downloads', 'nextblocktheme'),
        'parent_item_colon'   => __('Parent Download', 'nextblocktheme'),
        'all_items'           => __('All Downloads', 'nextblocktheme'),
        'view_item'           => __('View Download', 'nextblocktheme'),
        'add_new_item'        => __('Add New Download', 'nextblocktheme'),
        'add_new'             => __('Add New', 'nextblocktheme'),
        'edit_item'           => __('Edit Download', 'nextblocktheme'),
        'update_item'         => __('Update Download', 'nextblocktheme'),
        'search_items'        => __('Search Download', 'nextblocktheme'),
        'not_found'           => __('Not Found', 'nextblocktheme'),
        'not_found_in_trash'  => __('Not found in Trash', 'nextblocktheme'),
    );

    $args = array(
        'label'               => __('download', 'nextblocktheme'),
        'description'         => __('Download', 'nextblocktheme'),
        'labels'              => $labels,
        'supports'            => array('title'),
        'taxonomies'          => array('genres'),
        'hierarchical'        => false,
        'public'              => true,
        'menu_icon'           => 'dashicons-download',
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 9,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest'        => true,
        'rewrite'                => array(
            'slug' => 'downloads',
            'with_front' => false
        ),
    );
    register_post_type('downloads', $args);
}
add_action('init', 'downloads_custom_post_type', 0);

// Download Custom Taxonomy
add_action('init', 'downloads_taxonomy', 0);
function downloads_taxonomy()
{
    $labels = array(
        'name' => _x('Categories', 'nextblocktheme'),
        'singular_name' => _x('Category', 'nextblocktheme'),
        'search_items' =>  __('Category Search'),
        'all_items' => __('All Categories'),
        'parent_item' => __('Parent Category'),
        'parent_item_colon' => __('Parent Category:'),
        'edit_item' => __('Edit Category'),
        'update_item' => __('Update Category'),
        'add_new_item' => __('Add New Category'),
        'new_item_name' => __('New Category Name'),
        'menu_name' => __('Categories'),
    );

    register_taxonomy('download_category', array('downloads'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'download_category', 'with_front' => false),
    ));
}

// Download custom fields
function add_custom_meta_downloads()
{

    add_meta_box(
        'custom_download_meta',
        'Download Information',
        'custom_download_meta',
        'downloads',
    );
}
add_action('add_meta_boxes', 'add_custom_meta_downloads');

function custom_download_meta()
{

    wp_nonce_field(plugin_basename(__FILE__), 'wp_download_meta_nonce');
    $attachment = get_post(get_post_meta(get_the_ID(), 'download_file_id', true));

    $html = '<style>table{ width: 100%; }table tr th{width: 10%;text-align: left;}table tr td{ width: 90%; } table tr td input, table tr td textarea{ width: 100%; }table tr .download_post_button{width: auto;margin-right: 15px!important;}.download_post_remove{width: auto}</style>';
    $html .= '<table>';
    $html .= '<tbody>';
    $html .= '<tr>';
    $html .= '<th>Description</th>';
    $html .= '<td><textarea name="description" id="description" rows="5">' . get_post_meta(get_the_ID(), 'description', true) . '</textarea></td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<th>File select</th>';
    $html .= '<td><input type="hidden" id="download_file" name="download_file" value="' . get_post_meta(get_the_ID(), 'download_file_id', true) . '">';
    if (!empty($attachment) && ($attachment->post_mime_type == 'application/pdf' || $attachment->post_mime_type == 'application/doc' || $attachment->post_mime_type == 'application/docx')) {
        $html .= '<div id="file_wrapper"><img src="' . includes_url() . '/images/media/document.png" /><div class="filename">' . basename(get_attached_file(get_post_meta(get_the_ID(), 'download_file_id', true))) . '</div></div>';
    } else {
        $html .= '<div id="file_wrapper">' . wp_get_attachment_image(get_post_meta(get_the_ID(), 'download_file_id', true), 'full') . '</div>';
    }

    $html .= '<p>';
    $html .= '<input type="button" class="button button-secondary download_post_button" id="download_post_button" name="download_post_button" value="Add File">';
    $html .= '<input type="button" class="button button-secondary download_post_remove" id="download_post_remove" name="download_post_remove" value="Remove File">';
    $html .= '</p>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</tbody>';
    $html .= '<table>';


    echo $html;
}

add_action('admin_enqueue_scripts', 'load_media');
function load_media()
{
    wp_enqueue_media();
}

function save_download_custom_meta_data($id)
{

    /* --- security verification --- */
 /*   if (!empty($_POST) && !wp_verify_nonce($_POST['wp_download_meta_nonce'], plugin_basename(__FILE__))) {
        return $id;
    } // end if */

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $id;
    } // end if

    if (isset($_REQUEST) && isset($_REQUEST['post_type']) && 'page' == $_REQUEST['post_type']) {
        if (!current_user_can('edit_page', $id)) {
            return $id;
        } // end if
    } else {
        if (!current_user_can('edit_page', $id)) {
            return $id;
        } // end if
    }
    /* - end security verification - */

    if (isset($_POST['download_file']) && '' !== $_POST['download_file']) {
        update_post_meta($id, 'download_file_id', $_POST['download_file']);
    } else {
        update_post_meta($id, 'download_file_id', '');
    }

    if (!empty($_POST['description'])) {
        update_post_meta($id, 'description', $_POST['description']);
    }
} // end save_download_custom_meta_data
add_action('save_post', 'save_download_custom_meta_data');

add_action('admin_footer', 'add_custom_script');
function add_custom_script()
{ ?>
    <script>
        jQuery(document).ready(function($) {
            function taxonomy_media_upload(button_class) {
                var custom_media = true,
                    original_attachment = wp.media.editor.send.attachment;
                $('body').on('click', button_class, function(e) {
                    var button_id = '#' + $(this).attr('id');
                    var send_attachment = wp.media.editor.send.attachment;
                    var button = $(button_id);
                    custom_media = true;
                    wp.media.editor.send.attachment = function(props, attachment) {

                        if (custom_media) {
                            $('#download_file').val(attachment.id);
                            console.log(attachment);
                            if (attachment.mime == "application/pdf" || attachment.mime == "application/doc" || attachment.mime == "application/docx") {
                                $('#file_wrapper').html('<img class="custom_media_file" src="" style="margin:0;padding:0;max-height:100px;float:none;" /><div class="filename"><div></div></div>');
                                $('#file_wrapper .custom_media_file').attr('src', attachment.icon).css('display', 'block');
                                $('#file_wrapper .filename').html('<div>' + attachment.filename + '</div>').css('display', 'block');
                            } else {
                                $('#file_wrapper').html('<img class="custom_media_file" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                                $('#file_wrapper .custom_media_file').attr('src', attachment.url).css('display', 'block');
                            }

                        } else {
                            return original_attachment.apply(button_id, [props, attachment]);
                        }
                    }
                    wp.media.editor.open(button);
                    return false;
                });
            }
            taxonomy_media_upload('.download_post_button.button');
            $('body').on('click', '.download_post_remove', function() {
                $('#download_file').val('');
                $('#file_wrapper').html('<img class="custom_media_file" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                if ($('#file_wrapper').find('.filename').length > 0) {
                    $('#file_wrapper').find('.filename').html('');
                }
            });

            $(document).ajaxComplete(function(event, xhr, settings) {
                var queryStringArr = settings.data.split('&');
                if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                    var xml = xhr.responseXML;
                    $response = $(xml).find('post_id').text();
                    if ($response != "") {
                        $('#file_wrapper').html('');
                    }
                }
            });
        });
    </script>
<?php
}

// Customer Custom Post
function customer_custom_post_type()
{

    $labels = array(
        'name'                => _x('Customers', 'Post Type General Name', 'nextblocktheme'),
        'singular_name'       => _x('Customers', 'Post Type Singular Name', 'nextblocktheme'),
        'menu_name'           => __('Customers', 'nextblocktheme'),
        'parent_item_colon'   => __('Parent Customer', 'nextblocktheme'),
        'all_items'           => __('All Customers', 'nextblocktheme'),
        'view_item'           => __('View Customer', 'nextblocktheme'),
        'add_new_item'        => __('Add New Customer', 'nextblocktheme'),
        'add_new'             => __('Add New', 'nextblocktheme'),
        'edit_item'           => __('Edit Customer', 'nextblocktheme'),
        'update_item'         => __('Update Customer', 'nextblocktheme'),
        'search_items'        => __('Search Customer', 'nextblocktheme'),
        'not_found'           => __('Not Found', 'nextblocktheme'),
        'not_found_in_trash'  => __('Not found in Trash', 'nextblocktheme'),
    );

    $args = array(
        'label'               => __('Customer', 'nextblocktheme'),
        'description'         => __('Customer', 'nextblocktheme'),
        'labels'              => $labels,
        'supports'            => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'custom-fields',),
        'taxonomies'          => array('genres'),
        'hierarchical'        => false,
        'public'              => true,
        'menu_icon'           => 'dashicons-admin-users',
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 8,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest'        => true,
        'rewrite'                => array(
            'slug' => 'customers',
            'with_front' => false
        ),
    );
    register_post_type('customers', $args);
}
add_action('init', 'customer_custom_post_type', 0);

// Download Custom Taxonomy
add_action('init', 'customers_taxonomy', 0);
function customers_taxonomy()
{
    $labels = array(
        'name' => _x('Categories', 'nextblocktheme'),
        'singular_name' => _x('Category', 'nextblocktheme'),
        'search_items' =>  __('Category Search'),
        'all_items' => __('All Categories'),
        'parent_item' => __('Parent Category'),
        'parent_item_colon' => __('Parent Category:'),
        'edit_item' => __('Edit Category'),
        'update_item' => __('Update Category'),
        'add_new_item' => __('Add New Category'),
        'new_item_name' => __('New Category Name'),
        'menu_name' => __('Categories'),
    );

    register_taxonomy('category', array('customers'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'category', 'with_front' => false),
    ));
}

// News Custom Post
function news_custom_post_type()
{

    $labels = array(
        'name'                => _x('News', 'Post Type General Name', 'nextblocktheme'),
        'singular_name'       => _x('News', 'Post Type Singular Name', 'nextblocktheme'),
        'menu_name'           => __('News', 'nextblocktheme'),
        'parent_item_colon'   => __('Parent News', 'nextblocktheme'),
        'all_items'           => __('All News', 'nextblocktheme'),
        'view_item'           => __('View News', 'nextblocktheme'),
        'add_new_item'        => __('Add New News', 'nextblocktheme'),
        'add_new'             => __('Add New', 'nextblocktheme'),
        'edit_item'           => __('Edit News', 'nextblocktheme'),
        'update_item'         => __('Update News', 'nextblocktheme'),
        'search_items'        => __('Search News', 'nextblocktheme'),
        'not_found'           => __('Not Found', 'nextblocktheme'),
        'not_found_in_trash'  => __('Not found in Trash', 'nextblocktheme'),
    );

    $args = array(
        'label'               => __('News', 'nextblocktheme'),
        'description'         => __('News', 'nextblocktheme'),
        'labels'              => $labels,
        'supports'            => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
        'taxonomies'          => array('genres'),
        'hierarchical'        => false,
        'public'              => true,
        'menu_icon'           => 'dashicons-buddicons-topics',
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 7,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest'        => true,
        'rewrite'                => array(
            'slug' => 'news',
            'with_front' => false
        ),
    );
    register_post_type('news', $args);
}
add_action('init', 'news_custom_post_type', 0);

// News Custom Taxonomy
add_action('init', 'news_taxonomy', 0);
function news_taxonomy()
{
    $labels = array(
        'name' => _x('Topics', 'nextblocktheme'),
        'singular_name' => _x('Topic', 'nextblocktheme'),
        'search_items' =>  __('Topic Search'),
        'all_items' => __('All Topics'),
        'parent_item' => __('Parent Topic'),
        'parent_item_colon' => __('Parent Topic:'),
        'edit_item' => __('Edit Topic'),
        'update_item' => __('Update Topic'),
        'add_new_item' => __('Add New Topic'),
        'new_item_name' => __('New Topic Name'),
        'menu_name' => __('Topics'),
    );

    register_taxonomy('topics', array('news'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'topics', 'with_front' => false),
    ));
}
