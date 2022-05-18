<?php

/**
 * EZPZ_TWEAKS
 *
 * @package   EZPZ_TWEAKS
 * @author    WP EzPz <info@wpezpz.dev>
 * @copyright 2022 WP EzPz
 * @license   GPL 3.0+
 * @link      https://wpezpzdev.com/
 */
namespace EZPZ_TWEAKS\Engine\Settings;

use CMB2;
use EZPZ_TWEAKS\Backend\Settings_Page;

class Settings {
//    private static $Pages;
    private static $Tabs;
    private static $Sections;
    private static $Fields;


    public static function add_tab( $page, $id, $title, $icon_url, $is_cmb2, $callback, $priority ): bool
	{
        $cmb2_instance = $is_cmb2 ? self::add_cmb2_box( $id ) : null;

        self::$Tabs[] = [
            'page' => $page,
            'id' => $id,
            'title' => $title,
            'icon_url' => $icon_url,
            'callback' => $callback,
            'is_cmb2' => $is_cmb2,
            'priority' => $priority,
            'cmb2_instance' => $cmb2_instance,
        ];

        return true;
    }

    public static function add_tabs( $tabs, $page = '' ): bool
	{
        foreach ( $tabs as $tab ) {
            if ( empty($tab['page'])) {
                $tab['page'] = $page;
            }

            self::add_tab( $tab['page'], $tab['id'], $tab['title'], $tab['icon_url'], $tab['is_cmb2'], $tab['callback'], $tab['priority'] );
        }

        return true;
    }

	public static function get_tabs( $page = false ): array {
        // get tabs by page
        if ($page) {
            $tabs = [];
            foreach ( self::$Tabs as $tab ) {
                if ($tab['page'] === $page) {
                    $tabs[] = $tab;
                }
            }
            return $tabs;
        }
        return self::$Tabs;
    }

    public static function get_tab( $id ) {
        foreach ( self::$Tabs as $tab ) {
            if ( $tab['id'] === $id ) {
                return $tab;
            }
        }

        return false;
    }

    public static function add_section( $page, $tab, $section_id, $title = '', $description = '', $is_cmb2 = false, $priority = 10 ): bool
	{
        $cmb2_instance = $is_cmb2 ? self::add_cmb2_box( $section_id ) : null;

        self::$Sections[] = [
            'id' => $section_id,
            'page' => $page,
            'tab' => $tab,
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'is_cmb2' => $is_cmb2,
            'cmb2_instance' => $cmb2_instance,
        ];

        return true;
    }

    public static function add_sections( $sections ): bool
	{
        foreach ( $sections as $section ) {
            self::add_section( $section['id'], $section['title'], $section['callback'], $section['page'], $section['tab'], $section['priority'] );
        }

        return true;
    }

    public static function get_sections($page = false, $tab = false) {
        $sections = self::$Sections;

        // early return if no sections
        if (empty($sections)) {
            return [];
        }
        if ( $page ) {
            $sections = array_filter( $sections, function( $field ) use ( $page ) {
                return $field['page'] === $page;
            });
        }
        if ( $tab ) {
            $sections = array_filter( $sections, function( $field ) use ( $tab ) {
                return $field['tab'] === $tab;
            });
        }

        return $sections;
    }

    public static function get_section($section_id = false) {
        $sections = self::$Sections;

        // early return if no sections
        if (empty($sections)) {
            return [];
        }

        if ($section_id) {
            foreach ($sections as $section) {
                if ($section['id'] === $section_id) {
                    return $section;
                }
            }
        }

        return $sections;
    }

    public static function add_field( $page, $tab, $section, $field_id, $title = '', $description = '', $callback = '', $only_callback = false, $cmb2_args = false ,$priority = 10 ): bool
	{
        self::$Fields[] = [
            'id' => $field_id,
            'page' => $page,
            'tab' => $tab,
            'section' => $section,
            'title' => $title,
            'description' => $description,
            'callback' => $callback,
            'only_callback' => $only_callback,
            'cmb2_args' => $cmb2_args,
            'priority' => $priority,
        ];

        return true;
    }

    public static function add_fields( $fields, $page = '', $tab = '', $section = '' ): bool
	{

        foreach ( $fields as $field ) {
            if ( ! isset( $field['page'] ) ) {
                $field['page'] = $page;
            }
            if ( ! isset( $field['tab'] ) ) {
                $field['tab'] = $tab;
            }
            if ( ! isset( $field['section'] ) ) {
                $field['section'] = $section;
            }
            if ( ! isset( $field['callback'] ) ) {
                $field['callback'] = false;
            }
            if ( ! isset( $field['only_callback'] ) ) {
                $field['only_callback'] = false;
            }
            if ( ! isset( $field['priority'] ) ) {
                $field['priority'] = 10;
            }
            if ( ! isset( $field['description'] ) ) {
                $field['description'] = '';
            }


            self::add_field( $field['page'], $field['tab'], $field['section'], $field['field_id'], $field['title'], $field['description'], $field['callback'], $field['only_callback'], $field['cmb2_args'], $field['priority'] );
        }

        return true;
    }

    public static function get_fields( $page = false, $tab = false, $section = false ): array
	{
        // early return all fields if no page, tab or section is set
        if ( ! $page && ! $tab && ! $section ) {
            return self::$Fields;
        }


        // filter fields by page, tab or section
        $fields = self::$Fields;
        if ( $page ) {
            $fields = array_filter( $fields, function( $field ) use ( $page ) {
                return $field['page'] === $page;
            });
        }
        if ( $tab ) {
            $fields = array_filter( $fields, function( $field ) use ( $tab ) {
                return $field['tab'] === $tab;
            });
        }
        if ( $section ) {
            $fields = array_filter( $fields, function( $field ) use ( $section ) {
                return $field['section'] === $section;
            });
        } else {
            // remove field if section['is_cmb2'] is set and equals true
            $fields = array_filter( $fields, function( $field ) {
                if ( !empty($field['section'])) {
                    $section = self::get_section($field['section']);
    
                    return ! (isset($section['is_cmb2']) && $section['is_cmb2'] === false);
                }
                return true;
            });
        }

        return $fields;
    }


    public static function get_cmb2_instance( $id, $section_id = false ) {
        if ($section_id) {
            $section = self::get_section($section_id);
            if (isset($section['cmb2_instance'])&& !empty($section['cmb2_instance'])) {
                return $section['cmb2_instance'];
            }
        }
        $tab = self::get_tab( $id );
        if ( $tab ) {
            return $tab['cmb2_instance'];
        }

        return false;
    }

    public static function add_cmb2_box( $tab ): CMB2
	{
		return new_cmb2_box([
			'id'         => EZPZ_TWEAKS_TEXTDOMAIN . '_options_' . $tab,
			'hookup'     => false,
			'show_names'   => true,
			'show_on'    => array(
				'key'    => 'options-page',
				'value'  => array( EZPZ_TWEAKS_TEXTDOMAIN . '-' . $tab )
			),
		]);
    }

    public static function add_cmb2_field( $tab, $field_args, $section_id = false ) {
        $cmb = (!$section_id) ? self::get_cmb2_instance( $tab ) : self::get_cmb2_instance( $tab, $section_id );

        return $cmb->add_field($field_args);
    }

    public static function add_group_field( $tab, $field, $section_id = false ) {
        $cmb = (!$section_id) ? self::get_cmb2_instance( $tab ) : self::get_cmb2_instance( $tab, $section_id );
        
        if (!is_object($cmb)) {
            return false;
        }
        $group_id = $field['group_id'];
        unset($field['group_id']);

        return $cmb->add_group_field($group_id, $field);
    }

    public static function get_first_tab( $page ){
        $tabs = self::get_tabs($page);
        if (empty($tabs)) {
            return false;
        }
        return $tabs[0];
    }

    public static function get_search_data() {
        $search_data = [];
		$all_fields = self::get_fields();
		foreach ($all_fields as $field) {
			$search_data[$field['id']] = [
				'id' => $field['id'],
				'title' => $field['title'],
				'description' => $field['description'],
			];

            if (isset($field['tab'])) {
                $search_data[$field['id']]['tab'] = $field['tab'];
            }
		}

        return $search_data;
    }
}
