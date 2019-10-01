<?php
/**
 * WHOI Directory API
 *
 * @wordpress-plugin
 * Plugin Name:       WHOI Directory API
 * Plugin URI:        https://directory.whoi.edu
 * Description:       Sets custom WP API endpoints for www.whoi.edu Directory functions
 * Version:           1.0.0
 * Author:            Ethan Andrews
 * Author URI:        https://www.whoi.edu
 * Text Domain:       whoi-directory-api
 */
namespace WHOI\UserAPI;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Search User Pod by form field
 *
 * @param array $data Options for the function.
 * @return array|null User list
 */
 add_action( 'rest_api_init', function () {
   register_rest_route( 'whoi_directory/v1', '/users/search/', array(
     'methods' => 'POST',
     'callback' => __NAMESPACE__ . '\search_users',
   ) );
 } );

function search_users( $data ) {

    $user_search_terms = $data['user_search_terms'];
    $user_search_terms = esc_sql(sanitize_text_field($user_search_terms));

    $search_dept = $data['search_dept'];
    $search_dept = esc_sql(sanitize_text_field($search_dept));

    // set up find parameters, where meta field matches $user_search_terms
    $params = array(
        'limit' => -1,
        'orderby' => 'last_name.meta_value ASC',
        'where' => 'name_search.meta_value Like "%' . $user_search_terms . '%" AND department.meta_value LIKE "%' . $search_dept . '%"'
     );

    //search in User pod
    $users = pods( 'user', $params );

    $export_data = array();

    if ( 0 < $users->total() ) {
        while ( $users->fetch() ) {
            if ( ! $users->field( 'privacy_flag' ) ) {
                // Get User Meta data for first/last name
                $all_meta_for_user = get_user_meta( $users->field( 'id' ) );

                $first_name = $all_meta_for_user['first_name'][0];
                $last_name = $all_meta_for_user['last_name'][0];

                $user_export = array(
                        'username' => $users->field( 'user_login' ),
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'hr_job_title' => $users->field( 'hr_job_title' ),
                        'department' => $users->field( 'department' ),
                        'building' => $users->field( 'building' )
                    );
                array_push($export_data, $user_export);
            }
        }

        return json_encode($export_data);

    } else {
        return null;
    }
}

/**
 * Search User Pod by username
 *
 * @param array $data username from query var
 * @return array|null Single User
 */
 add_action( 'rest_api_init', function () {
   register_rest_route( 'whoi_directory/v1', '/users/detail/(?P<username>\S+)/', array(
     'methods' => 'GET',
     'callback' => __NAMESPACE__ . '\get_user_by_username',
   ) );
 } );

function get_user_by_username( $data ) {

    $username = $data['username'];
    $username = esc_sql(sanitize_text_field($username));

    // set up find parameters, where meta field matches $user_search_terms
    $params = array(
        'limit' => 1,
        'where' => 't.user_login="' . $username . '"'
     );

    //search in User pod
    $users = pods( 'user', $params );

    $export_data = array();

    if ( 0 < $users->total() ) {
        while ( $users->fetch() ) {
            if ( ! $users->field( 'privacy_flag' ) ) {
                // Get User Meta data for first/last name
                $all_meta_for_user = get_user_meta( $users->field( 'id' ) );

                $first_name = $all_meta_for_user['first_name'][0];
                $last_name = $all_meta_for_user['last_name'][0];

                $user_export = array(
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'website' => $users->field( 'user_url' ),
                        'hr_job_title' => $users->field( 'hr_job_title' ),
                        'working_title' => $users->field( 'working_title' ),
                        'department' => $users->field( 'department' ),
                        'office_phone' => $users->field( 'office_phone' ),
                        'user_email' => $users->field( 'user_email' ),
                        'building' => $users->field( 'building' ),
                        'office' => $users->field( 'office' ),
                        'mail_stop' => $users->field( 'mail_stop' ),
                        'labgroup_site' => $users->field( 'labgroup_site' ),
                        'education' => $users->field( 'education' ),
                        'other_info' => $users->field( 'other_info' ),
                        'photo' => $users->field( 'photo' ),
                        'vita' => $users->field( 'vita' ),
                        'privacy_flag' => $users->field( 'privacy_flag' )
                    );
                array_push($export_data, $user_export);
            }
        }

        return json_encode($export_data);

    } else {
        return null;
    }
}

/**
 * Get all users by Department code
 *
 * @param array $data Deparmental Code
 * @return array|null User list
 */
 add_action( 'rest_api_init', function () {
   register_rest_route( 'whoi_directory/v1', '/users/department/(?P<id>\d+)', array(
     'methods' => 'GET',
     'callback' => __NAMESPACE__ . '\get_users_by_department',
     'args' => array(
       'id' => array(
         'validate_callback' => function($param, $request, $key) {
           return is_numeric( $param );
         }
       ),
     ),
   ) );
 } );

function get_users_by_department( $data ) {

    // set up find parameters, where meta field matches $user_search_terms
    $params = array(
        'limit' => -1,
        'orderby' => 'last_name.meta_value ASC',
        'where' => 'department_code.meta_value = "' . $data['id'] . '"'
     );

    //search in User pod
    $users = pods( 'user', $params );

    $export_data = array();

    if ( 0 < $users->total() ) {
        while ( $users->fetch() ) {

            if ( ! $users->field( 'privacy_flag' ) ) {
                // Get User Meta data for first/last name
                $all_meta_for_user = get_user_meta( $users->field( 'id' ) );

                $first_name = $all_meta_for_user['first_name'][0];
                $last_name = $all_meta_for_user['last_name'][0];

                $user_export = array(
                        'username' => $users->field( 'user_login' ),
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'hr_job_title' => $users->field( 'hr_job_title' ),
                        'department' => $users->field( 'department' ),
                        'building' => $users->field( 'building' )
                    );
                array_push($export_data, $user_export);
            }
        }

        return $export_data;

    } else {
        return null;
    }
}
