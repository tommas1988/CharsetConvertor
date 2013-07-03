<?php
namespace Tcc\ScriptFrontend;

class Printer
{
    protected $runner;

    public function __construct(Runner $runner)
    {
        $this->runner;
    }

    public function print($state)
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

    protected function invalidStateError($state)
    {
        throw new InvalidArgumentException(sprintf(
            'Invalid state argument, type: %s and value: %s',
            gettype($state), $state));
    }

    protected printAppHeader()
    {

    }

    protected printConvertProcess()
    {

    }

    protected printConvertResult()
    {

    }
}
