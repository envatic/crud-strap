<?php

namespace Envatic\CrudStrap\Fields\Inputs;

class FormTextArea extends Input
{
    /**
     * render text area imports
     */
    public function imports(): array
    {
        return [
            'import FormLabel from "@/Components/FormLabel.vue"',
            'import FormTextArea from "@/Components/FormTextArea.vue"'
        ];
    }
    /**
     * render textarea input
     */
    public function render()
    {
        $name = str($this->field->name());
        $label = $this->field->label(true);
        $placeHolder = $this->field->placeholder();
        $help = $this->field->help(true);
        return <<<TEXT
        <div class="w-full grid">
			<FormLabel class="mb-1">{{ \$t("$label") }}</FormLabel>
			<FormTextArea
				v-model="form.{$name}"
				:rows="3"
				{$placeHolder}
			/>
			<p
				v-if="form.errors?.{$name}"
				class="text-red"
			>
				{{ form.errors?.{$name} }}.
			</p>
            <p v-else>{{\$t('$help')}}</p>
		</div>
TEXT;
    }
}
