<?php
add_action('wp_footer', 'add_this_script_footer_2');
function add_this_script_footer_2() { 
?>
<script>
//declare form fields in variables
const form = document.querySelector('#unsub-form')
const unsubEmail = document.querySelector('#email-unsub-input')
const unsubToken = document.querySelector('#unsub-token')
const unsubMessage = document.querySelector('#unsub-message')

if(window.location.href.includes('unsub')) {
    form.addEventListener('submit', e => {
    e.preventDefault()
    let data = new FormData()
    data.append('action', 'unsub_email_function')
    data.append('unsub_email', unsubEmail.value)
    data.append('unsub_token', unsubToken.value)
    let url = window.location.href.split('/unsub/')[0]; //remove everything after the page url (/unsub/) where is located the unsubscription form
    if (unsubEmail.value != '' && unsubToken.value != '') {
        let ajaxScript = { ajaxUrl : url + '/wp-admin/admin-ajax.php' } // !!!the url must be changed
        fetch( ajaxScript.ajaxUrl, { method: 'POST', body: data } )
        .then( response => response.text())
        .then( data => unsubMessage.innerHTML = data) // console.log(data)
        .catch( err => console.log( err ) )
    }
  })
}
</script>
<?php
}
// if ( is_admin() ) {
// add_action( 'wp_ajax_unsub_email_function', 'unsub_email_function' );
// }
add_action( 'wp_ajax_nopriv_unsub_email_function', 'unsub_email_function' );
function unsub_email_function() {
    $post_to_delete = '';
    $unsub_email = $_POST['unsub_email'];
    $unsub_token = $_POST['unsub_token'];
    //get the existing records from the cf7-flamingo database (they are stored as wp custom posts)
    $args = array(
        'post_type' => 'flamingo_inbound',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key'   => '_field_dgemail',
                'value' => $unsub_email
            ),
            array(
                'key'   => '_field_token',
                'value' => $unsub_token
            )
        )
    );
    //   'meta_key' => "_field_dgemail",
    //   'meta_value' => $unsub_email,
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ( $query->have_posts() ) {
            $query->the_post();
            foreach ($query->posts as $post) {
                $post_to_delete = $post->ID;
            }
            echo 'Successfully unsubscribed';
        }
    } else {
        echo 'Error. Record does not exists';
    }
    wp_trash_post( $post_to_delete );
    wp_die(); // this is required to terminate immediately and return a proper response
    // }
}
add_shortcode('show_unsub_form', 'unsub_form');
function unsub_form() {
    $html = 
    '<div>
        <form id="unsub-form" action="">
            <div>
                <input type="email" id="email-unsub-input" name="email-unsub-input" value="'.$_GET['email'].'" disabled>
            </div>
            <div>
            <input type="hidden" id="unsub-token" name="unsub-token" value="'.$_GET['token'].'" disabled>
            </div>
            <div>
                <input type="submit" id="email-unsub-submit" name="email-unsub-submit" value="Потвърждавам" />
            </div>
        </form>
        <div id="unsub-message"></div>
    </div>';
    return $html;
}
