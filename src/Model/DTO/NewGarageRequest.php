<?php

namespace App\Model\DTO;

final readonly class NewGarageRequest
{
    public function __construct(
        public int $floors,
        public int $spots,
    ) {}
}