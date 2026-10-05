<?php

namespace Meteor\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Uid\NilUuid;
use Symfony\Component\Uid\UuidV7;

class UuidV7StringType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'CHAR(36) COMMENT \'(DC2Type:uuid_string)\'';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof UuidV7) {
            return $value;
        }

        return UuidV7::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            if (!UuidV7::isValid($value)) {
                throw new \InvalidArgumentException(
                    sprintf('Expected %s, got %s', UuidV7::class, get_debug_type($value))
                );
            }
            $value = UuidV7::fromString($value);
        }

        if (!$value instanceof UuidV7 || $value->toRfc4122() === NilUuid::v7()->toRfc4122()) {
            throw new \InvalidArgumentException(
                sprintf('Expected %s, got %s', UuidV7::class, get_debug_type($value))
            );
        }

        return $value->toRfc4122();
    }

    public function getName()
    {
        return 'uuid_string';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
