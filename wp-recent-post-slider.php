<?php

/**
 *
 * @link              www.mvb1.de
 * @since             1.0.0
 * @package           wp-recent-post-slider
 *
 * @wordpress-plugin
 * Plugin Name:       wp-recent-post-slider
 * Plugin URI:        www.mvb1.de
 * Description:       Anzeige der letzten Posts in einem Slider mit Shortcode: [recent-post-slider numberposts="5"]
 * Version:           0.3.0
 * Author:            Martin von Berg
 * Author URI:        www.mvb1.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined('ABSPATH') or die('Are you ok?');

add_shortcode('recent-post-slider', 'show_recent_posts');

// Wortlänge für excerpt vorgeben. Gilt für alle Excerpts auf der ganzen Seite!!!
//function wp_slider_excerpt_length( $length ) {
//    return 50; // Anzahl Worte!
//}
//add_filter( 'excerpt_length', 'wp_slider_excerpt_length');

function show_recent_posts($attr)
{
	global $wpdb;
	
	// Pfade und Verzeichnisse definieren
	//$plugin_path = $plugin_url = plugins_url('/', __FILE__);
	//$wp_postmap_path = $plugin_path . 'images/';
	
	// Parameter extrahieren und vordefinieren
	extract(shortcode_atts(array(
		'numberposts' => 5,
	), $attr));

	$custom_posts = get_posts($attr);
	$i = 0;
	$string = '';
	$dateformat = get_option('date_format');
	
	$string .= '<div class="rps-wrapper">';
	$string .= '<div class="rps-carousel">';
	
	$min_width = 200;
	$max_width = 700;

	$imgsizes = "(max-width: 480px) 100vw, (max-width: 700px) 50vw, (max-width: 1024px) 33vw, 350px"; //abgeleitet aus dem javascript für slick

	$upload_dir    = wp_get_upload_dir();
		
	// Post auslesen und <a>-Tags mit allen Informationen schreiben
	
	foreach ($custom_posts as $post) { 
			$title = substr($post->post_title,0,80); // Länge des Titels beschränken, Anzahl Zeichen
			$featimage = get_the_post_thumbnail_url($post->ID, $size='thumbnail'); 
			$featured_image_id = get_post_thumbnail_id( $post->ID );
			$image_meta = wp_get_attachment_metadata( $featured_image_id );
			
			$my_srcset = '';

			if ( is_array($image_meta) ) {
				// Retrieve the uploads sub-directory from the full size image.
				$dirname = _wp_get_attachment_relative_path( $image_meta['file'] );

				if ( $dirname ) {
					$dirname = trailingslashit( $dirname );
				}
				
				$image_baseurl = trailingslashit( $upload_dir['baseurl'] ) . $dirname;

				/*
				* If currently on HTTPS, prefer HTTPS URLs when we know they're supported by the domain
				* (which is to say, when they share the domain name of the current request).
				*/
				if ( is_ssl() && 'https' !== substr( $image_baseurl, 0, 5 ) && parse_url( $image_baseurl, PHP_URL_HOST ) === $_SERVER['HTTP_HOST'] ) {
					$image_baseurl = set_url_scheme( $image_baseurl, 'https' );
				}

				$image_sizes = $image_meta['sizes'];
				
				usort($image_sizes, 'cmp');

				foreach ($image_sizes as $image) {
					$width = $image['width'];
					
					if (($width >= $min_width) & ($width <= $max_width) & ($image['mime-type'] == 'image/jpeg')) { 
						$my_srcset .= $image_baseurl . $image['file'] . ' ' . $image['width'] . 'w, ';
					}
				}
				$my_srcset = rtrim($my_srcset, ', '); 
				if ( ! empty($my_srcset)) {
					$my_srcset = 'srcset="' . $my_srcset . '" sizes="' . $imgsizes . '"';
				}
			}

			$postlink = get_permalink($post->ID);
			$category = get_the_category($post->ID)[0]->cat_name;
			$date = get_the_date($dateformat,$post->ID);
			$excerpt = get_the_excerpt($post->ID);
			
			if (strpos($excerpt, 'urzbeschr') !== false) { 
				$excerpt = ltrim(strstr($excerpt," "));
			}

			if (strpos($excerpt, '<a') !== false) { 
				$posalttag = strpos($excerpt,"<a");
				$excerpt = substr($excerpt,0,$posalttag);
			}
			
			$i++;
			$string .= '<div class ="rps-blog-cart">';
			$string .= '<a href = "' . $postlink . '">';
			$string .= '<img loading="lazy" ' . $my_srcset . ' src="'. $featimage .'" alt="' . $category . ' '. $title .'">';
			$string .= '<p class="rps-category">' . $category . '</p>';
			$string .= '<p class="rps-date">' . $date . '</p>';
			$string .= '<h4 class="rps-title">' . $title . '</h4>';
			$string .= '<p class="rps-excerpt">' . $excerpt . '</p>';
			$string .= '<button class="rps-button">Weiterlesen...</button></a>';
			$string .= '</div>';
		
	}
	
	$string  .= '</div></div>';
	
	return $string;
}

require_once __DIR__ . '/wp-recent-post-slider-enq.php';

function cmp($a, $b)
	{
		if ($a['width'] == $b['width']) {
			return 0;
		}
		return ($a['width'] < $b['width']) ? -1 : 1;
		
	}