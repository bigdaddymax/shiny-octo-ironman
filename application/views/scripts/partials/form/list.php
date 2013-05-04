<?php
if (is_array($this->forms)) {
    echo '<table>';
    foreach ($this->forms as $form) {
        $class = '';
        if ($form->final){
            if ('approve' == $form->decision){
            $class = ' class="approved" ';
            } else {
                $class = ' class="declined" ';
            }
        }
        if (!$form->public){
            $class=' class="private"';
        }
        echo '<tr ' .$class. ' onClick="openForm(\'form_'.$form->formId.'\')" ><td>'.$form->formName.'</td><td>'.$form->formId.'</td><td>'.$form->date.'</td></tr>';
    }
    echo '</table>';
}