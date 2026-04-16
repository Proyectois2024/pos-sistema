<?php

function app_hash_password($plainPassword){
    return password_hash($plainPassword, PASSWORD_DEFAULT);
}

function app_verify_legacy_password($plainPassword, $storedHash){
    $legacyHash = crypt($plainPassword, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
    return hash_equals($storedHash, $legacyHash);
}

function app_verify_password($plainPassword, $storedHash){
    if(!$storedHash){
        return false;
    }

    $info = password_get_info($storedHash);

    if(isset($info["algo"]) && $info["algo"] !== null && $info["algo"] !== 0){
        return password_verify($plainPassword, $storedHash);
    }

    return app_verify_legacy_password($plainPassword, $storedHash);
}

function app_password_needs_rehash($storedHash){
    if(!$storedHash){
        return false;
    }

    $info = password_get_info($storedHash);

    if(!isset($info["algo"]) || $info["algo"] === null || $info["algo"] === 0){
        return true;
    }

    return password_needs_rehash($storedHash, PASSWORD_DEFAULT);
}

if (!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', 'America/Guatemala');
}

date_default_timezone_set(APP_TIMEZONE);

if (!function_exists('app_now')) {
    function app_now($format = 'Y-m-d H:i:s') {
        return date($format);
    }
}

class Conexion {

    private static $link = null;

    public static function conectar() {

        if(self::$link === null){

            $host = getenv("MYSQLHOST") ?: "localhost";
            $port = getenv("MYSQLPORT") ?: "3306";
            $db   = getenv("MYSQLDATABASE") ?: "pos";
            $user = getenv("MYSQLUSER") ?: "root";
            $pass = getenv("MYSQLPASSWORD") ?: "";

            self::$link = new PDO(
                "mysql:host=".$host.";port=".$port.";dbname=".$db.";charset=utf8mb4",
                $user,
                $pass
            );

            self::$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$link->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return self::$link;
    }
}
