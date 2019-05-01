<?php
/**
  * Plugin Name: Yada Wiki Homepage Display
  * Plugin URI: https://github.com/ngagnon1/yada-wiki-homepage-display
  * Description: Displays the content and title of a wiki page on the front page
  * Version: 1.0
  * Author: Nathan Gagnon
  * Author URI: http://parsing.life/
  *
  * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
**/

/** ADDING OPTION TO SETTINGS MENU **/

function wiki_homepage_display_register_settings() {

   add_settings_section(
    'wiki_homepage_display',
    'Yada Wiki Homepage Display',
    'wiki_homepage_display_options_callback',
    'reading'
  );
   
   add_settings_field(
    'wiki_homepage_display_page_title',
    'Wiki Page Title',
    'wiki_homepage_display_setting_callback_function',
    'reading',
    'wiki_homepage_display'
   );
   
   register_setting( 'reading', 'wiki_homepage_display_page_title' );
}

add_action( 'admin_init', 'wiki_homepage_display_register_settings' );


function wiki_homepage_display_options_callback(){
  echo '<p>Enter the title of the Wiki Page you would like to display as your homepage</p>';
}

function wiki_homepage_display_setting_callback_function() {
  echo '<input name="wiki_homepage_display_page_title" id="wiki_homepage_display_page_title" value="'.addslashes(get_option( 'wiki_homepage_display_page_title' )).'" />';
}

/** UPDATING HOMEPAGE CONTENT AND TITLE **/

class Yada_Wiki_Homepage_Display{
  public static $title_attempts = 0;
  public static $content_updated = false;
}

function change_homepage_content_to_wiki( $content ){
  if( is_front_page() && is_main_query() && Yada_Wiki_Homepage_Display::$content_updated == false ){

    Yada_Wiki_Homepage_Display::$content_updated = true; //only do once, avoid recursive calls from apply_filters

    $title = trim(get_option( 'wiki_homepage_display_page_title' ));
    if( $title ){
      $page = get_page_by_title( $title, OBJECT, 'yada_wiki' );
      if( $page ){
        $content = apply_filters('the_content', get_post_field('post_content', $page->ID));
        return $content;
      }
    }
  }
  return $content;
}
add_filter( 'the_content', 'change_homepage_content_to_wiki' );

function change_homepage_title_to_wiki( $content, $id ){
  if( is_front_page() ){

    if( Yada_Wiki_Homepage_Display::$title_attempts < 4 ){ //prevent infinite recursion if apply_filters changes the title 
      Yada_Wiki_Homepage_Display::$title_attempts++;

      $title = trim(get_option( 'wiki_homepage_display_page_title' ));
      if( $title && strpos( $content, $title ) === false ){
        $content = apply_filters('the_title', $title);
      }
    }
  }
  return $content;
}
add_filter( 'the_title', 'change_homepage_title_to_wiki' );


