<?php

namespace VendorName\Skeleton\Common\Traits;

use Exception;

trait HasRequiredFields
{
    abstract public function requiredFields(): array;

    private function getMissingFields($fields): array
    {
        $required_fields = $this->requiredFields();
        // if $fields is empty then return all missing fields
        if (count($fields) == 0) {
            return array_keys(array_filter($required_fields, function ($value) {
                return $value == null;
            }));
        }

        return array_keys(array_filter($required_fields, function ($value, $key) use ($fields) {
            return in_array($key, $fields) && $value == null;
        }, ARRAY_FILTER_USE_BOTH));
    }

    private function requiredFieldsShouldExist(...$fields): void
    {
        $missing_fields = $this->getMissingFields($fields);

        if (count($missing_fields) > 0) {
            throw new Exception('Missing required fields: '.implode(',', $missing_fields));
        }
    }
}
