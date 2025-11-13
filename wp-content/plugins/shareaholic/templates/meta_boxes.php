<label>
	<input type='checkbox' name='shareaholic[disable_share_buttons]'
	<?php if ( get_post_meta( $post->ID, 'shareaholic_disable_share_buttons', true ) ) { ?>
	checked
	<?php } ?>>
	<?php printf( __( 'Hide Share Buttons', 'shareaholic' ) ); ?>
</label>

<br>

<label>
	<input type='checkbox' name='shareaholic[disable_recommendations]'
	<?php if ( get_post_meta( $post->ID, 'shareaholic_disable_recommendations', true ) ) { ?>
	checked
	<?php } ?>>
	<?php printf( __( 'Hide Related Content', 'shareaholic' ) ); ?>
</label>

<br>

<label>
	<input type='checkbox' name='shareaholic[exclude_recommendations]'
	<?php if ( get_post_meta( $post->ID, 'shareaholic_exclude_recommendations', true ) ) { ?>
	checked
	<?php } ?>>
	<?php printf( __( 'Exclude from Related Content', 'shareaholic' ) ); ?>
</label>

<br>

<label>
	<input type='checkbox' name='shareaholic[disable_open_graph_tags]'
	<?php if ( get_post_meta( $post->ID, 'shareaholic_disable_open_graph_tags', true ) ) { ?>
	checked
	<?php } ?>>
	<?php printf( __( 'Do not include Open Graph tags', 'shareaholic' ) ); ?>
</label>
