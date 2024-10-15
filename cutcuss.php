<?php
/*
Plugin Name: Cut Cuss
Description: A plugin that censors cuss words.
Version: 1.0
Author: Ey Rhen
License: GPL2
*/
function cutcuss_activation()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "cutcuss_words";
    $charset_collate = $wpdb->get_charset_collate();


    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    word tinytext NOT NULL,
    PRIMARY KEY  (id)
) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'cutcuss_activation');
function cutcuss_deactivation() {}
register_deactivation_hook(__FILE__, 'cutcuss_deactivation');

function cutcuss_menu()
{
    add_menu_page(
        'CutCuss Page',
        'Cutcuss',
        'manage_options',
        'cutcuss',
        'cutcuss_page_html',
        'dashicons-admin-generic',
        20
    );
}
add_action('admin_menu', 'cutcuss_menu');




function cutcuss_page_html()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['status']) && $_GET['status'] == 'success') {
        echo '<div class="updated notice"><p>Subission Successdul</p></div>';
    } elseif (isset($_GET['status']) && $_GET['status'] == 'error') {
        echo '<div class="error notice"><p>Submission Failed</p></div>';
    }

    $words = get_words();

?>
    <div class="wrap">
        <h1>Cutcuss Plugin</h1>
        <p>Censor the unwanted words in your posts.</p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

            <input type="hidden" name="action" value="cutcuss_handle_form">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Enter a word</th>
                    <td><input type="text" name="word" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <ul class="word-list">
            <?php foreach ($words as $word): ?>
                <li id="<?php echo $word['id']; ?>">
                    <span class="word"><?php echo $word['word']; ?></span>
                    <a href="#" class="delete">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <style>
        .word-list {
            list-style: none;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .word-list li {
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }

        .word-list li:last-child {
            border-bottom: none;
        }

        .word {
            flex: 1;
        }

        .delete {
            color: red;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.2s ease-in-out;
        }

        .delete:hover {
            background-color: #f1f1f1;
        }
    </style>

<?php
}

function cutcuss_handle_form()
{
    if (isset($_POST['word']) && !empty($_POST['word'])) {

        $word = sanitize_text_field($_POST['word']);
        insert_word($word);
        wp_redirect(admin_url('admin.php?page=cutcuss&status=success'));
        exit;
    } else {
        wp_redirect(admin_url('admin.php?page=cutcuss&status=error'));
        exit;
    }
}
add_action('admin_post_cutcuss_handle_form', 'cutcuss_handle_form');

function insert_word($word)
{
    global $wpdb;
    $table_name = $wpdb->prefix . "cutcuss_words";

    $wpdb->insert(
        $table_name,
        array(
            'word' => $word
        ),
        array('%s')
    );
}
function get_words()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'cutcuss_words';

    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    if (!empty($results)) {
        return $results;
    } else {
        return [];
    }
}
