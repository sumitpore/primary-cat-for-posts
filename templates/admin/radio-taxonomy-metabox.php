<div id="taxonomy-<?php esc_attr_e( $taxonomy ); ?>" class="radio-buttons-for-taxonomies categorydiv form-no-clear">

    <?php // Below hidden element submits cat as -1 if user has not selected any cat. ?>
    <input type="hidden" name="<?php esc_attr_e( $radio_input_name); ?>" value="-1">

    <ul id="<?php esc_attr_e( $taxonomy ); ?>-tabs" class="category-tabs">
        <li class="tabs">
            <a href="#<?php esc_attr_e( $taxonomy ); ?>-all" tabindex="3">
                <?php echo esc_html( $taxonomy_object->labels->all_items ); ?>
            </a>
        </li>
        <li class="hide-if-no-js">
            <a href="#<?php esc_attr_e( $taxonomy ); ?>-pop" tabindex="3">
                <?php esc_html_e( 'Most Used', PRIMARY_CAT_FOR_POSTS_TEXTDOMAIN ); ?>
            </a>
        </li>
    </ul>

    <?php wp_nonce_field( 'radio_nonce-' . $taxonomy, '_radio_nonce-' . $taxonomy ); ?>

    <div id="<?php esc_attr_e( $taxonomy ); ?>-pop" class="tabs-panel" style="display: none;">
        <ul id="<?php esc_attr_e( $taxonomy ); ?>checklist-pop" class="categorychecklist form-no-clear" >
            <?php $popular_ids = []; ?>
            <?php foreach ( $popular as $term ) : ?>
                <?php
                    $popular_ids[] = $term->term_id;
                    $value = is_taxonomy_hierarchical( $taxonomy ) ? $term->term_id : $term->slug;
                    $id = 'popular-' . $taxonomy . '-' . $term->term_id;
                ?>
                <li id="<?php esc_attr_e( $id ); ?>">
                    <label class="selectit">
                        <?php
                            printf(
                                '<input type="radio" id="%s" value="%s" %s %s>&nbsp; %s<br />',
                                esc_attr( 'in-' . intval( $id ) ),
                                esc_attr( $value ),
                                checked( $single_term_id, $term->term_id, false ),
                                esc_html( $disabled ),
                                esc_attr( $term->name )
                            );
                        ?>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div id="<?php esc_attr_e( $taxonomy ); ?>-all" class="tabs-panel">
        <ul id="<?php esc_attr_e( $taxonomy ); ?>checklist" data-wp-lists="list:<?php esc_attr_e( $taxonomy ); ?>" class="categorychecklist form-no-clear">
                <?php
                wp_terms_checklist(
                    $post->ID, array(
                        'taxonomy' => $taxonomy,
                        'popular_cats' => $popular_ids,
                    )
                );
                ?>
        </ul>
    </div>
</div>