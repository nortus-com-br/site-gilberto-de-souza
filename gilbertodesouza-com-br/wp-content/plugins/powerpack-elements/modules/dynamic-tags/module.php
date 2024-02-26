<?php
namespace PowerpackElements\Modules\DynamicTags;

use PowerpackElements\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Module extends Module_Base {

	const POWERPACK_TAGS_GROUP = 'powerpack-tags';

	public function get_name() {
		return 'pp-dynamic-tags';
	}

	public function get_widgets() {
		return [];
	}

	/**
	 * Construct
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'elementor/dynamic_tags/register_tags', [ $this, 'register_tags' ], 15, 1 );
	}

	public function register_tags( $dynamic_tags ) {
		$tags = [
			'Taxonomy_Thumbnail',
		];

		\Elementor\Plugin::$instance->dynamic_tags->register_group( self::POWERPACK_TAGS_GROUP, [
			'title' => __( 'PowerPack', 'powerpack' ),
		] );

		foreach ( $tags as $tag ) {
			switch ( $tag ) {
				case 'Taxonomy_Thumbnail':
					$enabled_thumbnails = get_option( 'pp_elementor_taxonomy_thumbnail_enable' );
					if ( 'enabled' === $enabled_thumbnails ) {
						$dynamic_tags->register_tag( 'PowerpackElements\\Modules\\DynamicTags\\Tags\\' . $tag );
					}
					break;
				default:
					$dynamic_tags->register_tag( 'PowerpackElements\\Modules\\DynamicTags\\Tags\\' . $tag );
			}
		}
	}

}
