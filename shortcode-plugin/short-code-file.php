<?php

// Its a simple shortcode text message

// add_shortcode("practice-shortcode", "show_shortcode_function");
// function show_shortcode_function(){
//     return "Hello, This is a shortcode created using PHP";
// }

// Parametrize shortcode

add_shortcode("parametrize-shortcode","parametrize_shortcode_function");
function parametrize_shortcode_function($attributes){
$attributes=shortcode_atts( array(
"username"=> "Sheraz",
"password"=> "Default password",

),$attributes,"parametrize-shortcode");
return "Username: ".$attributes["username"]."<br>Password: ".$attributes["password"];
}

// DB operations shortcode for post
//step 1: create a shortcode built in code
add_shortcode("db-operations","show_post_titles_and_content");

function show_post_titles_and_content(){
    global $wpdb;
    $table_prefix = $wpdb->prefix; // wp_
    $table_name = $table_prefix . "posts";
    $query = $wpdb->get_results("SELECT ID, post_title FROM {$table_name} WHERE post_type='post' AND post_status='publish' ");
    
    if (count($query) > 0) {
        $outputHtml = "<ul>";
        foreach ($query as $post) {
            $outputHtml .= '<li><a href="' . get_permalink($post->ID) . '">' . esc_html($post->post_title) . '</a></li>';
        }
        $outputHtml .= "</ul>";
        return $outputHtml;
    }
    
    return "No posts found.";
}
    