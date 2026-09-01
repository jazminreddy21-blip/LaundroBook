<?php

/*
This file is the database connection file, we make refrence to this file-
when we need to make a database connection.

Adjust host/user/password/database to match your local XAMPP setup.

*/


class Connection{

    private static ?mysqli $connection = null;

    public static function getConnection(): mysqli{
        if(self::$connection === null){
            //throws a msqli exception when queries fail instead of silently returning false, works with try catch.
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            self::$connection = new mysqli('localhost', 'root', '', '');
            self::$connection->set_charset('utf8mb4');
        }
        return self::$connection;
    }
}