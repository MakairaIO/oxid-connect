<?php

/**
 * @noinspection UnserializeExploitsInspection
 * @noinspection MkdirRaceConditionInspection
 */

namespace Makaira\Connect;

use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use function unserialize;
use function var_export;

use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

trait AssertSnapshotTrait
{
    private $snapshotCount = 0;

    private function slugify($text, string $divider = '_'): string
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', $divider, $text);

        // trim
        $text = trim($text, $divider);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    protected function assertSnapshot($actual, string $suffix, $encoder, $decoder, ?string $message = null)
    {
        if (!($this instanceof TestCase)) {
            throw new RuntimeException(sprintf("This trait '%s' can only be used in PHPUnit test cases", __TRAIT__));
        }

        $snapshotFile = sprintf("%s.%s", $this->getSnapshotFilename(), $suffix);

        if (!file_exists($snapshotFile)) {
            file_put_contents($snapshotFile, $encoder($actual));

            throw new IncompleteTestError();
        }

        $expected = $decoder(file_get_contents($snapshotFile));

        if (null === $message) {
            $message = sprintf("Current object doesn't match the contents of %s", basename($snapshotFile));
        }

        // Normalize actual value
        $actual = $decoder($encoder($actual));

        $this->assertEqualsCanonicalizing($expected, $actual, $message);
    }

    protected function assertPhpSnapshot($actual, ?string $message = null)
    {
        $this->assertSnapshot($actual, 'serialized', 'serialize', 'unserialize', $message);
    }

    protected function assertJsonSnapshot($actual, ?string $message = null)
    {
        $encoder = static function ($actual) {
            return json_encode($actual, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
        };

        $decoder = static function ($expected) {
            return json_decode($expected, false, 512, JSON_THROW_ON_ERROR);
        };

        $this->assertSnapshot($actual, 'json', $encoder, $decoder, $message);
    }

    /**
     * @return string
     */
    private function getSnapshotFilename(): string
    {
        $reflection = new ReflectionClass($this);

        $snapshotDir = sprintf('%s/__snapshots__', dirname($reflection->getFileName()));

        $snapshotFilename = sprintf(
            '%s--%s--%u',
            $reflection->getShortName(),
            $this->slugify($this->getName()),
            $this->snapshotCount
        );

        $this->snapshotCount++;

        if (!is_dir($snapshotDir)) {
            mkdir($snapshotDir, 0755, true);
        }

        return sprintf("%s/%s", $snapshotDir, $snapshotFilename);
    }
}
