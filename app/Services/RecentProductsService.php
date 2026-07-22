<?php

namespace App\Services;

/**
 * Convierte la cookie de navegación en una lista pequeña y segura de IDs.
 * La cookie nunca se considera una fuente confiable aunque Laravel la cifre.
 */
class RecentProductsService
{
    public const MAX_ITEMS = 6;

    /** @return list<int> */
    public function parse(?string $cookie): array
    {
        $decoded = json_decode($cookie ?? '[]', true);

        if (! is_array($decoded)) {
            return [];
        }

        $ids = array_map('intval', array_filter($decoded, function (mixed $id): bool {
            $isInteger = is_int($id) || (is_string($id) && ctype_digit($id));

            return $isInteger && (int) $id > 0;
        }));

        return array_slice(array_values(array_unique($ids)), 0, self::MAX_ITEMS);
    }

    /** @return list<int> */
    public function record(?string $cookie, int $productId): array
    {
        return array_slice(array_values(array_unique([$productId, ...$this->parse($cookie)])), 0, self::MAX_ITEMS);
    }
}
