<?php declare(strict_types=1);

namespace App\Service\Schedulable;

final class SchedulableHolder
{
    /** @var SchedulableInterface[] */
    private array $items = [];

    /**
     * @return SchedulableInterface[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param SchedulableInterface[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function addItem(SchedulableInterface $item): void
    {
        $this->items[] = $item;
    }
}
