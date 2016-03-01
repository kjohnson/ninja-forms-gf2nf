<?php

final class NF_GF2NF_FormConverter
{
    public function convert( $form )
    {
        if( ! isset( $form[ 'id' ] ) ) return FALSE;

        $fields = $this->convert_fields( $form );
        $actions = $this->convert_actions( $form );
        $this->convert_form( $form, $fields, $actions );
    }


    /*
    |--------------------------------------------------------------------------
    | Fields
    |--------------------------------------------------------------------------
    */

    private function convert_fields( $form )
    {
        $order = 0;
        $converted_fields = array();
        foreach( $form[ 'fields' ] as $key => $field ){

            $converted_field = $this->convert_field( $field, $form );
            $converted_field[ 'order' ] = $order;
            $order++;

            if( isset( $converted_field[ 'new_fields' ] ) ){
                foreach( $converted_field[ 'new_fields' ] as $new_field ){
                    $new_field[ 'order' ] = $order;
                    $order++;
                    $converted_fields[] = $this->convert_field( $new_field, $form );
                }
                unset( $converted_field[ 'new_fields' ] );
            }

            $converted_fields[] = $converted_field;
        }

        if( isset( $form[ 'button' ] ) ) {

            $submit = $this->convert_button( $form['button'] );
            $submit[ 'order' ] = $order;
            $order++;

            if( $submit ){
                $converted_fields[] = $submit;
            }
        }

        return $converted_fields;
    }

    private function convert_field( $field, $form )
    {
        unset( $field[ 'id' ] );
        unset( $field[ 'size' ] );
        unset( $field[ 'formId' ] );
        unset( $field[ 'inputType' ] );

        if( isset( $field[ 'type' ] ) ){
            switch( $field[ 'type' ] ){
                case 'section':
                    $field[ 'type' ] = 'html';
                    $field[ 'default' ] = $field[ 'description' ];
                    unset( $field[ 'description' ] );
                    break;
                case 'name':
                    // Convert `name` field to `firstname` field.
                    $field[ 'type' ] = 'firstname';
                    $field[ 'label' ] = $field[ 'inputs' ][ 0 ][ 'label' ];

                    // Clone `firstname` field as `lastname` field.
                    $new_field = $field;
                    $new_field[ 'type' ] = 'lastname';
                    $new_field[ 'label' ] = $field[ 'inputs' ][ 1 ][ 'label' ];

                    unset( $field[ 'inputs' ] );
                    unset( $new_field[ 'inputs' ] );

                    // Return a 'new field' for conversion.
                    $field[ 'new_fields' ][] = $new_field;
                    break;
            }
        }

        if( isset( $field[ 'isRequired' ] ) ){
            // TODO: only one of these will be used.
            $field[ 'req' ] = $field[ 'isRequired' ];
            $field[ 'required' ] = $field[ 'isRequired' ];
            unset( $field[ 'isRequired' ] );
        } else {
            // TODO: only one of these will be used.
            $field[ 'req' ] = 0;
            $field[ 'required' ] = 0;
        }

        if( isset( $form[ 'labelPlacement' ] ) ){
            switch( $form[ 'labelPlacement' ] ){
                case 'top_label':
                    $field[ 'label_pos' ] = 'above';
                    break;
            }
        }

        return $field;
    }

    private function convert_button( $button )
    {
        if( ! isset( $button[ 'type' ] ) ) return FALSE;
        if( ! isset( $button[ 'text' ] ) ) return FALSE;
        if( 'text' != $button[ 'type' ] ) return FALSE;

        return array(
            'type' => 'submit',
            'label' => $button[ 'text' ],
            'processing_label' => $button[ 'text' ],

            // TODO: only one of these will be used.
            'req' => 0,
            'required' => 0,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    private function convert_actions( $form )
    {
        $actions = array();

        if( isset( $form[ 'confirmations' ] ) ){
            foreach( $form[ 'confirmations' ] as $confirmation ){
                $action = $this->convert_confirmation( $confirmation, $form );
                $actions[] = $action;
            }
        }

        if( isset( $form[ 'notifications' ] ) ){
            foreach( $form[ 'notifications' ] as $notification ){
                $action = $this->convert_notification( $notification, $form );
                $actions[] = $action;
            }
        }

        return $actions;
    }

    private function convert_confirmation( $confirmation, $form )
    {
        if( isset( $confirmation[ 'name' ] ) ){
            $confirmation[ 'label' ] = $confirmation[ 'name' ];
            unset( $confirmation[ 'name' ] );
        }

        if( isset( $confirmation[ 'type' ] ) ){
            switch( $confirmation[ 'type' ] ){
                case 'message':
                    $confirmation[ 'type' ] = 'successmessage';
                    break;
            }
        }

        return $confirmation;
    }

    private function convert_notification( $notification, $form )
    {
        if( isset( $notification[ 'name' ] ) ){
            $notification[ 'label' ] = $notification[ 'name' ];
            unset( $notification[ 'name' ] );
        }

        if( isset( $notification[ 'toType' ] ) ){

            switch( $notification[ 'toType' ] ){
                case 'field':
                    $field_id = $notification[ 'toField' ];
                    $notification[ 'to' ] = "{field:$field_id}";
                    break;
                case 'email':
                    break;
                case 'routing':
                    // TODO: requires conditional logic.
                    break;
            }
        }

        if( isset( $notification[ 'from' ] ) ){
            $notification[ 'from' ] = $this->convert_merge_tags( $notification[ 'from' ] );
        }

        unset( $notification[ 'type' ] );
        unset( $notification[ 'event' ] );
        unset( $notification[ 'toType' ] );
        unset( $notification[ 'toField' ] );

        $notification[ 'type' ] = 'email';

        return $notification;
    }

    private function convert_merge_tags( $value )
    {
        //TODO: Convert Merge Tags.
        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    private function convert_form( $form, $fields, $actions )
    {
        /* Field Stuff */
        unset( $form[ 'fields' ] );
        unset( $form[ 'button' ] );
        unset( $form[ 'labelPlacement' ] );
        unset( $form[ 'descriptionPlacement' ] );

        /* Action Stuff */
        unset( $form[ 'confirmation' ] );
        unset( $form[ 'notification' ] );
        unset( $form[ 'confirmations' ] );
        unset( $form[ 'notifications' ] );
        unset( $form[ 'autoResponder' ] );

        /* Form Stuff */
        unset( $form[ 'id' ] );
        unset( $form[ 'postAuthor' ] );
        unset( $form[ 'postStatus' ] );
        unset( $form[ 'description' ] );
        unset( $form[ 'postCategory' ] );
        unset( $form[ 'useCurrentUserAsAuthor' ] );

        if( isset( $form[ 'maxEntriesAllowed' ] ) ){
            $form[ 'limit_submissions' ] = $form[ 'maxEntriesAllowed' ];
            unset( $form[ 'maxEntriesAllowed' ] );
        }

        $converted_form = Ninja_Forms()->form()->get();
        $converted_form->update_settings( $form );
        $converted_form->save();

        $form_id = $converted_form->get_id();

        foreach( $fields as $field ){
            $converted_field = Ninja_Forms()->form( $form_id )->field()->get();
            $converted_field->update_settings( $field );
            $converted_field->save();
        }

        foreach( $actions as $action ){
            $converted_action = Ninja_Forms()->form( $form_id )->action()->get();
            $converted_action->update_settings( $action );
            $converted_action->save();
        }

        return $converted_form;
    }
}