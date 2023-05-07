<?php
declare(strict_types=1);

namespace PhpRest2\Orm;

abstract class AbstractOrmEntity 
{
    public string $table = '';

    public function getTableName() : string {
        $table = $this->table;
        if ($table === '') {
            $ary = explode('\\', $this->classPath);
            $table = end($ary);
            $table = \PhpRest2\uncamelize($table);
        }
        return $table;
    }
}