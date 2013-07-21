<?php
/**
 * CharsetConvertor
 *
 * @author Tommas Yuan
 * @link   http://github.com/tommas1988/CharsetConvertor the source code repository
 */

namespace Tcc\ScriptFrontend\Printer;

use Tcc\ScriptFrontend\Runner;

interface PrinterInterface
{
    /**
     * Set application frontend
     *
     * @param Tcc\ScriptFrontend\Runner $runner
     */
    public function setAppRunner(Runner $runer);

    /**
     * Get application frontend
     *
     * @return Tcc\ScriptFrontend\Runner
     */
    public function getAppRunner();

    /**
     * Update printer to print
     *
     * @param int $state
     */
    public function update($state);
}