<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2021 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/json-normalizer
 */

namespace Ergebnis\Json\Normalizer\Test\Unit;

use Ergebnis\Json\Normalizer\Format;
use Ergebnis\Json\Normalizer\IndentNormalizer;
use Ergebnis\Json\Normalizer\Json;
use Ergebnis\Json\Printer\PrinterInterface;

/**
 * @internal
 *
 * @covers \Ergebnis\Json\Normalizer\IndentNormalizer
 *
 * @uses \Ergebnis\Json\Normalizer\Format\Format
 * @uses \Ergebnis\Json\Normalizer\Format\Indent
 * @uses \Ergebnis\Json\Normalizer\Format\JsonEncodeOptions
 * @uses \Ergebnis\Json\Normalizer\Format\NewLine
 * @uses \Ergebnis\Json\Normalizer\Json
 */
final class IndentNormalizerTest extends AbstractNormalizerTestCase
{
    public function testNormalizeUsesPrinterToNormalizeJsonWithIndent(): void
    {
        $indent = Format\Indent::fromString('  ');

        $json = Json::fromEncoded(
            <<<'JSON'
{
    "status": "original"
}
JSON
        );

        $indented = <<<'JSON'
{
    "name": "Andreas Möller (indented)",
    "url": "https://localheinz.com"
}
JSON;

        $printer = $this->createMock(PrinterInterface::class);

        $printer
            ->expects(self::once())
            ->method('print')
            ->with(
                self::identicalTo($json->encoded()),
                self::identicalTo($indent->toString()),
            )
            ->willReturn($indented);

        $normalizer = new IndentNormalizer(
            $indent,
            $printer,
        );

        $normalized = $normalizer->normalize($json);

        self::assertSame($indented, $normalized->encoded());
    }
}
