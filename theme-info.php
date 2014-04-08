<?php
/*
Plugin Name:  Theme Info
Description:  Provides a simple way of displaying up-to-date information about specific WordPress Theme Directory hosted themes in your blog posts and pages.
Plugin URI:   http://lud.icro.us/wordpress-theme-info/
Version:      0.1
Author:       John Blackbourn
Author URI:   http://johnblackbourn.com/
Text Domain:  theme_info
Domain Path:  /languages/
License:      GPL v2 or later

Copyright © 2014 John Blackbourn

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class ThemeInfo {

	public $plugin;
	public $meta;

	public function __construct() {

		add_action( 'init',               array( $this, 'action_init' ) );
		add_action( 'admin_menu',         array( $this, 'action_admin_menu' ) );
		add_action( 'admin_head',         array( $this, 'action_admin_head' ) );
		add_action( 'save_post',          array( $this, 'action_save_post' ) );
		add_action( 'update_theme_info', array( $this, 'action_update_theme_info' ) );
		add_action( 'admin_init',         array( $this, 'action_admin_init' ) );

		add_shortcode( 'theme',          array( $this, 'shortcode_theme_info' ) );

		$this->plugin = array(
			'url' => plugin_dir_url( __FILE__ ),
			'dir' => plugin_dir_path( __FILE__ ),
		);

	}

	public function action_init() {
		load_plugin_textdomain( 'theme_info', false, dirname( plugin_basename( __FILE__ ) ) );
	}

	public function get_theme_info( $slug = null ) {

		if ( !$slug )
			return false;

		require_once ABSPATH . 'wp-admin/includes/theme-install.php';

		$info   = array();
		$slug   = sanitize_title( $slug );
		$plugin = themes_api( 'theme_information', array( 'slug' => $slug ) );

		if ( !$plugin or is_wp_error( $plugin ) )
			return false;

		$attributes = array(
			'name'           => 'name',
			'slug'           => 'slug',
			'version'        => 'version',
			'author'         => 'author',
			'profile_url'    => 'author_profile',
			'contributors'   => 'contributors',
			'requires'       => 'requires',
			'tested'         => 'tested',
			'compatibility'  => 'compatibility',
			'rating_raw'     => 'rating',
			'num_ratings'    => 'num_ratings',
			'downloaded_raw' => 'downloaded',
			'updated_raw'    => 'last_updated',
			'homepage_url'   => 'homepage',
			'description'    => array( 'sections', 'description' ),
			'other_notes'    => array( 'sections', 'other_notes' ),
			'download_url'   => 'download_link',
			'donate_url'     => 'donate_link',
			'tags'           => 'tags',
		);

		foreach ( $attributes as $name => $key ) {

			if ( is_array( $key ) ) {
				$_key = $plugin->$key[0];
				if ( isset( $_key[$key[1]] ) )
					$info[$name] = $_key[$key[1]];
			} else {
				if ( isset( $plugin->$key ) )
					$info[$name] = $plugin->$key;
			}

		}

		$info['downloaded']  = number_format_i18n( $info['downloaded_raw'] );
		$info['rating']      = ceil( 0.05 * $info['rating_raw'] );
		$info['link_url']    = "http://wordpress.org/themes/{$info['slug']}/";
		$info['updated']     = date_i18n( get_option('date_format'), strtotime( $info['updated_raw'] ) );
		$info['updated_ago'] = sprintf( __('%s ago'), human_time_diff( strtotime( $info['updated_raw'] ) ) );
		$info['download']    = '<a href="' . $info['download_url'] . '">%s</a>';
		$info['homepage']    = '<a href="' . $info['homepage_url'] . '">%s</a>';
		$info['link']        = '<a href="' . $info['link_url']     . '">%s</a>';
		$info['profile']     = '<a href="' . $info['profile_url']  . '">%s</a>';

		if ( isset( $info['donate_url'] ) )
			$info['donate'] = '<a href="' . $info['donate_url'] . '">%s</a>';

		if ( isset( $info['contributors'] ) ) {
			foreach ( (array) $info['contributors'] as $name => $link )
				$info['contributors'][$name] = '<a href="' . $link . '">' . $name . '</a>';
			$info['contributors'] = implode( ', ', $info['contributors'] );
		}

		if ( isset( $info['tags'] ) )
			$info['tags'] = implode( ', ', (array) $info['tags'] );

		if ( preg_match( '|href="([^"]+)"|i', $info['author'], $matches ) )
			$info['author_url'] = $matches[1];

		if ( preg_match( '|>([^<]+)<|i', $info['author'], $matches ) )
			$info['author_name'] = $matches[1];
		else
			$info['author_name'] = $info['author'];

		if ( isset( $info['other_notes'] ) and preg_match_all( '|<h3>([^<]+)</h3>|i', $info['other_notes'], $matches, PREG_SET_ORDER ) ) {
			for ( $i = 0; isset( $matches[$i] ); $i++ ) {
				$end = isset( $matches[$i+1][0] ) ? $matches[$i+1][0] : '$';
				preg_match( '|' . $matches[$i][0] . '(.*)' . $end . '|si', $info['other_notes'], $match );
				$info[sanitize_title( $matches[$i][1] )] = $match[1];
			}
		}

		# The following values are *deprecated* but remain for those who may be using them:
		$info['download_link']     = $info['download_url']; # use download_url instead
		$info['tags_list']         = $info['tags'];         # use tags instead
		$info['extend']            = $info['link_url'];     # use link_url instead
		$info['last_updated_nice'] = $info['updated'];      # use updated instead
		$info['last_updated']      = $info['updated'];      # use updated instead
		$info['last_updated_ago']  = $info['updated_ago'];  # use updated_ago instead
		$info['last_updated_raw']  = $info['updated_raw'];  # use updated_raw instead

		/*
		 * The `theme_info` filter below allows a plugin/theme to add or
		 * modify the available shortcodes.
		 *
		 * Example 1:
		 *
		 * function myfunction( $info, $slug, $plugin ) {
		 * 	$info['fullname'] = $info['name'] . ' v' . $info['version'];
		 * 	return $info;
		 * }
		 * add_filter( 'theme_info', 'myfunction', 10, 3 );
		 *
		 * The above code would create a `[theme fullname]` shortcode which
		 * would return something like `My Wonderful Theme v1.0`
		 *
		 * Example 2:
		 *
		 * function myfunction( $info, $slug, $plugin ) {
		 * 	$info['requires'] = 'Requires at least WordPress version ' . $info['requires'];
		 * 	return $info;
		 * }
		 * add_filter( 'theme_info', 'myfunction', 10, 3 );
		 *
		 * The above would modify the `[theme requires]` shortcode so it returns
		 * a full sentence explaining the minimum WP version requirement.
		 *
		 */

		return apply_filters( 'theme_info', $info, $slug, $plugin );

	}

	public function action_update_theme_info() {

		$q = new WP_Query;

		$posts = $q->query( array(
			'posts_per_page' => -1,
			'meta_key'       => 'theme',
			'post_type'      => 'any'
		) );

		if ( !count( $posts ) )
			return;

		foreach ( $posts as $p ) {
			$theme_info = $this->get_theme_info( get_post_meta( $p->ID, 'theme', true ) );
			if ( $theme_info )
				update_post_meta( $p->ID, 'theme-info', $theme_info );
		}

	}

	public function action_save_post( $post_ID ) {

		if ( wp_is_post_revision( $post_ID ) or wp_is_post_autosave( $post_ID ) )
			return;

		if ( !isset( $_POST['theme_info'] ) )
			return;

		if ( empty( $_POST['theme_info'] ) ) {

			delete_post_meta( $post_ID, 'theme' );
			delete_post_meta( $post_ID, 'theme-info' );

		} else {

			$plugin = trim( stripslashes( $_POST['theme_info'] ) );
			$theme_info = $this->get_theme_info( $plugin );

			if ( !$theme_info )
				return false; # @TODO: display error msg?

			update_post_meta( $post_ID, 'theme', $plugin );
			update_post_meta( $post_ID, 'theme-info', $theme_info );

		}

		return;

	}

	public function shortcode_theme_info( $atts ) {

		global $post;

		$atts = shortcode_atts( array(
			0      => 'name',
			'text' => ''
		), $atts );

		$att = $atts[0];
		$key = $post->ID;

		if ( empty( $this->meta[$key] ) )
			$this->meta[$key] = get_post_meta( $post->ID, 'theme-info', true );

		if ( !isset( $this->meta[$key][$att] ) )
			return '';

		if ( false !== strpos( $this->meta[$key][$att], '%s' ) ) {

			$texts = array(
				'download' => __( 'Download', 'theme-info' ),
				'homepage' => __( 'Visit theme homepage', 'theme-info' ),
				'donate'   => __( 'Donate', 'theme-info' ),
				'link'     => $this->meta[$key]['name'],
				'profile'  => $this->meta[$key]['author_name']
			);

			$text = ( $atts['text'] ) ? $atts['text'] : $texts[$att];
			$this->meta[$key][$att] = str_replace( '%s', $text, $this->meta[$key][$att] );

		}

		/*
		 * The `theme_info_shortcode` filter below allows a plugin/theme
		 * to format or otherwise modify the output of the shortcode.
		 *
		 * Example:
		 *
		 * function myfunction( $output, $attribute, $slug ) {
		 * 	if ( 'screenshots' == $attribute ) {
		 *   $output = str_replace( array( '<ol', '</ol' ), array( '<ul', '</ul' ), $output );
		 *  }
		 * 	return $output;
		 * }
		 * add_filter( 'theme_info_shortcode', 'myfunction', 10, 3 );
		 *
		 * The above would modify the 'screenshots' output so the screenhots are
		 * displayed in an unordered list instead of an ordered list.
		 *
		 */

		return apply_filters( 'theme_info_shortcode', $this->meta[$key][$att], $att, $this->meta[$key]['slug'] );

	}

	public function action_admin_head() {
		if ( self::is_post_writing_screen() ) {
		?>
		<script type="text/javascript">

			jQuery(function($) {

				$('#theme_info_shortcodes').hide();
				$('#theme_info_show_shortcodes').show().click(function(){
					$('#theme_info_shortcodes').toggle();
					text = $('#theme_info_shortcodes').is(':visible') ? '<?php esc_js( _e( '[ hide ]', 'theme_info' ) ); ?>' : '<?php esc_js( _e( '[ show ]', 'theme_info' ) ); ?>';
					$(this).text(text);
					return false;
				});
				$('#theme_info_shortcodes dt').click(function(){
					if ( ( typeof window.tinyMCE != 'undefined' ) && ( window.tinyMCE.activeEditor ) && ( !tinyMCE.activeEditor.isHidden() ) ) {
						tinyMCE.execCommand('mceInsertContent', false, $(this).text() + '</p>');
					} else {
						edInsertContent(document.getElementById('content'), $(this).text());
					}
				});

			} );

		</script>
		<style type="text/css">

			#theme_info {
				width: 98%;
				margin-top: 5px
			}

			#theme_info_shortcodes dl {
				margin: 5px 5px 10px;
				overflow: auto;
				font-size: 0.9em;
				border-bottom: 1px solid #dfdfdf;
				padding-bottom: 8px;
			}

			#theme_info_shortcodes dt {
				float: left;
				clear: left;
				width: 52%;
				margin: 0 1% 5px 0;
				cursor: pointer;
			}

			#theme_info_shortcodes dt:hover {
				color: #D54E21;
			}

			#theme_info_shortcodes dd {
				float: left;
				width: 47%;
				margin: 0 0 5px 0;
			}

			#theme_info_show_shortcodes {
				display: none;
			}

			#theme_info_shortcodes p {
				font-style: italic;
			}

		</style>
		<?php
		}
	}

	public function meta_box( $post ) {
		?>
		<label for="theme_info"><?php _e( 'Theme slug:', 'theme_info' ); ?></label>
		<input type="text" name="theme_info" id="theme_info" value="<?php esc_attr_e( get_post_meta( $post->ID, 'theme', true ) ); ?>" />
		<p class="howto"><?php _e( 'To display information about a theme, you should use one of the shortcodes below.', 'theme_info' ); ?></p>
		<div id="theme_info_shortcodes">
			<p><?php _e( 'Plain info:', 'theme_info' ); ?></p>
			<dl>
				<dt>[theme author_name]</dt>
				<dd class="howto"><?php _e( 'Author&rsquo;s name', 'theme_info' ); ?></dd>
				<dt>[theme author_url]</dt>
				<dd class="howto"><?php _e( 'Author&rsquo;s URL', 'theme_info' ); ?></dd>
				<dt>[theme download_url]</dt>
				<dd class="howto"><?php _e( 'URL of ZIP file', 'theme_info' ); ?></dd>
				<dt>[theme downloaded]</dt>
				<dd class="howto"><?php _e( 'Download count', 'theme_info' ); ?></dd>
				<dt>[theme homepage_url]</dt>
				<dd class="howto"><?php _e( 'URL of homepage', 'theme_info' ); ?></dd>
				<dt>[theme donate_url]</dt>
				<dd class="howto"><?php _e( 'URL of donations page', 'theme_info' ); ?></dd>
				<dt>[theme link_url]</dt>
				<dd class="howto"><?php _e( 'URL of wp.org page', 'theme_info' ); ?></dd>
				<dt>[theme name]</dt>
				<dd class="howto"><?php _e( 'Name', 'theme_info' ); ?></dd>
				<dt>[theme profile_url]</dt>
				<dd class="howto"><?php _e( 'URL of author&rsquo;s wp.org profile', 'theme_info' ); ?></dd>
				<dt>[theme rating]</dt>
				<dd class="howto"><?php _e( 'Rating out of 5', 'theme_info' ); ?></dd>
				<dt>[theme slug]</dt>
				<dd class="howto"><?php _e( 'Slug', 'theme_info' ); ?></dd>
				<dt>[theme tags]</dt>
				<dd class="howto"><?php _e( 'List of tags', 'theme_info' ); ?></dd>
				<dt>[theme updated_ago]</dt>
				<dd class="howto"><?php _e( 'Last updated ago (hours/days/weeks)', 'theme_info' ); ?></dd>
				<dt>[theme updated]</dt>
				<dd class="howto"><?php _e( 'Last updated date', 'theme_info' ); ?></dd>
				<dt>[theme version]</dt>
				<dd class="howto"><?php _e( 'Version number', 'theme_info' ); ?></dd>
			</dl>
			<p><?php _e( 'Formatted info:', 'theme_info' ); ?></p>
			<dl>
				<dt>[theme author]</dt>
				<dd class="howto"><?php _e( 'Link to author&rsquo;s homepage', 'theme_info' ); ?></dd>
				<dt>[theme contributors]</dt>
				<dd class="howto"><?php _e( 'List of contributors', 'theme_info' ); ?></dd>
				<dt>[theme description]</dt>
				<dd class="howto"><?php _e( 'Long description', 'theme_info' ); ?></dd>
				<dt>[theme download]</dt>
				<dd class="howto"><?php _e( 'Link to ZIP file', 'theme_info' ); ?></dd>
				<dt>[theme homepage]</dt>
				<dd class="howto"><?php _e( 'Link to homepage', 'theme_info' ); ?></dd>
				<dt>[theme donate]</dt>
				<dd class="howto"><?php _e( 'Link to donations page', 'theme_info' ); ?></dd>
				<dt>[theme link]</dt>
				<dd class="howto"><?php _e( 'Link to wp.org page', 'theme_info' ); ?></dd>
				<dt>[theme profile]</dt>
				<dd class="howto"><?php _e( 'Link to author&rsquo;s wp.org profile', 'theme_info' ); ?></dd>
				<dt>[theme latest_change]</dt>
				<dd class="howto"><?php _e( 'Latest changelog entry', 'theme_info' ); ?></dd>
				<dt>[theme other_notes]</dt>
				<dd class="howto"><?php _e( 'Other notes', 'theme_info' ); ?></dd>
			</dl>
		</div>
		<p><a href="#" id="theme_info_show_shortcodes"><?php _e( '[ show ]', 'theme_info' ); ?></a></p>
		<?php
	}

	public function action_admin_menu() {
		add_meta_box(
			'themeinfo',
			__( 'Theme Info', 'theme_info' ),
			array( $this, 'meta_box' ),
			'post',
			'side'
		);
		add_meta_box(
			'themeinfo',
			__( 'Theme Info', 'theme_info' ),
			array( $this, 'meta_box' ),
			'page',
			'side'
		);
	}

	public static function is_post_writing_screen() {
		foreach ( array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ) as $file )
			if ( strpos( $_SERVER['REQUEST_URI'], $file ) )
				return true;
		return false;
	}

	public function action_admin_init() {
		if ( !wp_next_scheduled( 'update_theme_info' ) )
			wp_schedule_event( time(), 'hourly', 'update_theme_info' );
	}

}

function get_theme_info( $slug, $attribute = 'version' ) {

	global $themeinfo;

	$slug = sanitize_title( $slug );

	if ( empty( $themeinfo->meta[$slug] ) )
		$themeinfo->meta[$slug] = $themeinfo->get_theme_info( $slug );

	if ( isset( $themeinfo->meta[$slug][$attribute] ) )
		return $themeinfo->meta[$slug][$attribute];
	else
		return false;

}

function theme_info( $slug, $attribute = 'version' ) {
	echo get_theme_info( $slug, $attribute );
}

global $theme_info;

$themeinfo = new ThemeInfo;