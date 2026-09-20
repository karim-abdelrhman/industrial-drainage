<?php

namespace App\Support;

final class NumericInterval
{
    public function __construct(
        public readonly ?float $min,
        public readonly bool $minInclusive,
        public readonly ?float $max,
        public readonly bool $maxInclusive,
    ) {}

    public function contains(float $value): bool
    {
        if ($this->min !== null) {
            if ($this->minInclusive ? $value < $this->min : $value <= $this->min) {
                return false;
            }
        }

        if ($this->max !== null) {
            if ($this->maxInclusive ? $value > $this->max : $value >= $this->max) {
                return false;
            }
        }

        return true;
    }

    public function overlaps(self $other): bool
    {
        return ! $this->isLeftOf($other) && ! $other->isLeftOf($this);
    }

    private function isLeftOf(self $other): bool
    {
        if ($this->max === null || $other->min === null) {
            return false;
        }

        if ($this->max < $other->min) {
            return true;
        }

        if ($this->max > $other->min) {
            return false;
        }

        return ! ($this->maxInclusive && $other->minInclusive);
    }
}
