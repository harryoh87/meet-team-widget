<section class="<?php echo $instance['span'] === 'span4' ? 'span4' : 'span8'; ?>">
    <div class="member clearfix">
        <div class="badge">
            <img src="<?php echo $user->badge_image_url; ?>" alt="Team Player" title="Team Player" />
            <h4><?php echo $user->badge_name; ?></h4>
            <div class="badge-tail">&nbsp;</div>
        </div>
        <article>
            <h4><a href="#"><?php echo $user->display_name; ?></a></h4>
            <address><?php echo $user->user_city;?>, <?php echo $user->user_state; ?></address>
            <?php if ($show_specializations === 'on') : ?>
                <h5>Specializes in</h5>
                <ul>
                    <?php foreach ($user->categories as $category) : ?>
                        <li><a href="#"><?php echo $category->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
        <article class="stats">
            <p>Last posted on <time datetime="<?php echo $user->pubdate; ?>" pubdate="pubdate"><?php echo $user->most_recent_post_date; ?></time>.</p>
            <?php if ($show_response_stats === 'on') : ?>
                <ul>
                    <li><?php //echo $user->total_answers; ?>8 answers</li>
                    <li><?php echo $user->total_posts; ?> blog post(s)</li>
                    <li><?php echo $user->total_comments; ?> comment(s)</li>
                </ul>
            <?php endif; ?>
        </article>
    </div>
</section>