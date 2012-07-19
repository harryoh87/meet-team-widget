<section class="span4 featured-members content-container">
    <hgroup class="content-header">
        <h3><?php echo $title; ?></h3>
        <h4><?php echo $subtitle; ?></h4>
    </hgroup>
    <section class="content-body">
        <?php foreach($users as $user){
            include "user.php";
        } ?>
    </section>
</section>