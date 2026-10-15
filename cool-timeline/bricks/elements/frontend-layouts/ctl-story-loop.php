<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
if ( ! class_exists( 'CTL_Bricks_Story_Loop' ) ) {

	class CTL_Bricks_Story_Loop {

		private $element    = null;
		private $story_data = array();
		private $story_id   = '';
		private $story_index = 0;

		public function __construct( $element ) {
			$this->element = is_object( $element ) ? $element : null;
		}

		public function ctl_story_loop( $story, $index, $story_class ) {
			if ( ! is_object( $this->element ) ) {
				return '';
			}

			$this->story_data  = is_array( $story ) ? $story : array();
			$this->story_index = (int) $index;
			$story_class       = is_array( $story_class ) ? $story_class : array();
			$element_id       = isset( $this->element->id ) ? $this->element->id : '0';
			$this->story_id   = 'story-' . $element_id . '-' . $index;

			$show_year  = $this->ctl_should_show_year();
			$has_labels = true;

			if ( $has_labels ) {
				$story_class[] = 'ctl-has-labels';
			}

			$html = '';

			if ( $show_year ) {
				$html .= $this->ctl_story_year_label();
			}

			$html .= '<div id="' . esc_attr( $this->story_id ) . '" class="' . esc_attr( implode( ' ', $story_class ) ) . '" data-ctl-story-index="' . (int) $index . '">';
			$html .= $this->ctl_story_label();
			$html .= $this->ctl_story_icon();
			$padding = isset( $this->element->settings['contentBoxPadding'] ) ? $this->element->settings['contentBoxPadding'] : array();
			$flush_title = is_array( $padding );
			foreach ( array( 'top', 'right', 'left' ) as $side ) {
				$value = isset( $padding[ $side ] ) ? trim( (string) $padding[ $side ] ) : '0';
				// Reduce only zero sums/differences; leave other CSS expressions unchanged.
				if ( 0 === stripos( $value, 'calc(' ) ) {
					$zero = '[+-]?(?:0+(?:\.0*)?|\.0+)(?:[a-z]+|%)?';
					$pattern = '/(?:calc)?\(\s*' . $zero . '(?:\s+[+-]\s+' . $zero . ')*\s*\)/i';
					for ( $depth = 0; $depth < 16; $depth++ ) {
						$reduced = preg_replace( $pattern, '0', $value );
						if ( $reduced === $value ) {
							break;
						}
						$value = $reduced;
					}
				}
				if ( '' !== $value && ! preg_match( '/^[+-]?(?:0+(?:\.0*)?|\.0+)(?:[a-z]+|%)?$/i', $value ) ) {
					$flush_title = false;
				}
			}
			$html .= '<div class="ctl-bricks-content' . ( $flush_title ? ' ctl-bricks-title-flush' : '' ) . '">';
			$html .= '<div class="ctl-bricks-arrow" aria-hidden="true"></div>';
			$html .= $this->ctl_story_title();
			$html .= $this->ctl_render_image_media( 'ctl-bricks-media' );
			$html .= $this->ctl_story_desc();
			$html .= '</div>';
			$html .= '</div>';

			return $html;
		}

		private function ctl_is_builder() {
			return is_object( $this->element ) && isset( $this->element->is_frontend ) && empty( $this->element->is_frontend );
		}

		private function ctl_show_placeholders() {
			return true;
		}

		private function ctl_year_text_raw() {
			$year = '';

			if ( isset( $this->story_data['year'] ) && ! empty( $this->story_data['year'] ) ) {
				$year = $this->element->render_dynamic_data( $this->story_data['year'] );
			}

			return is_string( $year ) ? trim( $year ) : '';
		}

		private function ctl_should_show_year() {
			if ( ! isset( $this->story_data['showYearLabel'] ) ) {
				return false;
			}

			$val = $this->story_data['showYearLabel'];

			return true === $val || 1 === $val || '1' === $val || 'yes' === $val || 'on' === $val || 'true' === $val;
		}

		private function ctl_builder_inline_attrs( $field, $placeholder ) {
			if ( ! $this->ctl_is_builder() ) {
				return '';
			}

			return ' contenteditable="true"'
				. ' data-ctl-inline="' . esc_attr( $field ) . '"'
				. ' data-ctl-bricks-placeholder="' . esc_attr( $placeholder ) . '"'
				. ' spellcheck="true"';
		}

		private function ctl_render_placeholder( $text, $class, $field = '' ) {
			$attrs = '' !== $field ? $this->ctl_builder_inline_attrs( $field, $text ) : '';

			return '<div class="' . esc_attr( trim( $class . ' ctl-bricks-placeholder' ) ) . '"' . $attrs . '>' . esc_html( $text ) . '</div>';
		}

		private function ctl_placeholder_image_icon() {
			return '<svg class="ctl-bricks-placeholder-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 56" aria-hidden="true" focusable="false"><circle cx="56" cy="16" r="8"/><path d="M0 56l24-28 14 16 12-16 30 28H0z"/></svg>';
		}

		private function ctl_render_image_placeholder( $media_class ) {
			$class = trim( $media_class . ' ctl-bricks-media-placeholder' );
			$inner = '<div class="ctl-bricks-placeholder-media">' . $this->ctl_placeholder_image_icon() . '</div>';

			return '<div class="' . esc_attr( $class ) . '">' . $inner . '</div>';
		}

		private function ctl_get_image_html( $image, $size = 'large', $attr = array() ) {
			if ( ! isset( $image ) || ! is_array( $image ) || empty( $image ) ) {
				return '';
			}

			$size  = ( isset( $size ) && is_string( $size ) && '' !== $size ) ? $size : 'large';
			$attr  = is_array( $attr ) ? $attr : array();
			$title = ( isset( $this->story_data['title'] ) && ! empty( $this->story_data['title'] ) ) ? $this->story_data['title'] : '';
			$html  = '';

			if ( isset( $image['id'] ) && ! empty( $image['id'] ) ) {
				$attachment_id = absint( $image['id'] );
				$html          = wp_get_attachment_image( $attachment_id, $size, false, $attr );

				if ( empty( $html ) ) {
					$html = wp_get_attachment_image( $attachment_id, 'large', false, $attr );
				}
			} elseif ( isset( $image['url'] ) && ! empty( $image['url'] ) ) {
				$img_attr = '';

				foreach ( $attr as $key => $value ) {
					if ( '' === $value && 'alt' !== $key ) {
						continue;
					}

					$img_attr .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
				}

				$html = '<img src="' . esc_url( $image['url'] ) . '" alt="' . esc_attr( $title ) . '"' . $img_attr . '>';
			}

			if ( '' !== $html && function_exists( 'wp_filter_content_tags' ) ) {
				$html = wp_filter_content_tags( $html );
			}

			return $html;
		}

		private function ctl_render_image_media( $media_class ) {
			if ( ! isset( $this->story_data['image'] ) || ! is_array( $this->story_data['image'] ) || empty( $this->story_data['image'] ) ) {
				return $this->ctl_show_placeholders()
					? $this->ctl_render_image_placeholder( $media_class )
					: '';
			}

			$image = $this->story_data['image'];
			$size  = 'large';

			if ( isset( $image['size'] ) && is_string( $image['size'] ) && '' !== $image['size'] ) {
				$size = $image['size'];
			}

			$html = $this->ctl_get_image_html( $image, $size );

			if ( '' === $html ) {
				return $this->ctl_show_placeholders()
					? $this->ctl_render_image_placeholder( $media_class )
					: '';
			}

			return '<div class="' . esc_attr( trim( $media_class . ' ' . $size ) ) . '">' . $html . '</div>';
		}

		private function ctl_story_title() {
			$placeholder = __( 'Add title here', 'cool-timeline' );
			$inline      = $this->ctl_builder_inline_attrs( 'title', $placeholder );

			if ( ! isset( $this->story_data['title'] ) || empty( $this->story_data['title'] ) ) {
				return $this->ctl_show_placeholders()
					? $this->ctl_render_placeholder( $placeholder, 'ctl-bricks-title', 'title' )
					: '';
			}

			$title = $this->element->render_dynamic_data( $this->story_data['title'] );

			return '<div class="ctl-bricks-title"' . $inline . '>' . esc_html( $title ) . '</div>';
		}

		private function ctl_story_desc() {
			$placeholder = __( 'Add description here', 'cool-timeline' );
			$inline      = $this->ctl_builder_inline_attrs( 'description', $placeholder );

			if ( ! isset( $this->story_data['description'] ) || empty( $this->story_data['description'] ) ) {
				return $this->ctl_show_placeholders()
					? $this->ctl_render_placeholder( $placeholder, 'ctl-bricks-description', 'description' )
					: '';
			}

			$content = $this->element->render_dynamic_data( $this->story_data['description'] );

			return '<div class="ctl-bricks-description"' . $inline . '>' . wp_kses_post( $content ) . '</div>';
		}

		private function ctl_get_icon_type() {
			$raw = 'icon';

			if ( isset( $this->story_data['iconType'] ) && ! empty( $this->story_data['iconType'] ) ) {
				$raw = $this->story_data['iconType'];
			}

			if ( is_array( $raw ) ) {
				$raw = reset( $raw );
			}

			$type = strtolower( trim( (string) $raw ) );

			return ( 'dot' === $type ) ? 'dot' : 'icon';
		}

		private function ctl_story_icon() {
			if ( 'dot' === $this->ctl_get_icon_type() ) {
				return '<div class="ctl-bricks-icondot" aria-hidden="true"><span class="ctl-bricks-icondot-inner"></span></div>';
			}

			$icon = ( isset( $this->story_data['icon'] ) && ! empty( $this->story_data['icon'] ) )
				? $this->story_data['icon']
				: array(
					'library' => 'themify',
					'icon'    => 'ti-time',
				);

			return '<div class="ctl-bricks-icon">' . \Bricks\Element::render_icon( $icon ) . '</div>';
		}

		private function ctl_story_label() {
			$date = '';
			$sub  = '';

			if ( isset( $this->story_data['dateLabel'] ) && ! empty( $this->story_data['dateLabel'] ) ) {
				$date = $this->element->render_dynamic_data( $this->story_data['dateLabel'] );
			}

			if ( isset( $this->story_data['subLabel'] ) && ! empty( $this->story_data['subLabel'] ) ) {
				$sub = $this->element->render_dynamic_data( $this->story_data['subLabel'] );
			}

			$builder = $this->ctl_show_placeholders();
			if ( '' === $date && '' === $sub && ! $builder ) {
				return '';
			}

			$html  = '<div class="ctl-bricks-labels">';
			$html .= $this->ctl_story_label_field( 'dateLabel', $date, 'ctl-bricks-label-big', __( 'Add Label', 'cool-timeline' ) );
			$html .= $this->ctl_story_label_field( 'subLabel', $sub, 'ctl-bricks-label-small', __( 'Add Sub Label', 'cool-timeline' ) );
			$html .= '</div>';

			return $html;
		}

		private function ctl_story_label_field( $field, $value, $class, $placeholder ) {
			$has_value = '' !== $value;

			if ( ! $has_value && ! $this->ctl_show_placeholders() ) {
				return '';
			}

			$classes = $class;
			$text    = $has_value ? esc_html( $value ) : esc_html( $placeholder );
			if ( ! $has_value ) {
				$classes .= ' ctl-bricks-placeholder';
			}

			return '<div class="' . esc_attr( $classes ) . '"' . $this->ctl_builder_inline_attrs( $field, $placeholder ) . '>' . $text . '</div>';
		}

		private function ctl_story_year_label() {
			if ( ! $this->ctl_should_show_year() ) {
				return '';
			}

			$year = $this->ctl_year_text_raw();
			if ( '' === $year ) {
				$year = '2000';
			}

			$inline = $this->ctl_builder_inline_attrs( 'year', '' );

			return '<div class="ctl-bricks-year" data-ctl-story-index="' . (int) $this->story_index . '"><div class="ctl-bricks-year-text"><span class="ctl-bricks-year-text-inner"' . $inline . '>' . esc_html( $year ) . '</span></div></div>';
		}
	}
}
