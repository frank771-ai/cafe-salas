<?php

namespace Tests\Unit;

use App\Services\RecentProductsService;
use PHPUnit\Framework\TestCase;

/** Verifica que una cookie manipulada nunca crezca ni introduzca IDs inválidos. */
class RecentProductsServiceTest extends TestCase
{
    public function test_it_sanitizes_deduplicates_and_limits_cookie_ids(): void
    {
        $service = new RecentProductsService;
        $cookie = json_encode([1, '2', -3, 2, 'bad', 1.5, '1e3', 4, 5, 6, 7, 8]);

        $this->assertSame([1, 2, 4, 5, 6, 7], $service->parse($cookie));
        $this->assertSame([9, 1, 2, 4, 5, 6], $service->record($cookie, 9));
    }

    public function test_it_rejects_non_array_json(): void
    {
        $service = new RecentProductsService;

        $this->assertSame([], $service->parse('"not-an-array"'));
        $this->assertSame([], $service->parse('{broken'));
    }
}
