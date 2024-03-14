<?php
/*
Plugin Name:  Kinder garden swap for Contact Form 7
Description:  Swap kinder garden places
Version:      1.0
Author:       Ivan Dimitrov
*/
add_action( 'wp_enqueue_scripts', 'enqueue_scripts_styles' );
function enqueue_scripts_styles() {
  wp_enqueue_style( 'style', plugins_url('css/style.css', __FILE__), array(), '1.1', 'all' );
	wp_enqueue_script( 'form', plugins_url('js/form.js', __FILE__), array( 'jquery' ), 1.1, true );
}
//include file
require_once( __DIR__ . '/unsub.php');
add_action( 'wp_head', 'add_to_header' );
function add_to_header() { ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<?php 
}
add_action('wp_footer', 'add_this_script_footer');
function add_this_script_footer(){ 
?>
<script>
//declare all form fields in variables
const submissionName = document.querySelector('[name="dgname"]');
const submissionEmail = document.querySelector('[name="dgemail"]');
const submissionPhone = document.querySelector('[name="dgphone"]');
const submissionSocial = document.querySelector('[name="dgsocial"]');
const submissionGroup = document.querySelector('[name="group"]');
const submissionDgAccepted = document.querySelector('[name="accepted"]');
const tokenFlamingo = document.querySelector('input[name="token"]')
const submissionDgWishes = document.querySelectorAll('input[name="wishes[]"]');
let submissionDgWishesArray = []; // we need to declare this to store items later
const matchesContainer = document.querySelector("#matches-container"); // we will store the generated html here
const closeButton = document.createElement('span')
closeButton.textContent = "Close"
closeButton.id = "close-window"
// matchesContainer.append(closeButton)
matchesContainer.addEventListener( 'click', e => {
    if (e.target.id.includes("close-window")) {
      console.log(e.target.parentElement)
      e.target.parentElement.style.display = 'none';
    }
})
//set unique token
tokenFlamingo.value = Math.random().toString(36).substr(2) + Math.random().toString(36).substr(2);
document.addEventListener('wpcf7mailsent', function (event) {
    matchesContainer.style.display = 'flex'
    //get the values from the multiple checkboxes and store them in an array submissionDgWishesArray
    submissionDgWishes.forEach(dgWish => {
    if (dgWish.checked) {
      submissionDgWishesArray.push(dgWish.value);
    }
    })
    let data = new FormData()
    data.append('action', 'example_ajax_request')
    data.append('submission_name', submissionName.value)
    data.append('submission_email', submissionEmail.value)
    data.append('submission_phone', submissionPhone.value)
    data.append('submission_social', submissionSocial.value)
    data.append('submission_group', submissionGroup.value)
    data.append('submission_dg_acc', submissionDgAccepted.value)
    data.append('submission_dg_wish', submissionDgWishesArray.join())
    // matchesContainer.style.display = 'flex';
    let ajaxScript = { ajaxUrl : window.location.origin + '/wp-admin/admin-ajax.php' } // !!!the url must be changed
      fetch( ajaxScript.ajaxUrl, { method: 'POST', body: data } )
      .then( response => response.text())
      .then( (data) => {
        matchesContainer.innerHTML = data
        matchesContainer.prepend(closeButton)
        // matchesContainer.style.display = 'flex'
      }) // console.log(data)
      .catch( err => console.log( err ) )
  })
</script>
<?php
}
add_action( 'wp_ajax_nopriv_example_ajax_request', 'example_ajax_request' );
function example_ajax_request() {
    //get the existing records from the cf7-flamingo database (they are stored as wp custom posts)
    $contact_info = '';
    $args = array(
      'post_type' => 'flamingo_inbound	',
      'posts_per_page' => -1,
      //'meta_key' => '_field_dgname'
    );
    $loop = new WP_Query($args);
    while ( $loop->have_posts() ) {
      $loop->the_post();
      $post_id = get_the_ID();
      $dg_name = get_post_meta($post_id, "_field_dgname", true);
      $dg_email = get_post_meta($post_id, "_field_dgemail", true);
      $dg_phone = get_post_meta($post_id, "_field_dgphone", true);
      $dg_social = get_post_meta($post_id, "_field_dgsocial", true);
      $dg_group = get_post_meta($post_id, "_field_group", true);
      $dg_accepted = get_post_meta($post_id, "_field_accepted", true);
      $dg_wishes = get_post_meta($post_id, "_field_wishes", true);
      // user inputs from the form
      $submission_name = $_POST['submission_name'];
      $submission_email = $_POST['submission_email'];
      $submission_phone = $_POST['submission_phone'];
      $submission_social = $_POST['submission_social'];
      $submission_group = $_POST['submission_group'];
      $submission_dg_acc = $_POST['submission_dg_acc'];
      $submission_dg_wish = $_POST['submission_dg_wish'];
      //$contact_info = $_POST['$contact_info'];
      // search for matches between the user inputs and the existing records
      $submission_dg_wish_arr = explode(",", $submission_dg_wish);
      if(array_intersect($submission_dg_wish_arr, $dg_accepted) && in_array($submission_dg_acc, $dg_wishes) && in_array($submission_group, $dg_group)) { // check for the groups too
        $match_emails[] = $dg_email;
        // implode(", ", $match_emails); // if the wp_mail function do not accept array, try to use this string
        $contact_info .=
        '<div class="result-match">
        <h2>Match:</h2>
        <div><span>Name: </span><span>' . $dg_name . '</span></div>
        <div><span>Phone: </span><span>' . $dg_phone . '</span></div>
        <div><span>Social profile: </span><span>' . $dg_social . '</span></div>
        <div><span>Accepted: </span><span>' . implode(", ", $dg_accepted) . '</span></div>
        <div><span>Group: </span><span>' . implode(", ", $dg_group) . '</span></div>
        <div><span>Wishes: </span><span>' . implode("<br>", $dg_wishes) . '</span></div>
        </div>';
      }
    }
    $user_info =
    '<div class="result-user">
    <h2>Данни:</h2>
    <div><span>Name: </span><span>' . $submission_name . '</span></div>
    <div><span>Email: </span><span>' . $submission_email . '</span></div>
    <div><span>Phone: </span><span>' . $submission_phone . '</span></div>
    <div><span>Social profile (социална мрежа): </span><span>' . $submission_social . '</span></div> 
    <div><span>Accepted: </span><span>' . $submission_dg_acc . '</span></div>
    <div><span>Group: </span><span>' . $submission_group . '</span></div>
    <div><span>Wishes: </span><span>' . str_replace(",", "<br>", $submission_dg_wish) . '</span></div>        
    </div>';
    if ($contact_info != '') {
    echo $user_info.''.$contact_info;
    foreach ($match_emails as $match_email) {
      $match_token = '';
      $token_query = new WP_Query(array('post_type' => 'flamingo_inbound	','posts_per_page' => 1, 'meta_key' => '_field_dgemail', 'meta_value' => $match_email));
      while ( $token_query->have_posts() ) { 
        $token_query->the_post();
        $token_query_post_id = get_the_ID();
        $match_token = get_post_meta($token_query_post_id, "_field_token", true);  
      }
      $to = $match_email; // this should be array - see above
      $subject = 'New match';
      $body = $user_info;
      $body .= '<div>Unsubscribe: <a target="_blank" href="' . get_home_url() . '/unsub?email=' . $match_email . '&token=' . $match_token . '">Click</a></div>';
      $headers = array('Content-Type: text/html; charset=UTF-8');
      wp_mail( $to, $subject, $body, $headers );
    }
    $to_match = $submission_email;
    $subject_match = 'Succesfully Registered. Your matches';
    $body_match = $contact_info;
    $headers_match = array('Content-Type: text/html; charset=UTF-8');
    wp_mail( $to_match, $subject_match, $body_match, $headers_match );
    }
    if ($contact_info == '') {
      echo $user_info . '<div class="result-match">No matches. If there are matches in the future you will be informed by Email</div>';
      $to_match = $submission_email;
      $subject_match = 'Succesfully Registered.';
      $body_match = $contact_info;
      $headers_match = array('Content-Type: text/html; charset=UTF-8');
      wp_mail( $to_match, $subject_match, $body_match, $headers_match );
    }
    wp_die(); // this is required to terminate immediately and return a proper response
}
// if ( is_admin() ) {
// add_action( 'wp_ajax_example_ajax_request', 'example_ajax_request' );
// }


// Import page and contact form 7 form

// function import_xml_page() {
//   $xml_file = plugin_dir_path(__FILE__) . 'page.xml'; // Assuming 'page.xml' is in the same directory as the plugin file

//   if (file_exists($xml_file)) {
//       $xml_data = simplexml_load_file($xml_file);

//       foreach ($xml_data->channel->item as $item) {
//           $title = (string) $item->title;
//           $content = (string) $item->children('content', true)->encoded;

//           // Check if the page already exists based on title
//           $existing_page = new WP_Query(array(
//               'post_type' => 'page',
//               'post_title' => $title,
//               'posts_per_page' => 1
//           ));

//           if (!$existing_page->have_posts()) {
//               // Page doesn't exist, insert it
//               wp_insert_post(array(
//                   'post_title' => $title,
//                   'post_content' => $content,
//                   'post_status' => 'publish',
//                   'post_type' => 'page',
//                   'import_id' => 9999
//               ));
          
//               update_option('page_on_front', 9999);
//               update_option('show_on_front', 'page');

//           }
//       }
//   }
// }

// // Call the import function after WordPress is fully loaded
// add_action('wp_loaded', 'import_xml_page');



// function import_contact_forms_from_xml() {
//     $xml_file = plugin_dir_path(__FILE__) . 'cf.xml'; // URL to the XML file containing Contact Form 7 forms

//     // Load the XML file
//     $xml_data = simplexml_load_file($xml_file);

//     foreach ($xml_data->channel->item as $item) {
//         $title = (string) $item->title;
//         $content = (string) $item->children('content', true)->encoded;
//         $form_id = (int) $item->children('wp', true)->post_id;

//         // Check if the form already exists based on the form ID
//         $existing_form = get_post($form_id);

//         if (!$existing_form) {
//             // Form doesn't exist, insert it
//             $form_data = array(
//                 'post_title' => $title,
//                 'post_content' => $content,
//                 'post_status' => 'publish',
//                 'post_type' => 'wpcf7_contact_form',
//                 'import_id' => $form_id // Import ID is used to prevent duplicate imports
//             );

//             $form_id = wp_insert_post($form_data);

//             if ($form_id) {
//                 // Optionally set the newly created form as the front page
//                 // update_option('page_on_front', $form_id);
//                 // update_option('show_on_front', 'page');
//             }
//         }
//     }
// }

// // Call the import function after WordPress is fully loaded
// add_action('wp_loaded', 'import_contact_forms_from_xml');