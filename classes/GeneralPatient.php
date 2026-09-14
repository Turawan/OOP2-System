<?php

class GeneralPatient extends Patient
{
    public function __construct(string $name, int $age, string $symptoms)
    {
        parent::__construct($name, $age, $symptoms);
    }

    public function getCategory(): string
    {
        return 'General Patient';
    }

    public function getTriageDecision(): string
    {
        return 'STANDARD';
    }

    public function getPriorityScore(): int
    {
        return 1;
    }

    public function getSummary(): string
    {
        return $this->name . ' is a general patient receiving standard triage.';
    }
}
