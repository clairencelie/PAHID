<?php

namespace App\Services\Ai;

interface AiClientInterface
{
    public function verifyEntity(array $prospectData): array;

    public function checkLoa(string $loaText, array $context = []): array;
}
