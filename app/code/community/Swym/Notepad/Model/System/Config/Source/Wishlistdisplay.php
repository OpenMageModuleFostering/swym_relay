<?php
class Swym_Notepad_Model_System_Config_Source_Wishlistdisplay
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'text',
                'label' => 'Text'
            ),
            array(
                'value' => 'icon',
                'label' => 'Icon'
            ),
        );
    }
}