<?php
class Database {
    public static function connect() {
        return new PDO(
            "mysql:host=localhost;dbname=mixjix65_api_keys;charset=utf8",
            "mixjix65_api_keys",
            "mix12341",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }
}
