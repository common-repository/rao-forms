<?php
namespace RFIP\Admin\Settings;

class RFIP_SubMenu extends RFIP_Menu {

	function __construct( $options, RFIP_Menu $parent ){
		parent::__construct( $options );

		$this->parent_id = $parent->settings_id;
	}

}