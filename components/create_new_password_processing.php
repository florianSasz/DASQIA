<?php
/**
 * processes data coming from create_new_password.php; checks if password and tokens are valid; sets new password if both are ok;
 * tokens come from the link the user received by email by using reset_password.php
 */
require_once "./redirect_user.php";
require_once "./string_utilities.php";
require_once "./database.php";

function process() {
    session_start();
    $selector =  $_POST["selector"];
    $validator =  $_POST["validator"];
    $password =  $_POST["new_password"];
    $passwordRepeat =  $_POST["new_password_re"];
    
    if ($password !== $passwordRepeat) {
        $_SESSION["passwordError"] = "Entered passwords are not the same.";
        RedirectUser::returnTo("../public/create_new_password.php?selector=" . $selector . "&validator=" . $validator . "&newPassword=unequal");
    }

    if (!checkMinPasswordLength($password)) {
        $_SESSION["passwordError"] = "Entered password is to short.<br>At least 7 characters are required.";
        RedirectUser::returnTo("../public/create_new_password.php?selector=" . $selector . "&validator=" . $validator . "&newPassword=toshort");
    }
    
    $currentData = date("U");
    $database = new DatabaseAccess();
    $databasePasswordResetDate = $database->getResetPassword($selector, $currentData);
    if (!$databasePasswordResetDate) {
        // error: invalid token selector
        RedirectUser::returnToLoginPage();
    }
    
    $tokenBin = hex2bin($validator); // binary token, required to compare to the value in the database
    $tokenCheck = password_verify($tokenBin, $databasePasswordResetDate["token"]);
    
    if ($tokenCheck === false) {
        // error: token false
        RedirectUser::returnTo("../public/create_new_password.php?selector=" . $selector . "&validator=" . $validator . "&token=wrong");
    } elseif ($tokenCheck === true) {
        $tokenEmail = $databasePasswordResetDate["email"];

        if ($user = $database->getUserShort($tokenEmail)) {
            $database->updatePassword($user["id"], $password);
            $_SESSION["newPassword"] = true;
        }
        $database->resetPasswordToken($tokenEmail);
    }
}

if (isset($_POST["navigation"]) && $_POST["navigation"] === "reset") {
    process();
} 
RedirectUser::returnToLoginPage();
?>