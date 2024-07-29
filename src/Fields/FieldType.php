<?php

namespace Envatic\CrudStrap\Fields;

use Envatic\CrudStrap\Crud;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FieldType
{
    public $name;
    public Collection $modifiers;
    protected $typeLookup = [
        'datetime' => 'dateTime',
        'mediumtext' => 'mediumText',
        'longtext' => 'longText',
        'bigint' => 'bigInteger',
        'mediumint' => 'mediumInteger',
        'tinyint' => 'tinyInteger',
        'smallint' => 'smallInteger',
        'bigIncrements' => 'bigIncrements',
        'bigInteger' => 'bigInteger',
        'binary' => 'binary',
        'boolean' => 'boolean',
        'char' => 'char',
        'dateTimeTz' => 'dateTimeTz',
        'dateTime' => 'dateTime',
        'date' => 'date',
        'decimal' => 'decimal',
        'double' => 'double',
        'enum' => 'string',
        'select' => 'string',
        'radioselect' => 'string',
        'radiocards' => 'string',
        'float' => 'float',
        'foreignId' => 'foreignId',
        'foreignIdFor' => 'foreignIdFor',
        'foreignUuid' => 'foreignUuid',
        'geometryCollection' => 'geometryCollection',
        'geometry' => 'geometry',
        'id' => 'id',
        'increments' => 'increments',
        'integer' => 'integer',
        'ipAddress' => 'ipAddress',
        'json' => 'json',
        'array' => 'json',
        'jsonb' => 'jsonb',
        'lineString' => 'lineString',
        'longText' => 'longText',
        'macAddress' => 'macAddress',
        'mediumIncrements' => 'mediumIncrements',
        'mediumInteger' => 'mediumInteger',
        'mediumText' => 'mediumText',
        'morphs' => 'morphs',
        'multiPoint' => 'multiPoint',
        'multiPolygon' => 'multiPolygon',
        'nullableMorphs' => 'nullableMorphs',
        'nullableTimestamps' => 'nullableTimestamps',
        'nullableUuidMorphs' => 'nullableUuidMorphs',
        'point' => 'point',
        'polygon' => 'polygon',
        'rememberToken' => 'rememberToken',
        'set' => 'set',
        'smallIncrements' => 'smallIncrements',
        'smallInteger' => 'smallInteger',
        'softDeletesTz' => 'softDeletesTz',
        'softDeletes' => 'softDeletes',
        'string' => 'string',
        'file' => 'string',
        'logo' => 'string',
        'image' => 'image',
        'text' => 'text',
        'timeTz' => 'timeTz',
        'time' => 'time',
        'timestampTz' => 'timestampTz',
        'timestamp' => 'timestamp',
        'timestampsTz' => 'timestampsTz',
        'timestamps' => 'timestamps',
        'tinyIncrements' => 'tinyIncrements',
        'tinyInteger' => 'tinyInteger',
        'tinyText' => 'tinyText',
        'unsignedBigInteger' => 'unsignedBigInteger',
        'unsignedDecimal' => 'unsignedDecimal',
        'unsignedInteger' => 'unsignedInteger',
        'unsignedMediumInteger' => 'unsignedMediumInteger',
        'unsignedSmallInteger' => 'unsignedSmallInteger',
        'unsignedTinyInteger' => 'unsignedTinyInteger',
        'uuidMorphs' => 'uuidMorphs',
        'uuid' => 'uuid',
        'year' => 'year',
    ];

    protected $inputLookup = [
        'string' => 'text',
        'char' => 'text',
        'varchar' => 'text',
        'text' => 'textarea',
        'mediumtext' => 'textarea',
        'longtext' => 'textarea',
        'json' => 'textarea',
        'jsonb' => 'textarea',
        'binary' => 'textarea',
        'password' => 'password',
        'email' => 'email',
        'number' => 'number',
        'integer' => 'number',
        'bigint' => 'number',
        'mediumint' => 'number',
        'tinyint' => 'number',
        'smallint' => 'number',
        'decimal' => 'number',
        'double' => 'number',
        'float' => 'number',
        'date' => 'date',
        'datetime' => 'dateTime',
        'timestamp' => 'dateTime',
        'time' => 'time',
        'radio' => 'radio',
        'boolean' => 'switch',
        'enum' => 'select',
        'select' => 'select',
        'radioselect' => 'radioselect',
        'radiocards' => 'radiocards',
        'file' => 'file',
        'logo' => 'logo',
        'image' => 'image',
        'uuid' => 'text',
        'bigIncrements' => 'number',
        'bigInteger' => 'number',
        'dateTimeTz' => 'dateTime',
        'dateTime' => 'dateTime',
        'foreignId' => 'number',
        'foreignIdFor' => 'number',
        'foreignUuid' => 'number',
        'geometryCollection' => 'texteara',
        'geometry' => 'texteara',
        'increments' => 'number',
        'ipAddress' => 'text',
        'jsonb' => 'texteara',
        'lineString' => 'text',
        'longText' => 'texteara',
        'macAddress' => 'text',
        'mediumIncrements' => 'number',
        'mediumInteger' => 'number',
        'mediumText' => 'number',
        'multiLineString' => 'text',
        'multiPoint' => 'text',
        'multiPolygon' => 'text',
        'nullableTimestamps' => 'dateTime',
        'point' => 'text',
        'polygon' => 'text',
        'rememberToken' => 'text',
        'set' => 'text',
        'smallIncrements' => 'number',
        'smallInteger' => 'number',
        'timeTz' => 'dateTime',
        'timestampTz' => 'dateTime',
        'timestampsTz' => 'dateTime',
        'tinyIncrements' => 'number',
        'tinyInteger' => 'number',
        'tinyText' => 'number',
        'unsignedBigInteger' => 'number',
        'unsignedDecimal' => 'number',
        'unsignedInteger' => 'number',
        'unsignedMediumInteger' => 'number',
        'unsignedSmallInteger' => 'number',
        'unsignedTinyInteger' => 'number',
        'uuid' => 'text',
        'year' => 'dateTime',
    ];

    function __construct(public string $type, public string $fieldName)
    {
        $list = Str::of($type)->explode('|');
        $this->name = $list->shift();
        $this->modifiers = $list->mapInto(FieldMigrationModifier::class);
    }

    /**
     * get the migration for the field type
     */
    function getMigration(): string
    {
        [$type, $parameters] = explode(':', $this->name, 2) + [null, null];
        $variables = $parameters
            ? "," . Crud::parseFunctionParams($parameters)
            : "";
        $dbType = $this->typeLookup[$type] ?? $type;
        $migration = "\$table->" . $dbType . "('" . $this->fieldName . "'" . $variables . ")";
        $modifiers = $this->getMigrationModifiers();
        return $migration . $modifiers . ";\n";
    }



    /**
     * get the modifier functions for the migration
     */
    function getMigrationModifiers(): string
    {
        return $this->modifiers->map(function (FieldMigrationModifier $modifier) {
            return $modifier->toMigration();
        })->implode("");
    }

    /**
     * return $this->name without migration modifiers
     */
    function parsed(): ?string
    {
        return Str::of($this->name)
            ->explode(":")
            ->shift();
    }


    function isUuid(): bool
    {
        return $this->parsed() == 'uuid';
    }

    function isBool(): bool
    {
        return $this->parsed() == 'boolean';
    }

    function isEnum(): bool
    {
        $parsed = $this->parsed();
        return in_array($parsed, ['select', 'enum', 'radiocards', 'radioselect']);
    }

    function isFile(): bool
    {
        return $this->parsed() == 'file'
        ||  $this->parsed() == 'logo'
        ||  $this->parsed() == 'image';
    }

    function isJson(): bool
    {
        return $this->parsed() == 'json' ||  $this->parsed() == 'array';
    }

    public function isDateTime()
    {
        return in_array($this->parsed(), [
            'dateTimeTz',
            'dateTime',
            'datetime',
            'date',
            'timeTz',
            'time',
            'timestampTz',
            'timestamp',
            'timestampsTz',
        ]);
    }

    /**
     * get the input type for this field type
     */
    function getInputType(): string
    {
        $type = str($this->name)->explode(':', 2)->shift();
        return $this->inputLookup[$type] ?? $type;
    }
}
