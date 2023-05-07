<?php
declare(strict_types=1);

namespace PhpRest2\Controller;

use PhpRest2\Application;
use PhpRest2\AttributeInterface;
use PhpRest2\Controller\Attribute;
use Symfony\Component\Cache\Adapter\ApcuAdapter as ApcuCache;
use DI\Attribute\Inject;
use PhpRest2\Exception\BadRequestException;

class ControllerBuilder
{
    #[Inject]
    private ApcuCache $cache;

    public function build(string $classPath): ?Controller {
        $cacheKey = 'buildController' . md5($classPath . Application::getInstance()->unionId);
        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            $controller = $cacheItem->get();
            if ($controller->modifyTimespan === filemtime($controller->filePath)) {
                return $controller;
            }
        }

        $classRef = new \ReflectionClass($classPath);
        $attributes = $classRef->getAttributes(Attribute\Controller::class);
        if (count($attributes) !== 1) return null;

        $controller = new Controller($classPath);
        $controller->name     = $classRef->getShortName();
        $controller->filePath = $classRef->getFileName();
        $controller->modifyTimespan = filemtime($controller->filePath);
        // 收集class上的注解
        $attributes = $classRef->getAttributes(AttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF);
        foreach ($attributes as $attribute) {
            $attribute->newInstance()->bind2Target($controller);
        }

        $controller->actions = $this->buildActions($classRef);

        $cacheItem->set($controller);
        $cacheItem->expiresAfter(3600);
        $this->cache->save($cacheItem);

        return $controller;
    }

    /**
     * 收集 controller->actions
     * @return Action[]
     */
    private function buildActions(\ReflectionClass $classRef) : array
    {
        $actions = [];
        foreach ($classRef->getMethods(\ReflectionMethod::IS_PUBLIC) as $methodRef) {
            if ($methodRef->isStatic()) continue;

            $attributes = $methodRef->getAttributes(Attribute\Action::class);
            if (count($attributes) !== 1) continue;

            $action = new Action();
            $action->funcName = $methodRef->getName();
            $action->name = $action->funcName;

            $attributes = $methodRef->getAttributes(AttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF);
            $paramAttributes = [];
            foreach ($attributes as $attribute) {
                // 写在action上的 Param 注解先收集起来一会用
                if ($attribute->getName() === Attribute\Param::class) {
                    $paramAttributes[] = $attribute;
                } else {
                    $attribute->newInstance()->bind2Target($action);
                }
            }

            // 收集参数
            $action->params = $this->buildActionParams($methodRef, $action->method);

            // 写在action上的 Param 注解 生效于具体参数
            foreach ($paramAttributes as $attribute) {
                $attr = $attribute->newInstance();
                if (false === array_key_exists($attr->name, $action->params)) {
                    throw new BadRequestException("方法 {$action->funcName} 的参数注解 {$attr->name} 未被使用");
                }
                $param = $action->params[$attr->name];
                if ($param) $attr->bind2Target($param);
            }

            $actions[$action->funcName] = $action;
        }

        return $actions;
    }

    /**
     * 收集 action->params
     * @return Param[]
     */
    private function buildActionParams(\ReflectionMethod $methodRef, string $method) : array
    {
        $params = [];
        foreach ($methodRef->getParameters() as $p) {
            $param = new Param();
            $param->varName     = $p->getName();
            $param->isOptional  = $p->isOptional();
            $param->default     = $param->isOptional ? $p->getDefaultValue() : null;
            $param->bind        = in_array($method, ['POST', 'PUT']) ? 'request' : 'query';
            $param->bind        = "{$param->bind}.{$param->varName}";
            if ($p->hasType()) {
                $param->varType = $p->getType()?->getName();
                if ($param->varType === 'array') {
                    $param->isArray = true;
                    $param->varType = 'mixed';
                }
            }
            
            $params[$param->varName] = $param;
        }
        return $params;
    }
}