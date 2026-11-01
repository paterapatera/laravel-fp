<?php

namespace App\Announcements;

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Support\Result;
use DateTimeImmutable;
use DateTimeZone;

final class AnnouncementRegistrar
{
    /**
     * @return Result<Announcement, list<string>>
     */
    public function register(
        string $title,
        string $summary,
        string $body,
        string $startsAt = '',
        string $endsAt = '',
    ): Result {
        $errors = $this->blankFields($title, $summary, $body);
        $start = $this->classifyWindowSide($startsAt);
        $end = $this->classifyWindowSide($endsAt);

        if ($start['kind'] === 'empty' && $end['kind'] !== 'empty') {
            $errors[] = 'starts_at';
        }

        if ($end['kind'] === 'empty' && $start['kind'] !== 'empty') {
            $errors[] = 'ends_at';
        }

        if ($start['kind'] === 'invalid') {
            $errors[] = 'starts_at_invalid';
        }

        if ($end['kind'] === 'invalid') {
            $errors[] = 'ends_at_invalid';
        }

        if ($start['kind'] === 'hour' && $end['kind'] === 'hour' && $end['at'] < $start['at']) {
            $errors[] = 'window';
        }

        if ($errors !== []) {
            /** @var Result<Announcement, list<string>> $result */
            $result = Result::err($errors);

            return $result;
        }

        $announcement = Announcement::query()->create([
            'title' => $title,
            'summary' => $summary,
            'body' => $body,
            'status' => AnnouncementStatus::Draft,
            'starts_at' => $start['kind'] === 'hour' ? $start['at'] : null,
            'ends_at' => $end['kind'] === 'hour' ? $end['at'] : null,
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

    /**
     * @return array{kind: 'empty'}|array{kind: 'invalid'}|array{kind: 'hour', at: DateTimeImmutable}
     */
    private function classifyWindowSide(string $value): array
    {
        if (AnnouncementVisibility::isBlank($value)) {
            return ['kind' => 'empty'];
        }

        $matched = preg_match('/\A(\d{4})-(\d{2})-(\d{2}) (\d{1,2})\z/', $value, $matches);

        if ($matched !== 1) {
            return ['kind' => 'invalid'];
        }

        $year = (int) $matches[1];
        $month = (int) $matches[2];
        $day = (int) $matches[3];
        $hour = (int) $matches[4];

        if ($hour > 23 || ! checkdate($month, $day, $year)) {
            return ['kind' => 'invalid'];
        }

        $at = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i:s',
            sprintf('%04d-%02d-%02d %02d:00:00', $year, $month, $day, $hour),
            new DateTimeZone('UTC'),
        );

        if (! $at instanceof DateTimeImmutable) {
            return ['kind' => 'invalid'];
        }

        return ['kind' => 'hour', 'at' => $at];
    }
}
