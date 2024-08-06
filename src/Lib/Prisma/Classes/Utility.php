<?php

namespace Lib\Prisma\Classes;

use Lib\Validator;

enum ArrayType: string
{
    case Associative = 'associative';
    case Indexed = 'indexed';
    case Value = 'value';
}

abstract class Utility
{
    public static function checkFieldsExistWithReferences(
        array $select,
        array &$relatedEntityFields,
        array &$primaryEntityFields,
        array $relationName,
        array $fields,
        string $modelName,
        string $timestamp
    ) {
        if (isset($select) && is_array($select)) {
            foreach ($select as $key => $value) {
                if ($key === $timestamp) continue;

                if (is_numeric($key) && is_string($value)) {
                    if (array_key_exists($value, $fields))
                        throw new \Exception("The '$value' is indexed, waiting example: ['$value' => true]");
                }

                if (isset($value) && empty($value) || !is_bool($value)) {
                    if (is_string($key) && !array_key_exists($key, $fields)) {
                        throw new \Exception("The field '$key' does not exist in the $modelName model.");
                    }

                    if (is_string($key) && array_key_exists($key, $fields)) {
                        if (!is_bool($value) && !is_array($value)) {
                            throw new \Exception("The '$key' is indexed, waiting example: ['$key' => true]");
                        }
                    }

                    if (!is_array($value))
                        continue;
                }

                if (is_string($key) && is_array($value)) {
                    if (isset($value['select'])) {
                        $relatedEntityFields[$key] = $value['select'];
                    } else {
                        if (is_array($value) && empty($value)) {
                            $relatedEntityFields[$key] = [$key];
                        } else {
                            if (!is_bool($value) || empty($value)) {
                                throw new \Exception("The '$key' is indexed, waiting example: ['$key' => true] or ['$key' => ['select' => ['field1' => true, 'field2' => true]]]");
                            }
                        }
                    }
                } else {
                    foreach (explode(',', $key) as $fieldName) {
                        if ($key === $timestamp || $fieldName === $timestamp) continue;
                        $fieldName = trim($fieldName);

                        if (!array_key_exists($fieldName, $fields)) {
                            $availableFields = implode(', ', array_keys($fields));
                            throw new \Exception("The field '$fieldName' does not exist in the $modelName model. Available fields are: $availableFields");
                        }

                        if (
                            in_array($fieldName, $relationName) ||
                            (isset($fields[$fieldName]) && in_array($fields[$fieldName]['type'], $relationName))
                        ) {
                            $relatedEntityFields[$fieldName] = [$fieldName];
                            continue;
                        }

                        $isRelationalOrInverse = false;
                        if (isset($fields[$fieldName]['decorators'])) {
                            foreach ($fields[$fieldName]['decorators'] as $decoratorKey => $decoratorValue) {
                                if ($decoratorKey === 'relation' || $decoratorKey === 'inverseRelation') {
                                    $isRelationalOrInverse = true;
                                    break;
                                }
                            }
                        }

                        if (!$isRelationalOrInverse) {
                            if (in_array($fieldName, $primaryEntityFields)) continue;
                            $primaryEntityFields[] = $fieldName;
                        }
                    }
                }
            }
        }
    }

    public static function checkFieldsExist(array $select, array $fields, string $modelName)
    {
        foreach ($select as $key => $value) {
            if (is_numeric($key) && is_string($value)) {
                if (array_key_exists($value, $fields))
                    throw new \Exception("The '$value' is indexed, waiting example: ['$value' => true]");
            }

            if (isset($value) && empty($value) || !is_bool($value)) {
                if (is_string($key) && !array_key_exists($key, $fields)) {
                    throw new \Exception("The field '$key' does not exist in the $modelName model.");
                }

                if (is_array($value) && !empty($value)) {

                    $isRelatedModel = false;

                    foreach ($fields as $field) {
                        $relation = $field['decorators']['relation'] ?? null;
                        $inverseRelation = $field['decorators']['inverseRelation'] ?? null;
                        $implicitRelation = $field['decorators']['implicitRelation'] ?? null;

                        if (isset($relation['name']) && $relation['name'] == $key || isset($inverseRelation['fromField']) && $inverseRelation['fromField'] == $key || isset($implicitRelation['fromField']) && $implicitRelation['fromField'] == $key) $isRelatedModel = true;
                    }

                    if ($isRelatedModel) continue;

                    $keys = array_keys($value);
                    foreach ($keys as $fieldName) {
                        $fieldName = trim($fieldName);
                        if (!array_key_exists($fieldName, $fields)) {
                            throw new \Exception("The field '$fieldName' does not exist in the $modelName model.");
                        }
                    }
                }

                continue;
            }

            foreach (explode(',', $key) as $fieldName) {
                $fieldName = trim($fieldName);
                if (!array_key_exists($fieldName, $fields)) {
                    throw new \Exception("The field '$fieldName' does not exist in the $modelName model.");
                }
            }
        }
    }

    public static function checkArrayContents(array $array): ArrayType
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (array_keys($value) !== range(0, count($value) - 1)) {
                    return ArrayType::Associative;
                } else {
                    return ArrayType::Indexed;
                }
            } else {
                return ArrayType::Value;
            }
        }
    }

    public static function checkIncludes(array $include, array &$relatedEntityFields, array &$includes, array $fields, string $modelName)
    {
        if (isset($include) && is_array($include)) {
            foreach ($include as $key => $value) {
                self::processIncludeValue($key, $value, $relatedEntityFields, $fields, $modelName, $key);

                if (is_numeric($key) && is_string($value)) {
                    throw new \Exception("The '$value' is indexed, waiting example: ['$value' => true]");
                }

                if (isset($value) && empty($value) || !is_bool($value)) {
                    continue;
                }

                if (!array_key_exists($key, $fields)) {
                    throw new \Exception("The field '$key' does not exist in the $modelName model.");
                }

                $includes[$key] = $value;
            }
        }
    }

    private static function processIncludeValue($key, $value, &$relatedEntityFields, $fields, $modelName, $parentKey)
    {
        if (isset($value['select'])) {
            $relatedEntityFields[$parentKey] = $value;
        } elseif (is_array($value)) {
            if (empty($value)) {
                $relatedEntityFields[$parentKey] = [$parentKey];
            } else {
                foreach ($value as $k => $v) {
                    if (is_string($k) && (is_bool($v) || empty($v))) {
                        $relatedEntityFields[$parentKey]['include'] = [$k => $v];
                    } else {
                        self::processIncludeValue($k, $v, $relatedEntityFields, $fields, $modelName, $parentKey);
                    }
                }
            }
        } else {
            if (!is_bool($value) || empty($value)) {
                throw new \Exception("The '$value' is indexed, waiting example: ['$value' => true] or ['$value' => ['select' => ['field1' => true, 'field2' => true]]]");
            }
        }
    }

    public static function processConditions(array $conditions, &$sqlConditions, &$bindings, $dbType, $prefix = '', $level = 0)
    {
        foreach ($conditions as $key => $value) {
            if (in_array($key, ['AND', 'OR', 'NOT'])) {
                $groupedConditions = [];
                if ($key === 'NOT') {
                    self::processNotCondition($value, $groupedConditions, $bindings, $dbType, $prefix . $key . '_', $level);
                    if (!empty($groupedConditions)) {
                        $conditionGroup = '(' . implode(" $key ", $groupedConditions) . ')';
                        $conditionGroup = 'NOT ' . $conditionGroup;
                        $sqlConditions[] = $conditionGroup;
                    }
                } else {
                    foreach ($value as $conditionKey => $subCondition) {
                        if (is_numeric($conditionKey)) {
                            self::processConditions($subCondition, $groupedConditions, $bindings, $dbType, $prefix . $key . $conditionKey . '_', $level + 1);
                        } else {
                            self::processSingleCondition($conditionKey, $subCondition, $groupedConditions, $bindings, $dbType, $prefix . $key . $conditionKey . '_', $level + 1);
                        }
                    }
                    if (!empty($groupedConditions)) {
                        $conditionGroup = '(' . implode(" $key ", $groupedConditions) . ')';
                        $sqlConditions[] = $conditionGroup;
                    }
                }
            } else {
                self::processSingleCondition($key, $value, $sqlConditions, $bindings, $dbType, $prefix, $level);
            }
        }
    }

    private static function processSingleCondition($key, $value, &$sqlConditions, &$bindings, $dbType, $prefix, $level)
    {
        $fieldQuoted = $dbType == 'pgsql' ? "\"$key\"" : "`$key`";
        if (is_array($value)) {
            foreach ($value as $condition => $val) {
                $bindingKey = ":" . $prefix . $key . "_" . $condition . $level;
                switch ($condition) {
                    case 'contains':
                    case 'startsWith':
                    case 'endsWith':
                    case 'equals':
                    case 'not':
                        $validatedValue = Validator::string($val);
                        $likeOperator = $condition === 'contains' ? ($dbType == 'pgsql' ? 'ILIKE' : 'LIKE') : '=';
                        if ($condition === 'startsWith') $validatedValue .= '%';
                        if ($condition === 'endsWith') $validatedValue = '%' . $validatedValue;
                        if ($condition === 'contains') $validatedValue = '%' . $validatedValue . '%';
                        $sqlConditions[] = $condition === 'not' ? "$fieldQuoted != $bindingKey" : "$fieldQuoted $likeOperator $bindingKey";
                        $bindings[$bindingKey] = $validatedValue;
                        break;
                    case 'gt':
                    case 'gte':
                    case 'lt':
                    case 'lte':
                        $validatedValue = is_float($val) ? Validator::float($val) : Validator::int($val);
                        $operator = $condition === 'gt' ? '>' : ($condition === 'gte' ? '>=' : ($condition === 'lt' ? '<' : '<='));
                        $sqlConditions[] = "$fieldQuoted $operator $bindingKey";
                        $bindings[$bindingKey] = $validatedValue;
                        break;
                    case 'in':
                    case 'notIn':
                        $inPlaceholders = [];
                        foreach ($val as $i => $inVal) {
                            $inKey = $bindingKey . "_" . $i;
                            // Assuming the values are strings; adjust validation as necessary.
                            $validatedValue = Validator::string($inVal);
                            $inPlaceholders[] = $inKey;
                            $bindings[$inKey] = $validatedValue;
                        }
                        $inClause = implode(', ', $inPlaceholders);
                        $sqlConditions[] = "$fieldQuoted " . ($condition === 'notIn' ? 'NOT IN' : 'IN') . " ($inClause)";
                        break;
                    default:
                        // Handle other conditions or log an error/warning for unsupported conditions
                        throw new \Exception("Unsupported condition: $condition");
                        break;
                }
            }
        } else {
            // For scalar non-array values; direct equals condition assumed
            $bindingKey = ":" . $prefix . $key . $level;
            // Use appropriate validation based on expected type; assuming string for simplicity
            $validatedValue = Validator::string($value);
            $sqlConditions[] = "$fieldQuoted = $bindingKey";
            $bindings[$bindingKey] = $validatedValue;
        }
    }

    private static function processNotCondition($conditions, &$sqlConditions, &$bindings, $dbType, $prefix, $level = 0)
    {
        foreach ($conditions as $key => $value) {
            self::processSingleCondition($key, $value, $sqlConditions, $bindings, $dbType, $prefix . 'NOT_', $level);
        }
    }

    public static function checkForInvalidKeys(array $data, array $fields, string $modelName)
    {
        foreach ($data as $key => $value) {
            if (!empty($key) && !in_array($key, $fields)) {
                throw new \Exception("The field '$key' does not exist in the $modelName model. Accepted fields: " . implode(', ', $fields));
            }
        }
    }
}
