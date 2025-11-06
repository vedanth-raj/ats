<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';
require_once 'firebase_helper.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $sap_id = $_POST['sap_id'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $manifesto = $_POST['manifesto'];

    // Store in MySQL
    $sql = "INSERT INTO nominees (name, sap_id, department, position, manifesto) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $sap_id, $department, $position, $manifesto);

    if ($stmt->execute()) {
        // Push to Firebase
        $nomineeData = [
            'name' => $name,
            'sap_id' => $sap_id,
            'department' => $department,
            'manifesto' => $manifesto
        ];

        $firebaseResponse = pushToFirebase($position, $nomineeData);
        if ($firebaseResponse !== false) {
            $message = "Nominee added successfully!";
        } else {
            $message = "Nominee added to database, but failed to push to Firebase.";
        }
    } else {
        $message = "Error adding nominee: " . $conn->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Nominee - Campus Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Campus Voting System</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Add Nominee</h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="sap_id" class="form-label">SAP ID</label>
                                <input type="text" class="form-control" id="sap_id" name="sap_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" class="form-control" id="department" name="department" required>
                            </div>
                            <div class="mb-3">
                                <label for="position" class="form-label">Position</label>
                                <select class="form-control" id="position" name="position" required>
                                    <option value="">Select Position</option>
                                    <option value="President">President</option>
                                    <option value="Vice President">Vice President</option>
                                    <option value="Cultural Secretary">Cultural Secretary</option>
                                    <option value="Sports Secretary">Sports Secretary</option>
                                    <option value="Technical Secretary">Technical Secretary</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="manifesto" class="form-label">Manifesto</label>
                                <textarea class="form-control" id="manifesto" name="manifesto" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Nominee</button>
                            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
