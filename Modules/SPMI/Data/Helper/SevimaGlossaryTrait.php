<?php

namespace Modules\SPMI\Data\Helper;

use Sevima\SevimaGlossary\SevimaGlossary;

trait SevimaGlossaryTrait
{
    protected $glossary = null;

    public function __construct()
    {
        $db = config('database.connections.siakadv1');
        $this->glossary = new SevimaGlossary();
        $this->glossary->initConnection(
            $db['database'],
            $db['username'],
            $db['password'],
            $db['host'],
            $db['port'],
            SevimaGlossary::SIACLOUD
        );
    }

    public function initTracerStudyConnection()
    {
        $db = config('database.connections.tracerstudy');
        $this->glossary->initConnection(
            $db['database'],
            $db['username'],
            $db['password'],
            $db['host'],
            $db['port'],
            SevimaGlossary::TRACERSTUDY
        );
    }
}
