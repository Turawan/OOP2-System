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

class EmergencyPatient extends Patient
{
    private string $emergencyType;

    public function __construct(string $name, int $age, string $symptoms, string $emergencyType)
    {
        parent::__construct($name, $age, $symptoms);
        $this->emergencyType = trim($emergencyType);
    }

    public function getCategory(): string
    {
        return 'Emergency Patient';
    }

    public function getTriageDecision(): string
    {
        return 'IMMEDIATE';
    }

    public function getPriorityScore(): int
    {
        return 3;
    }

    public function getSummary(): string
    {
        return $this->name . ' is marked for immediate assessment due to an emergency: ' . $this->emergencyType . '.';
    }
}

class PediatricPatient extends Patient
{
    private string $guardian;

    public function __construct(string $name, int $age, string $symptoms, string $guardian)
    {
        parent::__construct($name, $age, $symptoms);
        $this->guardian = trim($guardian);
    }

    public function getCategory(): string
    {
        return 'Pediatric Patient';
    }

    public function getTriageDecision(): string
    {
        return $this->age <= 5 ? 'URGENT' : 'STANDARD';
    }

    public function getPriorityScore(): int
    {
        return $this->age <= 5 ? 2 : 1;
    }

    public function getSummary(): string
    {
        return $this->name . ' is a pediatric patient accompanied by guardian ' . $this->guardian . '.';
    }
}

class SeniorPatient extends Patient
{
    private bool $hasChronicCondition;

    public function __construct(string $name, int $age, string $symptoms, bool $hasChronicCondition)
    {
        parent::__construct($name, $age, $symptoms);
        $this->hasChronicCondition = $hasChronicCondition;
    }

    public function getCategory(): string
    {
        return 'Senior Patient';
    }

    public function getTriageDecision(): string
    {
        return $this->hasChronicCondition ? 'URGENT' : 'STANDARD';
    }

    public function getPriorityScore(): int
    {
        return $this->hasChronicCondition ? 2 : 1;
    }

    public function getSummary(): string
    {
        $condition = $this->hasChronicCondition ? 'with a chronic condition' : 'without a declared chronic condition';
        return $this->name . ' is a senior patient ' . $condition . '.';
    }
}
