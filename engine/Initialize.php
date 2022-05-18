<?php
/**
 * EZPZ_TWEAKS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpz.dev>
 * @copyright 2020 WP EzPz
 * @license   GPL 2.0+
 * @link      https://wpezpzdev.com/
 */

namespace EZPZ_TWEAKS\Engine;

use Composer\Autoload\ClassLoader;
use Exception;
use EZPZ_TWEAKS\Engine;
use EZPZ_TWEAKS\Engine\cmb2\Type_Select2_Multiple;
use Throwable;
use function apply_filters;
use function array_diff;
use function array_keys;
use function do_action;
use function esc_html__;
use function is_array;
use function is_dir;
use function is_file;
use function scandir;
use function strlen;
use function strncmp;
use function strtolower;
use function substr;
use function substr_count;
use function wp_die;

/**
 * Plugin Name Initializer
 */
class Initialize {

	/**
	 * List of class to initialize.
	 *
	 * @var array
	 */
	public $classes = array();

	/**
	 * Instance of this W_Is_Methods.
	 *
	 * @var object
	 */
	protected $is = null;

	/**
	 * Composer autoload file list.
	 *
	 * @var ClassLoader
	 */
	private $composer;

	/**
	 * The Constructor that load the entry classes
	 *
	 * @param ClassLoader $composer Composer autoload output.
	 *
	 * @since 1.0.0
	 */
	public function __construct( ClassLoader $composer ) {
		$this->is       = new Engine\Is_Methods;
		$this->composer = $composer;

		$this->get_classes( 'Internals' );
		$this->get_classes( 'Integrations' );

		if ( $this->is->request( 'backend' ) ) {
			$this->get_classes( 'Backend' );
		}

		if ( $this->is->request( 'frontend' ) ) {
			$this->get_classes( 'Frontend' );
		}

		$this->load_classes();

		$this->register_cmb2_custom_types();
	}

	/**
	 * Based on the folder loads the classes automatically using the Composer autoload to detect the classes of a Namespace.
	 *
	 * @param string $namespace Class name to find.
	 *
	 * @return array Return the classes.
	 * @since 1.0.0
	 */
	private function get_classes( string $namespace ) {
		$prefix    = $this->composer->getPrefixesPsr4();
		$classmap  = $this->composer->getClassMap();
		$namespace = 'EZPZ_TWEAKS\\' . $namespace;

		// In case composer has autoload optimized
		if ( isset( $classmap['EZPZ_TWEAKS\\Engine\\Initialize'] ) ) {
			$classes = array_keys( $classmap );

			foreach ( $classes as $class ) {
				if ( 0 !== strncmp( (string) $class, $namespace, strlen( $namespace ) ) ) {
					continue;
				}

				$this->classes[] = $class;
			}

			return $this->classes;
		}

		$namespace .= '\\';

		// In case composer is not optimized
		if ( isset( $prefix[ $namespace ] ) ) {
			$folder    = $prefix[ $namespace ][0];
			$php_files = $this->scandir( $folder );
			$this->find_classes( $php_files, $folder, $namespace );

			if ( ! WP_DEBUG ) {
				wp_die( esc_html__( EZPZ_TWEAKS_NAME . ' is on production environment with missing `composer dumpautoload -o` that will improve the performance on autoloading itself.', EZPZ_TWEAKS_TEXTDOMAIN ) );
			}

			return $this->classes;
		}

		return $this->classes;
	}

	/**
	 * Get php files inside the folder/subfolder that will be loaded.
	 * This class is used only when Composer is not optimized.
	 *
	 * @param string $folder Path.
	 *
	 * @return array List of files.
	 * @since 1.0.0
	 */
	private function scandir( string $folder ) {
		$temp_files = scandir( $folder );
		$files      = array();

		if ( is_array( $temp_files ) ) {
			$files = $temp_files;
		}

		return array_diff( $files, array( '..', '.', 'index.php' ) );
	}

	/**
	 * Load namespace classes by files.
	 *
	 * @param array $php_files List of files with the Class.
	 * @param string $folder Path of the folder.
	 * @param string $base Namespace base.
	 *
	 * @since 1.0.0
	 */
	private function find_classes( array $php_files, string $folder, string $base ) {
		foreach ( $php_files as $php_file ) {
			$class_name = substr( $php_file, 0, - 4 );
			$path       = $folder . '/' . $php_file;

			if ( is_file( $path ) ) {
				$this->classes[] = $base . $class_name;

				continue;
			}

			// Verify the Namespace level
			if ( substr_count( $base . $class_name, '\\' ) < 2 ) {
				continue;
			}

			if ( ! is_dir( $path ) || strtolower( $php_file ) === $php_file ) {
				continue;
			}

			$sub_php_files = $this->scandir( $folder . '/' . $php_file );
			$this->find_classes( $sub_php_files, $folder . '/' . $php_file, $base . $php_file . '\\' );
		}
	}

	/**
	 * Initialize all the classes.
	 *
	 * @since 1.0.0
	 */
	private function load_classes() {
		$this->classes = apply_filters( 'ezpz-tweaks_classes_to_execute', $this->classes );

		foreach ( $this->classes as $class ) {
			try {
				$temp = new $class;
				$temp->initialize();
			} catch ( Throwable $err ) {
				do_action( 'ezpz-tweaks_initialize_failed', $err );

				if ( WP_DEBUG ) {
					throw new Exception( $err->getMessage() );
				}
			}
		}
	}

	private function register_cmb2_custom_types() {

		new Type_Select2_Multiple();
	}

}
