<?php

namespace Tests\Unit;

use App\Support\BrazilianNumber;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BrazilianNumberTest extends TestCase
{
    public function test_it_normalizes_brazilian_currency(): void
    {
        $this->assertSame('1234.56', BrazilianNumber::decimal('R$ 1.234,56'));
        $this->assertSame('1234.5678', BrazilianNumber::decimal('1.234,5678', 4));
        $this->assertSame('1234.56', BrazilianNumber::decimal('1234.56'));
        $this->assertSame('R$ 1.234,56', BrazilianNumber::currency('1234.56'));
    }

    public function test_it_rejects_invalid_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BrazilianNumber::decimal('12 reais');
    }
}
