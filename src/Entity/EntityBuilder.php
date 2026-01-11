<?php
declare(strict_types=1);

namespace PhpRest2\Entity;

use PhpRest2\Application;
use PhpRest2\AttributeInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter as ApcuCache;
use DI\Attribute\Inject;

final class EntityBuilder
{
    #[Inject]
    private ApcuCache $cache;

    public function build(string $classPath): ?Entity {
        $cacheKey = 'buildEntity' . md5($classPath . Application::getInstance()->unionId);
        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            $entity = $cacheItem->get();
            if ($entity->modifyTimespan === filemtime($entity->filePath)) {
                return $entity;
            }
        }

        $classRef = new \ReflectionClass($classPath);
        $entity = new Entity($classPath);
        $entity->name = $classRef->getShortName();
        $entity->filePath = $classRef->getFileName();
        $entity->modifyTimespan = filemtime($entity->filePath);

        $attributes = $classRef->getAttributes(AttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF);
        foreach ($attributes as $attribute) {
            $attribute->newInstance()->bind2Target($entity);
        }

        foreach ($classRef->getProperties(\ReflectionProperty::IS_PUBLIC) as $p) {
            // 过滤
            if ($p->isDefault() === false || $p->isStatic()  === true) { continue; }

            $property = new Property();
            $property->varName = $p->getName();
            $property->hasDefaultValue = $p->hasDefaultValue();
            if ($p->hasType()) {
                $property->allowsNull = $p->getType()?->allowsNull();
                $property->varType    = $p->getType()?->getName();
                if ($property->varType === 'array') {
                    $property->isArray = true;
                    $property->varType = 'mixed';
                }
            }

            // 收集属性上的注解
            $attributes = $p->getAttributes(AttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF);
            foreach ($attributes as $attribute) {
                $attribute->newInstance()->bind2Target($property);
            }

            $entity->properties[] = $property;
        }

        $cacheItem->set($entity);
        $cacheItem->expiresAfter(3600);
        $this->cache->save($cacheItem);

        return $entity;
    }
}