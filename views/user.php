<section class="span4">
  <div class="member clearfix">
    <div class="badge">
      <img src="<?php //echo get_template_directory_uri() ?>/assets/img/zzexpert.jpg" alt="Team Player" title="Team Player" />
      <h4><?php echo $user->display_name; ?></h4>
      <div class="badge-tail">&nbsp;</div>
    </div>
    <article>
      <h4><a href="#"></a></h4>
      <address><?php echo $user->user_city;?>, <?php echo $user->user_state; ?></address>
      <h5>Specializes in</h5>

      <ul>
          <?php $categories = get_terms('category', array('include' => $user->meta['um-taxonomy-category'])); ?>
          <?php foreach($categories as $category) : ?>
              <li><a href="#"><?php echo $category->name; ?></a></li>
          <?php endforeach; ?>
      </ul>
    </article>
    <article class="stats">
      <p>Last posted on <time datetime="2011-09-28" pubdate="pubdate"><?php echo $user->most_recent_post_date; ?></time>.</p>
      <ul>
        <li>8 answers</li>
        <li><?php echo count_user_posts($user->ID); ?> blog post(s)</li>
        <li><?php echo count(get_comments(array('user_id'=>$user->ID))); ?> comment(s)</li>
      </ul>
    </article>
  </div>
</section>