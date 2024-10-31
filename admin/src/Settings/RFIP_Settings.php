<?php
namespace RFIP\Admin\Settings;

class RFIP_Settings {

    private $locale = "rao-forms";
    public function __construct() {
        add_action('init', array($this, "load_menu"));
    }

    public function load_menu() {
        $raoFormsWPMenu = new RFIP_Menu( array(
            'slug' => 'rao-forms',
            'title' => __( 'RaoForms', $this->locale ),
            'desc' => __( 'RaoForms Dashboard', $this->locale ),
            'icon' => 'dashicons-welcome-widgets-menus',
            'function'  => '',
            'capability' => 'manage_options',
			'position' => 99,

        ) );


        $connecttoCF7_tab = new RFIP_Tab(
            array(
                'slug' => 'connect-to-cf7',
                'title'=> __( "Connect to CF7", $this->locale ),
            ),
                
                $raoFormsWPMenu
            
        );

        $connecttoWPForms_tab = new RFIP_Tab(
            array(
                'slug'  =>  "connect-to-wpforms",
                'title' =>  __( 'Connect to WP Forms', $this->locale ),
            ),
            $raoFormsWPMenu
        );

        $connecttoNinjaForms_tab = new RFIP_Tab(
            array(
                'slug'  =>  "connect-to-ninjaforms",
                'title' =>  __( 'Connect to Ninja Forms', $this->locale ),
            ),
            $raoFormsWPMenu
        );



    }
}