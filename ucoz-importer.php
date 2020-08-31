<?php
/*
Plugin Name: Importing users from Ucoz to Wordpress
Description: Плагин для импорта и авторизации пользователей Ucoz в Wordpress. На текущей версии загружайте users.txt в корень плагина перед его активацией.
Author: Eduard Kanashevsky
Version: 0.9
*/

function installer(){
    include('installer.php');
}
register_activation_hook( __file__, 'installer' );

add_filter( 'authenticate', 'verification_and_creation', 10, 3 );
function verification_and_creation( $user, $username, $password ){
        if (!empty($_POST)) {
    
            $login = $_POST['log'];
            $pass = $_POST['pwd']; 

            //Ищем пользователя в старой базе данных
            global $wpdb;
            $table_name = $wpdb->prefix . "ucoz_users";
            $results   = $wpdb->get_results( "SELECT * FROM $table_name WHERE user_login = '$login'");
            
            if(!empty($results)){
                $old_user=$results[0];
                //Если пароль верный, то создаем нового пользователя в основной БД
                if ($old_user->user_hash == crypt($pass, $old_user->user_salt)) {
                    preg_match('/^([^ ]+ +[^ ]+) +(.*)$/', $old_user->user_nicename, $newname);
                    $nicename = explode(' ', $old_user->user_nicename);
                    $user_id = wp_insert_user(
                        array(
                            'user_email' => $old_user->user_email,
                            'user_login' => $old_user->user_login,
                            'user_pass'  => $pass,
                            'first_name' => $nicename[0],
                            'last_name'  => $nicename[1],
                        )
                    );
                    if (!empty($user_id)) {
                        //Удаляем запись из старой базы
                        $wpdb->delete( $table_name, array( 'id' => $old_user->id ) );
                    }
                }
            }
        }
    return $user;
}
