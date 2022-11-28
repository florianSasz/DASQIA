<?php
/**
 * processes data coming from reset_password.php; will send an email to a user if the given email exists in the database;
 * email contains a link to reset the users password 
 */

require_once "./string_utilities.php";
require_once "./database.php";
require_once "./redirect_user.php";

function backToReset(string $error) {
    $_SESSION["email"] = $_POST["email"];
    RedirectUser::returnTo("../public/reset_password.php");
}

function sendResetMail(string $email) {
    $database = new DatabaseAccess();
    if ($database->getUserShort($_POST["email"])) {

        $selector = bin2hex(random_bytes(8)); 
        $token = random_bytes(32);
        $url = "http://localhost/projektarbeit/public/create_new_password.php"; // url to which the link for the pw reset will lead
        $url .= "?selector=" . $selector . "&validator=" . bin2hex($token);
        $expires = date("U") + 600; // link is valid for 10min

        $database->resetPasswordToken($_POST["email"]);
        $database->setPasswordToken($_POST["email"], $selector, $token, $expires);

        $to = $_POST["email"];
        $subject = "Reset DASQIA password";
        $txt = 
            "<p>
                Here is your password reset link:
                <br>
                <a href='" . $url . "'>" . $url . "</a>
                <br><br>
                This link is valid for 10min.
            </p>"; 

        $headers = array(
            "MIME-Version" => "1.0",
            "Content-type" => "text/html;charset=UTF-8",
            "From" => "DASQIA <florian369357@gmail.com>", // email that will be displayed in the email header
        );

        mail($to,$subject,$txt,$headers);
    }
    $_SESSION["sendResetEmail"] = true;
}

function process() {
    session_start(); 
    if (!isEmail($_POST["email"])) {
        backToReset("Please enter a valid e-mail.");
    }
    sendResetMail($_POST["email"]);
}

if (isset($_POST["navigation"]) && $_POST["navigation"] == "reset") {
    process();
}
RedirectUser::returnToLoginPage();
?>