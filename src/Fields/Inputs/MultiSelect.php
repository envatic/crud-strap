<?php

namespace Envatic\CrudStrap\Fields\Inputs;

class MultiSelect extends Input
{

    public function imports()
    {
        return <<<IMP
import {HiSolidChevronDown} from "oh-vue-icons/icons";
import MultiSelect from "@/Components/MultiSelect/MultiSelect.vue";
import VueIcon from "@/Components/VueIcon.vue";
IMP;
    }


    public function render()
    {
        $name = str($this->field->name());
        $label = $this->field->label(true);
        $placeholder = $this->field->placeholder(true);
        $options = $this->field->options()->getRadioCardOptions()->implode("\n\t\t\t\t\t");
        return  <<<DTX
         <div>
			<FormLabel class="mb-1">{{ \$t("{$label}") }}</FormLabel>
			<Multiselect
				:options="$options"
				valueProp="value"
				label="label"
				:placeholder="\$t('$placeholder')"
				v-model="{$name}"
				searchable
				closeOnSelect
				object
			>
				<template #caret="{isOpen}">
					<VueIcon
						:class="{'rotate-180': isOpen}"
						class="mr-3 relative z-10 opacity-60 flex-shrink-0 flex-grow-0 transition-transform duration-500 w-6 h-6"
						:icon="HiSolidChevronDown"
					/> </template
			></Multiselect>
		</div>
       
DTX;
    }
}
