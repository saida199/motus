<?php
session_start();

class Game {
    private $word;
    private $attempts;
    private $maxAttempts = 6;

    public function __construct($word) {
        $this->word = $word;
        $this->attempts = $_SESSION['attempts'] ?? 0;
        $_SESSION['history'] = $_SESSION['history'] ?? [];
    }

    public function checkWord($input) {
        $result = [];

        for ($i = 0; $i < strlen($this->word); $i++) {
            if ($input[$i] === $this->word[$i]) {
                $result[$i] = 'correct';
            } elseif (strpos($this->word, $input[$i]) !== false) {
                $result[$i] = 'present';
            } else {
                $result[$i] = 'absent';
            }
        }

        $_SESSION['history'][] = [
            'word' => $input,
            'result' => $result
        ];

        $this->attempts++;
        $_SESSION['attempts'] = $this->attempts;

        return $result;
    }

    public function isWin($input) {
        return $input === $this->word;
    }

    public function isGameOver() {
        return $this->attempts >= $this->maxAttempts;
    }

    public function getWord() {
        return $this->word;
    }
}

// Charger JSON
$words = json_decode(file_get_contents(__DIR__ . '/../words.json'), true);

// Initialisation
if (!isset($_SESSION['word'])) {
    $_SESSION['word'] = $words[array_rand($words)];
    $_SESSION['attempts'] = 0;
    $_SESSION['history'] = [];
}

$game = new Game($_SESSION['word']);
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = strtolower($_POST['word']);

    if (strlen($input) !== strlen($_SESSION['word'])) {
        $message = "Mot invalide";
    } else {
        $game->checkWord($input);

        if ($game->isWin($input)) {
            $message = "🎉 Victoire !";
            saveScore($input);
            session_destroy();
        } elseif ($game->isGameOver()) {
            $message = "❌ Perdu ! Mot: " . $game->getWord();
            session_destroy();
        }
    }
}

// Sauvegarde DB
function saveScore($word) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=motus", "root", "");
        $stmt = $pdo->prepare("INSERT INTO word (word) VALUES (?)");
        $stmt->execute([$word]);
    } catch (Exception $e) {}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Motus</title>
    <style>
        body { font-family: Arial; text-align: center; background:#121213; color:white; }
        .box {
            display:inline-block;
            width:50px;
            height:50px;
            line-height:50px;
            margin:3px;
            font-weight:bold;
            border-radius:5px;
        }
        .correct { background:#6aaa64; }
        .present { background:#c9b458; }
        .absent { background:#3a3a3c; }
    </style>
</head>
<body>

<h1>🎮 Motus</h1>
<p>Première lettre : <b><?php echo $_SESSION['word'][0]; ?></b></p>

<form method="POST">
    <input type="text" name="word" required>
    <button>Tester</button>
</form>

<div>
<?php
foreach ($_SESSION['history'] as $try) {
    foreach ($try['result'] as $i => $status) {
        echo "<div class='box $status'>" . strtoupper($try['word'][$i]) . "</div>";
    }
    echo "<br>";
}
?>
</div>

<p><?php echo $message; ?></p>

<a href="score.php" style="color:white;">Voir les scores</a>

</body>
</html>
