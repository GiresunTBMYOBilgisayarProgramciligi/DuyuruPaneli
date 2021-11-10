<?php

namespace App;

/**
 * SQLite connnection
 */
class SQLiteConnection
{
    /**
     * PDO instance
     * @var type
     */
    public $pdo;

    function __construct() {
        if (!file_exists(\App\Config::ROOT_PATH . "/db")) {
            mkdir(\App\Config::ROOT_PATH . "/db");
            $this->connect();
            try {
                $this->setup();
            } catch (\PDOException $e) {
                // handle the exception here
                echo $e->getMessage();
            }

            echo "Veri tabanı kurulumu yapıldı";
        }
        $this->connect();
        return $this->pdo;
    }

    /**
     * return in instance of the PDO object that connects to the SQLite database
     * @return \PDO
     */
    public function connect() {
        try {
            $this->pdo = new \PDO("sqlite:" . Config::PATH_TO_SQLITE_FILE);
        } catch (\PDOException $e) {
            // handle the exception here
            echo $e->getMessage();
        }
    }

    public function setup() {
           $q= $this->pdo->exec("
        CREATE TABLE IF NOT EXISTS user(
                                   id INTEGER PRIMARY KEY,
                                   userName TEXT NOT NULL UNIQUE,
                                   mail TEXT NOT NULL,
                                   password TEXT,
                                   name TEXT,
                                   lastName TEXT,
                                   createdDate TEXT
                               );
        ");
            $this->pdo->exec("
        CREATE TABLE IF NOT EXISTS slider(
            id INTEGER PRIMARY KEY,
            title TEXT,
            content TEXT,
            image TEXT,
            qrCode TEXT,
            createdDate TEXT,
            userId integer,
            fullWidth INTEGER,
            link TEXT,
            FOREIGN KEY (
                userId
            )
            REFERENCES user (id) ON DELETE set null ON UPDATE CASCADE
            );
        ");

            $this->pdo->exec("
        CREATE TABLE IF NOT EXISTS announcement(
            id INTEGER PRIMARY KEY,
            title TEXT,
            content TEXT,
            qrCode TEXT,
            createdDate TEXT,
            userId INTEGER,
            link TEXT,            
            FOREIGN KEY (
                        userId
                )REFERENCES user(id) on delete set null on update cascade
);
        ");
            $this->pdo->exec("
            INSERT INTO user(userName, mail, password, name, lastName, createdDate) values('sametatabasch','sametatabasch@gmail.com','" . password_hash("123456", PASSWORD_DEFAULT) . "','Samet','ATABAŞ','" . date('Y.m.d H:i:s') . "');
        ");

    }
}