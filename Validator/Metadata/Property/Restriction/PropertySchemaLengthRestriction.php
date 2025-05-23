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

namespace ApiPlatform\Symfony\Validator\Metadata\Property\Restriction;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class PropertySchemaLengthRestrictions.
 *
 * @author Andrii Penchuk penja7@gmail.com
 */
class PropertySchemaLengthRestriction implements PropertySchemaRestrictionMetadataInterface
{
    /**
     * {@inheritdoc}
     *
     * @param Length $constraint
     */
    public function create(Constraint $constraint, ApiProperty $propertyMetadata): array
    {
        $restriction = [];

        if (isset($constraint->min)) {
            $restriction['minLength'] = (int) $constraint->min;
        }

        if (isset($constraint->max)) {
            $restriction['maxLength'] = (int) $constraint->max;
        }

        return $restriction;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Constraint $constraint, ApiProperty $propertyMetadata): bool
    {
        if (method_exists(PropertyInfoExtractor::class, 'getType')) {
            $type = $propertyMetadata->getExtraProperties()['nested_schema'] ?? false ? Type::string() : $propertyMetadata->getNativeType();

            return $constraint instanceof Length && $type?->isIdentifiedBy(TypeIdentifier::STRING);
        }

        $types = array_map(fn (LegacyType $type) => $type->getBuiltinType(), $propertyMetadata->getBuiltinTypes() ?? []);
        if ($propertyMetadata->getExtraProperties()['nested_schema'] ?? false) {
            $types = [LegacyType::BUILTIN_TYPE_STRING];
        }

        return $constraint instanceof Length && \count($types) && \in_array(LegacyType::BUILTIN_TYPE_STRING, $types, true);
    }
}
