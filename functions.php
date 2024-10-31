<?php
/*
Plugin Name: Personal Tweet Me Button
Plugin URI: http://www.smartersoft.nl/producten/tweetMe
Description: Adds a tweet me widget, with the sites twitter account or the authors twitter account. Also adds the possibility to tweet from your post edit screen.
Version: 1.3
Requires at least: WordPress 3.0
Tested up to: WordPress 3.5.1
License: GLPv2
Author: Smartersoft
Author URI: http://www.smartersoft.nl/
Network: true
*/


function s_tweetme_load() {
	add_action( 'show_user_profile', 's_tweetme_fields' );
	add_action( 'edit_user_profile', 's_tweetme_fields' );
	add_action( 'personal_options_update', 's_tweetme_save_field' );
	add_action( 'edit_user_profile_update', 's_tweetme_save_field' );
	add_action('widgets_init', create_function('', 'return register_widget("s_tweetme");'));
	add_action('init','s_tweetme_widget_loadscript');
}

s_tweetme_load();

function s_tweetme_fields( $user ) { ?>
	<h3>Tweet me information</h3>
	<table class="form-table">
		<tr>
			<th><label for="twitter">Twitter</label></th>
			<td>
				<input type="text" name="twitter" id="twitter" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">This username will be used for displaying the personal tweet me button. </span>
			</td>
		</tr>
	</table>
<?php }

function s_tweetme_save_field( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
	update_usermeta( $user_id, 'twitter', $_POST['twitter'] );
}


class s_tweetme extends WP_Widget {
    /** constructor */
    function s_tweetme() {
        parent::WP_Widget('s_tweetme', $name = 'Tweet Me');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		$twitter_author = '';
        $twitter_site = esc_attr($instance['twitter']);
        $count = esc_attr($instance['count']);
		if ($instance['use_author'] == 'on' && (is_single() || is_page())) {
            $twitter_author = get_the_author_meta('twitter');
		}
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; 
					?>
					<div style="margin:10px;">
					<a href="http://twitter.com/share" class="twitter-share-button" data-count="<?php echo $count;?>" data-via="<?php echo ($twitter_author) ? $twitter_author : $twitter_site;?>" data-related="<?php echo ($twitter_author) ? $twitter_site: '';?>">Tweet</a>
					</div>
					
					
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['twitter'] = strip_tags($new_instance['twitter']);
    $instance['count'] = strip_tags($new_instance['count']);
	$instance['use_author'] = strip_tags($new_instance['use_author']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = esc_attr($instance['title']);
		$twitter = esc_attr($instance['twitter']);
        $count = esc_attr($instance['count']);
		$use_author = esc_attr($instance['use_author']);
        $countname = $this->get_field_name('count');
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('twitter'); ?>">Twitter account:<input class="widefat" id="<?php echo $this->get_field_id('twitter'); ?>" name="<?php echo $this->get_field_name('twitter'); ?>" type="text" value="<?php echo $twitter;?>" /></label></p>
            <p><label for="">Display count:<br/><input type="radio" name="<?php echo $countname;?>" value="horizontal" <?php echo ($count=='horizontal') ? 'checked': '';?> /> Horizontal<br/>
                <input type="radio" name="<?php echo $countname;?>" value="vertical" <?php echo ($count=='vertical' || !$count) ? 'checked': '';?> /> Vertical<br/>
                <input type="radio" name="<?php echo $countname;?>" value="none" <?php echo ($count=='none') ? 'checked': '';?>/> Don't show count</label></p>
			<p><label for="<?php echo $this->get_field_id('use_author'); ?>">Use author twitter: <input id="<?php echo $this->get_field_id('use_author'); ?>" name="<?php echo $this->get_field_name('use_author'); ?>" type="checkbox" <?php if($use_author == "on") echo 'checked';?> /></label></p>
        <?php 
    }

}

function s_tweetme_widget_loadscript (){
if( is_active_widget( false, false, 's_tweetme' ) ){ wp_enqueue_script('s_tweetme_script', 'http://platform.twitter.com/widgets.js',null,false,true);}}


function s_tweetme_meta_output($post) {?>
<a href="http://twitter.com/share" class="twitter-share-button" data-count="horizontal" data-counturl="<?php echo get_permalink($post->ID);?>" data-url="<?php echo get_permalink($post->ID); ?>" data-text="<?php echo $post->post_title." | ".get_bloginfo('name');?>">Tweet</a>
<?php }
function s_tweetme_meta_add() {
add_meta_box('tweetme_meta','Twitter','s_tweetme_meta_output','post','side','default');
add_meta_box('tweetme_meta','Twitter','s_tweetme_meta_output','page','side','default');
wp_enqueue_script('s_tweetme_script', 'http://platform.twitter.com/widgets.js',null,false,true);
	
}
add_action('admin_print_scripts-post.php','s_tweetme_meta_add');
?>