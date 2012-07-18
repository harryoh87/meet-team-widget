<section class="span4 featured-members content-container">
    <hgroup class="content-header">
        <h3>Meet the Team</h3>
        <h4>Whatever your question or issue, we're here to help</h4>
    </hgroup>
    <section class="content-body">

<?php
   foreach($users as $user){
       include "user.php";
   }
?>
    </section>
  </section>