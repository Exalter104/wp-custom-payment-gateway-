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
?>