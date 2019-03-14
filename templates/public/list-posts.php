<?php global $post; ?>
<?php if ( $posts ) : ?>
	<?php
	foreach ( $posts as $post ) :
		setup_postdata( $post );
		?>
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php the_content(); ?>
		<?php
	endforeach;
	wp_reset_postdata();
	?>
<?php endif; ?>
