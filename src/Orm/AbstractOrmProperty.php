<?php
declare(strict_types=1);

namespace PhpRest2\Orm;

abstract class AbstractOrmProperty
{
    public string $primaryKey = '';

    public string $field = '';

    public function getFieldName(): string {
        $field = $this->field;
        if ($field === '') $field = \PhpRest2\uncamelize($this->varName);
        return $field;
    }
}