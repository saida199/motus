<?php
$pdo = new PDO("mysql:host=localhost;dbname=motus", "root", "");
$results = $pdo->query("SELECT * FROM word ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scores</title>
</head>
<body>

<h1>🏆 Scores</h1>

<?php foreach ($results as $row): ?>
    <p><?php echo $row['word']; ?></p>
<?php endforeach; ?>

</body>
</html>
