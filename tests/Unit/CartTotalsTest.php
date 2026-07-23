<?php

namespace Tests\Unit;

use App\Services\CartService;
use PHPUnit\Framework\TestCase;

/** Verifica de forma aislada IVA, costo de envío y umbral de envío gratuito. */
class CartTotalsTest extends TestCase
{
    public function test_it_calculates_tax_and_shipping_for_a_regular_purchase(): void
    {
        $totals = (new CartService)->totals(collect([
            ['line_total' => 10000],
            ['line_total' => 5000],
        ]));

        $this->assertSame(15000, $totals['subtotal']);
        $this->assertSame(1950, $totals['tax']);
        $this->assertSame(2500, $totals['shipping']);
        $this->assertSame(19450, $totals['total']);
    }

    public function test_shipping_is_free_from_twenty_thousand_colones(): void
    {
        $totals = (new CartService)->totals(collect([['line_total' => 20000]]));

        $this->assertSame(2600, $totals['tax']);
        $this->assertSame(0, $totals['shipping']);
        $this->assertSame(22600, $totals['total']);
    }

    public function test_shipping_threshold_and_empty_cart_boundaries(): void
    {
        $belowThreshold = (new CartService)->totals(collect([['line_total' => 19999]]));
        $empty = (new CartService)->totals(collect());

        $this->assertSame(2500, $belowThreshold['shipping']);
        $this->assertSame(25099, $belowThreshold['total']);
        $this->assertSame(['subtotal' => 0, 'tax' => 0, 'shipping' => 0, 'total' => 0], $empty);
    }
}
