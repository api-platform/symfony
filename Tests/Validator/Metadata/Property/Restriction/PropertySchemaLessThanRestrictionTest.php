<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiPlatform\Symfony\Tests\Validator\Metadata\Property\Restriction;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Symfony\Validator\Metadata\Property\Restriction\PropertySchemaLessThanRestriction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\Negative;
use Symfony\Component\Validator\Constraints\NegativeOrZero;

/**
 * @author Tomas Norkūnas <norkunas.tom@gmail.com>
 */
final class PropertySchemaLessThanRestrictionTest extends TestCase
{
    use ProphecyTrait;

    private PropertySchemaLessThanRestriction $propertySchemaLessThanRestriction;

    protected function setUp(): void
    {
        $this->propertySchemaLessThanRestriction = new PropertySchemaLessThanRestriction();
    }

    #[IgnoreDeprecations]
    public function testSupports(): void
    {
        foreach ($this->supportsProvider() as [$constraint, $propertyMetadata, $expectedResult]) {
            self::assertSame($expectedResult, $this->propertySchemaLessThanRestriction->supports($constraint, $propertyMetadata));
        }
    }

    #[DataProvider('supportsProviderWithNativeType')]
    public function testSupportsWithNativeType(Constraint $constraint, ApiProperty $propertyMetadata, bool $expectedResult): void
    {
        self::assertSame($expectedResult, $this->propertySchemaLessThanRestriction->supports($constraint, $propertyMetadata));
    }

    public static function supportsProvider(): \Generator
    {
        yield 'supported int/float with union types' => [new LessThan(value: 10), (new ApiProperty())->withBuiltinTypes([new LegacyType(LegacyType::BUILTIN_TYPE_INT), new LegacyType(LegacyType::BUILTIN_TYPE_FLOAT)]), true];
        yield 'supported int' => [new LessThan(value: 10), (new ApiProperty())->withBuiltinTypes([new LegacyType(LegacyType::BUILTIN_TYPE_INT)]), true];
        yield 'supported float' => [new LessThan(value: 10.99), (new ApiProperty())->withBuiltinTypes([new LegacyType(LegacyType::BUILTIN_TYPE_FLOAT)]), true];
        yield 'supported negative' => [new Negative(), (new ApiProperty())->withBuiltinTypes([new LegacyType(LegacyType::BUILTIN_TYPE_INT)]), true];
        yield 'not supported negative or zero' => [new NegativeOrZero(), (new ApiProperty())->withBuiltinTypes([new LegacyType(LegacyType::BUILTIN_TYPE_INT)]), false];
        yield 'not supported property path' => [new LessThan(propertyPath: 'greaterThanMe'), (new ApiProperty())->withBuiltinTypes([new LegacyType(LegacyType::BUILTIN_TYPE_INT)]), false];
    }

    public static function supportsProviderWithNativeType(): \Generator
    {
        yield 'native type: supported int/float with union types' => [new LessThan(value: 10), (new ApiProperty())->withNativeType(Type::union(Type::int(), Type::float())), true];
        yield 'native type: supported int' => [new LessThan(value: 10), (new ApiProperty())->withNativeType(Type::int()), true];
        yield 'native type: supported float' => [new LessThan(value: 10.99), (new ApiProperty())->withNativeType(Type::float()), true];
        yield 'native type: supported negative' => [new Negative(), (new ApiProperty())->withNativeType(Type::int()), true];
        yield 'native type: not supported negative or zero' => [new NegativeOrZero(), (new ApiProperty())->withNativeType(Type::int()), false];
        yield 'native type: not supported property path' => [new LessThan(propertyPath: 'greaterThanMe'), (new ApiProperty())->withNativeType(Type::int()), false];
    }

    #[IgnoreDeprecations]
    public function testCreate(): void
    {
        self::assertEquals([
            'exclusiveMaximum' => 10,
            'maximum' => 10,
        ], $this->propertySchemaLessThanRestriction->create(new LessThan(value: 10), (new ApiProperty())->withBuiltinTypes([new LegacyType(LegacyType::BUILTIN_TYPE_INT)])));
    }

    public function testCreateWithNativeType(): void
    {
        self::assertEquals([
            'exclusiveMaximum' => 10,
            'maximum' => 10,
        ], $this->propertySchemaLessThanRestriction->create(new LessThan(value: 10), (new ApiProperty())->withNativeType(Type::int())));

        self::assertEquals([
            'exclusiveMaximum' => 0,
            'maximum' => 0,
        ], $this->propertySchemaLessThanRestriction->create(new Negative(), (new ApiProperty())->withNativeType(Type::int())));

        self::assertEquals([
            'exclusiveMaximum' => 10.99,
            'maximum' => 10.99,
        ], $this->propertySchemaLessThanRestriction->create(new LessThan(value: 10.99), (new ApiProperty())->withNativeType(Type::float())));
    }
}
