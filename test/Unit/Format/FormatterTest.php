<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/localheinz/json-normalizer
 */

namespace Localheinz\Json\Normalizer\Test\Unit\Format;

use Localheinz\Json\Normalizer\Format\Format;
use Localheinz\Json\Normalizer\Format\Formatter;
use Localheinz\Json\Normalizer\Format\FormatterInterface;
use Localheinz\Json\Normalizer\Format\Indent;
use Localheinz\Json\Normalizer\Format\NewLine;
use Localheinz\Json\Normalizer\JsonInterface;
use Localheinz\Json\Printer;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;
use Prophecy\Argument;

/**
 * @internal
 */
final class FormatterTest extends Framework\TestCase
{
    use Helper;

    public function testImplementsFormatterInterface(): void
    {
        $this->assertClassImplementsInterface(FormatterInterface::class, Formatter::class);
    }

    /**
     * @dataProvider providerFinalNewLine
     *
     * @param bool $hasFinalNewLine
     */
    public function testFormatEncodesWithJsonEncodeOptionsIndentsAndPossiblySuffixesWithFinalNewLine(bool $hasFinalNewLine): void
    {
        $faker = $this->faker();

        $jsonEncodeOptions = $faker->numberBetween(1);
        $indentString = \str_repeat(' ', $faker->numberBetween(1, 5));
        $newLineString = $faker->randomElement([
            "\r\n",
            "\n",
            "\r",
        ]);

        $encoded = <<<'JSON'
{
    "name": "Andreas Möller",
    "url": "https://localheinz.com"
}
JSON;

        $decoded = \json_decode($encoded);

        $json = $this->prophesize(JsonInterface::class);

        $json
            ->decoded()
            ->shouldBeCalled()
            ->willReturn($decoded);

        $encodedWithJsonEncodeOptions = \json_encode(
            $decoded,
            $jsonEncodeOptions
        );

        $printedWithIndentAndNewLine = <<<'JSON'
{
  "name": "Andreas Möller (printed)",
  "url": "https://localheinz.com"
}
JSON;

        $format = new Format(
            $jsonEncodeOptions,
            Indent::fromString($indentString),
            NewLine::fromString($newLineString),
            $hasFinalNewLine
        );

        $printer = $this->prophesize(Printer\PrinterInterface::class);

        $printer
            ->print(
                Argument::is($encodedWithJsonEncodeOptions),
                Argument::is($indentString),
                Argument::is($newLineString)
            )
            ->shouldBeCalled()
            ->willReturn($printedWithIndentAndNewLine);

        $formatter = new Formatter($printer->reveal());

        $formatted = $formatter->format(
            $json->reveal(),
            $format
        );

        $this->assertInstanceOf(JsonInterface::class, $formatted);

        $suffix = $hasFinalNewLine ? $newLineString : '';

        $this->assertSame($printedWithIndentAndNewLine . $suffix, $formatted->encoded());
    }

    public function providerFinalNewLine(): \Generator
    {
        $values = [
            'bool-false' => false,
            'bool-true' => true,
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }
}
