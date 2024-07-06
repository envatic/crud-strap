<?php

namespace Envatic\CrudStrap\Fields\Inputs;

class FileUpload extends Input
{
    /**
     * render text area imports
     */
    public function imports(): array
    {
        return [
            'import FormInput from "@/Components/FormInput.vue";',
            'import LogoInput from "@/Components/LogoInput.vue";',
            'import LogoInputLocal from "@/Components/LogoInputLocal.vue";',
            'import fakeLogo from "@/Components/no-image-available-icon.jpeg?url";'
        ];
    }
    /**
     * render textarea input
     */
    public function render()
    {
        $name = str($this->field->name());
        $label = $this->field->label(true);
        $help = $this->field->help(true);
        return <<<TEXT
        <div>
            <div class="gap-x-3 sm:col-span-2 grid md:grid-cols-2">
        		<FormInput
        			v-model="form.{$name}_uri"
        			:disabled="form.{$name}_upload"
        			placeholder="https://"
        			:error="form.errors.{$name}_uri"
        			:help="\$t('Supports png, jpeg or svg')"
        		>
        			<template #label>
        				<div class="flex">
        					<span class="mr-3">{{
        						\$t("{$label}")
        					}}</span>
        					<label
        						class="inline-flex items-center space-x-2"
        					>
        						<input
        							v-model="form.{$name}_upload"
        							class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:!bg-emerald-600 checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:before:bg-white"
        							type="checkbox"
        						/>
        						<span>{{ \$t("Upload to server") }}</span>
        					</label>
        				</div>
        			</template>
        		</FormInput>
        		<template v-if="form.{$name}_upload">
        			<LogoInput
        				v-if="\$page.props.config.s3"
        				v-model="form.{$name}_uri"
        				v-model:file="form.{$name}_path"
        				auto
        			/>
        			<LogoInputLocal
        				v-else
        				v-model="form.{$name}_uri"
        			/>
        		</template>
        		<img
        			v-else
        			class="w-12 h-12 my-auto rounded-full b-0"
        			:src="form.{$name}_uri ?? fakeLogo"
        		/>
    	    </div>
            <p
				v-if="form.errors.{$name}"
				class="text-red-500"
			>
				{{ form.errors.{$name} }}
			</p>
            <p
				v-else
				class="text-xs"
			>
				{{ \$t('$help') }}
			</p>
	    </div>
TEXT;
    }
}
