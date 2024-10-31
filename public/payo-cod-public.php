<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/payo-cod
 * @since      1.0.0
 *
 * @package    Payo_Cod
 * @subpackage Payo_Cod/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Payo_Cod
 * @subpackage Payo_Cod/public
 * @author     PAYO <https://wordpress.org/plugins/payo-cod>
 */
class Payo_Cod_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $payo_cod    The ID of this plugin.
	 */
	private $payo_cod;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $payo_cod       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $payo_cod, $version ) {

		$this->payo_cod = $payo_cod;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Payo_Cod_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Payo_Cod_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->payo_cod, plugin_dir_url( __FILE__ ) . 'css/payo-cod-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Payo_Cod_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Payo_Cod_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->payo_cod, plugin_dir_url( __FILE__ ) . 'js/payo-cod-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->payo_cod, 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));

	}

}
