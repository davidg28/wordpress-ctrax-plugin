<?php

namespace C_Trax_Integration\Includes;

/**
 * Installer class handles all of the database and Wordpress option data.
 * If there are upgrades to the tables or data, it is all done here.
 * @author Keith Andrews
 */
class Installer {

	private $settings;
	private $sql_storage;

	public function __construct() {
		$this->settings    = ( ! get_option( C_TRAX_INTEGRATION_OPTIONS ) ) ? new \stdClass() : maybe_unserialize( get_option( C_TRAX_INTEGRATION_OPTIONS ) );
		$this->sql_storage = C_TRAX_INTEGRATION_PATH . 'database/sql/';
	}

	/**
	 * Install the SQL scripts depending on database version
	 * @throws \Exception
	 */
	public function install() {
		// Run the initial SQL code and set the settings
		if ( ! isset( $this->settings->db_version ) ) {
			$version = $this->update();
			if ( $version !== false ) {
				$this->add_settings( $version );
			} else {
				throw new \Exception( 'Could not run SQL install script properly.', 0001 );
			}
		}
	}

	/**
	 * Update method to process all of the sql scripts against the database
	 * @return bool|int
	 * @throws \Exception
	 */
	public function update() {
		$result = false;
		$files  = [];
		if ( ! file_exists( $this->sql_storage ) ) {
			return null;
		}

		$dirFiles = scandir( $this->sql_storage );
		if ( is_array( $dirFiles ) ) {
			$files = array_diff(
				$dirFiles,
				[ '..', '.' ]
			);
		}

		// Run through if files are there
		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				$version = (int) str_replace( '.sql', '', $file );
				if ( $file && ! isset( $this->settings->db_version ) || (int) $this->settings->db_version < $version ) {
					if ( ! isset( $this->settings->db_version ) ) {
						$this->settings->db_version = 0;
					}

					$result = $this->run_sql_script( $file );
					if ( ! empty( $result ) ) {
						$this->update_settings( [ 'db_version' => $version ] );
						$result = $version;
					} else {
						throw new \Exception( 'Could not run SQL update script (' . $file . ') properly. Database remains at version ' . $this->settings->db_version . '. ', 0001 );
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Check to see if the plugin has a database update
	 * @return bool
	 */
	public function has_update() {
		$has   = false;
		$files = [];
		if ( ! file_exists( $this->sql_storage ) ) {
			return null;
		}

		$dirFiles = scandir( $this->sql_storage );
		if ( is_array( $dirFiles ) ) {
			$files = array_diff(
				$dirFiles,
				[ '..', '.' ]
			);
		}

		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				$version = (int) str_replace( '.sql', '', $file );
				if ( (int) $this->settings->db_version < $version ) {
					$has = true;
					break;
				}
			}
		}

		return $has;
	}

	/**
	 * Insert the settings for the plugin into the wp_options table
	 *
	 * @param $version
	 */
	public function add_settings( $version ) {
		$this->settings->db_version = $version;

		add_option( C_TRAX_INTEGRATION_OPTIONS, $this->settings );
	}

	/**
	 * Update settings based on the array passed
	 *
	 * @param  array  $settings
	 */
	public function update_settings( $settings = [] ) {
		if ( count( $settings ) > 0 ) {
			foreach ( $settings as $key => $value ) {
				$this->settings->{$key} = $value;
			}
		}

		\update_option( C_TRAX_INTEGRATION_OPTIONS, $this->settings );
		$this->settings = get_option( C_TRAX_INTEGRATION_OPTIONS );
	}

	/**
	 * Run the SQL script passed through
	 * Replace {WPDB_PREFIX} in the sql script with the actual wordpress db prefix
	 *
	 * @param  string  $sqlFile
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function run_sql_script( $sqlFile ) {
		global $wpdb;
		$wpdb->hide_errors();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$arr      = [];
		$sql      = '';
		$contents = fopen( $this->sql_storage . $sqlFile, "r" );
		$prefix   = $wpdb->prefix . 'c_trax_integration_';

		// Loop through each line of the SQL file
		while( ( $line = fgets( $contents ) ) !== false ) {
			// Replace wordpress and plugin prefix
			$sql .= str_replace( [ '{WPDB_PREFIX}', '{WP_PREFIX}' ], [ $prefix, $wpdb->prefix ], $line );

			if ( strpos( $line, ';' ) !== false ) {
				$result = $wpdb->query( $sql );

				if ( $result === false ) {
					throw new \Exception( 'Error running SQL file, ' . $sqlFile . ': ' . $wpdb->last_error . '<br/>' . $wpdb->print_error() );
				} else {
					$arr[] = $result;
				}
				$sql = '';
			}
		}

		fclose( $contents );
		flush();

		if ( ob_get_length() ) {
			ob_clean();
		}

		return $arr;
	}
}

?>