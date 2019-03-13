<?php if( $post_types ) : ?>
        <?php foreach( $post_types as $post_type_slug => $post_type_name) : ?>
        <?php 
            printf(
                    '<input type="checkbox" name="pcp_options[enabled_post_types][]" id="%s" value="%s" %s>%s<br />',
                    esc_attr( $post_type_slug ),
                    esc_attr( $post_type_slug ),
                    in_array( $post_type_slug, $enabled_post_types ) ? 'checked' : '',
                    esc_html( $post_type_name )
            );
        ?>
        <?php endforeach; ?>
<?php endif; ?>

