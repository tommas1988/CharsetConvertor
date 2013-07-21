<?php
/**
 * CharsetConvertor
 *
 * @author Tommas Yuan
 * @link   http://github.com/tommas1988/CharsetConvertor the source code repository
 */

namespace Tcc\ScriptFrontend\Printer;

use Tcc\ScriptFrontend\Runner;
use RuntimeException;
use InvalidArgumentException;

/**
 * Print message on console.
 */
class ConsolePrinter implements PrinterInterface
{
    /**
     * Application frontend
     *
     * @var Tcc\ScriptFrontend\Runner
     */
    protected $runner;

    /**
     * @param  Tcc\ScriptFrontend\Runner $runner
     * @return self
     */
    public function setAppRunner(Runner $runner)
    {
        $this->runner = $runner;

        return $this;
    }

    /**
     * @return Tcc\ScriptFrontend\Runner
     * @throws RuntimeException If application frontend is not setted yet
     */
    public function getAppRunner()
    {
        if (!$this->runner) {
            throw new RuntimeException('You haven`t set a app runner');
        }

        return $this->runner;
    }

    /**
     * Update Printer to print message
     *
     * @param int $state
     */
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

    /**
     * Print version number on console
     */
    public static function printVersion()
    {
        static::printAppHeader();
        print(Runner::VERSION);
    }

    /**
     * Print help message on console
     */
    public static function printHelpInfo()
    {

    }

    /**
     * Print application information on console
     */
    public static function printAppHeader()
    {
        print("CharsetConvertor by Tommas Yuan\n");
    }

    /**
     * Print undefined command error message on console
     */
    public static function printUndefinedCommand($command)
    {
        print("Unrecognize command: {$command}");
    }

    /**
     * Invalid state error
     *
     * @param mixed @state Invalid state
     */
    protected function invalidStateError($state)
    {
        throw new InvalidArgumentException(sprintf(
            'Invalid state argument, type: %s and value: %s',
            gettype($state), $state));
    }

    /**
     * Print convert process on console.
     */
    protected function printConvertProcess()
    {
        $runner = $this->getAppRunner();

        $totalCount    = $runner->convertFileCount(Runner::COUNT_ALL);
        $convertResult = $runner->getConvertResult();

        $states = '';

        $convertResult->rewind();
        while ($convertResult->valid()) {
            $states .= ($convertResult->getInfo() === null) ? '#' : '-';
            $convertResult->next();
        }

        printf("\r[%-{$totalCount}s] %d/%d", $states, count($convertResult), $totalCount);
    }

    /**
     * Print convert result on console.
     */
    protected function printConvertResult()
    {
        $runner = $this->getAppRunner();

        $results = '';
        if ($runner->getOption('verbose')) {
            $results = "\n";
            $resultStorage = $runner->getConvertResult();
            foreach ($resultStorage as $convertFile) {
                $results .= "\nFile name: " . $convertFile->getFilename()
                          . " path: " . $convertFile->getPath()
                          . " input charset: " . $convertFile->getInputCharset()
                          . " output charset: " . $convertFile->getOutputCharset();

                $errMsg = $resultStorage->getInfo();
                if ($errMsg !== null) {
                    $results .= "\nError: {$errMsg}";
                }
            }
        }

        printf("\n\nTotal Files: %d, convert failure: %d\nDone!%s", 
            $runner->convertFileCount(Runner::COUNT_ALL),
            $runner->convertFileCount(Runner::COUNT_FAILURE),
            $results);
    }
}
