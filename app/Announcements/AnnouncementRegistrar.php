<?php

namespace App\Announcements;

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Support\Result;

final class AnnouncementRegistrar
{
    /**
     * @return Result<Announcement, list<string>>
     */
    public function register(string $title, string $summary, string $body): Result
    {
        $blankFields = $this->blankFields($title, $summary, $body);

        if ($blankFields !== []) {
            /** @var Result<Announcement, list<string>> $result */
            $result = Result::err($blankFields);

            return $result;
        }

        $announcement = Announcement::query()->create([
            'title' => $title,
            'summary' => $summary,
            'body' => $body,
            'status' => AnnouncementStatus::Draft,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        /** @var Result<Announcement, list<string>> $result */
        $result = Result::ok($announcement);

        return $result;
    }

    /**
     * @return list<string>
     */
    private function blankFields(string $title, string $summary, string $body): array
    {
        $fields = [
            'title' => $title,
            'summary' => $summary,
            'body' => $body,
        ];

        $blankFields = [];

        foreach ($fields as $name => $value) {
            if (AnnouncementVisibility::isBlank($value)) {
                $blankFields[] = $name;
            }
        }

        return $blankFields;
    }
}
