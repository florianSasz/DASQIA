<?php
/**
 * VerifyPassword class blocks password protected content and will only make it accessable when the user inputs the current user password
 */
require_once "../components/redirect_user.php";
class VerifyPassword {
    private string $currentPage;

    function __construct(string $currentPage) { // $currentPage: must call static method this.getCurrentURL() when calling from another script
        if (isset($_POST["navigation"]) && $_POST["navigation"] === "cancel") {
            RedirectUser::returnToHomePage();
        } 
        $this->currentPage = $currentPage;
    }

    // https://www.javatpoint.com/how-to-get-current-page-url-in-php
    public static function getCurrentURL() {
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url = "https://";   
        } else {
            $url = "http://";   
        }
        // Append the host(domain name, ip) to the URL.   
        $url.= $_SERVER['HTTP_HOST'];   
        
        // Append the requested resource location to the URL   
        $url.= $_SERVER['REQUEST_URI']; 
        
        return $url;
    }

    public function isVerified() {
        return (isset($_SESSION["passwordVerfied"]) && $_SESSION["passwordVerfied"]);
    }   

    private function reload() {
        RedirectUser::returnTo($this->currentPage);
    }

    private function verifyInput() {
        $_SESSION["passwordVerfied"] = password_verify($_POST["passwordToVerify"], $_SESSION["user"]["password"]);
    }

    public function verifyPassword() {
        if (isset($_POST["passwordToVerify"])) {
            $this->verifyInput();
            if ($_SESSION["passwordVerfied"]) {
                $this->reload();
            }
        }
        return $this->displayForm();
    }

    private function displayForm() {
        if (isset($_SESSION["passwordVerfied"])) {
            $errorMessage = "<p class='redText center'>The entered password is wrong.</p>";
        } else {
            $errorMessage = "";
        }
        $output =
            "<div class='bubble verifyPwBubble'>
                <p class='blueSubHeadline verifyPwHeadline'>verify password</p>
                <form action='" . $this->currentPage . "' method='post'>
                    <div class='verifyPwHeadline'>
                        <p class='noMargin'>enter your password:</p>
                        <input type='password' class='' name='passwordToVerify' size='30'>
                    </div>
                    <div class='center'>
                        <input type='submit' class='button' name='navigation' value='verify password'>
                        <input type='submit' class='button' name='navigation' value='cancel'>
                    </div>"
                    .
                    $errorMessage
                    .
                "</form>
            </div>";
        return $output;        
    }
}
?>