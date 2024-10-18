<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not set in session.");
}

if (!isset($_GET['tournament_id'])) {
    die("Error: Tournament ID not set in query parameter.");
}

$tournament_id = $_GET['tournament_id'];

// Fetch tournament data
$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, b.solo_players, b.duo_teams, b.duo_players_per_team, 
            b.squad_teams, b.squad_players_per_team, b.rounds, b.placement, b.rules, b.prizes,
            s.provider, s.channel_name, s.social_media, s.social_media_input
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN streams s ON t.id = s.tournament_id
        WHERE t.id = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tournament = $result->fetch_assoc();
    } else {
        die("Error: Tournament not found.");
    }
    $stmt->close();
} else {
    die("Error preparing the tournament statement: " . $conn->error);
}

$conn->close();

// Display tournament details
?>

<div class="tournament-details">
    <h2><?php echo htmlspecialchars($tournament['tname']); ?></h2>
    <p>Selected Game: <?php echo htmlspecialchars($tournament['selected_game']); ?></p>
    <p>Tournament Date: <?php echo htmlspecialchars($tournament['sdate']); ?></p>
    <p >Tournament Time: <?php echo htmlspecialchars($tournament['stime']); ?></p>
    <p>About: <?php echo htmlspecialchars($tournament['about']); ?></p>
    <p>Bracket Type: <?php echo htmlspecialchars($tournament['bracket_type']); ?></p>
    <p>Match Type: <?php echo htmlspecialchars($tournament['match_type']); ?></p>
    <p>Prizes: <?php echo htmlspecialchars($tournament['prizes']); ?></p>
    <p>Stream Provider: <?php echo htmlspecialchars($tournament['provider']); ?></p>
    <p>Stream Channel Name: <?php echo htmlspecialchars($tournament['channel_name']); ?></p>
    <p>Stream Social Media: <?php echo htmlspecialchars($tournament['social_media']); ?></p>
    <p>Stream Social Media Input: <?php echo htmlspecialchars($tournament['social_media_input']); ?></p>
</div>