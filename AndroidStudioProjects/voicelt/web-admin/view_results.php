<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once 'firebase_helper.php';

$votes = getFromFirebase('votes');
$nominees = getFromFirebase('nominees');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results - Campus Voting System</title>
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
        <h1 class="text-center mb-4">Live Voting Results</h1>

        <?php if ($nominees): ?>
            <?php foreach ($nominees as $position => $positionNominees): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($position); ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>SAP ID</th>
                                        <th>Department</th>
                                        <th>Votes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($positionNominees as $nomineeId => $nominee): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($nominee['name']); ?></td>
                                            <td><?php echo htmlspecialchars($nominee['sap_id']); ?></td>
                                            <td><?php echo htmlspecialchars($nominee['department']); ?></td>
                                            <td><?php echo isset($votes[$position][$nomineeId]['count']) ? $votes[$position][$nomineeId]['count'] : 0; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">No nominees found.</div>
        <?php endif; ?>
    </div>
</body>
</html>
