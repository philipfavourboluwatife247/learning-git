<!-- Post 1 -->

<?php

      $image = "images/user_female.jpg";


      ?>
<div id="post">

<div>
  <img src="<?php echo $image ?>" alt="userphoto" style="width: 75px; margin-right: 4px;">
</div>

<div>
    <div style="font-weight: bold; color: #462c7d;">
        <?php echo $ROW_USER['username'] ?>
     </div>
   <?php echo $ROW['post'] ?>
    <br><br>
    <a href="#">Like</a> . <a href="#">Comment</a> . <span style="color: #999;"><?php echo $ROW['date'] ?></span>
</div>
</div>
