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

function show_recent_posts($attr)
{
	// Parameter extrahieren und vordefinieren
	extract(shortcode_atts(array(
		'numberposts' => 5,
	), $attr));

	$custom_posts = get_posts($attr);
	$i = 0;
	$dateformat = get_option('date_format');

	$string = '';
	$string .= '<div class="rps-wrapper">';
	$string .= '<div class="rps-carousel">';
	
	foreach ($custom_posts as $post) { 
			$title = substr($post->post_title,0,80); // Länge des Titels beschränken, Anzahl Zeichen
			$featimage = get_the_post_thumbnail_url($post->ID, $size='thumbnail'); 
			$featured_image_id = get_post_thumbnail_id( $post->ID );
			$content = '<body>' . $post->post_content . '</body>';
			//echo $content;

			$doc = new DOMDocument();
			$doc->validateOnParse = true;
			$doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
			$paragraphs = $doc->getElementsByTagName('p');
			$ps ='';

			foreach ($paragraphs as $p) {
				$ps .= $p->textContent;
				if (strlen($ps) > 100) {break;};
			}
			//$ps = htmlentities($ps);
			//$ps= html_entity_decode($ps);
			//$ps = htmlspecialchars($ps, ENT_QUOTES | ENT_HTML5 | ENT_DISALLOWED | ENT_SUBSTITUTE, 'UTF-8' );
			echo $ps;
			echo '</br>';

			$srcset = wp_get_attachment_image_srcset( $featured_image_id );
			$postlink = get_permalink($post->ID); // guid vorhanden, aber kein schöner permalink
			$category = get_the_category($post->ID)[0]->cat_name;
			$date = get_the_date($dateformat,$post->ID); // $post->post_date
			$excerpt = get_the_excerpt($post->ID); // löschen
			
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
			$string .= '<img loading="lazy" ' . $srcset . ' src="'. $featimage .'" alt="' . $category . ' '. $title .'">';
			$string .= '<p class="rps-category">' . $category . '</p>';
			$string .= '<p class="rps-date">' . $date . '</p>';
			$string .= '<h4 class="rps-title">' . $title . '</h4>';
			$string .= '<p class="rps-excerpt">' . $excerpt . '</p>';
			$string .= '<button class="rps-button">Weiterlesen...</button></a>';
			$string .= '</div>';
		
	}
	
	$string  .= '</div></div>';
	
	//return $string;
}

require_once __DIR__ . '/wp-recent-post-slider-enq.php';

function cmp($a, $b)
	{
		if ($a['width'] == $b['width']) {
			return 0;
		}
		return ($a['width'] < $b['width']) ? -1 : 1;
		
	}