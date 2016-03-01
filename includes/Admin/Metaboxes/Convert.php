<?php

final class NF_GF2NF_Admin_Metaboxes_Convert
{

    public function __construct()
    {
        add_action( 'admin_init', array( $this, 'register' ) );
        add_action( 'plugins_loaded', array( $this, 'convert_form_listener' ) );
    }

    public function register()
    {
        add_meta_box(
            'nf_import_export_forms_GF2NF',
            __( 'Convert Gravity Forms to Ninja Forms', 'ninja-forms-gf2nf' ),
            array( $this, 'template' ),
            'nf_import_export_forms',
            'advanced',
            'low'
        );
    }

    public function template()
    {
        NF_GF2NF::template( 'admin-metabox-convert.html.php' );
    }

    public function convert_form_listener()
    {
        if( isset( $_FILES[ 'nf_gf2nf_convert_form' ] ) && $_FILES[ 'nf_gf2nf_convert_form' ] ){

            $import = file_get_contents( $_FILES[ 'nf_gf2nf_convert_form' ][ 'tmp_name' ] );

            $forms = json_decode( $import, TRUE );

            $converter = new NF_GF2NF_FormConverter();
            $converted_forms = array();
            foreach( $forms as $form ){
                if( ! isset( $form[ 'id' ] ) ) continue;
                $converted_forms[] = $converter->convert( $form );
            }

            if( 1 == count( $converted_forms ) ){
                $form_id = $converted_forms[ 0 ]->get_id();
                wp_redirect( admin_url( "admin.php?page=ninja-forms&form_id=$form_id" ) );
            } else {
                wp_redirect( admin_url( 'admin.php?page=ninja-forms' ) );
            }
        }
    }
}