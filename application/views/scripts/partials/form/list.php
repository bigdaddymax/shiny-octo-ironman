<?php
if (is_array($this->forms)) {
    echo '<table>';
    foreach ($this->forms as $form) {
        echo '<tr onClick="openForm(\'form_'.$form->formId.'\')"><td>'.$form->formName.'</td><td>'.$form->formId.'</td><td>'.$form->date.'</td></tr>';
    }
    echo '</table>';
}