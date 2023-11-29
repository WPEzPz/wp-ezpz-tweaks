<?php

/**
 * EZPZ_TWEAKS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpzdev.com>
 * @copyright 2022 WP EzPz
 * @license   GPL 3.0+
 * @link      https://wpezpzdev.com/
 */
namespace EZPZ_TWEAKS\Engine\Backups;

use EZPZ_TWEAKS\Engine\Settings\Settings;

class Import_Export {

    public function add_options() {
        Settings::add_field(
            'wpezpz-tweaks',
            'import-export',
            'backups',
            'backups',
            __( 'Settings Backup', EZPZ_TWEAKS_TEXTDOMAIN ),
            __( 'Take a backup of your plugin settings in case you wish to restore them in future. Use it as backup before making substantial changes to WPEzPz Tweaks settings.', EZPZ_TWEAKS_TEXTDOMAIN ),
            array( $this, 'option_callback'),
            true,
            false,
            30
        );
    }

    public function option_callback() {
        $backups = get_option( 'wpezpz_backups', [] );
        ?>
        <div class="postbox ezpz_tweeks_backups">
            
            <h3 class="hndle"><?php esc_html_e( 'Settings Backup', EZPZ_TWEAKS_TEXTDOMAIN ); ?> <button type="button" class="button button-primary " data-action="createBackup"><?php esc_html_e( 'Create Backup', EZPZ_TWEAKS_TEXTDOMAIN ); ?></button></h3>

            <div class="inside">
                <p class="description"><?php esc_html_e( 'Take a backup of your plugin settings in case you wish to restore them in future. Use it as backup before making substantial changes to WPEzPz Tweaks settings.', EZPZ_TWEAKS_TEXTDOMAIN ); ?></p>

                <div class="ezpz-tweeks-settings-backup-form cmb2-form">
                    <div class="list-table with-action">
                        <table class="form-table">
                            <tbody>
                                <?php foreach ( $backups as $key => $backup ) : ?>
                                    <tr data-key="<?php echo esc_attr( $key ); ?>">
                                        <th>
                                            <?php
                                            /* translators: Snapshot formatted date */
                                            printf( esc_html__( 'Backup: %s', EZPZ_TWEAKS_TEXTDOMAIN ), date_i18n( 'M jS Y, H:i a', $key ) );
                                            ?>
                                        </th>
                                        <td style="width:195px;padding-left:0;">
                                            <button type="button" class="button button-secondary button-small ezpz-tweeks-action" data-action="restoreBackup" data-key="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Restore', EZPZ_TWEAKS_TEXTDOMAIN ); ?></button>
                                            <button type="button" class="button button-link-delete button-small ezpz-tweeks-action" data-action="deleteBackup" data-key="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Delete', EZPZ_TWEAKS_TEXTDOMAIN ); ?></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ( empty( $backups ) ) : ?>
                                    <tr class="hidden">
                                        <th>
                                        </th>
                                        <td style="width:195px;padding-left:0;">
                                            <button type="button" class="button button-primary ezpz-tweeks-action" data-action="restoreBackup" data-key=""><?php esc_html_e( 'Restore', EZPZ_TWEAKS_TEXTDOMAIN ); ?></button>
                                            <button type="button" class="button button-link-delete ezpz-tweeks-action" data-action="deleteBackup" data-key=""><?php esc_html_e( 'Delete', EZPZ_TWEAKS_TEXTDOMAIN ); ?></button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <p id="ezpz-tweeks-no-backup-message"<?php echo ! empty( $backups ) ? ' class="hidden"' : ''; ?>><?php esc_html_e( 'There is no backup.', EZPZ_TWEAKS_TEXTDOMAIN ); ?></p>

                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Related feature: Settings Backup
     * Register AJAX for create, delete or restore a backup file
     */
    public function register_ajax() {
        add_action( 'wp_ajax_ezpz_tweaks_create_backup', array( $this, 'create_backup') );
        add_action( 'wp_ajax_ezpz_tweaks_delete_backup', array( $this, 'delete_backup') );
        add_action( 'wp_ajax_ezpz_tweaks_restore_backup', array( $this, 'restore_backup') );

        return $this;
    }

    /**
     * helper method for ajax calls
     */
    private function error( $message = '' ) {
        if ( empty( $message ) ) {
            $message = __( 'An error occurred.', EZPZ_TWEAKS_TEXTDOMAIN );
        }

        wp_send_json_error( array(
            'message' => $message,
        ) );
        wp_die();
    }

    /**
     * helper method for ajax calls
     */
    private function success( $data = '' ) {
        wp_send_json_success( $data );
        wp_die();
    }

    /**
     * helper method for ajax calls
     */
    private function checking_nonce( $nonce_key ) {
        if ( ! check_ajax_referer( $nonce_key, 'security', false ) ) {
            
            wp_send_json_error( __('Invalid security token sent.', EZPZ_TWEAKS_TEXTDOMAIN), 403 );
            wp_die();

        }
    }

    /**
     * import data from a backup file
     */
    private function do_import_data( array $data ) {
        if ( !\current_user_can( 'manage_options' ) ) {
			return false;
		}

        update_option( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding', $data[ 0 ] );
        update_option( EZPZ_TWEAKS_TEXTDOMAIN . '-performance', $data[ 1 ] );
        update_option( EZPZ_TWEAKS_TEXTDOMAIN . '-security', $data[ 2 ] );

		return true;
	}

    /**
     * Store Backup
     */
    public function run_backup( $action = 'add', $key = null ) {
		$backups = get_option( 'wpezpz_backups', [] );

		// Restore.
		if ( 'restore' === $action ) {
			if ( ! isset( $backups[ $key ] ) ) {
                new \WP_Error(
                    'ezpz-tweaks_import_settings_failed',
                    __( 'Failed to import the settings.', EZPZ_TWEAKS_TEXTDOMAIN )
                );
				return false;
			}

			$this->do_import_data( $backups[ $key ], true );

			return true;
		}

		// Add.
		if ( 'add' === $action ) {
			$key     = current_time( 'U' );
			$backups = [ $key => $this->get_export_data() ] + $backups;
		}

		// Delete.
		if ( 'delete' === $action && isset( $backups[ $key ] ) ) {
			unset( $backups[ $key ] );
		}

		update_option( 'wpezpz_backups', $backups, false );

		return $key;
	}

    public function get_export_data() {
        $data = [
            0 => get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-customizing-branding', [] ),
            1 => get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-performance', [] ),
            2 => get_option( EZPZ_TWEAKS_TEXTDOMAIN . '-security', [] ),
        ];

        return $data;
    }

    public function create_backup() {
		$this->checking_nonce( 'ezpz-nonce' );

		$key = $this->run_backup();
		if ( is_null( $key ) ) {
			$this->error( esc_html__( 'Unable to create backup this time.', EZPZ_TWEAKS_TEXTDOMAIN ) );
		}

		$this->success(
			[
				'key'     => $key,
				/* translators: Backup formatted date */
				'backup'  => sprintf( esc_html__( 'Backup: %s', EZPZ_TWEAKS_TEXTDOMAIN ), date_i18n( 'M jS Y, H:i a', $key ) ),
				'message' => esc_html__( 'Backup created successfully.', EZPZ_TWEAKS_TEXTDOMAIN ),
			]
		);
	}

    public function delete_backup() {
        $this->checking_nonce( 'ezpz-nonce' );

        $key = sanitize_text_field( $_GET['key'] );


        $key = $this->run_backup( 'delete', $key );
        if ( is_null( $key ) ) {
			$this->error( esc_html__( 'Unable to remove backup this time.', EZPZ_TWEAKS_TEXTDOMAIN ) );
		}

        $this->success(
			[
				'key'     => $key,
				'message' => esc_html__( 'Backup removed successfully.', EZPZ_TWEAKS_TEXTDOMAIN ),
			]
		);
    }

    public function restore_backup() {
        $this->checking_nonce( 'ezpz-nonce' );

        $key = sanitize_text_field( $_GET['key'] );

        $key = $this->run_backup( 'restore', $key );
        if ( is_null( $key ) ) {
            $this->error( esc_html__( 'Unable to restore backup this time.', EZPZ_TWEAKS_TEXTDOMAIN ) );
        }

        $this->success(
            [
                'key'     => $key,
                'message' => esc_html__( 'Backup restored successfully.', EZPZ_TWEAKS_TEXTDOMAIN ),
            ]
        );
    }

}