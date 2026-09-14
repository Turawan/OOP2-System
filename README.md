# MedTriage — Healthcare Medical Clinic Triage Tracker

A PHP Object-Oriented Programming 2 midterm project implementing a **Medical Clinic Triage Tracker** without a database.

## Project Requirements Covered

- PHP
- No MySQL / phpMyAdmin / SQL
- HTML form input and validation
- Parent class: `Patient`
- Child classes: `EmergencyPatient`, `PediatricPatient`, `SeniorPatient`
- Inheritance using `extends`
- Method overriding (`getCategory`, `getTriageDecision`, `getPriorityScore`, `getSummary`)
- Polymorphism through a `Patient` array and the same method calls on different objects
- Encapsulation with `private`, `protected`, and `public`
- Constructors for object initialization
- Temporary session-based storage only
- Responsive HTML/CSS interface

These choices follow the OOP 2 midterm specification, which requires at least one parent class, three child classes, two overridden methods, polymorphism, constructors, encapsulation, HTML forms, validation, and no database. fileciteturn0file0L22-L37

## How It Works

1. Open `index.php` in a PHP-enabled local server.
2. Select a patient type.
3. Enter the patient's information.
4. The correct subclass is created through its constructor.
5. The application calls common `Patient` methods polymorphically.
6. Patients are sorted by priority and displayed in the triage queue.

## Triage Behavior

- **EmergencyPatient** → IMMEDIATE, priority score 3
- **PediatricPatient** → URGENT when age is 5 or below; otherwise STANDARD
- **SeniorPatient** → URGENT when a chronic condition is declared; otherwise STANDARD
- **General Patient** → STANDARD

## Files

```text
OOP2-System/
├── classes/
│   └── Patient.php
├── index.php
├── style.css
├── README.md
└── UML.md
```

## Running Locally

With PHP installed, from the project folder run:

```bash
php -S localhost:8000
```

Then visit `http://localhost:8000`.

The midterm instructions explicitly allow temporary storage of multiple objects in PHP arrays and state that data may disappear when the page/session is reset. fileciteturn0file0L39-L49

## Important Note

This system is for an academic OOP demonstration. It is not intended to replace professional medical triage or clinical decision-making.
