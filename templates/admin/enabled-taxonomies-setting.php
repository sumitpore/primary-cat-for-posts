<?php if ( $taxonomies ) : ?>
		<?php foreach ( $taxonomies as $taxonomy_name => $taxonomy ) : ?>
			<?php
			printf(
				'<input type="checkbox" name="pcp_options[enabled_taxonomies][]" id="%s" value="%s" %s>%s<br />',
				esc_attr( $taxonomy_name ),
				esc_attr( $taxonomy_name ),
				in_array( $taxonomy_name, $enabled_taxonomies ) ? 'checked' : '',
				esc_html( $taxonomy->labels->name )
			);
			?>
		<?php endforeach; ?>
	<?php
endif;
