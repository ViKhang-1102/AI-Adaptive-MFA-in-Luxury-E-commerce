<!DOCTYPE html>
<html>
<head>
    <title>Check Customers</title>
</head>
<body>
    <h1>Customers in Database</h1>
    <?php
    // Connect to DB
    $pdo = new PDO('mysql:host=localhost;dbname=ecommerce2026', 'root', '');
    $stmt = $pdo->query('SELECT id, name, email, role FROM users WHERE role = "customer" ORDER BY id');
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($customers)) {
        echo '<p>No customers found!</p>';
    } else {
        echo '<table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Edit Link</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($customers as $c) {
            echo '<tr>
                <td>' . htmlspecialchars($c['id']) . '</td>
                <td>' . htmlspecialchars($c['name']) . '</td>
                <td>' . htmlspecialchars($c['email']) . '</td>
                <td>' . htmlspecialchars($c['role']) . '</td>
                <td><a href="/admin/customers/' . htmlspecialchars($c['id']) . '/edit">Edit</a></td>
            </tr>';
        }
        
        echo '</tbody></table>';
    }
    ?>
</body>
</html>
