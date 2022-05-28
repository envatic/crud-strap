## Supported Fields

You can use any of the fields from the list.

### Valid Migration Field Types:

```
* datetime 
* mediumtext 
* longtext 
* bigint 
* mediumint 
* tinyint 
* smallint 
* bigIncrements 
* bigInteger
* binary
* boolean
* char
* dateTimeTz
* dateTime
* date
* decimal
* double
* enum
* float
* foreignId
* foreignIdFor
* foreignUuid
* geometryCollection
* geometry
* id
* increments
* integer
* ipAddress
* json
* jsonb
* lineString
* longText
* macAddress
* mediumIncrements
* mediumInteger
* mediumText
* morphs
* multiPoint
* multiPolygon
* nullableMorphs
* nullableTimestamps
* nullableUuidMorphs
* point
* polygon
* rememberToken // leave fieldname empty
* set
* smallIncrements
* smallInteger
* softDeletesTz
* softDeletes
* string
* text
* timeTz
* time
* timestampTz
* timestamp
* timestampsTz
* timestamps
* tinyIncrements
* tinyInteger
* tinyText
* unsignedBigInteger
* unsignedDecimal
* unsignedInteger
* unsignedMediumInteger
* unsignedSmallInteger
* unsignedTinyInteger
* uuidMorphs
* uuid
* year
```

# conversions to form fields 
only fields with validation will be used in forms.
> if you need field to be included in your form simply include validation rule for it in the json file
The generator converts database fields to form fields as follows
````php
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
    'datetime' => 'datetime-local',
    'timestamp' => 'datetime-local',
    'time' => 'time',
    'radio' => 'radio',
    'boolean' => 'radio',
    'enum' => 'select',
    'select' => 'select',
    'file' => 'file',
    'uuid' => 'text',
    'bigIncrements' => 'number',
    'bigInteger' => 'number',
    'dateTimeTz' => 'datetime-local',
    'dateTime' => 'datetime-local',
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
    'nullableTimestamps' => 'datetime-local',
    'point' => 'text',
    'polygon' => 'text',
    'rememberToken' => 'text',
    'set' => 'text',
    'smallIncrements' => 'number',
    'smallInteger' => 'number',
    'timeTz' => 'datetime-local',
    'timestampTz' => 'datetime-local',
    'timestampsTz' => 'datetime-local',
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
    'year' => 'datetime-local',
````
# overiding conversion params
overide in config file `app/config/crudstrap.php`

```php
'type_lookup' => [
    'uuid' => 'uuid',
],
```

[&larr; Back to index](README.md)
