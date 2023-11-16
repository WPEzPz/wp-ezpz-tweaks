<?php
/**
 * EZPZ_TWEAKS
 * Related feature: Hide user in admin panel
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpzdev.com>
 * @license   GPL 2.0+
 * @link      https://wpezpzdev.com/
 */

namespace EZPZ_TWEAKS\Engine\Features;

class Hidden_Users {
    /**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function initialize() {

        add_action( 'pre_user_query',array( $this, 'ezpz_pre_user_query') );
		add_action( 'views_users',array( $this, 'reset_count_of_visible_users') );

	}

	/**
	 * Related feature: Hide user in admin panel
	 * Change query for hiding users if user is not in EZPZ-setting
	 */
	function ezpz_pre_user_query($user_search) {
		global $pagenow;
		if (!isset($this->security_option['hide_user_in_admin']) || empty($this->security_option['hide_user_in_admin'])) {
			return $user_search;
		}
		$userids = $this->security_option['hide_user_in_admin'];

		// if current user is hidden, do nothing
		$current_user_id = get_current_user_id();
		if (in_array($current_user_id, $userids)) {
			return $user_search;
		}

		// if user is not in EZPZ-setting, Hide the hidden users
		if ( !empty($userids) && !( ( $pagenow == 'admin.php' ) && ( sanitize_text_field($_GET['page']) == 'wpezpz-tweaks')) ) {
			global $wpdb;
			$query = '';

			foreach ($userids as $userid) {
				$query .= " AND {$wpdb->users}.ID != '$userid'";
			}

			$user_search->query_where = str_replace( 'WHERE 1=1', "WHERE 1=1$query", $user_search->query_where );
		}
	}


	/**
	 * Related feature: Hide user in admin panel
	 * Re-Count the users after hide some users
	 */
	function reset_count_of_visible_users($views){
		if (!isset($this->security_option['hide_user_in_admin']) || empty($this->security_option['hide_user_in_admin'])) {
			return $views;
		}
		$hidden_userids = $this->security_option['hide_user_in_admin'];
		$hidden_userids_count = count($hidden_userids);

		// if current user is hidden, do nothing
		$current_user_id = get_current_user_id();
		if (in_array($current_user_id, $hidden_userids)) {
			return $views;
		}

		if ( $hidden_userids_count > 0 ) {
			
			$users = count_users();
			$avail_roles = array_keys($users['avail_roles']);
			
			foreach ($hidden_userids as $hidden_userid) {
				$hidden_user_roles = implode(', ', \get_userdata( $hidden_userid )->roles);

				foreach ($avail_roles as $role) {
					if ( $hidden_user_roles == $role ) {
						$users['avail_roles'][$role]--;
					}
				}
			}

			
			foreach($views as $key => $view) {
				if ($key == 'all') {
					$count = $users['total_users'] - $hidden_userids_count;
				} else {
					$count = $users['avail_roles'][$key];
				}

				if ($count == 0) {
					unset($views[$key]);
					break;
				}
				$views[$key] = preg_replace('/\d+/', $count, $views[$key]);
			}
		}

		return $views;
	}
}