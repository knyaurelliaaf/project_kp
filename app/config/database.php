<?php

class Database
{
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db   = "tracker_k3";

    public function connect()
    {
        $conn = mysqli_connect(
            $this->host,
            $this->user,
            $this->pass,
            $this->db
        );

        if (!$conn) {
            die("Koneksi gagal: " . mysqli_connect_error());
        }

        return $conn;
    }
}