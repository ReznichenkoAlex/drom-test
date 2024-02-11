<?php

$path = __DIR__. DIRECTORY_SEPARATOR . 'root';

try {
    $summer = new DirectorySummer($path, 'count');
    echo $summer->getSumFromTargetFiles();
} catch (Throwable $exception) {
    echo $exception->getMessage();
}

readonly class DirectorySummer
{
    private const REAL_NUMBER_REGEX = '/-?[0-9]+(?:[.,][0-9]+)?/';

    public function __construct(
        private string $directoryPath,
        private string $targetFileName,
    ) {
        $this->assertDirectoryExists($this->directoryPath);
        $this->assertDirectoryIsReadable($this->directoryPath);
        $this->asserTargetFileNameIsValid($this->targetFileName);
    }

    private function assertDirectoryExists(string $path): void
    {
        if ( ! is_dir($path)) {
            throw new InvalidArgumentException('directory not found');
        }
    }

    private function assertDirectoryIsReadable(string $path): void
    {
        if ( ! is_readable($path)) {
            throw new RuntimeException('permission denied');
        }
    }

    private function asserTargetFileNameIsValid(string $targetFileName): void
    {
        if (in_array($targetFileName, ['', '.', '..'], true)) {
            throw new LogicException('invalid target file name');
        }
    }

    public function getSumFromTargetFiles(): int
    {
        $sum = 0;
        foreach ($this->getTargetFiles() as $file) {
            $numbers = $this->getNumbersFromFile($file);
            $numbers = $this->filterRealNumbers($numbers);
            $sum += array_sum(array_map('intval', $numbers));
        }

        return $sum;
    }

    /**
     * @yield iterable|SplFileInfo
     */
    private function getTargetFiles(): iterable
    {
        $directoryIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directoryPath, FilesystemIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($directoryIterator as $file) {
            if ($file->getFilename() === $this->targetFileName) {
                yield $file;
            }
        }
    }

    /**
     * @return array<string>
     */
    private function getNumbersFromFile(SplFileInfo $file): array
    {
        $content = file_get_contents($file->getPathname());
        preg_match_all(self::REAL_NUMBER_REGEX, $content, $numbers);

        return $numbers[0];
    }

    /**
     * @return array<string>
     */
    private function filterRealNumbers(array $numbers): array
    {
        $numbers = array_map(
            static fn (string $number) => str_replace(',', '.', $number),
            $numbers
        );

        return array_filter(
            $numbers,
            static fn (string $number) => fmod($number, (int) $number) === 0.0,
        );
    }
}
