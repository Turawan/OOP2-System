<?php
session_start();
require_once __DIR__ . '/classes/Patient.php';

if (!isset($_SESSION['patients'])) {
    $_SESSION['patients'] = [];
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $age = filter_var($_POST['age'] ?? null, FILTER_VALIDATE_INT);
    $symptoms = trim($_POST['symptoms'] ?? '');

    if ($name === '') $errors[] = 'Patient name is required.';
    if ($age === false || $age === null || $age < 0 || $age > 120) $errors[] = 'Age must be a valid number from 0 to 120.';
    if ($symptoms === '') $errors[] = 'Symptoms are required.';
    if (!in_array($type, ['emergency', 'pediatric', 'senior', 'general'], true)) $errors[] = 'Please select a valid patient type.';

    if (!$errors) {
        switch ($type) {
            case 'emergency':
                $detail = trim($_POST['emergency_type'] ?? '');
                if ($detail === '') $errors[] = 'Emergency type is required.';
                else $patient = new EmergencyPatient($name, $age, $symptoms, $detail);
                break;
            case 'pediatric':
                $detail = trim($_POST['guardian'] ?? '');
                if ($detail === '') $errors[] = 'Guardian name is required.';
                elseif ($age > 17) $errors[] = 'Pediatric patients must be 17 or younger.';
                else $patient = new PediatricPatient($name, $age, $symptoms, $detail);
                break;
            case 'senior':
                if ($age < 60) $errors[] = 'Senior patients must be 60 or older.';
                else $patient = new SeniorPatient($name, $age, $symptoms, isset($_POST['chronic']) && $_POST['chronic'] === 'yes');
                break;
            default:
                $patient = new Patient($name, $age, $symptoms) {
                    public function getCategory(): string { return 'General Patient'; }
                    public function getTriageDecision(): string { return 'STANDARD'; }
                    public function getPriorityScore(): int { return 1; }
                };
        }

        if (!$errors) {
            $_SESSION['patients'][] = $patient;
            usort($_SESSION['patients'], fn(Patient $a, Patient $b) => $b->getPriorityScore() <=> $a->getPriorityScore());
            header('Location: index.php?success=1');
            exit;
        }
    }
}

if (isset($_GET['clear'])) {
    $_SESSION['patients'] = [];
    header('Location: index.php');
    exit;
}

$patients = $_SESSION['patients'];
$total = count($patients);
$immediate = count(array_filter($patients, fn($p) => $p->getTriageDecision() === 'IMMEDIATE'));
$urgent = count(array_filter($patients, fn($p) => $p->getTriageDecision() === 'URGENT'));
$standard = count(array_filter($patients, fn($p) => $p->getTriageDecision() === 'STANDARD'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MedTriage | Clinic Triage Tracker</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar"><div class="brand"><span class="brand-icon">+</span><div><strong>MedTriage</strong><small>Medical Clinic Triage Tracker</small></div></div><nav><a href="#dashboard">Dashboard</a><a href="#register">Register Patient</a><a href="#queue">Triage Queue</a></nav></header>
<main>
<section class="hero" id="dashboard"><div><p class="eyebrow">OOP 2 • MIDTERM PROJECT</p><h1>Clinic Triage <span>Tracker</span></h1><p>Prioritize patients using PHP Object-Oriented Programming. No database required — records are kept temporarily during the session.</p><a class="btn" href="#register">+ Register Patient</a></div><div class="hero-card"><div class="pulse">♥</div><h3>Live Triage Status</h3><p>Patients are automatically prioritized based on their subclass behavior.</p></div></section>
<section class="stats"><div><b><?= $total ?></b><span>Total Patients</span></div><div><b><?= $immediate ?></b><span>Immediate</span></div><div><b><?= $urgent ?></b><span>Urgent</span></div><div><b><?= $standard ?></b><span>Standard</span></div></section>
<section class="grid" id="register"><div class="panel"><div class="panel-head"><div><p class="eyebrow">PATIENT INPUT</p><h2>Register Patient</h2></div><span class="lock">PHP OOP</span></div>
<?php if ($errors): ?><div class="alert error"><strong>Please fix:</strong><ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if (isset($_GET['success'])): ?><div class="alert success">Patient successfully registered and added to the triage queue.</div><?php endif; ?>
<form method="post">
<label>Patient Type<select name="type" id="type" required onchange="toggleFields()"><option value="">Select patient type</option><option value="emergency">Emergency Patient</option><option value="pediatric">Pediatric Patient</option><option value="senior">Senior Patient</option><option value="general">General Patient</option></select></label>
<div class="two"><label>Full Name<input name="name" required maxlength="80" placeholder="e.g. Juan Dela Cruz"></label><label>Age<input name="age" required type="number" min="0" max="120" placeholder="Age"></label></div>
<label>Symptoms / Main Concern<textarea name="symptoms" required maxlength="300" placeholder="Describe the patient's main concern..."></textarea></label>
<div id="emergencyField" class="conditional"><label>Emergency Type<input name="emergency_type" placeholder="e.g. Chest pain, severe bleeding"></label></div>
<div id="pediatricField" class="conditional"><label>Guardian Name<input name="guardian" placeholder="Parent or guardian"></label></div>
<div id="seniorField" class="conditional"><label>Chronic Condition?<select name="chronic"><option value="no">No</option><option value="yes">Yes</option></select></label></div>
<button class="btn full" type="submit">Assess & Add to Queue</button>
</form></div>
<div class="panel info"><p class="eyebrow">TRIAGE GUIDE</p><h2>Priority Levels</h2><div class="guide"><div class="dot red"></div><div><b>IMMEDIATE</b><p>Emergency patients receive the highest priority for immediate assessment.</p></div></div><div class="guide"><div class="dot yellow"></div><div><b>URGENT</b><p>Young pediatric patients and seniors with chronic conditions are prioritized.</p></div></div><div class="guide"><div class="dot green"></div><div><b>STANDARD</b><p>Patients requiring routine assessment are placed in the standard queue.</p></div></div><hr><h3>OOP Concepts Demonstrated</h3><ul class="oop"><li><b>Inheritance:</b> Emergency, Pediatric, and Senior inherit Patient.</li><li><b>Polymorphism:</b> the same triage methods behave differently per subclass.</li><li><b>Encapsulation:</b> protected and private properties with public getters.</li><li><b>Constructors:</b> each object is initialized with patient data.</li></ul></div></section>
<section class="panel queue" id="queue"><div class="panel-head"><div><p class="eyebrow">PROCESSING OUTPUT</p><h2>Triage Queue</h2></div><a class="clear" href="?clear=1" onclick="return confirm('Clear all temporary patient records?')">Clear Queue</a></div>
<?php if (!$patients): ?><div class="empty">No patients registered yet. Use the form above to create your first patient object.</div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Priority</th><th>Patient</th><th>Type</th><th>Age</th><th>Symptoms</th><th>Object Behavior</th></tr></thead><tbody><?php foreach ($patients as $i => $p): ?><tr><td><span class="badge <?= $p->getPriorityClass() ?>"><?= htmlspecialchars($p->getTriageDecision()) ?></span><small>#<?= $i + 1 ?></small></td><td><b><?= htmlspecialchars($p->getName()) ?></b><small><?= htmlspecialchars($p->getPatientId()) ?></small></td><td><?= htmlspecialchars($p->getCategory()) ?></td><td><?= $p->getAge() ?></td><td><?= htmlspecialchars($p->getSymptoms()) ?></td><td><?= htmlspecialchars($p->getSummary()) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
</main><footer>MedTriage • PHP OOP 2 Midterm Project • No Database</footer>
<script>function toggleFields(){const t=document.getElementById('type').value;document.querySelectorAll('.conditional').forEach(x=>x.style.display='none');const m={emergency:'emergencyField',pediatric:'pediatricField',senior:'seniorField'};if(m[t])document.getElementById(m[t]).style.display='block';}toggleFields();</script>
</body></html>
