<?php
namespace Tcc\ScriptFrontend\Printer;

use Tcc\ScriptFrontend\Runner;
use RuntimeException;

class ConsolePrinter implements PrinterInterface
{
    protected $runner;

    public function setAppRunner(Runner $runner)
    {
        $this->runner;
    }

    public function getAppRunner()
    {
        if (!$this->runner) {
            throw new RuntimeException('You haven`t set a app runner');
        }

        return $this->runner;
    }

    public function update($state)
    {
        if (!is_int($state)) {
            $this->invalidStateError($state);
        }

        switch ($state) {
            case Runner::PRE_CONVERT:
                $this->printAppHeader();
                break;
            case Runner::CONVERTING:
                $this->printConvertProcess();
                break;
            case Runner::CONVERT_POST:
                $this->printConvertResult();
                break;
            default :
                $this->invalidStateError($state);
                break;
        }
    }

    public static function printVersion()
    {
        static::printAppHeader();
        print(Runner::VERSION);
    }

    public static function printHelpInfo()
    {

    }

    public static function printAppHeader()
    {
        print("CharsetConvertor by Tommas Yuan\n");
    }

    public static function printUndefinedCommand($command)
    {
        print("Unrecognize command: {$command}");
    }

    protected function invalidStateError($state)
    {
        throw new InvalidArgumentException(sprintf(
            'Invalid state argument, type: %s and value: %s',
            gettype($state), $state));
    }

    protected function printConvertProcess()
    {
        $runer = $this->getAppRunner();

        $totalCount    = $runner->convertFileCount(Runner::COUNT_ALL);
        $convertResult = $runner->getConvertResult();

        $states = '';
        foreach ($convertResult as $result) {
            $states .= ($state === null) ? '#' : '-';
        }

        printf("\r[%-{$totalCount}s] %d/%d", $states, count($convertedFiles), $totalCount);
    }

    protected function printConvertResult()
    {
        $runner = $this->getAppRunner();

        $results = '';
        if ($runner->getOption('verbose')) {
            $results = "\n";
            foreach ($runner->getConvertResult() as $convertFile => $errMsg) {
                $results .= "\nFile name: " . $convertFile->getFilename()
                          . " path: " . $convertFile->getPath()
                          . " input charset: " . $convertFile->getInputCharset()
                          . " output charset: " . $convertFile->getOutputCharset();
                if ($errMsg !== null) {
                    $results .= "Error: {$errMsg}";
                }
            }
        }

        printf("\n\nTotal Files: %d, convert failure: %d\nDone!%s", 
            $runner->convertFileCount(Runner::COUNT_ALL),
            $runner->convertFileCount(Runner::COUNT_FAILURE),
            $results);
    }
}
