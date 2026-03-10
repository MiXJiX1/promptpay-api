<?php
class Database {
    public static function connect() {
        return new PDO(
            "mysql:host=localhost;dbname=api_keys;charset=utf8",
            "user",
            "password",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }
}
