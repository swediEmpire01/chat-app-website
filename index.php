<?php
    session_start();
    include_once "./db.php";
    $is_user= false;
    try{
        $result= mysqli_query($conn, "SELECT * FROM registered_users");
        if(mysqli_num_rows($result) > 0){
            while($row= mysqli_fetch_assoc($result)){
                if($_SESSION["email"] == $row["email"] && password_verify($_SESSION["password"], $row["password"])){
                    $is_user= true;
                    
                    $hash= $_SESSION["password"];
                    $email= $_SESSION["email"];
                    if(empty($row["profile_photo"] || $row["profile_photo"]=== NULL)){
                        $_SESSION["user_photo"] =  "./assets/blog/pfp.jpg";
                    }else{
                        $_SESSION["user_photo"] = $row["profile_photo"];
                    }
                    if( empty($row["name_of_user"]) || $row["name_of_user"]=== NULL){
                        $_SESSION["name_of_user"] =  "@Name_of_user";
                    }else{
                        $_SESSION["name_of_user"] = $row["name_of_user"]  ;
                    }
                }
            };
        };
    }
    catch(mysqli_sql_exception){
        echo "<div class='error'> Disconnected to Database </div>";
    }
    if(!$is_user){
        header("Location: login.php");
    }

    if(isset($_POST["upload"])){
        $file_name= $_FILES["profile-upload"]["name"];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        
        $temp_name= $_FILES["profile-upload"]["tmp_name"];
        $profile_location= "./photos/profiles/".$file_name;
        $allowed_photo_types= array("jpeg", "jpg", "png");

        if(in_array($ext, $allowed_photo_types)){
           if( move_uploaded_file( $temp_name, $profile_location)){
                $mysql_connect_code="UPDATE `registered_users` SET `profile_photo`='./photos/profiles/$file_name' WHERE `email`= '$email'";
                $_SESSION["user_photo"] =  "./photos/profiles/".$file_name;

                try {
                    mysqli_query($conn, $mysql_connect_code);
                    echo "<div class='succes'> File was Uploaded</div>";
                }
                catch(mysqli_sql_exception){
                    echo "<div class='error'> File could not Upload</div>";
                }
           }
           else{
                echo "<div class='error'> File could not Upload</div>";
           }
        }
        else{
             echo "<div class='error'> Not supported file type </div>";
        }
    }
    
    if(isset($_POST["remove"])){
        $mysql_connect_code="UPDATE `registered_users` SET `profile_photo`='./assets/blog/pfp.jpg' WHERE `email`= '$email'";
        $_SESSION["user_photo"] =  "./assets/blog/pfp.jpg";
        try {
            mysqli_query($conn, $mysql_connect_code);
            echo "<div class='succes'> File was Uploaded</div>";
        }
        catch(mysqli_sql_exception){
            echo "<div class='error'> File could not Upload</div>";
        }

    }
    if(isset($_POST["name-update-submit"])){
        $name= filter_input(INPUT_POST,"name-edit", FILTER_SANITIZE_SPECIAL_CHARS);
        if(!empty($name)){
            $mysql_connect_code="UPDATE `registered_users` SET`name_of_user`='$name' WHERE `email`= '$email'";

            try {
                mysqli_query($conn, $mysql_connect_code);
            }
            catch(mysqli_sql_exception){
                echo "<div class='error'> Unexpected Error</div>";
            }
        }
    }
    
    if(isset($_POST["send"])){

        $email= $_SESSION["email"];
        if(!empty($_POST["text"])){
            $text= $_POST["text"];
            try{
                $mysql_connect_code="INSERT INTO messages (`message_id`, `sender_email`, `message_text`, `sent_time`) 
                                    VALUES (NULL, '$email', '$text', current_timestamp())";

                
                try {
                    mysqli_query($conn, $mysql_connect_code);
                }
                catch(mysqli_sql_exception){
                    echo "<div class='error'> Could not send message </div>";
                }
            }
            catch(mysqli_sql_exception){
                echo "<div class='error'> Disconnected from Database </div>";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tell'em</title>
    <link rel="stylesheet" href="./blogStyle.css">
    <link rel="shortcut icon" href="./assets/logo.svg" type="image/x-icon">
</head>
<body>
    <?php
    ?>
    <div class="drk-side"></div>
    <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"])?>" class="edit-info"  enctype="multipart/form-data"  method="post">
        <div class="img-edit">
            <img src="<?php echo $_SESSION["user_photo"]?>" alt="photo-upload">
            <div id="upload-img">
                <input type="file" name="profile-upload" class="profile-upload">
             </div>
        </div>
        
        <div class="edit-photo">
            <input id="remove" name="upload" type="submit" value="Upload"  ></input>
            <input id="opload" name="remove" type="submit" value="Remove" ></input>
        </div>
        <div class="edit-name">
            <label for="name-edit">
                Name: 
                <input type="text" name="name-edit" id="name-edit">
            </label>
            <input type="submit" value="Submit" name="name-update-submit">
        </div>
        <div class="exit">
            <button type="button">Close</button>
        </div>
    </form>

    <header>
        <div class="logo">
            tell'em<span>.</span>
        </div>
        <div class="short-status">
            <div class="messages-unread-counter">
                <img src="./assets/logo.svg" alt="messages-icon" class="messages">
            </div>

            <div class="profile-header">
                <img src="<?php echo $_SESSION["user_photo"] ?>" alt="profile-icon" id="profile-icon">
            </div>
        </div>
    </header>
    <main class="chat-box">
        <div class="group-name">
            <h2>
                After Destroying The Exams
            </h2>
        </div>
    
        <div class="messages-display">
            <?php
                $emailsEdit= '';
                try{
                    $result= mysqli_query($conn, "SELECT * FROM messages");
                    if(mysqli_num_rows($result) > 0){
                        while($row= mysqli_fetch_assoc($result)){
                            if(empty($row["name_of_user"])){
                                $other_user_name= $row["sender_email"];
                            }
                            else{
                                $other_user_name= $row["name_of_user"];
                            }

                            if($_SESSION["email"] == $row["sender_email"] ){
                                echo "<div class='user-message' >
                                        <span >" .
                                           $row["message_text"] . "
                                        </span>
                                    </div>";
                            }
                            else{
                                $emailsClsses=  $other_user_name;

                                $atPosition = strpos($other_user_name, '@');
                                if ($atPosition !== false) {
                                    $emailsClsses = substr($emailsClsses, 0, $atPosition);
                                }
                                $emailsEdit= $emailsEdit .".". $emailsClsses ."?";
                                echo "<div class='member-message'>
                                    <span >
                                    <span class='other-users-id'><span class=" . $emailsClsses . ">~". $other_user_name ."</span></span>" .
                                           $row["message_text"] . "
                                    </span>
                                </div>";

                                

                            }
                        };
                    };
                    echo "<a id='down'><div id='down-location'></div></a>";
                }
                catch(mysqli_sql_exception){
                    echo "<div class='error'> Disconnected to Database </div>";
                }
                echo "<span id='emailsEdit'>" . $emailsEdit ."</span>";
            ?>
        </div>
        <a href="#down-location"><div id='down-arrow'></div></a>
        <form action="<?php htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
            <div class="message-container">
                <input type="text" placeholder="write message..." name="text">
                <button type="submit" name="send" id="send">Send</button>
            </div>
        </form>
    </main>
    <section>
        <div class="display-options">
            <button type="button">Profile</button>
        </div>
        <div class="profile-container">
            <div class="contacts">
                <div class="profile">
                    <div class="profile-pic">
                        <img src="<?php echo $_SESSION["user_photo"]  ?>" alt="profile-pic">
                        <span id="photo-view">
                            <img src="./assets/blog/cmer-v2.svg" alt="camera-edit-icon">
                        </span>
                    </div>
                    <div class="credentials">
                        <h4><?php echo $_SESSION["name_of_user"] ?></h4>
                        <a href="mailto:"><?php echo $_SESSION["email"] ?></a>
                        <button type="button">
                            Edit info
                        </button>
                    </div>
                </div>
                <div class="group-contacts">
                    <h4>Group Members</h4>
                    <?php
                        try{
                            $result= mysqli_query($conn, "SELECT * FROM registered_users");
                            if(mysqli_num_rows($result) > 0){
                                while($row= mysqli_fetch_assoc($result)){


                                    if($row["email"] != $_SESSION["email"]){
                                        if(empty($row["profile_photo"])){
                                            $other_user_photo =  './assets/blog/pfp.jpg';
                                        }else{
                                            $other_user_photo =  $row["profile_photo"] ;
                                        }
                                        if(empty($row["name_of_user"])){
                                            $other_user_name =  'nameUnknown';
                                        }else{
                                            $other_user_name =  $row["name_of_user"];
                                        }
                                            echo "<div class='member-profile'>
                                                    <img src='". $other_user_photo . "' alt='member-profile-pic'>
                                                    <span >
                                                        <span class='member-name'>
                                                            @". $other_user_name . "
                                                        </span>
                                                        <span class='member-email'>
                                                            ". $row["email"] . "
                                                        </span>
                                                    </span>
                                                </div >";
                                                
                                    }
                                };
                            };
                        }
                        catch(mysqli_sql_exception){
                            echo "<div class='error'> Disconnected to Database </div>";
                        }
                    ?>
                </div>
            </div>
        </div>
    </section>
    
    
    <?php
        mysqli_close($conn);
    ?>
</body>
<script>
    if(window.history.replaceState){
        window.history.replaceState(null, null, window.location.href);
    }
    document.body.addEventListener('submit',e=>{
        e.defaultPrevented();
    });
    if(document.querySelector('.messages-display').scrollHeight > document.querySelector('.messages-display').clientHeight){
            document.querySelector('#down-arrow').style.display= "flex";
        }

    let errors= document.querySelectorAll('.error');
    for(let i= 0; i <errors.length; i++){
        setTimeout(()=>{
            errors[i].style.display= "none";
        }, 5000);
    }
    let dones= document.querySelectorAll('.succes');
    for(let i= 0; i < dones.length; i++){
        setTimeout(()=>{
            dones[i].style.display= "none";
        }, 5000);
    }
    let divsEdit= ['.img-edit', '.edit-photo', '.edit-name']
    for(let i= 0; i < 3; i++){
            document.querySelector(divsEdit[i]).style.display= "none";
        }
    document.getElementById("photo-view").addEventListener('click', e=>{
        document.querySelector('form.edit-info').style.display= "flex";
        document.querySelector('div.drk-side').style.display= "flex";
        let items= ['.img-edit', '.edit-photo'];
        for(let i= 0; i < items.length; i++){
            document.querySelector(items[i]).style= "flex";
        }
    });
    
    document.querySelector(".exit").childNodes[1].addEventListener('click', e=>{
        document.querySelector('form.edit-info').style.display= "none";
        document.querySelector('div.drk-side').style.display= "none";
        for(let i= 0; i < 3; i++){
            document.querySelector(divsEdit[i]).style.display= "none";
        }
    })
    
    document.querySelector("div.credentials button").addEventListener('click', e=>{
        document.querySelector('form.edit-info').style.display= "flex";
        document.querySelector('div.drk-side').style.display= "flex";
        document.querySelector('.edit-name').style.display= "flex";
    })

    function randomNumber(min, max){
        return Math.floor(Math.random() * (max + min));
    }
    let emails= document.getElementById('emailsEdit').innerText;
    emails = emails.split("?");


    for(let i= 0; i < emails.length; i++){
        if(emails[i]){
            let list= document.querySelectorAll(`${emails[i]}`);
                colors=[];
            for(let n= 0; n < 3; n++){
                colors.push(randomNumber(0, 255))
            }
                for(let b= 0; b < list.length; b++){
                    list[b].style.color= `rgb(${colors[0]}, ${colors[1]}, ${colors[2]})`
                }
            }
        }
</script>
</html>