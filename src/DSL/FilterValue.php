<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic\DSL;

use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Class FilterValue
 * @package ATPawelczyk\Elastic\DSL
 */
class FilterValue implements FilterInterface
{
    public const TERM = 'term';
    public const TERMS = 'terms';
    public const RANGE = 'range';

    /** @var string[] */
    public const TYPES = [self::TERM, self::TERMS, self::RANGE];

    private $type;
    private $field;
    private $value;

    /**
     * FilterValue constructor.
     * @param string $type
     * @param string $field
     * @param string|mixed[] $value - [1,2,3], ['gte', '1990-12-21']
     */
    public function __construct(string $type, string $field, $value)
    {
        if (!in_array($type, static::TYPES)) {
            throw new InvalidArgumentException("Type {$type} is not allowed.");
        }

        $this->type = $type;
        $this->field = $field;
        $this->value = $value;

        $this->validateQuery();
    }

    /**
     * Validate value for type context
     */
    private function validateQuery(): void
    {
        switch (true) {
            case $this->type === static::TERM && !is_string($this->value) && !is_int($this->value):
                throw new UnexpectedValueException('Value should be a string or integer when type is term');
            case $this->type === static::TERMS && !is_array($this->value):
                throw new UnexpectedValueException('Value should be an array when type is terms');
            case $this->type === static::RANGE && !is_array($this->value):
                throw new UnexpectedValueException('Value should be an array when type is range');
        }
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [$this->type => [$this->field => $this->value]];
    }
}
