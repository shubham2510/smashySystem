 <?php
 require_once '../core/connect.php';
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
header('Location: ../login.php');
                



                    exit();
					
                    } else {
                    // If registration fails return user to registration.php and promt user failure error.
                    header('Location: registration.php');
                    $_SESSION['message'] = 'Please fill all fields!';
                    exit();
                }
                }
                    
                    
                   
        