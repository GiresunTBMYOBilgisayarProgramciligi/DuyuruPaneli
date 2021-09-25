<?php
namespace App;

/**
 * SQLite connnection
 */
class SQLiteConnection {
    /**
     * PDO instance
     * @var type
     */
    public $pdo;

    function __construct()
    {
        try {
            $this->pdo = new \PDO("sqlite:" . Config::PATH_TO_SQLITE_FILE);
        } catch (\PDOException $e) {
            // handle the exception here
            echo $e->getMessage();
        }
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
        }
        return $this->pdo;
    }
    public function setup(){

        $this->pdo->exec("
        create table if not exists user(
          id INTEGER PRIMARY KEY,
          userName TEXT NOT NULL,
          mail TEXT NOT NULL,
          password TEXT,
          name TEXT,
          lastName TEXT,
          createdDate TEXT
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
            FOREIGN KEY (
                        userId
                )REFERENCES user(id) on delete set null on update cascade
);
        ");
        $this->pdo->exec("
            INSERT INTO user(userName, mail, password, name, lastName, createdDate) values
            ('sametatabasch','sametatabasch@gmail.com','123456','Samet','ATABAŞ',datetime('now','localtime'));
        ");
    }
}