<?php
/**
 * genereal methods to redirect a user to another page
 */
class RedirectUser {

    private static function getPrefix() {
        return (str_ends_with(getcwd(), "components")) ? "../public" : ".";
    }

    public static function returnToProjectPage(): never {
        header("Location: " . self::getPrefix() . "/project.php");
        exit();
    }
    
    public static function returnToLoginPage(): never {
        header("Location: " . self::getPrefix() . "/index.php");
        exit();
    }
    
    public static function returnToHomePage(): never {
        header("Location: " . self::getPrefix() . "/home.php");
        exit();
    }

    public static function returnTo(string $location): never {
        header("Location: " . $location);
        exit();
    }
}
?>