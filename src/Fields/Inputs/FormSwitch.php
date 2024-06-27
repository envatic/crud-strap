<?php

namespace Envatic\CrudStrap\Fields\Inputs;

class FormSwitch extends Input
{
    public function imports()
    {
        return <<<IMP
import FormSwitch from "@/Components/FormSwitch.vue";
IMP;
    }
    public function render()
    {
        $name = str($this->field->name());
        $label = $this->field->label(true);
        return <<< SWITCH
        <FormSwitch v-model="form.{$name}">{{\$t('$label')}}</FormSwitch>
SWITCH;
    }
}
