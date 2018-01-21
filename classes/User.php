<?php

/* This class will include everything associated with users:
 * Login - Login()
 * Logout - logOut()
 * Password recovery - forgotPassword(), newPassword(), updatePassword()
 * User creation - Registration()
 * User e-mail verification - Verify()
 */

class User{
    
    /* __constructor()
     * Constructor will be called every time Login class is called ($login = new Login())
     */
    public function __construct(){
                  
        /* Check if user is logged in. */
        $this->isLoggedIn();
         
        /* If login data is posted call validation function. */
        if (isset($_POST["login"])) {
            $this->Login();
        }     
        /* If forgot password form data is posted call forgotPassword() function. */
        if (isset($_POST["forgotPassword"])) {
            $this->forgotPassword();
        }    
        if (isset($_POST["updatePassword"])) {
            $this->updatePassword();
        } 
        /* If registration data is posted call createUser function. */
        if (isset($_POST["registration"])) {
            $this->Registration();
        }
         
    } /* End __constructor() */
    
    
    /* Function Login()
    *  Function that validates user login data, cross-checks with database.
    *  If data is valid user is logged in, session variables are set. 
    */
    private function Login(){
        
    // Require credentials for DB connection.
    require ("core/connect.php");
        
        // Check that data has been submited.
        if(isset($_POST['login'])){
            
            // User input from Login Form(loginForm.php).
            $user = trim($_POST['username']);
            $userpsw = trim($_POST['password']);
            
            // Check that both username and password fields are filled with values.
            if(!empty($user) && !empty($userpsw)){
                 $securing = password_hash($userpsw, PASSWORD_DEFAULT);
                /* Query the username from DB, if response is greater than 0 it means that users exists & 
                 * we continue to compare the password hash provided by the user side with the DB data. */
                 $query = $conn->prepare("SELECT username, password FROM users WHERE username = :name AND password = :pass");
                    $query->execute([
                        "name" => $user,
                        "pass" => $securing          
                    ]);
               
               var_dump($query->rowCount() );
                if ($query->rowCount() === 0)  {
                        header("Location: home.php");
                    } else {
                         echo '<p>Sorry, that username and password wasn\'t recognised.</p>';
                    }

//continue
                //work on this
                if ($result->num_rows === 1) {
                    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
                    // Cross-reference password that is given by user with the hashed password in database.
                    if (password_verify($userpsw, $row['password'])) {
                        // Username is set as Session user_id for this user.  
                        $_SESSION['user_id'] = $row['username'];          
                    } else {
                        $_SESSION['message'] = 'Invalid username or password.';
                    } 
                } else {
                    $_SESSION['message'] = 'Invalid username or password.';
                }   
            } else {
                $_SESSION['message'] = 'Please fill all required fields.';
            }
        }
    }   /* End Login() */
    
    
    /* Function logOut()
     * Logs user out, destroys all session data.
     */
    public function logOut(){
        
        session_destroy();  // Destroy all session data.
        header('Location: index.html');
        
    } /* End logOut() */  
    
    
    /* Function isLoggedIn()
     * Check if user is already logged in, if not then prompt login form.
     */
    public function isLoggedIn(){
        
    // Require credentials for DB connection.
    require ('core/connect.php');

        if(!empty($_SESSION['user_id'])){   
            return true;        
        } else {    
            return false;
        }

    } /* End isLoggedIn() */
    
    
    /* Function forgotPassword()
     * If the email exists $forgot_password_key is created to database, after this user will be sent an reset key via e-mail.
     * This is the first step of password reset.
     */
    private function forgotPassword(){
        
        // User input from Forgot password form(forgot.php).
        $email = trim($_POST['email']);
        
        // Require credentials for DB connection. 
        require ('core/connect.php');
        
        // Check if username or email is already taken.
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        // Always give this message to prevent data colleting even if the e-mail doesn't exist(The password reset e-mail is only sent if the e-mail exists in database).
        $_SESSION['SuccessMessage'] = 'E-mail has been sent.';
        
        // If e-mail exists a key for password reset is created into database, after this an e-mail will be sent to user with link and the token key.
        if ($result->num_rows != 0) {
            $forgot_password_key = password_hash($email, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET fpassword_key = ? WHERE email = ?");
            $stmt->bind_param("ss", $forgot_password_key, $email);
            $stmt->execute();
            $stmt->close();
            
            $_SESSION['SuccessMessage'] = 'User has been created!';
            
            $message = "Your reset key is: ".$forgot_password_key."";
            $to= $email;
            $subject="Reset password";
            $from = 'test@membership.com'; // Insert the e-mail from where you want to send the emails.
            $body='<a href="YOURWEBSITEURL/password_reset.php?email='.$email.'&key='.$forgot_password_key.'">password_reset.php?email='.$email.'&key='.$forgot_password_key.'</a>'; // Replace YOURWEBSITEURL with your own URL for the link to work.
            $headers = "From: " .$from. "\r\n";
            $headers .= "Reply-To: ". $from . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            mail($to,$subject,$body,$headers);

        }
 
    } /* End forgotPassword() */
    
    
    /* Function newPassword()
     * URL opened from e-mail, get email & token key from URL.
     * If the e-mail and token exist in database prompt new password form.
     * Otherwise prompt an error.
     * This is the second step of password reset.
     */
    public function newPassword(){
        
        // Values from password_reset.php URL.
        $email = htmlspecialchars($_GET['email']);
        $forgot_password_key = htmlspecialchars($_GET['key']);
        
        // Require credentials for DB connection. 
        require ('coreconnect.php');
        
        $stmt = $conn->prepare("SELECT email,fpassword_key  FROM users WHERE email = ? AND fpassword_key = ?");
        $stmt->bind_param("ss", $email, $forgot_password_key);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows != 0) {
            include("views/passwordResetForm.php");
        } else {
            $_SESSION['message'] = 'Please contact support at support@membership.com';
        }
        
    } /* End newPassword() */
    
    
    /* function updatePassword()
     * Get information from Password Reset Form, if the email & token key are correct, update the passwordin database.
     * This is the third and final step of password reset.
     */
    private function updatePassword(){
        
        // User input from Forgot password form(passwordResetForm.php).
        $password3 = trim($_POST['password3']);
        $password2 = trim($_POST['password2']);
        $email = $_POST['email'];
        $forgot_password_key = $_POST['key'];
        
        // Require credentials for DB connection. 
        require ('coro/connect.php');
        
        // Check that both entered passwords match.
        if($password3===$password2){

            if(!empty($password3) && !empty($email)){
                $securing = password_hash($password2, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ?, fpassword_key = ?  WHERE email = ? AND fpassword_key = ?");
                $clean_password_key = "";
                $stmt->bind_param("ssss", $securing, $clean_password_key, $email, $forgot_password_key);
                $stmt->execute();
                $stmt->close();
            } else {
                $_SESSION['message'] = 'Please fill all required fields.';
            }
            
        } else {
            $_SESSION['message'] = 'Passwords do not match!';
        }
        
    } /* End updatePassword() */
    
    
    /* Function Registration(){
     * Function that includes everything for new user creation.
     * Data is taken from registration form, converted to prevent SQL injection and
     * checked that values are filled, if all is correct data is entered to user database.
     */
    private function Registration(){
    
        // Require credentials for DB connection.
        require ('core/connect.php');

            // Variables for createUser()
            $username = trim($_POST['username']);  
            $password = trim($_POST['password']);
            $password2 = trim($_POST['password2']);
            $email = $_POST['email'];
            
            // Check if email is in the correct format.
                    if(!preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email)){
                        header('Location: registration.php');
                        $_SESSION['message'] = 'Please insert correct e-mail.';
                        exit();
                    }
                    
            if($password===$password2){
                // Create hashed user password.
                $securing = password_hash($password, PASSWORD_DEFAULT);
                $code = substr(md5(mt_rand()),0,15);

                $query = $conn->prepare("SELECT username, email FROM users WHERE username = :name OR email = :email");
                    $query->execute([
                        "name" => $username,
                        "email" => $email          
                    ]);
               
                if ($query->rowCount() != 0) {
                        // Promt user error about username or email already taken.
                       echo "Username or Email already taken";
                        exit();
                } 

                // Check that all fields are filled with values.
                if(!empty($username) && !empty($password) && !empty($email)){

                    // Check if username or email is already taken this PDO statements and you connection is mysqli .
                    $query = $conn->prepare("INSERT INTO users(username, email, password, activation_code)
                         VALUES(:username, :email, :password, :code)");
                    $query->execute([
                        "username" => $username,
                        "email" => $email,
                        "password" => $securing,
                        "code" => $code
                    ]);
					
                    
                    
if(!isset($_SESSION)) { session_start();}
$_SESSION['message'] =
                
                 "Account has been created successfully....Login here"; 
/* Start session, this is necessary, it must be the first thing in the PHP document after <?php syntax ! */ 
header('Location: login.php');
                



                    exit();
					
                    } else {
                    // If registration fails return user to registration.php and promt user failure error.
                    header('Location: registration.php');
                    $_SESSION['message'] = 'Please fill all fields!';
                    exit();
                }
                }
                    
                    
                   
        
    }   /* End Registration() */
    
    
    /* Function Verify(){
     * User e-mail verification on verify.php
     * E-mail and activation code are cross-referenced with database, if both are correct
     * is_activated is updated in database.
     */
    public function Verify(){
        
        if(isset($_GET['id']) && isset($_GET['code'])){
            
            // Variables for Verify() 
            $user_email=htmlspecialchars($_GET['id']);
            $activation_code=htmlspecialchars($_GET['code']);
            
            // Require credentials for DB connection.
            require ('core/connect.php');
            
            // Cross-reference e-mail and activation_code in database with values from URL.
            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ? and activation_code = ?");
            $stmt->bind_param("ss", $user_email, $activation_code);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close(); 
            // If e-mail and activation_code exist and are correct then update user is_activated value.
            if($result->num_rows == 1){
                $stmt = $conn->prepare("UPDATE users SET is_activated = ? WHERE email = ? and activation_code = ?");
                $verified = 1;
                $stmt->bind_param("iss", $verified, $user_email, $activation_code);
                $stmt->execute();
                $stmt->close();
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }   /* End Verify() */
    
    
}   /* End class UserClass */
