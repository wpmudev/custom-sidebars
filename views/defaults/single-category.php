<div class="defaultsSelector">
<h3 class="csh3title" title="<?php _e( 'Click to toogle',  CSB_LANG ); ?>"><?php _e( 'By category', CSB_LANG ); ?></h3>
<div class="defaultsContainer"><?php if ( ! empty( $categories ) ) : foreach ( $categories as $c ) : ?>

        <div id="category-page-<?php echo esc_attr( $c->term_id ); ?>" class="postbox closed" >
            <h3 class='hndle'><span><?php echo esc_html( $c->name ); ?></span></h3>

            <div class="inside">
            <?php if ( ! empty( $modifiable ) ) : foreach ( $modifiable as $m ): $sb_name = $allsidebars[$m]['name']; ?>
                <p><?php echo esc_html( $sb_name ); ?>:
                    <select name="category_posts_<?php echo esc_attr( $c->cat_ID ); ?>_<?php echo esc_attr( $m ); ?>">
                        <option value=""></option>
                    <?php foreach ( $allsidebars as $key => $sb ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( @$defaults['category_posts'][$c->cat_ID][$m], $key ); ?>>
                            <?php echo esc_attr( $sb['name'] ); ?>
                        </option>
                    <?php endforeach;?>
                    </select>
                    <?php if ( ! isset( $cs_is_defaults ) ) : ?>
                        <a href="#" class="selectSidebar"><?php printf( __( '<- Set %s here.',  CSB_LANG ), $current_sidebar['name'] ); ?></a>
                    <?php endif; ?>
                </p>
            <?php endforeach; else : ?>
                <p><?php _e( 'There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.', CSB_LANG ); ?></p>
            <?php endif;?>
            </div>

        </div>

        <?php endforeach; else : ?>
            <p><?php _e( 'There are no categories available.', CSB_LANG ); ?></p>
        <?php endif; ?></div>
</div>