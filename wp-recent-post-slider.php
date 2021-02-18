<?php

/**
 *
 * @link              www.mvb1.de
 * @since             5.3.0
 * @package           wp-recent-post-slider
 *
 * @wordpress-plugin
 * Plugin Name:       wp-recent-post-slider
 * Plugin URI:        www.mvb1.de
 * Description:       Anzeige der letzten Posts in einem Slider mit Shortcode: [recent-post-slider numberposts="5"]
 * Version:           0.5.0
 * Author:            Martin von Berg
 * Author URI:        www.mvb1.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * PHP-Version:		  5.4+, tested with PHP 8.0
 */

 // namespace definition fehlt hier!

defined('ABSPATH') or die('Are you ok?');

add_shortcode('recent-post-slider', 'show_recent_posts');

function show_recent_posts($attr)
{
	// Parameter extrahieren und vordefinieren
	extract(shortcode_atts(array(
		'numberposts' => 5,
	), $attr));

	$custom_posts = get_posts($attr);
	$dateformat = get_option('date_format');

	$string = '';
	$string .= '<div class="rps-wrapper">';
	$string .= '<div class="rps-carousel">';

	$min_width = 200;
	$max_width = 700;

	$imgsizes = "(max-width: 480px) 100vw, (max-width: 700px) 50vw, (max-width: 1024px) 33vw, 350px"; //abgeleitet aus dem javascript für slick

	$upload_dir    = wp_get_upload_dir();

	$lenexcerpt = 300;
	$lentitle = 80;
	
	foreach ($custom_posts as $post) { 
			$title = substr($post->post_title,0, $lentitle); // Länge des Titels beschränken, Anzahl Zeichen
			$featimage = get_the_post_thumbnail_url($post->ID, $size='thumbnail'); 
			$featured_image_id = get_post_thumbnail_id( $post->ID );
			$image_meta = wp_get_attachment_metadata( $featured_image_id );
			$content = $post->post_content;
			$postlink = get_permalink($post->ID); // guid vorhanden, aber kein schöner permalink
			$category = get_the_category($post->ID)[0]->cat_name;
			$date = get_the_date($dateformat,$post->ID); // $post->post_date
			
			// Excerpt nur aus den Absätzen <p> herstellen! Schlüsselwörter entfernen, dürfen dann im Text nicht vorkommen
			// Absätze mit [shortcodes] werden ignoriert.
			// der html-code muss mit zeilenumbrüchen formatiert sein, sonst geht das nicht!
			$p = '';
			foreach(preg_split("/((\r?\n)|(\r\n?))/", $content) as $line){ 
				$sub = substr($line,0,3); // html-tag aus der zeile ausschneiden
				$isshortcode = strpos($line,'['); 
				if (($sub == '<p>') and ($isshortcode == false)) {
					$p .= substr($line,3);
				}
				$p = str_replace('</p>','',$p);
			} 
			$p = str_replace('Kurzbeschreibung:','',$p);
			$p = str_replace('Tourenbeschreibung:','',$p);
			$p = strip_tags($p); // html-Tags entfernen
			$p = substr($p,0, $lenexcerpt); // erst jetzt auf die richtige länge kürszen
			$excerpt = $p . '...';

			// special imgsrcset für slick erstellen, dass Standard srcset von WP kann nicht genutzt werden!
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

// Vergleichsfunktion
function cmp($a, $b)
	{
		if ($a['width'] == $b['width']) {
			return 0;
		}
		return ($a['width'] < $b['width']) ? -1 : 1;
		
	}