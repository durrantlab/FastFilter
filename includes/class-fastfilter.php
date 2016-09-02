<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class FastFilter {

	/**
	 * The single instance of FastFilter.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'fastfilter';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = ''; //defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new FastFilter_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Make a shortcode
		add_shortcode( 'fastfilter', "fast_filter_func" );
	} // End __construct ()

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		$theme_name = get_option('fstfilt_theme');
		if ($theme_name == "") {
			$theme_name = "one_col";
		}
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/'.$theme_name.'.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'fastfilter', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'fastfilter';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main FastFilter Instance
	 *
	 * Ensures only one instance of FastFilter is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see FastFilter()
	 * @return Main FastFilter instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}

// [fastfilter categories="0,1,good_articles"]
function fast_filter_func( $atts ) {
	$a = shortcode_atts( array(
		'categories' => 'uncategorized'
	), $atts );

	// Get the categories
	$categories = explode(",", $a['categories']);

	// Need to map all category names and slugs to numerical ids.
	$cat_name_to_id = array();
	$cat_name_to_slug = array();
	$cat_slug_to_name = array();
	
	$allcats = get_categories();
	if (!empty($allcats)) {
		foreach($allcats as $cat) {
			$cat_name_to_id[$cat->name] = $cat->cat_ID;
			$cat_name_to_id[$cat->slug] = $cat->cat_ID;
			$cat_name_to_id[$cat->cat_name] = $cat->cat_ID;
			$cat_name_to_id[$cat->category_nicename] = $cat->cat_ID;

			$cat_name_to_slug[$cat->name] = $cat->slug;
			$cat_slug_to_name[$cat->slug] = $cat->name;
		}
	}

	// If a category in the user-defined list isn't a number, assume it's a
	// name and get the corresponding number.
	for ($i = 0; $i < count($categories); $i++) {
		$cat = $categories[$i];
		if (!(is_numeric($cat))) {
			$categories[$i] = $cat_name_to_id[$categories[$i]];
		}
	}

	// Now go through reach of the user specified categories and get all the
	// posts

	$all_tags = array();
	$tag_name_to_slug = array();

	$html = "";
	$args = array('category' => implode(",", $categories));
	$myposts = get_posts($args);
	$indx = 0;
	if ((count($myposts) > 0) && ($args["category"] !== "")) {

		try {
			foreach ($myposts as $post) {
				$tsd = $post;
			}
		} catch (Exception $e) {
			error_log("HI".print_r($posttags, true));			
		}

		foreach ($myposts as $post) {
			$status = $post->post_status;

			if ($status == "publish") {
				$title = $post->post_title;
				$content = $post->post_content;
				//$excerpt = $post->post_excerpt;
				$time = $post->post_modified;

				$id = $post->ID;
				$link = get_permalink($post);

				$posttags = get_the_tags($post->ID);
				$tag_names = array();
				$tag_slugs = array();
				if (count($posttags) > 0) {
					foreach ($posttags as $posttag) {
						$tag_names[] = $posttag->name;
						$tag_slugs[] = $posttag->slug;
						$tag_name_to_slug[$posttag->name] = $posttag->slug; 
						$all_tags[] = $posttag->name;
					}
				}

				$tag_names_as_str = implode(" ", $tag_names);
				$tag_slugs_as_str = implode(" ", $tag_slugs);

				$indx++;
				$odd_even_class = "fastfilter-even";
				if ($indx % 2 == 0) {$odd_even_class = "fastfilter-odd";}

				$html .= "
				<div class='fastfilter-post-item fastfilter-post-item-styling $tag_slugs_as_str $odd_even_class' data-href='$link'>
					<div class='fastfilter-title'><a class='fastfilter-post-url' href='$link'>$title</a></div>
					<div class='fastfilter-content'>
						<div class='fastfilter-gradient-cover'></div>
						$content
					</div>
					<div class='fastfilter-date'>$time</div>
					<div class='fastfilter-tags-list'>$tag_names_as_str</div>
					<div class='fastfilter-clearfix'></div>
				</div>
				";
			}
		};
	}
	
	// Add the "no results found" box too.
	$html .= "
	<div style='display:none;' class='fastfilter-no-results fastfilter-post-item-styling ".(odd_even_class == "fastfilter-even" ? "fastfilter-odd" : "fastfilter-even")."'>
		<div class='fastfilter-title'>No Results!</div>
		<div class='fastfilter-content'>
			No results matched your filter query!
		</div>
	</div>
	";

	// Tell jQuery how to show and hide these post cards.
	$show_and_hide = get_option("fstfilt_show_and_hide");
	if ($show_and_hide == "") {
		$show_and_hide = "slide";
	}

	$html .= "
		<div style='display: none;' id='show_and_hide_for_jquery'>$show_and_hide</div>
	";


	$all_tags = array_unique($all_tags);

	// Make checkboxes for each of the tags.
	$instructions = get_option("fstfilt_instructions");
	if ($instructions == "") {
		$instructions = "Filter the items below by selecting the required tags.";
	}

	$checkboxes = "
		<div class='fastfilter-description'>
			<p>".$instructions."<p>
		</div>
		<div class='fastfilter-checkbox-bar'>
	";

	sort($all_tags);

	foreach ($all_tags as $name) {
		$slug = $tag_name_to_slug[$name];
		$checkboxes .= "
			<label class='fastfilter-label'><input class='fastfilter-checkbox' 
				type='checkbox' name='checkbox' value='$slug'>
				<div class='fastfilter-label-text'>$name</div>
			</label>
		";
	}

	$checkboxes .= "
		</div>
	";

	$html = $checkboxes.$html;

	if (get_option("fstfilt_give_credit") == "on") {
		$html .= '<div style="clear: both;">The FastFilter plugin is brought to you by <a href="http://durrantlab.com/usr/plugin_redirect.php">Jacob Durrant</a>.</div>';
	};

	return $html;
}
