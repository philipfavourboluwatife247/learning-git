<?php

session_start();


    include("classes/connect.php");
    include("classes/login.php");
    include("classes/user.php");
    include("classes/post.php");

    //check if user is logged in

    if(isset($_SESSION['palsworld_userid']) && is_numeric($_SESSION['palsworld_userid'])){

      $id = $_SESSION['palsworld_userid'];

      $login = new Login();
      $result = $login->check_login($id);

      if($result){

            // retrieve user data
            

            $user = new User();
            $user_data = $user->get_data($id);

            if(!$user_data)
            {

              header('Location: login.php');
              die;
            }

      }

      else{
            header('Location: login.php');
            die;
      }


    }

    else{
      header('Location: login.php');
      die;
}


//Posting starts here

if($_SERVER['REQUEST_METHOD'] == "POST")
{

    $post = new Post();

    $id = $_SESSION['palsworld_userid'];
    $result = $post->create_post($id,$_POST);

    if($result == ""){

      header("Location: myuser-profile.php");
      die;
    }

    else{

      echo "<div style='background-color: red; color:white; text-align:center; font-size: 12px; font-weight:bold'>";
      echo "The following errors occured!<br>";
      echo $result;
      echo "</div>";
    }
}


//collect posts

    $post = new Post();

    $id = $_SESSION['palsworld_userid'];

    $posts = $post->get_posts($id);

 
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Profile | Palsworld</title>
  <link rel="stylesheet" href="user-profile.css">
</head>
<body>

  <!--Top bar -->
    <div id = "purple-bar">

        <div id="bar-content">

          Palsworld &nbsp &nbsp<input type="text" id="search-box" placeholder="Search for People">
         
          <a href="logout.php">
          <span style="font-size: 13px; float: right; margin-top: 10px; margin-left: 10px; border-radius: 3px; 
          text-align: center; padding:2px; color: #FFF; border: thin solid #bcb3d0;">
          Logout</span></a>
          <img id="userimage-bar" src="selfie.jpg" alt="userphoto">
          

        </div>
    
    </div>

    <!-- Cover Area -->

    <div id="content">

        <div id="first-content">

          <img src="photo1.jpg" alt="bg image" style="width: 100%;">
          <img src="selfie.jpg" alt="userphoto" id="profile-pic"> <br>
          <div style="font-size:20px;">
          <?php echo $user_data['username'] ?>
          </div>
          <br>
          <div id="menu-buttons"> Timeline</div> 
          <div id="menu-buttons"> About</div> 
          <div id="menu-buttons"> Friends</div> 
          <div id="menu-buttons"> Photos</div> 
          <div id="menu-buttons"> Settings</div> 
        </div>


        <!-- Below the cover area-->
        <div style="display: flex;">

            <!--Friends Area-->
          <div style="min-height: 400px; flex:1;">
          
             <div id="friends-bar">

                Friends <br>

                <div id="friends">
                  <img id="friends-img" src="user1.jpg" alt="friends image">
                  <br>
                  Sam Nkolo
              </div>

              <div id="friends">
                <img id="friends-img" src="user2.jpg" alt="friends image">
                <br>
                Jonah Freeman
            </div>

            <div id="friends">
              <img id="friends-img" src="user3.jpg" alt="friends image">
              <br>
              Daniel Lubams
          </div>

          <div id="friends">
            <img id="friends-img" src="user4.jpg" alt="friends image">
            <br>
            Sharlie Snow
        </div>


             </div> 
          
          </div>

              <!-- Post Area -->

          <div style="min-height: 400px; flex:2.5; padding: 20px; padding-right: 0px;">
          
            <div style="border: thin solid #aaa; padding: 10px; background-color: #fff;">

            <form method="post">
                <textarea name="post" placeholder="What's on your mind?"></textarea>
                <input type="submit" id="post-button" value="Post">
                <br><br>
                </form>
            </div>
            
              <!-- Posts -->

              <div id="post-bar">

                 <?php

                if($posts){


                  foreach ($posts as $ROW) {
                    # code...

                        $user = new User(); 
                        $ROW_USER = $user->get_data($ROW['userid']);

                        include("post.php");
                  }

                }


                ?>


              </div> 

          </div>

        </div>

    </div>
</body>
</html>