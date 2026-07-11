<?php
declare(strict_types=1);

class Deconnexion
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function execute(): void
    {
        $_SESSION = [];

        session_destroy();

        header('Location: /vite-gourmand/');
        exit;
    }
}

$deconnexion = new Deconnexion();
$deconnexion->execute();