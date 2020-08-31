<?php
	global $wpdb;
	$table_name = $wpdb->prefix . "ucoz_users";

    //Проверяем наличии базы и создаем новую
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		             id mediumint(9) NOT NULL AUTO_INCREMENT,
		             user_login varchar(60) NOT NULL COLLATE utf8_general_ci,
		             user_hash varchar(255) NOT NULL COLLATE utf8_general_ci,
		             user_nicename varchar(50) NOT NULL COLLATE utf8_general_ci,
		             user_email	varchar(100) NOT NULL COLLATE utf8_general_ci,
		             user_salt VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
		             user_status int(11) NOT NULL,
		             UNIQUE KEY id (id)
		          );";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

			$file = file_get_contents('users.txt', true);

			 if (!empty($file)) {
			 	$i = 0;
    			$users = explode("\N", $file);
				foreach ($users as $user) {
					//При желании можно вытащить больше данных, вплоть до IP адреса при регистрации, тогда добавляем переменный в list
					list($user_login, $user_id, $user_hash, $age, $empty_field, $user_nicename, $user_confirmed, $user_email) = explode("|", $user); 
					$data[$i]['user_login'] = $user_login;
					$data[$i]['user_hash'] = $user_hash;
					$data[$i]['user_nicename'] = $user_nicename;
					$data[$i]['user_email'] = $user_email;
					$data[$i]['user_confirmed'] = $user_confirmed;

				    preg_match('/(\$1\$[^\$]+)/', $user_hash, $user_salt);
				    $data[$i]['user_salt'] = $user_salt[0];
				    $user_salt = $data[$i]['user_salt'];
				    $user_login = str_replace("\n", "", $user_login );
					
					//Вносим в базу
			   		$wpdb->insert($table_name, 
			   			array("user_login" => $user_login, 
			   				"user_nicename" => $user_nicename, 
			   				"user_hash" => $user_hash, 
			   				"user_email" => $user_email, 
			   				"user_salt" =>  $user_salt, 
			   				"user_status" => $user_confirmed), 
			   			array("%s", "%s", "%s", "%s", "%s", "%s"));
			        $i++;
				}
   			 }
    }
