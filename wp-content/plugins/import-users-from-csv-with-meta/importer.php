<?php
if (!defined('ABSPATH')) {
  exit();
}

function acui_import_users(
  $file,
  $form_data,
  $attach_id = 0,
  $is_cron = false,
  $is_frontend = false
) {
  if (!function_exists('get_editable_roles')) {
    require_once ABSPATH . 'wp-admin/includes/user.php';
  } ?>
	<div class="wrap">
		<h2><?php echo apply_filters(
    'acui_log_main_title',
    __('Importing users', 'import-users-from-csv-with-meta')
  ); ?></h2>
		<?php
  set_time_limit(0);

  do_action('before_acui_import_users');

  global $wpdb;
  $wp_users_fields = acui_get_wp_users_fields();
  $acui_not_meta_fields = acui_get_not_meta_fields();
  $acui_restricted_fields = acui_get_restricted_fields();
  $all_roles = array_keys(wp_roles()->roles);
  $editable_roles = array_keys(get_editable_roles());

  if (is_plugin_active('wp-access-areas/wp-access-areas.php')) {
    $wpaa_labels = WPAA_AccessArea::get_available_userlabels();
  }

  $buddypress_fields = [];

  if (
    is_plugin_active('buddypress/bp-loader.php') ||
    class_exists('BP_XProfile_Group')
  ) {
    $profile_groups = BP_XProfile_Group::get(['fetch_fields' => true]);

    if (!empty($profile_groups)) {
      foreach ($profile_groups as $profile_group) {
        if (!empty($profile_group->fields)) {
          foreach ($profile_group->fields as $field) {
            $buddypress_fields[] = $field->name;
            $buddypress_types[] = $field->type;
          }
        }
      }
    }
  }

  $users_registered = [];
  $headers = [];
  $headers_filtered = [];
  $is_backend = !$is_frontend && !$is_cron;
  $update_existing_users = isset($form_data['update_existing_users'])
    ? sanitize_text_field($form_data['update_existing_users'])
    : '';

  $role_default = isset($form_data['role']) ? $form_data['role'] : [''];
  if (!is_array($role_default)) {
    $role_default = [$role_default];
  }
  array_walk($role_default, 'sanitize_text_field');

  $update_roles_existing_users = isset(
    $form_data['update_roles_existing_users']
  )
    ? sanitize_text_field($form_data['update_roles_existing_users'])
    : '';
  $empty_cell_action = isset($form_data['empty_cell_action'])
    ? sanitize_text_field($form_data['empty_cell_action'])
    : '';
  $delete_users = isset($form_data['delete_users'])
    ? sanitize_text_field($form_data['delete_users'])
    : '';
  $delete_users_assign_posts = isset($form_data['delete_users_assign_posts'])
    ? sanitize_text_field($form_data['delete_users_assign_posts'])
    : '';
  $delete_users_only_specified_role = isset(
    $form_data['delete_users_only_specified_role']
  )
    ? sanitize_text_field($form_data['delete_users_only_specified_role'])
    : false;

  $change_role_not_present = isset($form_data['change_role_not_present'])
    ? sanitize_text_field($form_data['change_role_not_present'])
    : '';
  $change_role_not_present_role = isset(
    $form_data['change_role_not_present_role']
  )
    ? sanitize_text_field($form_data['change_role_not_present_role'])
    : '';
  $activate_users_wp_members =
    !isset($form_data['activate_users_wp_members']) ||
    empty($form_data['activate_users_wp_members'])
      ? 'no_activate'
      : sanitize_text_field($form_data['activate_users_wp_members']);

  if ($is_cron) {
    $allow_multiple_accounts =
      get_option('acui_cron_allow_multiple_accounts') == 'allowed'
        ? 'allowed'
        : 'not_allowed';
  } else {
    $allow_multiple_accounts = empty($form_data['allow_multiple_accounts'])
      ? 'not_allowed'
      : sanitize_text_field($form_data['allow_multiple_accounts']);
  }

  $approve_users_new_user_approve = empty(
    $form_data['approve_users_new_user_appove']
  )
    ? 'no_approve'
    : sanitize_text_field($form_data['approve_users_new_user_appove']);

  update_option(
    'acui_manually_send_mail',
    isset($form_data['sends_email']) && $form_data['sends_email'] == 'yes'
  );
  update_option(
    'acui_manually_send_mail_updated',
    isset($form_data['send_email_updated']) &&
      $form_data['send_email_updated'] == 'yes'
  );

  // disable WordPress default emails if this must be disabled
  if (!get_option('acui_automatic_wordpress_email')) {
    add_filter(
      'send_email_change_email',
      function () {
        return false;
      },
      999
    );
    add_filter(
      'send_password_change_email',
      function () {
        return false;
      },
      999
    );
  }

  // action
  echo apply_filters(
    'acui_log_header',
    '<h3>' .
      __('Ready to registers', 'import-users-from-csv-with-meta') .
      '</h3>'
  );
  echo apply_filters(
    'acui_log_header_first_row_explanation',
    '<p>' .
      __(
        'First row represents the form of sheet',
        'import-users-from-csv-with-meta'
      ) .
      '</p>'
  );

  $row = 0;
  $positions = [];
  $error_importing = false;

  ini_set('auto_detect_line_endings', true);

  $delimiter = acui_detect_delimiter($file);

  $manager = new SplFileObject($file);
  while ($data = $manager->fgetcsv($delimiter)):
    if (empty($data[0])) {
      continue;
    }

    if (count($data) == 1) {
      $data = $data[0];
    }

    if (!is_array($data)) {
      echo apply_filters(
        'acui_message_csv_file_bad_formed',
        __(
          'CSV file seems to be bad formed. Please use LibreOffice to create and manage CSV to be sure the format is correct',
          'import-users-from-csv-with-meta'
        )
      );
      break;
    }

    foreach ($data as $key => $value) {
      $data[$key] = preg_replace(
        '/<script\b[^>]*>(.*?)<\/script>/is',
        '',
        trim($value)
      );
    }

    for ($i = 0; $i < count($data); $i++) {
      $data[$i] = acui_string_conversion($data[$i]);

      if (is_serialized($data[$i])) {
        // serialized
        $data[$i] = maybe_unserialize($data[$i]);
      } elseif (strpos($data[$i], '::') !== false) {
        // list of items
        $data[$i] = explode('::', $data[$i]);
      }
    }

    if ($row == 0):

      $data = apply_filters('pre_acui_import_header', $data);

      // check min columns username - email
      if (count($data) < 2) {
        echo "<div id='message' class='error'>" .
          __(
            'File must contain at least 2 columns: username and email',
            'import-users-from-csv-with-meta'
          ) .
          '</div>';
        break;
      }

      $i = 0;
      $password_position = false;
      $id_position = false;

      foreach ($acui_restricted_fields as $acui_restricted_field) {
        $positions[$acui_restricted_field] = false;
      }

      foreach ($data as $element) {
        $headers[] = $element;

        if (in_array(strtolower($element), $acui_restricted_fields)) {
          $positions[strtolower($element)] = $i;
        }

        if (
          !in_array(strtolower($element), $acui_restricted_fields) &&
          !in_array($element, $buddypress_fields)
        ) {
          $headers_filtered[] = $element;
        }

        $i++;
      }

      $columns = count($data);

      update_option('acui_columns', $headers_filtered);
      ?>
					<style type="text/css">
						.wrap{
							overflow-x:auto!important;
						}

						.wrap table{
							min-width:800px!important;
						}

						.wrap table th,
						.wrap table td{
							width:200px!important;
						}
					</style>
					<h3><?php echo apply_filters(
       'acui_log_inserting_updating_data_title',
       __('Inserting and updating data', 'import-users-from-csv-with-meta')
     ); ?></h3>
					<table>
						<tr>
							<th><?php _e('Row', 'import-users-from-csv-with-meta'); ?></th>
							<?php foreach ($headers as $element):
         echo '<th>' . $element . '</th>';
       endforeach; ?>
							<?php do_action('acui_header_table_extra_rows'); ?>
						</tr>
					<?php $row++; // if number of columns is not the same that columns in header // users only are able to import users with a role they are allowed to edit // if user have used id

     // we check if username is the same than in row // if email is invalid // if email is blank // if user exists, we take his ID by login, we will update his mail if it has changed // if the email is registered, we take the user from this and we don't allow repeated emails // if the email is registered and repeated emails are allowed
     // if user is new, but the password in csv is empty, generate a password for this user
     // if user is new, but the password in csv is empty, generate a password for this user // in case the user is generating errors after this checks

     // Multisite add user to current blog

     // WP Members activation

     // New User Approve // passwords -> continue // hashed pass // wp_user data // wp-access-areas // buddypress // buddypress group // buddypress member_type // wp_usermeta data

     // wordpress default user created and edited emails

     // send mail

     /**
      * **********************************************************
      * update user_id if exist on CSV
      * **********************************************************
      */
     /************************************************************** */

     //

     // update
    else:
      $data = apply_filters(
        'pre_acui_import_single_user_data',
        $data,
        $headers
      );

      if (count($data) != $columns):
        echo '<script>alert("' .
          __('Row number', 'import-users-from-csv-with-meta') .
          " $row " .
          __(
            'does not have the same columns than the header, we are going to skip',
            'import-users-from-csv-with-meta'
          ) .
          '");</script>';
        $error_importing = true;
        continue;
      endif;

      do_action('pre_acui_import_single_user', $headers, $data);
      $data = apply_filters(
        'pre_acui_import_single_user_data',
        $data,
        $headers
      );

      $username = $data[0];
      $email = $data[1];
      $csv_user_id = $data[2];
      $user_id = 0;
      $problematic_row = false;
      $password_position = $positions['password'];
      $password = '';
      $role_position = $positions['role'];
      $role = '';
      $id_position = $positions['id'];

      if (!empty($id_position)) {
        $id = $data[$id_position];
      } else {
        $id = '';
      }

      $created = true;

      if ($password_position === false) {
        $password = wp_generate_password(
          apply_filters('acui_auto_password_length', 12),
          apply_filters('acui_auto_password_special_chars', true),
          apply_filters('acui_auto_password_extra_special_chars', false)
        );
      } else {
        $password = $data[$password_position];
      }

      if ($role_position === false) {
        $role = $role_default;
      } else {
        $roles_cells = explode(',', $data[$role_position]);

        if (!is_array($roles_cells)) {
          $roles_cells = [$roles_cells];
        }

        array_walk($roles_cells, 'trim');
        $role = $roles_cells;
      }

      if (!empty(array_diff($role, $all_roles))) {
        $roles_printable = implode(', ', $role);
        $error_string = sprintf(
          __(
            'Some of the next roles "%s" does not exists',
            'import-users-from-csv-with-meta'
          ),
          $roles_printable
        );
        echo '<script>alert("' .
          __('Problems with user 379: ', 'import-users-from-csv-with-meta') .
          $username .
          __(
            ', we are going to skip. \r\nError:  ',
            'import-users-from-csv-with-meta'
          ) .
          $error_string .
          '");</script>';
        $created = false;
        continue;
      }

      if (!empty(array_diff($role, $editable_roles))) {
        $roles_printable = implode(', ', $role);
        $error_string = sprintf(
          __(
            'You do not have permission to assign some of the next roles "%s"',
            'import-users-from-csv-with-meta'
          ),
          $roles_printable
        );
        echo '<script>alert("' .
          __('Problems with user 401: ', 'import-users-from-csv-with-meta') .
          $username .
          __(
            ', we are going to skip. \r\nError:  ',
            'import-users-from-csv-with-meta'
          ) .
          $error_string .
          '");</script>';
        $created = false;
        continue;
      }

      if (!empty($id)) {
        if (acui_user_id_exists($id)) {
          if ($update_existing_users == 'no') {
            continue;
          }

          $user = get_user_by('ID', $id);

          if ($user->user_login == $username) {
            $user_id = $id;

            if ($password !== '') {
              wp_set_password($password, $user_id);
            }

            if (!empty($email)) {
              $updateEmailArgs = [
                'ID' => $user_id,
                'user_email' => $email,
              ];
              wp_update_user($updateEmailArgs);
            }

            $created = false;
          } else {
            echo '<script>alert("' .
              __('Problems with ID', 'import-users-from-csv-with-meta') .
              ": $id , " .
              __(
                'username is not the same in the CSV and in database, we are going to skip.',
                'import-users-from-csv-with-meta'
              ) .
              '");</script>';
            continue;
          }
        } else {
          $userdata = [
            'ID' => $id,
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
          ];

          $user_id = wp_insert_user($userdata);
          $created = true;
        }
      } elseif (!empty($email) && sanitize_email($email) == '') {
        $problematic_row = true;
        $error_importing = true;
        $data[0] =
          __('Invalid EMail', 'import-users-from-csv-with-meta') . " ($email)";
      } elseif (empty($email)) {
        $problematic_row = true;
        $error_importing = true;
        $data[0] = __('EMail not specified', 'import-users-from-csv-with-meta');
      } elseif (username_exists($username)) {
        if ($update_existing_users == 'no') {
          continue;
        }

        $user_object = get_user_by('login', $username);
        $user_id = $user_object->ID;

        if ($password !== '') {
          wp_set_password($password, $user_id);
        }

        if (!empty($email)) {
          $updateEmailArgs = [
            'ID' => $user_id,
            'user_email' => $email,
          ];

          $update_email_result = wp_update_user($updateEmailArgs);

          if (is_wp_error($update_email_result)) {
            $problematic_row = true;
            $error_importing = true;
            $data[0] = $update_email_result->get_error_message();
          }
        }

        $created = false;
      } elseif (
        email_exists($email) &&
        $allow_multiple_accounts == 'not_allowed'
      ) {
        if ($update_existing_users == 'no') {
          continue;
        }

        $user_object = get_user_by('email', $email);
        $user_id = $user_object->ID;

        $data[0] =
          __('User already exists as:', 'import-users-from-csv-with-meta') .
          $user_object->user_login .
          '<br/>' .
          __(
            '(in this CSV file is called:',
            'import-users-from-csv-with-meta'
          ) .
          $username .
          ')';
        $problematic_row = true;
        $error_importing = true;

        if ($password !== '') {
          wp_set_password($password, $user_id);
        }

        $created = false;
      } elseif (email_exists($email) && $allow_multiple_accounts == 'allowed') {
        if ($password === '') {
          $password = wp_generate_password(
            apply_filters('acui_auto_password_length', 12),
            apply_filters('acui_auto_password_special_chars', true),
            apply_filters('acui_auto_password_extra_special_chars', false)
          );
        }
        $hacked_email = acui_hack_email($email);
        $user_id = wp_create_user($username, $password, $hacked_email);
        acui_hack_restore_remapped_email_address($user_id, $email);
      } else {
        if ($password === '') {
          $password = wp_generate_password(
            apply_filters('acui_auto_password_length', 12),
            apply_filters('acui_auto_password_special_chars', true),
            apply_filters('acui_auto_password_extra_special_chars', false)
          );
        }

        $user_id = wp_create_user($username, $password, $email);
      }

      $wpdb->update(
        $wpdb->prefix . 'users',
        [
          'ID' => $csv_user_id,
        ],
        ['ID' => $user_id]
      );
      $user_id = $csv_user_id;

      if (is_wp_error($user_id)) {
        $error_string = $user_id->get_error_message();
        echo '<script>alert("' .
          __('Problems with user 553:', 'import-users-from-csv-with-meta') .
          $username .
          __(
            ', we are going to skip. \r\nError: ',
            'import-users-from-csv-with-meta'
          ) .
          $error_string .
          '");</script>';
        $error_importing = true;
        continue;
      }

      $users_registered[] = $user_id;
      $user_object = new WP_User($user_id);

      if ($created || $update_roles_existing_users != 'no') {
        if (
          !(
            in_array('administrator', acui_get_roles($user_id), false) ||
            (is_multisite() && is_super_admin($user_id))
          )
        ) {
          if ($update_roles_existing_users == 'yes' || $created) {
            $default_roles = $user_object->roles;
            foreach ($default_roles as $default_role) {
              $user_object->remove_role($default_role);
            }
          }

          if (
            $update_roles_existing_users == 'yes' ||
            $update_roles_existing_users == 'yes_no_override' ||
            $created
          ) {
            if (!empty($role)) {
              if (is_array($role)) {
                foreach ($role as $single_role) {
                  $user_object->add_role($single_role);
                }
              } else {
                $user_object->add_role($role);
              }
            }

            $invalid_roles = [];
            if (!empty($role)) {
              if (!is_array($role)) {
                $role_tmp = $role;
                $role = [];
                $role[] = $role_tmp;
              }

              foreach ($role as $single_role) {
                $single_role = strtolower($single_role);
                if (get_role($single_role)) {
                  $user_object->add_role($single_role);
                } else {
                  $invalid_roles[] = trim($single_role);
                }
              }
            }

            if (!empty($invalid_roles)) {
              $problematic_row = true;
              $error_importing = true;
              if (count($invalid_roles) == 1) {
                $data[0] =
                  __('Invalid role', 'import-users-from-csv-with-meta') .
                  ' (' .
                  reset($invalid_roles) .
                  ')';
              } else {
                $data[0] =
                  __('Invalid roles', 'import-users-from-csv-with-meta') .
                  ' (' .
                  implode(', ', $invalid_roles) .
                  ')';
              }
            }
          }
        }
      }

      if (
        is_multisite() &&
        ($created || $update_roles_existing_users != 'no')
      ) {
        if (!empty($role)) {
          if (is_array($role)) {
            foreach ($role as $single_role) {
              add_user_to_blog(get_current_blog_id(), $user_id, $single_role);
            }
          } else {
            add_user_to_blog(get_current_blog_id(), $user_id, $role);
          }
        }
      }

      if ($activate_users_wp_members == 'activate') {
        update_user_meta($user_id, 'active', true);
      }

      if ($approve_users_new_user_approve == 'approve') {
        update_user_meta($user_id, 'pw_user_status', 'approved');
      } else {
        update_user_meta($user_id, 'pending', true);
      }

      if ($columns > 2) {
        for ($i = 2; $i < $columns; $i++):
          $data[$i] = apply_filters(
            'pre_acui_import_single_user_single_data',
            $data[$i],
            $headers[$i],
            $i
          );

          if (!empty($data)) {
            if (strtolower($headers[$i]) == 'password') {
              continue;
            } elseif (strtolower($headers[$i]) == 'user_pass') {
              $wpdb->update(
                $wpdb->users,
                ['user_pass' => wp_slash($data[$i])],
                ['ID' => $user_id]
              );
              wp_cache_delete($user_id, 'users');
              continue;
            } elseif (in_array($headers[$i], $wp_users_fields)) {
              if ($data[$i] === '' && $empty_cell_action == 'leave') {
                continue;
              } else {
                wp_update_user(['ID' => $user_id, $headers[$i] => $data[$i]]);
                continue;
              }
            } elseif (
              strtolower($headers[$i]) == 'wp-access-areas' &&
              is_plugin_active('wp-access-areas/wp-access-areas.php')
            ) {
              $active_labels = array_map('trim', explode('#', $data[$i]));

              foreach ($wpaa_labels as $wpa_label) {
                if (in_array($wpa_label->cap_title, $active_labels)) {
                  acui_set_cap_for_user(
                    $wpa_label->capability,
                    $user_object,
                    true
                  );
                } else {
                  acui_set_cap_for_user(
                    $wpa_label->capability,
                    $user_object,
                    false
                  );
                }
              }

              continue;
            } elseif (
              ($bpf_pos = array_search($headers[$i], $buddypress_fields)) !==
              false
            ) {
              switch ($buddypress_types[$bpf_pos]) {
                case 'datebox':
                  $date = $data[$i];
                  switch (true) {
                    case is_numeric($date):
                      $UNIX_DATE = ($date - 25569) * 86400;
                      $datebox = gmdate('Y-m-d H:i:s', $UNIX_DATE);
                      break;
                    case preg_match(
                      '/(\d{1,2})[\/-](\d{1,2})[\/-]([4567890]{1}\d{1})/',
                      $date,
                      $match
                    ):
                      $match[3] = '19' . $match[3];
                    case preg_match(
                      '/(\d{1,2})[\/-](\d{1,2})[\/-](20[4567890]{1}\d{1})/',
                      $date,
                      $match
                    ):
                    case preg_match(
                      '/(\d{1,2})[\/-](\d{1,2})[\/-](19[4567890]{1}\d{1})/',
                      $date,
                      $match
                    ):
                      $datebox = $match[3] . '-' . $match[2] . '-' . $match[1];
                      break;

                    default:
                      $datebox = $date;
                  }

                  $datebox = strtotime($datebox);
                  xprofile_set_field_data(
                    $headers[$i],
                    $user_id,
                    date('Y-m-d H:i:s', $datebox)
                  );
                  unset($datebox);
                  break;
                default:
                  xprofile_set_field_data($headers[$i], $user_id, $data[$i]);
              }

              continue;
            } elseif ($headers[$i] == 'bp_group') {
              $groups = explode(',', $data[$i]);
              $groups_role = explode(',', $data[$positions['bp_group_role']]);

              for ($j = 0; $j < count($groups); $j++) {
                $group_id = BP_Groups_Group::group_exists($groups[$j]);

                if (!empty($group_id)) {
                  groups_join_group($group_id, $user_id);

                  if ($groups_role[$j] == 'Moderator') {
                    groups_promote_member($user_id, $group_id, 'mod');
                  } elseif ($groups_role[$j] == 'Administrator') {
                    groups_promote_member($user_id, $group_id, 'admin');
                  }
                }
              }

              continue;
            } elseif ($headers[$i] == 'member_type') {
              bp_set_member_type($user_id, $data[$i]);
              continue;
            } elseif ($headers[$i] == 'bp_group_role') {
              continue;
            } elseif (in_array($headers[$i], $acui_not_meta_fields)) {
              continue;
            } else {
              if ($data[$i] === '') {
                if ($empty_cell_action == 'delete') {
                  delete_user_meta($user_id, $headers[$i]);
                } else {
                  continue;
                }
              } else {
                update_user_meta($user_id, $headers[$i], $data[$i]);
                continue;
              }
            }
          }
        endfor;
      }

      $styles = '';
      if ($problematic_row) {
        $styles = 'background-color:red; color:white;';
      }

      echo "<tr style='$styles' ><td>" . ($row - 1) . '</td>';
      foreach ($data as $element) {
        if (is_array($element)) {
          $element = implode(',', $element);
        }

        $element = sanitize_textarea_field($element);
        echo "<td>$element</td>";
      }

      do_action(
        'post_acui_import_single_user',
        $headers,
        $data,
        $user_id,
        $role
      );

      echo "</tr>\n";

      flush();

      $mail_for_this_user = false;
      if ($is_cron) {
        if (get_option('acui_cron_send_mail')) {
          if (
            $created ||
            (!$created && get_option('acui_cron_send_mail_updated'))
          ) {
            $mail_for_this_user = true;
          }
        }
      } else {
        if (isset($form_data['sends_email']) && $form_data['sends_email']) {
          if (
            $created ||
            (!$created &&
              (isset($form_data['send_email_updated']) &&
                $form_data['send_email_updated']))
          ) {
            $mail_for_this_user = true;
          }
        }
      }

      if (
        get_option('acui_automatic_created_edited_wordpress_email') == 'true' &&
        $created
      ) {
        do_action('register_new_user', $user_id);
      }

      if (
        get_option('acui_automatic_created_edited_wordpress_email') == 'true' &&
        !$created
      ) {
        do_action('edit_user_created_user', $user_id, 'both');
      }

      if (isset($mail_for_this_user) && $mail_for_this_user):
        $key = get_password_reset_key($user_object);
        $user_login = $user_object->user_login;

        $body = get_option('acui_mail_body');
        $subject = get_option('acui_mail_subject');

        $body = str_replace('**loginurl**', wp_login_url(), $body);
        $body = str_replace('**username**', $user_login, $body);
        $body = str_replace(
          '**lostpasswordurl**',
          wp_lostpassword_url(),
          $body
        );
        $subject = str_replace('**username**', $user_login, $subject);

        if (!is_wp_error($key)) {
          $passwordreseturl = apply_filters(
            'acui_email_passwordreseturl',
            network_site_url(
              'wp-login.php?action=rp&key=' .
                $key .
                '&login=' .
                rawurlencode($user_login),
              'login'
            )
          );
          $body = str_replace('**passwordreseturl**', $passwordreseturl, $body);

          $passwordreseturllink = wp_sprintf(
            '<a href="%s">%s</a>',
            $passwordreseturl,
            __('Password reset link', 'import-users-from-csv-with-meta')
          );
          $body = str_replace(
            '**passwordreseturllink**',
            $passwordreseturllink,
            $body
          );
        }

        if (empty($password) && !$created) {
          $password = __(
            'Password has not been changed',
            'import-users-from-csv-with-meta'
          );
        }

        $body = str_replace('**password**', $password, $body);
        $body = str_replace('**email**', $email, $body);

        foreach ($wp_users_fields as $wp_users_field) {
          if (
            $positions[$wp_users_field] != false &&
            $wp_users_field != 'password'
          ) {
            $body = str_replace(
              '**' . $wp_users_field . '**',
              $data[$positions[$wp_users_field]],
              $body
            );
            $subject = str_replace(
              '**' . $wp_users_field . '**',
              $data[$positions[$wp_users_field]],
              $subject
            );
          }
        }

        for ($i = 0; $i < count($headers); $i++) {
          $data[$i] = is_array($data[$i]) ? implode('-', $data[$i]) : $data[$i];
          $body = str_replace('**' . $headers[$i] . '**', $data[$i], $body);
          $subject = str_replace(
            '**' . $headers[$i] . '**',
            $data[$i],
            $subject
          );
        }

        $body = apply_filters(
          'acui_import_email_body_before_wpautop',
          $body,
          $headers,
          $data,
          $created,
          $user_id
        );

        $body = wpautop($body);

        $attachments = [];
        $attachment_id = get_option('acui_mail_attachment_id');
        if (!empty($attachment_id)) {
          $attachments[] = get_attached_file($attachment_id);
        }

        $email_to = apply_filters(
          'acui_import_email_to',
          $email,
          $headers,
          $data,
          $created,
          $user_id
        );
        $subject = apply_filters(
          'acui_import_email_subject',
          $subject,
          $headers,
          $data,
          $created,
          $user_id
        );
        $body = apply_filters(
          'acui_import_email_body',
          $body,
          $headers,
          $data,
          $created,
          $user_id
        );
        $headers_mail = apply_filters(
          'acui_import_email_headers',
          ['Content-Type: text/html; charset=UTF-8'],
          $headers,
          $data,
          $created,
          $user_id
        );
        $attachments = apply_filters(
          'acui_import_email_attachments',
          $attachments,
          $headers,
          $data,
          $created,
          $user_id
        );

        wp_mail($email_to, $subject, $body, $headers_mail, $attachments);
      endif;
    endif;

    $row++;
  endwhile;

  // let the filter of default WordPress emails as it were before deactivating them
  if (!get_option('acui_automatic_wordpress_email')) {
    remove_filter(
      'send_email_change_email',
      function () {
        return false;
      },
      999
    );
    remove_filter(
      'send_password_change_email',
      function () {
        return false;
      },
      999
    );
  }

  if ($attach_id != 0) {
    wp_delete_attachment($attach_id);
  }

  // delete all users that have not been imported
  $delete_users_flag = false;
  $change_role_not_present_flag = false;

  if ($delete_users == 'yes') {
    $delete_users_flag = true;
  }

  if ($is_cron && get_option('acui_cron_delete_users')) {
    $delete_users_flag = true;
    $delete_users_assign_posts = get_option(
      'acui_cron_delete_users_assign_posts'
    );
  }

  if ($is_backend && $change_role_not_present == 'yes') {
    $change_role_not_present_flag = true;
  }

  if ($is_cron && !empty(get_option('acui_cron_change_role_not_present'))) {
    $change_role_not_present_flag = true;
    $change_role_not_present_role = get_option(
      'acui_cron_change_role_not_present_role'
    );
  }

  if (
    $is_frontend &&
    !empty(get_option('acui_frontend_change_role_not_present'))
  ) {
    $change_role_not_present_flag = true;
    $change_role_not_present_role = get_option(
      'acui_frontend_change_role_not_present_role'
    );
  }

  if ($error_importing) {
    // if there is some problem of some kind importing we won't proceed with delete or changing role to users not present to avoid problems
    $delete_users_flag = false;
    $change_role_not_present_flag = false;
  }

  if ($delete_users_flag):
    require_once ABSPATH . 'wp-admin/includes/user.php';

    global $wp_roles; // get all roles
    $all_roles = $wp_roles->roles;
    $exclude_roles = array_diff(array_keys($all_roles), $editable_roles); // remove editable roles

    if (!in_array('administrator', $exclude_roles)) {
      // just to be sure
      $exclude_roles[] = 'administrator';
    }

    $args = [
      'fields' => ['ID'],
      'role__not_in' => $exclude_roles,
      'exclude' => [get_current_user_id()], // current user never cannot be deleted
    ];

    if ($delete_users_only_specified_role) {
      $args['role__in'] = $role_default;
    }

    $all_users = get_users($args);
    $all_users_ids = array_map(function ($element) {
      return intval($element->ID);
    }, $all_users);
    $users_to_remove = array_diff($all_users_ids, $users_registered);

    $delete_users_assign_posts =
      get_userdata($delete_users_assign_posts) === false
        ? false
        : $delete_users_assign_posts;

    foreach ($users_to_remove as $user_id) {
      empty($delete_users_assign_posts)
        ? wp_delete_user($user_id)
        : wp_delete_user($user_id, $delete_users_assign_posts);
    }
  endif;

  if ($change_role_not_present && !$delete_users_flag):
    require_once ABSPATH . 'wp-admin/includes/user.php';

    $all_users = get_users([
      'fields' => ['ID'],
      'role__not_in' => ['administrator'],
    ]);

    foreach ($all_users as $user) {
      if (!in_array($user->ID, $users_registered)) {
        $user_object = new WP_User($user->ID);
        $user_object->set_role($change_role_not_present_role);
      }
    }
  endif;
  ?>
			</table>

			<?php if (!$is_frontend): ?>
				<br/>
				<p><?php _e(
      'Process finished you can go',
      'import-users-from-csv-with-meta'
    ); ?> <a href="<?php echo get_admin_url(null, 'users.php'); ?>"><?php _e(
  'here to see results',
  'import-users-from-csv-with-meta'
); ?></a></p>
			<?php endif; ?>
			
			<?php
   ini_set('auto_detect_line_endings', false);

   do_action('after_acui_import_users');?>
	</div>
<?php
}

function acui_options()
{
  if (!current_user_can(apply_filters('acui_capability', 'create_users'))) {
    wp_die(
      __(
        'You are not allowed to see this content.',
        'import-users-from-csv-with-meta'
      )
    );
  }

  if (isset($_GET['tab'])) {
    $tab = $_GET['tab'];
  } else {
    $tab = 'homepage';
  }

  if (isset($_POST) && !empty($_POST)):
    if (!wp_verify_nonce($_POST['security'], 'codection-security')) {
      wp_die(__('Nonce check failed', 'import-users-from-csv-with-meta'));
    }

    switch ($tab) {
      case 'homepage':
        update_option(
          'acui_last_roles_used',
          empty($_POST['role'])
            ? ''
            : array_map('sanitize_text_field', $_POST['role'])
        );
        acui_fileupload_process($_POST, false);
        return;
        break;

      case 'frontend':
        do_action('acui_frontend_save_settings', $_POST);
        break;

      case 'columns':
        acui_manage_extra_profile_fields($_POST);
        break;

      case 'mail-options':
        acui_save_mail_template($_POST);
        break;

      case 'cron':
        do_action('acui_cron_save_settings', $_POST);
        break;
    }
  endif;

  if (isset($_GET['tab'])) {
    acui_admin_tabs($_GET['tab']);
  } else {
    acui_admin_tabs('homepage');
  }

  switch ($tab) {
    case 'homepage':
      ACUI_Homepage::admin_gui();
      break;

    case 'export':
      ACUI_Exporter::admin_gui();
      break;

    case 'frontend':
      ACUI_Frontend::admin_gui();
      break;

    case 'columns':
      ACUI_Columns::admin_gui();
      break;

    case 'meta-keys':
      ACUI_MetaKeys::admin_gui();
      break;

    case 'doc':
      ACUI_Doc::message();
      break;

    case 'mail-options':
      ACUI_Email_Options::admin_gui();
      break;

    case 'cron':
      ACUI_Cron::admin_gui();
      break;

    case 'donate':
      ACUI_Donate::message();
      break;

    case 'help':
      ACUI_Help::message();
      break;

    case 'new_features':
      ACUI_NewFeatures::message();
      break;

    default:
      do_action('acui_tab_action_' . $tab);
      break;
  }
}

function acui_get_wp_users_fields()
{
  return [
    'id',
    'user_email',
    'user_nicename',
    'user_url',
    'display_name',
    'nickname',
    'first_name',
    'last_name',
    'description',
    'jabber',
    'aim',
    'yim',
    'user_registered',
    'password',
    'user_pass',
    'locale',
    'show_admin_bar_front',
    'user_login',
  ];
}

function acui_get_restricted_fields()
{
  $wp_users_fields = acui_get_wp_users_fields();
  $wp_min_fields = ['Username', 'Email', 'bp_group', 'bp_group_role', 'role'];
  $acui_restricted_fields = array_merge($wp_users_fields, $wp_min_fields);

  return apply_filters('acui_restricted_fields', $acui_restricted_fields);
}

function acui_get_not_meta_fields()
{
  return apply_filters('acui_not_meta_fields', []);
}

function acui_detect_delimiter($file)
{
  $delimiters = [
    ';' => 0,
    ',' => 0,
    "\t" => 0,
    '|' => 0,
  ];

  $handle = @fopen($file, 'r');
  $firstLine = fgets($handle);
  fclose($handle);
  foreach ($delimiters as $delimiter => &$count) {
    $count = count(str_getcsv($firstLine, $delimiter));
  }

  return array_search(max($delimiters), $delimiters);
}

function acui_string_conversion($string)
{
  if (
    !preg_match(
      '%(?:
    [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
    |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
    |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
    |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
    |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
    |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
    |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
    )+%xs',
      $string
    )
  ) {
    return utf8_encode($string);
  } else {
    return $string;
  }
}

function acui_user_id_exists($user_id)
{
  if (get_userdata($user_id) === false) {
    return false;
  } else {
    return true;
  }
}

function acui_get_roles($user_id)
{
  $roles = [];
  $user = new WP_User($user_id);

  if (!empty($user->roles) && is_array($user->roles)) {
    foreach ($user->roles as $role) {
      $roles[] = $role;
    }
  }

  return $roles;
}

function acui_get_editable_roles()
{
  global $wp_roles;

  $all_roles = $wp_roles->roles;
  $editable_roles = apply_filters('editable_roles', $all_roles);
  $list_editable_roles = [];

  foreach ($editable_roles as $key => $editable_role) {
    $list_editable_roles[$key] = $editable_role['name'];
  }

  return $list_editable_roles;
}

function acui_admin_tabs($current = 'homepage')
{
  $tabs = [
    'homepage' => __('Import', 'import-users-from-csv-with-meta'),
    'export' => __('Export', 'import-users-from-csv-with-meta'),
    'frontend' => __('Frontend', 'import-users-from-csv-with-meta'),
    'cron' => __('Cron import', 'import-users-from-csv-with-meta'),
    'columns' => __('Extra profile fields', 'import-users-from-csv-with-meta'),
    'meta-keys' => __('Meta keys', 'import-users-from-csv-with-meta'),
    'mail-options' => __('Mail options', 'import-users-from-csv-with-meta'),
    'doc' => __('Documentation', 'import-users-from-csv-with-meta'),
    'donate' => __('Donate/Patreon', 'import-users-from-csv-with-meta'),
    'shop' => __('Shop', 'import-users-from-csv-with-meta'),
    'help' => __('Hire an expert', 'import-users-from-csv-with-meta'),
    'new_features' => __('New features', 'import-users-from-csv-with-meta'),
  ];

  $tabs = apply_filters('acui_tabs', $tabs);

  echo '<div id="icon-themes" class="icon32"><br></div>';
  echo '<h2 class="nav-tab-wrapper">';
  foreach ($tabs as $tab => $name) {
    $class = $tab == $current ? ' nav-tab-active' : '';

    if ($tab == 'shop') {
      $href = 'https://codection.com/tienda/';
      $target = '_blank';
    } else {
      $href = "?page=acui&tab=$tab";
      $target = '_self';
    }

    echo "<a class='nav-tab$class' href='$href' target='$target'>$name</a>";
  }
  echo '</h2>';
}

function acui_fileupload_process(
  $form_data,
  $is_cron = false,
  $is_frontend = false
) {
  if (
    !defined('DOING_CRON') &&
    (!isset($form_data['security']) ||
      !wp_verify_nonce($form_data['security'], 'codection-security'))
  ) {
    wp_die(__('Nonce check failed', 'import-users-from-csv-with-meta'));
  }

  if (empty($_FILES['uploadfile']['name']) || $is_frontend):
    $path_to_file = wp_normalize_path($form_data['path_to_file']);

    if (validate_file($path_to_file) !== 0) {
      wp_die(
        __(
          'Error, path to file is not well written',
          'import-users-from-csv-with-meta'
        ) . ": $path_to_file"
      );
    }

    if (!file_exists($path_to_file)) {
      wp_die(
        __(
          'Error, we cannot find the file',
          'import-users-from-csv-with-meta'
        ) . ": $path_to_file"
      );
    }

    acui_import_users($path_to_file, $form_data, 0, $is_cron, $is_frontend);
  else:
    $uploadfile = wp_handle_upload($_FILES['uploadfile'], [
      'test_form' => false,
      'mimes' => ['csv' => 'text/csv'],
    ]);

    if (!$uploadfile || isset($uploadfile['error'])) {
      wp_die(
        __(
          'Problem uploading file to import. Error details: ' .
            var_export($uploadfile['error'], true),
          'import-users-from-csv-with-meta'
        )
      );
    } else {
      acui_import_users(
        $uploadfile['file'],
        $form_data,
        acui_get_attachment_id_by_url($uploadfile['url']),
        $is_cron,
        $is_frontend
      );
    }
  endif;
}

function acui_manage_extra_profile_fields($form_data)
{
  if (
    !isset($form_data['security']) ||
    !wp_verify_nonce($form_data['security'], 'codection-security')
  ) {
    wp_die(__('Nonce check failed', 'import-users-from-csv-with-meta'));
  }

  if (
    isset($form_data['show-profile-fields-action']) &&
    $form_data['show-profile-fields-action'] == 'update'
  ) {
    update_option(
      'acui_show_profile_fields',
      isset($form_data['show-profile-fields']) &&
        $form_data['show-profile-fields'] == 'yes'
    );
  }

  if (
    isset($form_data['reset-profile-fields-action']) &&
    $form_data['reset-profile-fields-action'] == 'reset'
  ) {
    update_option('acui_columns', []);
  }
}

function acui_save_mail_template($form_data)
{
  if (
    !isset($form_data['security']) ||
    !wp_verify_nonce($form_data['security'], 'codection-security')
  ) {
    wp_die(__('Nonce check failed', 'import-users-from-csv-with-meta'));
  }

  $automatic_wordpress_email = sanitize_text_field(
    $form_data['automatic_wordpress_email']
  );
  $automatic_created_edited_wordpress_email = sanitize_text_field(
    $form_data['automatic_created_edited_wordpress_email']
  );
  $subject_mail = sanitize_text_field(
    stripslashes_deep($form_data['subject_mail'])
  );
  $body_mail = wp_kses_post(stripslashes($form_data['body_mail']));
  $template_id = intval($form_data['template_id']);
  $email_template_attachment_id = intval(
    $form_data['email_template_attachment_id']
  );

  update_option('acui_automatic_wordpress_email', $automatic_wordpress_email);
  update_option(
    'acui_automatic_created_edited_wordpress_email',
    $automatic_created_edited_wordpress_email
  );
  update_option('acui_mail_subject', $subject_mail);
  update_option('acui_mail_body', $body_mail);
  update_option('acui_mail_template_id', $template_id);
  update_option('acui_mail_attachment_id', $email_template_attachment_id);

  $template_id = absint($form_data['template_id']);

  if (!empty($template_id)) {
    wp_update_post([
      'ID' => $template_id,
      'post_title' => $subject_mail,
      'post_content' => $body_mail,
    ]);

    update_post_meta(
      $template_id,
      'email_template_attachment_id',
      $email_template_attachment_id
    );
  }
  ?>
	<div class="updated">
       <p><?php _e(
         'Mail template and options updated correctly',
         'import-users-from-csv-with-meta'
       ); ?></p>
    </div>
    <?php
}

// wp-access-areas functions
function acui_set_cap_for_user($capability, &$user, $add)
{
  $has_cap = $user->has_cap($capability);
  $is_change = ($add && !$has_cap) || (!$add && $has_cap);
  if ($is_change) {
    if ($add) {
      $user->add_cap($capability, true);
      do_action('wpaa_grant_access', $user, $capability);
      do_action("wpaa_grant_{$capability}", $user);
    } elseif (!$add) {
      $user->remove_cap($capability);
      do_action('wpaa_revoke_access', $user, $capability);
      do_action("wpaa_revoke_{$capability}", $user);
    }
  }
}

// misc
function acui_get_attachment_id_by_url($url)
{
  $wp_upload_dir = wp_upload_dir();
  // Strip out protocols, so it doesn't fail because searching for http: in https: dir.
  $dir = set_url_scheme(trailingslashit($wp_upload_dir['baseurl']), 'relative');

  // Is URL in uploads directory?
  if (false !== strpos($url, $dir)) {
    $file = basename($url);

    $query_args = [
      'post_type' => 'attachment',
      'post_status' => 'inherit',
      'fields' => 'ids',
      'meta_query' => [
        [
          'key' => '_wp_attachment_metadata',
          'compare' => 'LIKE',
          'value' => $file,
        ],
      ],
    ];

    $query = new WP_Query($query_args);

    if ($query->have_posts()) {
      foreach ($query->posts as $attachment_id) {
        $meta = wp_get_attachment_metadata($attachment_id);
        $original_file = basename($meta['file']);
        $cropped_files = wp_list_pluck($meta['sizes'], 'file');

        if ($original_file === $file || in_array($file, $cropped_files)) {
          return (int) $attachment_id;
        }
      }
    }
  }

  return false;
}
