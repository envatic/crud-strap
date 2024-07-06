<?php

namespace Envatic\CrudStrap\Fields\Inputs;

class RadioSelect extends Input
{

    public function imports(): array
    {
        return ['import RadioSelect from "@/Components/RadioSelect.vue";'];
    }


    public function render()
    {
        $name = str($this->field->name());
        $label = $this->field->label(true);
        $options = $this->field
            ->options()
            ->getRadioSelectOptions()
            ->implode(",\n\t\t\t\t\t");
        return  <<<DTX
         <div>
			<FormLabel class="mb-1">{{ \$t("{$label}") }}</FormLabel>
			 <RadioSelect
    			v-model="form.{$name}"
    			:options="[$options]"
    		/>
			<p
				v-if="form.errors.{$name}"
				class="text-xs text-red-500 mt-1"
			>
				{{ form.errors.{$name} }}
			</p>
		</div>
       
DTX;
    }
}
