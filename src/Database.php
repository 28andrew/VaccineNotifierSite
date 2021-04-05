<?php


namespace VaccineNotifier;


use DB;

class Database {
    public static function connect() {
        DB::$host = Config::get('mysql.host');
        DB::$port = Config::get('mysql.port');
        DB::$user = Config::get('mysql.user');
        DB::$password = Config::get('mysql.password');
        DB::$dbName = Config::get('mysql.db_name');
    }
}