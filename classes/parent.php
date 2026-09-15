<?php

abstract class Patient
{
    protected string $name;
    protected int $age;
    protected string $symptoms;
    private string $patientId;

    public function __construct(string $name, int $age, string $symptoms)
    {
        $this->patientId = 'PT-' . date('YmdHis') . '-' . random_int(100, 999);
        $this->name = trim($name);
        $this->age = $age;
        $this->symptoms = trim($symptoms);
    }

    public function getPatientId(): string
    {
        return $this->patientId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getSymptoms(): string
    {
        return $this->symptoms;
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
        return $this->name . ' requires routine triage assessment.';
    }

    public function getPriorityClass(): string
    {
        return match ($this->getTriageDecision()) {
            'IMMEDIATE' => 'danger',
            'URGENT' => 'warning',
            default => 'normal',
        };
    }
}
