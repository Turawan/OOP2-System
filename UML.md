# UML / Class Diagram

```text
                         <<abstract>>
                         Patient
        ------------------------------------------
        - name: string
        - age: int
        - symptoms: string
        - patientId: string (private)
        ------------------------------------------
        + __construct(name, age, symptoms)
        + getPatientId(): string
        + getName(): string
        + getAge(): int
        + getSymptoms(): string
        + getCategory(): string
        + getTriageDecision(): string
        + getPriorityScore(): int
        + getSummary(): string
        + getPriorityClass(): string
                   ▲
          ┌────────┼──────────────┐
          │        │              │
          │        │              │
+----------------+ +------------------+ +----------------------+
| EmergencyPatient| | PediatricPatient | | SeniorPatient        |
+----------------+ +------------------+ +----------------------+
| - emergencyType| | - guardian       | | - hasChronicCondition|
+----------------+ +------------------+ +----------------------+
| + __construct() | | + __construct()  | | + __construct()       |
| + getCategory() | | + getCategory()  | | + getCategory()       |
| + getTriage...  | | + getTriage...   | | + getTriage...        |
| + getPriority...| | + getPriority... | | + getPriority...      |
| + getSummary()  | | + getSummary()   | | + getSummary()        |
+----------------+ +------------------+ +----------------------+
```

## Polymorphism

The application stores all subclass instances in one `Patient` array. It then calls the same methods—especially `getTriageDecision()`, `getPriorityScore()`, and `getSummary()`—on each object. PHP dispatches the call to the overridden implementation belonging to the actual child object.

This directly demonstrates the required behavior that the same method call can produce different results for different subclasses. The project specification requires this type of polymorphism and at least two overridden methods. fileciteturn0file0L60-L66
