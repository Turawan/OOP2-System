<?php
// Load all Patient classes before starting the session so PHP can correctly
// restore Patient objects saved in the session.
require_once __DIR__ . '/classes/Patient.php';
require_once __DIR__ . '/classes/GeneralPatient.php';
session_start();

if (!isset($_SESSION['patients']) || !is_array($_SESSION['patients'])) {
    $_SESSION['patients'] = [];
}

foreach ($_SESSION['patients'] as $patient) {
    if (!$patient instanceof Patient) {
        $_SESSION['patients'] = [];
        break;
    }
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
            case 'general':
                $patient = new GeneralPatient($name, $age, $symptoms);
                break;
        }

        if (!$errors) {
            $_SESSION['patients'][] = $patient;
            usort($_SESSION['patients'], fn(Patient $a, Patient $b) => $b->getPriorityScore() <=> $a->getPriorityScore());
            header('Location: index.php?success=1');
            exit;
        }
    }
}

// Delete selected patient records by their original queue indexes.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    $selected = $_POST['delete_patient'] ?? [];
    if (!is_array($selected)) $selected = [$selected];

    $selectedIndexes = array_values(array_unique(array_map('intval', $selected)));
    foreach ($selectedIndexes as $index) {
        if (isset($_SESSION['patients'][$index])) {
            unset($_SESSION['patients'][$index]);
        }
    }
    $_SESSION['patients'] = array_values($_SESSION['patients']);

    header('Location: index.php?deleted=' . count($selectedIndexes));
    exit;
}

if (isset($_GET['clear']) && $_GET['clear'] === '1') {
    $_SESSION['patients'] = [];
    header('Location: index.php');
    exit;
}

$patients = $_SESSION['patients'];
$total = count($patients);
$immediate = count(array_filter($patients, fn(Patient $p) => $p->getTriageDecision() === 'IMMEDIATE'));
$urgent = count(array_filter($patients, fn(Patient $p) => $p->getTriageDecision() === 'URGENT'));
$standard = count(array_filter($patients, fn(Patient $p) => $p->getTriageDecision() === 'STANDARD'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedTriage | Healthcare Clinic Triage Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div>
        <p class="eyebrow">OOP 2 • PHP OOP MIDTERM PROJECT</p>
        <h1>MedTriage</h1>
        <p>Healthcare Medical Clinic Triage Tracker</p>
    </div>
    <a class="clear-link" href="?clear=1" onclick="return confirm('Clear ALL patient records? This cannot be undone.');">Clear All Records</a>
</header>

<main class="container">
    <section class="hero card">
        <div>
            <span class="pill">NO DATABASE • SESSION STORAGE</span>
            <h2>Organize patients by triage priority.</h2>
            <p>Create patient objects from different patient types and let polymorphic methods determine category, priority, and triage decision.</p>
        </div>
        <div class="hero-icon">+</div>
    </section>

    <?php if ($errors): ?>
        <div class="alert error"><strong>Please correct the following:</strong><ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?><div class="alert success">Patient added successfully and placed in the triage queue.</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert success"><?= (int)$_GET['deleted'] ?> patient record<?= ((int)$_GET['deleted'] === 1) ? '' : 's' ?> deleted successfully.</div><?php endif; ?>

    <section class="stats-grid">
        <div class="stat card"><span>Total Patients</span><strong><?= $total ?></strong></div>
        <div class="stat card"><span>Immediate</span><strong><?= $immediate ?></strong></div>
        <div class="stat card"><span>Urgent</span><strong><?= $urgent ?></strong></div>
        <div class="stat card"><span>Standard</span><strong><?= $standard ?></strong></div>
    </section>

    <div class="content-grid">
        <section class="card form-card">
            <div class="section-heading"><div><span class="eyebrow">PATIENT INPUT</span><h2>Add Patient</h2></div><span class="step">01</span></div>
            <form method="post" action="">
                <label>Patient Name<input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="e.g. Juan Dela Cruz" required></label>
                <div class="two-col">
                    <label>Age<input type="number" name="age" min="0" max="120" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" required></label>
                    <label>Patient Type<select name="type" id="patientType" required>
                        <option value="" selected disabled>Choose patient type...</option>
                        <option value="emergency" <?= (($_POST['type'] ?? '') === 'emergency') ? 'selected' : '' ?>>Emergency</option>
                        <option value="pediatric" <?= (($_POST['type'] ?? '') === 'pediatric') ? 'selected' : '' ?>>Pediatric</option>
                        <option value="senior" <?= (($_POST['type'] ?? '') === 'senior') ? 'selected' : '' ?>>Senior</option>
                        <option value="general" <?= (($_POST['type'] ?? '') === 'general') ? 'selected' : '' ?>>General</option>
                    </select></label>
                </div>
                <label>Symptoms<textarea name="symptoms" rows="4" placeholder="Describe the patient's symptoms..." required><?= htmlspecialchars($_POST['symptoms'] ?? '') ?></textarea></label>
                <div id="emergencyFields" class="conditional"><label>Emergency Type<input type="text" name="emergency_type" value="<?= htmlspecialchars($_POST['emergency_type'] ?? '') ?>" placeholder="e.g. Severe bleeding"></label></div>
                <div id="pediatricFields" class="conditional"><label>Guardian Name<input type="text" name="guardian" value="<?= htmlspecialchars($_POST['guardian'] ?? '') ?>" placeholder="Parent or guardian"></label></div>
                <div id="seniorFields" class="conditional"><label>Has Chronic Condition?<select name="chronic"><option value="no">No</option><option value="yes" <?= (($_POST['chronic'] ?? '') === 'yes') ? 'selected' : '' ?>>Yes</option></select></label></div>
                <button type="submit">Create Patient Object →</button>
            </form>
        </section>

        <aside class="card guide-card">
            <span class="eyebrow">TRIAGE GUIDE</span><h2>Priority Rules</h2>
            <div class="rule"><span class="badge danger">IMMEDIATE</span><p>Emergency patients are assigned the highest priority.</p></div>
            <div class="rule"><span class="badge warning">URGENT</span><p>Children age 5 or below and seniors with chronic conditions are prioritized.</p></div>
            <div class="rule"><span class="badge normal">STANDARD</span><p>General patients and non-urgent cases receive routine triage.</p></div>
            <div class="oop-box"><strong>OOP Demonstration</strong><p>Every queue item is stored as a <code>Patient</code> reference, while overridden methods behave according to the actual child object.</p></div>
        </aside>
    </div>

    <section class="card queue-card">
        <div class="section-heading">
            <div><span class="eyebrow">LIVE SESSION QUEUE</span><h2>Triage Results</h2></div>
            <span class="count"><?= $total ?> record<?= $total === 1 ? '' : 's' ?></span>
        </div>
        <?php if (!$patients): ?>
            <div class="empty"><strong>No patients yet.</strong><p>Use the form above to create your first patient object.</p></div>
        <?php else: ?>
            <form method="post" action="" id="deleteForm">
                <div class="queue-actions">
                    <button type="submit" name="delete_selected" value="1" class="delete-selected" onclick="return confirmDeleteSelected();">Delete Selected</button>
                    <span class="selection-hint">Select one or more patient records below.</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Select</th><th>Priority</th><th>Patient</th><th>Type</th><th>Age</th><th>Symptoms</th><th>Polymorphic Summary</th></tr></thead>
                        <tbody>
                        <?php foreach ($patients as $index => $patient): ?>
                            <tr>
                                <td><input type="checkbox" name="delete_patient[]" value="<?= $index ?>" class="patient-checkbox" aria-label="Select <?= htmlspecialchars($patient->getName()) ?>"></td>
                                <td><span class="badge <?= $patient->getPriorityClass() ?>"><?= htmlspecialchars($patient->getTriageDecision()) ?></span></td>
                                <td><strong><?= htmlspecialchars($patient->getName()) ?></strong><small><?= htmlspecialchars($patient->getPatientId()) ?></small></td>
                                <td><?= htmlspecialchars($patient->getCategory()) ?></td>
                                <td><?= $patient->getAge() ?></td>
                                <td><?= htmlspecialchars($patient->getSymptoms()) ?></td>
                                <td><?= htmlspecialchars($patient->getSummary()) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </section>
</main>

<footer>Academic OOP 2 project • Temporary session data only • Not a clinical decision-making tool</footer>
<script>
const typeSelect = document.getElementById('patientType');
const groups = { emergency: document.getElementById('emergencyFields'), pediatric: document.getElementById('pediatricFields'), senior: document.getElementById('seniorFields') };
function updateFields() { Object.values(groups).forEach(group => group.classList.remove('show')); if (groups[typeSelect.value]) groups[typeSelect.value].classList.add('show'); }
typeSelect.addEventListener('change', updateFields); updateFields();
function confirmDeleteSelected() {
    const selected = document.querySelectorAll('.patient-checkbox:checked').length;
    if (selected === 0) { alert('Please select at least one patient record to delete.'); return false; }
    return confirm(`Delete ${selected} selected patient record${selected === 1 ? '' : 's'}?`);
}
</script>
</body>
</html>