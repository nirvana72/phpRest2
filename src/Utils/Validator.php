<?php
declare(strict_types=1);

namespace PhpRest2\Utils;

/**
 * Validator
 * see https://github.com/vlucas/valitron#built-in-validation-rules
 */
final class Validator extends \Valitron\Validator
{
    /**
     * @param string $rules
     * @param string $field
     */
    public function myRule(string $rules, string $field): void {
        if (empty($rules)) return;
        foreach(explode('|', $rules) as $rule) {
            $args = explode('=', $rule);
            $rule = $args[0];
            $args = isset($args[1]) ? explode(',', $args[1]) : [];
            if (in_array($rule, ['in', 'notIn'])) {
                $args = [$args];
            }
            $this->rule($rule, $field, ...$args);
        }
    }
}