<?php

/**
 * Plugin Name: NYU Tags Accessibility Fix
 * Description: This plugin patches WordPress to work with the known Safari and VoiceOver incomatibility with AJAX. Plugin required to be Network Activated to work correctly.
 * Plugin URI: https://github.com/KonainM/NYU-Tags-Accessibility-Fix.git
 * Author: Konain Mukadam
 * Author URI: https://github.com/KonainM/
 * Version: 1.0
 */

 /*Original Meta Box Removal*/

 function nyu_post_tags_meta_box_remove() {
 	$id = 'tagsdiv-post_tag';
 	$post_type = 'post';
 	$position = 'side';
 	remove_meta_box( $id, $post_type, $position );
 }
 add_action( 'admin_menu', 'nyu_post_tags_meta_box_remove');

/*Add Fixed Meta Box*/

function nyu_add_new_tags_metabox(){
	$id = 'nyutagsdiv-post_tag';
	$heading = 'Tags';
	$callback = 'nyu_metabox_content';
	$post_type = 'post';
	$position = 'side';
	$pri = 'default';
	add_meta_box( $id, $heading, $callback, $post_type, $position, $pri );
}
add_action( 'admin_menu', 'nyu_add_new_tags_metabox');

/*Fixed Meta Box*/

function nyu_metabox_content( $post, $box ) {
	$defaults = array( 'taxonomy' => 'post_tag' );
	if ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) ) {
		$args = array();
	} else {
		$args = $box['args'];
	}
	$r = wp_parse_args( $args, $defaults );
	$tax_name = esc_attr( $r['taxonomy'] );
	$taxonomy = get_taxonomy( $r['taxonomy'] );
	$user_can_assign_terms = current_user_can( $taxonomy->cap->assign_terms );
	$comma = _x( ',', 'tag delimiter' );
	$terms_to_edit = get_terms_to_edit( $post->ID, $tax_name );
	if ( ! is_string( $terms_to_edit ) ) {
		$terms_to_edit = '';
	}
?>
<div class="tagsdiv" id="<?php echo $tax_name; ?>">
	<div class="jaxtag">
	<div class="nojs-tags hide-if-js">
		<label for="tax-input-<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_or_remove_items; ?></label>
		<p><textarea name="<?php echo "tax_input[$tax_name]"; ?>" rows="3" cols="20" class="the-tags" id="tax-input-<?php echo $tax_name; ?>" <?php disabled( ! $user_can_assign_terms ); ?> aria-describedby="new-tag-<?php echo $tax_name; ?>-desc"><?php echo str_replace( ',', $comma . ' ', $terms_to_edit ); // textarea_escaped by esc_attr() ?></textarea></p>
	</div>
 	<?php if ( $user_can_assign_terms ) : ?>
	<div class="ajaxtag hide-if-no-js">
		<label class="screen-reader-text" for="new-tag-<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_new_item; ?></label>
		<p><input data-wp-taxonomy="<?php echo $tax_name; ?>" placeholder=" " type="text" id="new-tag-<?php echo $tax_name; ?>" name="newtag[<?php echo $tax_name; ?>]" class="newtag form-input-tip" size="16" autocomplete="off" aria-describedby="new-tag-<?php echo $tax_name; ?>-desc" value="" />
		<input type="button" class="button tagadd" value="<?php esc_attr_e('Add'); ?>" /></p>
	</div>
	<p class="howto" id="new-tag-<?php echo $tax_name; ?>-desc"><?php echo $taxonomy->labels->separate_items_with_commas; ?></p>
	<?php elseif ( empty( $terms_to_edit ) ): ?>
		<p><?php echo $taxonomy->labels->no_terms; ?></p>
	<?php endif; ?>
	</div>
	<ul class="tagchecklist" role="list"></ul>
</div>
<?php if ( $user_can_assign_terms ) : ?>
<p class="hide-if-no-js"><button type="button" class="button-link tagcloud-link" id="link-<?php echo $tax_name; ?>" aria-expanded="false"><?php echo $taxonomy->labels->choose_from_most_used; ?></button></p>
<?php endif; ?>
<?php
}

?>
