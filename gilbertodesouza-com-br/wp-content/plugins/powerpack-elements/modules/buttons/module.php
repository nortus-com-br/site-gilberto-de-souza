<?php
namespace PowerpackElements\Modules\Buttons;

use PowerpackElements\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Module_Base {

	public function get_name() {
		return 'pp-buttons';
	}

	public function get_widgets() {
		return [
			'Buttons',
		];
	}
}
