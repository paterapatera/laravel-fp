<?php

namespace App\Announcements;

use App\Enums\AnnouncementStatus;
use DateTimeInterface;

final class AnnouncementVisibility
{
    public function isVisible(
        AnnouncementStatus $status,
        ?string $title,
        ?string $summary,
        ?string $body,
        ?DateTimeInterface $startsAt,
        ?DateTimeInterface $endsAt,
        DateTimeInterface $now,
    ): bool {
        if ($status !== AnnouncementStatus::Published) {
            return false;
        }

        if ($this->isBlank($title) || $this->isBlank($summary) || $this->isBlank($body)) {
            return false;
        }

        if ($startsAt === null || $endsAt === null) {
            return false;
        }

        return $now >= $startsAt && $now <= $endsAt;
    }

    private function isBlank(?string $value): bool
    {
        if ($value === null) {
            return true;
        }

        return preg_match('/\A\p{White_Space}*\z/u', $value) === 1;
    }
}
