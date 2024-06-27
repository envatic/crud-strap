<?php

namespace Envatic\CrudStrap\Fields;

use Envatic\CrudStrap\Crud;
use Envatic\CrudStrap\CrudFile;
use Envatic\CrudStrap\Fields\Inputs\FileUpload;
use Envatic\CrudStrap\Fields\Inputs\FormDatePicker;
use Envatic\CrudStrap\Fields\Inputs\FormInput;
use Envatic\CrudStrap\Fields\Inputs\FormSwitch;
use Envatic\CrudStrap\Fields\Inputs\FormTextArea;
use Envatic\CrudStrap\Fields\Inputs\Input;
use Envatic\CrudStrap\Fields\Inputs\MultiSelect;
use Envatic\CrudStrap\Fields\Inputs\RadioCards;
use Envatic\CrudStrap\Fields\Inputs\RadioSelect;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class Field
{


    protected FieldType $type;
    protected
    function __construct(public object $field, public CrudFile $crudfile)
    {
        $this->type = new FieldType(trim($this->field->type), trim($this->field->name));
    }

    public function name(): ?string
    {
        return trim($this->field->name ?? null);
    }

    public function hasForm(): bool
    {
        return !!$this->field->form;
    }

    public function label($str = false): ?string
    {
        $name = str($this->field->name())->ucfirst();
        if (!$this->field->form) return $str ? $name : ":label=\"{$name}\"";
        if (!$this->field->form->label) return $str ? $name : ":label=\"{$name}\"";
        $label = str($this->field->form->label)->trim();
        return $str ? $label : ":label=\"\$t('{$label}')\"";
    }

    public function placeholder($str = false): ?Stringable
    {
        if (!$this->field->form) return null;
        if (!$this->field->form->placeholder) return null;
        $placeholder = str($this->field->form->placeholder)->trim();
        return $str ? $placeholder : ":placeholder=\"\$t('{$placeholder}')\"";
    }

    public function help($str = false): ?Stringable
    {
        if (!$this->field->form) return null;
        if (!$this->field->form->help) return null;
        $help =  str($this->field->form->help)->trim();
        return $str ? $help : ":help=\"\$t('{$help}')\"";
    }

    public function defaultValue(): ?string
    {
        $default = $this->type()
            ->modifiers
            ->first(fn (FieldMigrationModifier $m) => $m->func == 'default');
        return $default?->params ?? null;
    }

    public function type(): FieldType
    {
        return $this->type;
    }

    function getFileSnippet()
    {
        $snippet = <<<EOD
        if (\$request->hasFile('{$this->field->name}')) {
            \$requestData['{$this->field->name}'] = \$request->file('{$this->field->name}')
                ->store('uploads', 'public');
        }
EOD;
    }
    /**
     * Validation Rules
     */
    public function rules(): ?string
    {
        if (isset($this->field->rules))
            return trim($this->field->rules);
        return null;
    }

    /**
     * Cast field in model
     */
    public function cast(): ?string
    {
        if (isset($this->field->cast)) {
            $cast_to = trim($this->field->cast);
            return "'{$this->name()}' => '$cast_to'";
        }
        if ($this->type()->isBool()) {
            return "'{$this->name()}' => 'boolean'";
        }
        if ($this->type()->isDateTime()) {
            return "'{$this->name()}' => 'datetime'";
        }
        if ($this->type()->isJson()) {
            return "'{$this->name()}' => 'array'";
        }
        return null;
    }

    /**
     * Options
     */
    public function options(): ?FieldOptions
    {
        if (isset($this->field->options))
            return new FieldOptions($this->field->options ?? null);
        return null;
    }

    /**
     * get the validation for this field
     */
    public function validation(): string
    {
        $rules = $this->rules();
        if (!is_array($rules)) {
            $rules = str($rules)->explode("|")->map(fn (string $rule) => Crud::stringifyArray($rule))->all();
        }
        if ($this->type()->isEnum()) {
            $enum_class = $this->useEnumClass();
            $rules = [...$rules, "new Enum($enum_class)"];
        }
        $rulesList = collect($rules)->implode(',');
        $ruleString =  "\n\t\t\t'{$this->name()}' => [{$rulesList}],";
        if ($this->type()->isFile()) {
            $key = $this->name();
            $ruleString .= "\n\t\t\t'{$key}_uri' => ['required', 'string']";
            $ruleString .= "\n\t\t\t'{$key}_upload' => ['required', 'boolean']";
            $ruleString .= "\n\t\t\t'{$key}_path' => ['nullable', 'string', 'required_if:{$key}_upload,true']";
        }
        return $ruleString;
    }



    public function getEnumClass(): string
    {
        if (!$this->type()->isEnum())  return null;
        $crud_name = $this->crudfile->name;
        $field_name = Str::of($this->name())->lower()->ucfirst();
        $model_name = Str::of($crud_name)->singular()->lower()->ucfirst();
        return $model_name . $field_name;
    }

    public function includeEnumClass(): string
    {
        $enum_class = $this->getEnumClass();
        if (!$enum_class) return '';
        return "use App\\Enums\\{$enum_class};";
    }

    public function useEnumClass(): string
    {
        $enum_class = $this->getEnumClass();
        if (!$enum_class) return '';
        return "{$enum_class}::class";
    }

    public function includeFileUploadClass()
    {
        if (!$this->type()->isFile()) return null;
        return 'use App\Actions\Uploads;';
    }

    public function useFileUploadClass()
    {
        if (!$this->type()->isFile()) return null;
        $name = $this->name();
        $crudNameSingular = $this->crudfile->crudNameSingular();
        return "app(Uploads::class)->upload(\$request,  \${$crudNameSingular}, '$name')";
    }

    public function isFillable(): bool
    {
        $name = $this->name();
        if (empty($name)) return false;
        if (isset($this->field->fillable))
            return $this->field->fillable;
        return true;
    }

    /**
     * get input html for this field
     */

    public function getFormInput(): Input
    {
        $input_type = $this->type()->getInputType();
        return match ($input_type) {
            'text',
            'password',
            'email',
            'number' => new FormInput($this),
            'textarea' => new FormTextArea($this),
            'date',
            'dateTime',
            'time' => new FormDatePicker($this),
            'radioselect' => new RadioSelect($this),
            'radiocards' => new RadioCards($this),
            'select' => new MultiSelect($this),
            'radio',
            'switch' => new FormSwitch($this),
            'file',
            'logo',
            'image' => new FileUpload($this)
        };
    }

    public function tableHeaderHtml()
    {
        $sanitized = str($this->name())->ucwords()->replace('_', ' ');
        $label = "{{\$t('{$sanitized}')}}";
        return "\t\t\t\t\t\t\t\t\t<th scope=\"col\" class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">$label</th>";
    }

    public function tableBodyHtml()
    {
        $field = $this->name();
        $crudNameSingular = $this->crudfile->crudNameSingular();
        return "\t\t\t\t\t\t\t\t\t<td class=\"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900\" >{{ {$crudNameSingular}.{$field} }}</td>";
    }

    public function emptyVueForm()
    {
        $field = $this->name();
        return "\t\t{$field}:\"\"";
    }

    public function filledVueForm()
    {
        $crudNameSingular = $this->crudfile->crudNameSingular();
        $field = $this->name();
        return "\t\t{$field}:props.{$crudNameSingular}.{$field}";
    }
}
