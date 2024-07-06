<?php

namespace Envatic\CrudStrap\Fields\Inputs;

class FormInput extends Input
{
    public function imports(): array
    {
        return ['import FormInput from "@/Components/FormInput.vue";'];
    }


    public function render()
    {
        $name = str($this->field->name());
        $label = $this->field->label();
        $placeHolder = $this->field->placeholder();
        $help = $this->field->help();
        $type = $this->field->type()->getInputType();
        return "
<FormInput
    {$label}
	v-model=\"form.{$name}\"
	class=\"col-span-3\"
    :type=\"{$type}\"
	:error=\"form.errors.{$name}\"
    {$placeHolder}
    {$help}
/>
        ";
    }
}
