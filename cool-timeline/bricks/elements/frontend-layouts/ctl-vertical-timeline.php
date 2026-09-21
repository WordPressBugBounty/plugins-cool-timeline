<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.Security.EscapeOutput.OutputNotEscaped
$stories       = ( isset( $stories ) && is_array( $stories ) ) ? $stories : array();
$layout        = ( isset( $layout ) && 'one-sided' === $layout ) ? 'one-sided' : 'centered';
$is_both_sided = ( 'one-sided' !== $layout );
$layout_class  = $is_both_sided ? 'ctl-both-sided' : 'ctl-vertical-right';

echo '<div ' . $this->render_attributes( '_root' ) . '>';
echo '<div class="ctl-wrapper ctl-vertical ' . esc_attr( $layout_class ) . '">';
echo '<div class="ctl-timeline">';
echo '<div class="ctl-start"></div>';

$ctl_story_loop_obj = new CTL_Bricks_Story_Loop( $this );

foreach ( $stories as $index => $story ) {
	$alignment = ( $is_both_sided && 1 === ( (int) $index ) % 2 ) ? 'ctl-story-left' : 'ctl-story-right';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup built with escaping in CTL_Bricks_Story_Loop.
	echo $ctl_story_loop_obj->ctl_story_loop( $story, $index, array( 'ctl-story', $alignment ) );
}

echo '<div class="ctl-end"></div>';
echo '</div>';
echo '</div>';
echo '</div>';
