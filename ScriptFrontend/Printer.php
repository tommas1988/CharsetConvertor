<?php
namespace Tcc\ScriptFrontend;

class Printer
{
    const PRE_CONVERT  = 0;
    const CONVERTING   = 1;
    const CONVERT_POST = 2;

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
            case static::PRE_CONVERT:
                $this->printAppHeader();
                break;
            case static::CONVERTING:
                $this->printConvertProcess();
                break;
            case static::CONVERT_POST:
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
