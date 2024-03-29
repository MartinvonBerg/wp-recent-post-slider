<?php
add_action('wp_enqueue_scripts', 'wp_recent_post_scripts');

function wp_recent_post_scripts()
{
  wp_reset_query();
  $plugin_url = plugins_url('/', __FILE__);

  if (is_front_page() || is_home()) {
    //If page is using slider portfolio template then load our slider script
    // Load Styles
    //wp_enqueue_style('wp_recent_post_style1', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.css');
    //wp_enqueue_style('wp_recent_post_style2', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.css');
    wp_enqueue_style('wp_recent_post_style1', $plugin_url . 'css/slick.css');
    wp_enqueue_style('wp_recent_post_style2', $plugin_url . 'css/slick-theme.css');
    wp_enqueue_style('wp_recent_post_style3', $plugin_url . 'css/wp-recent-post-slider.css');
   

    // Load Scripts
    //wp_enqueue_script('wp_recent_post_script1', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js',array('jquery'), '1.10.2', true);
    wp_enqueue_script('wp_recent_post_script1', $plugin_url . 'js/slick.min.js',array('jquery'), '1.10.2', true);
    wp_enqueue_script('wp_recent_post_script2', $plugin_url . 'js/wp-recent-post-slider.js', array('jquery'), '1.10.2', true);
   
  }
} // (.*?).min.css (.*?).min.js  /smrtzl/plugins/wp-recent-post-slider/(.*?).js  