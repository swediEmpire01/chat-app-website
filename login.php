<?php
    session_start();
    include_once "./db.php"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tell'em</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="./assets/logo.svg" type="image/x-icon">
</head>
<body>

    <?php
        if(isset($_POST["login"])) {
            //This is the LOGIN SECTION####################################
            //isset returns true if var is not declared or null
            $username= filter_input(INPUT_POST,"email", FILTER_SANITIZE_SPECIAL_CHARS);
            $password= filter_input(INPUT_POST,"password", FILTER_SANITIZE_SPECIAL_CHARS);

            if(empty($username) || empty($password)){
                //empty returns true if var is not declared, null, false, "";
                echo "<div class='error'> Invalid username or password </div>";
            }
            else{
                $_SESSION["email"] = $username;
                $_SESSION["password"] = $password;

                $mysql_connect_code= "SELECT * FROM registered_users";
            
                try{
                    //this excutes the SQL query
                    $result= mysqli_query($conn, $mysql_connect_code);

                    //this fetches the db users
                    if(mysqli_num_rows($result) > 0){
                         while($row= mysqli_fetch_assoc($result)){
                             //this compares if user login is equal
                             switch ($username) {
                                case $row["email"]:
                                    if(password_verify($password, $row["password"])){
                                        #this switches the pge
                                        header("Location: index.php");
                                    }
                                    else{
                                        echo "<div class='error'> Password is incorrect </div>";
                                    }
                                break;
                                default:
                                    echo "<div class='error'> You are not registered </div>";
                                break;
                             };
                         };
                    };
                }
                catch(mysqli_sql_exception){
                    echo "<div class='error'> Could not Register!</div>";

                };

            };
        };
        //This is the SIGNUP SECTION ##########################################
        
        if(isset($_POST["sign_up"])) {
            #this ensures that the singn up stays on when it reloads;
            echo "<style> 
                    form{
                        display: none;
                    } 
                    form#signUp{
                        display: flex;
                    }
                </style>";
                
            $email= filter_input(INPUT_POST,"email-sign", FILTER_SANITIZE_SPECIAL_CHARS);
            $password= filter_input(INPUT_POST,"password-sign", FILTER_SANITIZE_SPECIAL_CHARS);
            $password_confirm= filter_input(INPUT_POST,"password-sign-confirm", FILTER_SANITIZE_SPECIAL_CHARS);

            if(empty($email) || empty($password) || empty($password_confirm)){
                echo "<div class='error'> Invalid username or password </div>";
            }
            else{
                if($password_confirm === $password){
                    $done= true;

                    try{
                        //this excutes the SQL query
                        $result= mysqli_query($conn, "SELECT * FROM registered_users");
                        if(mysqli_num_rows($result) > 0){
                            while($row= mysqli_fetch_assoc($result)){
                                //this compares if user login is equal
                                if($email == $row["email"]){
                                    $done= false;
                                }
                            };
                        };
                    }
                    catch(mysqli_sql_exception){
                        echo "<div class='error'> Disconnected to Database </div>";

                    }

                    if($done){
                        $hash= password_hash($password, PASSWORD_DEFAULT);
                        $mysql_connect_code="INSERT INTO registered_users (`id_no`, `email`, `password`, `reg_date`) 
                                            VALUES (NULL, '$email', '$hash', current_timestamp())";

                        
                        try {
                            mysqli_query($conn, $mysql_connect_code);
                        }
                        catch(mysqli_sql_exception){
                            echo "<div class='error'> email is in use </div>";
                            $done= false;
                        }
                        if($done){
                            $_SESSION["password"]=$hash;
                            $_SESSION["email"]=$email;
                            
                            header("Location: login.php");
                        }

                    }
                    else{
                        echo "<div class='error'> email is in use </div>";
                    }
                }
                else{
                    echo "<div class='error'> Passwords don't match </div>";
                }
            }
        }
    ?>

        <!-- the  $_SERVER["PHP_SELF"] is the list of webpage info similar to js this or window  -->
    <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post" id="login">
        <span>
            <img src="./assets/logo.svg" alt="logo-icon" class="logo-icon">
        </span>
        <h1>Welcome Back to tell'em</h1>
        <label for="name">
            Email <input type="text" id="email" name="email" >
        </label>
        <label for="password">
            Password <input type="password" id="password" name="password" >
        </label>
        <br>
        <button type="submit" name="login" value="submit">Login</button>
        <h3 onclick="changeForm()">
            Don't have an account? SignUp
        </h3>
    </form>

    <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post" id="signUp">
        <span>
            <img src="./assets/logo.svg" alt="logo-icon" class="logo-icon">
        </span>
        <h1>Create an account</h1>
        <label for="name">
            Email <input type="text" id="email-sign" name="email-sign" >
        </label>
        <label for="password">
            Password <input type="password" id="password-sign" name="password-sign"  >
        </label>
        <label for="password-sign-confirm">
            Confirm Password <input type="password" id="password-sign-confirm" name="password-sign-confirm">
        </label>
        <br>
        <button type="submit" name="sign_up" value="submit">Submit</button>
        <h3 onclick="changeForm()">
            Alread have an account? Login
        </h3>
    </form>
    
</body>
    <script>
        let errors= document.querySelectorAll('.error');
        for(let i= 0; i <errors.length; i++){
            setTimeout(()=>{
            errors[i].style.display= "none"

            }, 5000);
        }
        document.body.addEventListener('submit',e=>{
            e.defaultPrevented()
        });
        let forms= document.querySelectorAll('form');
        let login= true;
         function changeForm(){
             if(login){
                 forms[0].style.display= "none";
                 forms[1].style.display= "flex";
                 login= false;
             }
             else{
                 forms[1].style.display= "none";
                 forms[0].style.display= "flex";
                 login= true;
             }
        }
    </script>
    <?php
        mysqli_close($conn);
    ?>
</html>