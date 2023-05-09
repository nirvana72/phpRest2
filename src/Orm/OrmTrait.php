<?php
declare(strict_types=1);

namespace PhpRest2\Orm;

use PhpRest2\Application;
use PhpRest2\Entity\EntityBuilder;
use PhpRest2\Exception\BadCodeException;
use PhpRest2\Database\Medoo;

trait OrmTrait
{
    /**
     * 填充数据
     * 
     * @param array $data 数据源
     * @param bool $withValidator 是否需要验证
     */
    public function fill(array $data, bool $withValidator = true)
    {
        $entity = Application::getInstance()->get(EntityBuilder::class)->build(self::class);
        return $entity->makeInstanceWithData($data, $withValidator, $this);
    }

    public static function findOne($where = [])
    {
        $self   = Application::getInstance()->make(self::class);
        $entity = Application::getInstance()->get(EntityBuilder::class)->build(self::class);
        $columns = [];
        foreach ($entity->properties as $p) {
            $field = $p->getFieldName();
            $columns[] = "{$field}({$p->varName})";
        }
        $data = $self->getDb()->get($entity->getTableName(), $columns, $where);
        if ($data === null) return null;
        $entity->makeInstanceWithData($data, false, $self);
        return $self;
    }

    public function insert(): \PDOStatement
    {
        $entity = Application::getInstance()->get(EntityBuilder::class)->build(self::class);
        $data = [];
        $autoPrimaryVarName = null;
        foreach ($entity->properties as $p) {
            if ($p->primaryKey !== '') {
                if ($p->primaryKey === 'auto') { // 自增主键略过不插入
                    $autoPrimaryVarName = $p->varName;
                    continue;
                } 
                if (false === isset($this->{$p->varName})) {
                    throw new BadCodeException("{$entity->classPath} insert 操作缺少主键值 {$p->varName}");
                }
            }
            // 只插入有值的属性到数据库
            if (isset($this->{$p->varName})) {
                $field = $p->getFieldName();
                $data[$field] = $this->{$p->varName};
            }
        }
        $res = $this->getDb()->insert($entity->getTableName(), $data);
        if ($res === null) {
            throw new BadCodeException("insert {$entity->getTableName()} error");
        }
        if ($res->errorInfo()[1] !== null) {
            throw new BadCodeException($res->errorInfo()[2]);
        }

        //自增主键赋值
        if ($autoPrimaryVarName !== null) {
            $this->{$autoPrimaryVarName} = $this->getDb()->getAutoId();
        }
        
        return $res;
    }

    public function update(array $withNullFiles = []) 
    {
        $entity = Application::getInstance()->get(EntityBuilder::class)->build(self::class);
        $data = [];
        $where = [];
        foreach ($entity->properties as $p) {
            $field = $p->getFieldName();
            // update 时， 主键放入where中
            if ($p->primaryKey !== '') {
                if (false === isset($this->{$p->varName})) {
                    throw new BadCodeException("{$entity->classPath} update 操作缺少主键值");
                }
                $where[$field] = $this->{$p->varName};
                continue;
            } 
            // 只更新有值的属性
            if (isset($this->{$p->varName})) {
                $data[$field] = $this->{$p->varName};
            } elseif(in_array($field, $withNullFiles)) {
                // 或者指定非空要插入null的字段
                $data[$field] = null;
            }
        }
        $res = $this->getDb()->update($entity->getTableName(), $data, $where);
        if ($res === null) {
            throw new BadCodeException("update {$entity->getTableName()} error");
        }
        if ($res->errorInfo()[1] !== null) {
            throw new BadCodeException($res->errorInfo()[2]);
        }
        return $res;
    }

    public static function delete($pk = null): \PDOStatement
    {
        $self = Application::getInstance()->make(self::class);
        return $self->remove($pk);
    }

    public function remove($pk = null): \PDOStatement
    {
        $entity = Application::getInstance()->get(EntityBuilder::class)->build(self::class);
        $where = $pk;
        if ($where === null) {
            foreach ($entity->properties as $p) {
                $field = $p->getFieldName();
                if ($p->primaryKey !== '') {
                    if (false === isset($this->{$p->varName})) {
                        throw new BadCodeException("{$entity->classPath} delete 操作缺少主键值");
                    }
                    $where[$p->field] = $this->{$p->varName};
                }
            }
        }
        $res = $this->getDb()->delete($entity->getTableName(), $where);
        if ($res === null) {
            throw new BadCodeException("delete {$entity->getTableName()} error");
        }
        if ($res->errorInfo()[1] !== null) {
            throw new BadCodeException($res->errorInfo()[2]);
        }
        return $res;
    }

    private function getDb(): Medoo
    {
        return Application::getInstance()->get(Medoo::class);
    }
}