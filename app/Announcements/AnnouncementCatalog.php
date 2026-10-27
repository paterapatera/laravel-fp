<?php

namespace App\Announcements;

use App\Models\Announcement;
use App\Support\Result;
use DateTimeInterface;
use Illuminate\Support\Collection;

final class AnnouncementCatalog
{
    public function __construct(private readonly AnnouncementVisibility $visibility) {}

    /**
     * @return Result<Collection<int, Announcement>, never>
     */
    public function visible(DateTimeInterface $now): Result
    {
        $announcements = Announcement::query()
            ->get()
            ->filter(fn (Announcement $announcement): bool => $this->matches($announcement, $now))
            ->sort(fn (Announcement $left, Announcement $right): int => $this->compare($left, $right))
            ->values();

        return Result::ok($announcements);
    }

    /**
     * @return Result<Announcement, string>
     */
    public function findVisible(int $id, DateTimeInterface $now): Result
    {
        $announcement = Announcement::query()->find($id);

        if (! $announcement instanceof Announcement || ! $this->matches($announcement, $now)) {
            return Result::err('not-visible');
        }

        return Result::ok($announcement);
    }

    /**
     * @param  array{startsAt: DateTimeInterface, createdAt: DateTimeInterface, title: string}  $left
     * @param  array{startsAt: DateTimeInterface, createdAt: DateTimeInterface, title: string}  $right
     */
    public function compareOrder(array $left, array $right): int
    {
        $byStart = $right['startsAt'] <=> $left['startsAt'];

        if ($byStart !== 0) {
            return $byStart;
        }

        $byCreated = $right['createdAt'] <=> $left['createdAt'];

        if ($byCreated !== 0) {
            return $byCreated;
        }

        return strcmp($left['title'], $right['title']);
    }

    private function matches(Announcement $announcement, DateTimeInterface $now): bool
    {
        return $this->visibility->isVisible(
            $announcement->status,
            $announcement->title,
            $announcement->summary,
            $announcement->body,
            $announcement->starts_at,
            $announcement->ends_at,
            $now,
        );
    }

    private function compare(Announcement $left, Announcement $right): int
    {
        if (! $left->starts_at instanceof DateTimeInterface || ! $left->created_at instanceof DateTimeInterface) {
            return 0;
        }

        if (! $right->starts_at instanceof DateTimeInterface || ! $right->created_at instanceof DateTimeInterface) {
            return 0;
        }

        return $this->compareOrder(
            [
                'startsAt' => $left->starts_at,
                'createdAt' => $left->created_at,
                'title' => (string) $left->title,
            ],
            [
                'startsAt' => $right->starts_at,
                'createdAt' => $right->created_at,
                'title' => (string) $right->title,
            ],
        );
    }
}
