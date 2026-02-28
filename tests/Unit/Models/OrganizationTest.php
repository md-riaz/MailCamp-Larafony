<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Organization;

class OrganizationTest extends TestCase
{
    public function testGenerateSlugFromSimpleName(): void
    {
        $slug = Organization::generateSlug('My Company');
        $this->assertSame('my-company', $slug);
    }

    public function testGenerateSlugFromNameWithSpecialCharacters(): void
    {
        $slug = Organization::generateSlug('Acme Corp. & Partners!');
        $this->assertSame('acme-corp-partners-', $slug);
    }

    public function testGenerateSlugFromNameWithMultipleSpaces(): void
    {
        $slug = Organization::generateSlug('Multiple   Spaces   Here');
        $this->assertSame('multiple-spaces-here', $slug);
    }

    public function testGenerateSlugPreservesHyphens(): void
    {
        $slug = Organization::generateSlug('already-hyphenated');
        $this->assertSame('already-hyphenated', $slug);
    }

    public function testGenerateSlugLowercasesOutput(): void
    {
        $slug = Organization::generateSlug('UPPERCASE');
        $this->assertSame('uppercase', $slug);
    }
}
