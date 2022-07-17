<?php

/**
 * @package redaxo\structure\content
 */
final class rex_ctype
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    private function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<int, rex_ctype>
     */
    public static function forTemplate(int $templateId): array
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT attributes FROM '. rex::getTable('template') .' WHERE id = ?', [$templateId]);
        $attributes = $sql->getArrayValue('attributes');

        /** @psalm-suppress MixedReturnStatement */
        $ctypesData = $attributes['ctype'] ?? [];

        $ctypes = [];
        foreach ($ctypesData as $id => $name) {
            $ctypes[] = new self($id, $name);
        }
        return $ctypes;
    }
}
