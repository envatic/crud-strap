<?php

namespace Envatic\CrudStrap\Fields\Inputs;

class FormDatePicker extends Input
{

    public function imports(): array
    {
        return [
            'import {DatePicker} from "v-calendar";',
            'import {UseTimeAgo} from "@vueuse/components";',
            'import FormLabel from "@/Components/FormLabel.vue";'
        ];
    }


    public function render()
    {
        $name = str($this->field->name());
        $label = $this->field->label(true);
        $placeHolder = $this->field->placeholder();
        $type = $this->field->type()->getInputType();
        return  <<<DTX
        <div>
			<FormLabel class="mb-1">{{ \$t("{$label}") }}</FormLabel>
			<DatePicker
				v-model="form.{$name}"
				mode="$type"
				is24hr
			>
				<template v-slot="{inputValue, inputEvents}">
					<input
						class="bg-white border-gray-300 text-gray-900 rounded-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-900 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white border block w-full focus:outline-none focus:ring-1 appearance-none py-2 text-sm pl-2 pr-2"
						:value="inputValue"
                        {$placeHolder}
						v-on="inputEvents"
					/>
				</template>
			</DatePicker>
			<p
				v-if="form.errors.{$name}"
				class="text-red-500"
			>
				{{ form.errors.{$name} }}
			</p>
			<UseTimeAgo
				v-else
				v-slot="{timeAgo}"
				:time="form.{$name}"
			>
				<p class="text-sx font-semibold text-emerald-500">
					{{ timeAgo }}
				</p>
			</UseTimeAgo>
		</div>
DTX;
    }
}
