<?php
session_start();
require_once "database/database.php";

// Vérification si l'utilisateur est authentifié
if (!isset($_SESSION['auth']['id'])) {
  header("Location: login.php");
  exit;
}

$user_auth = $_SESSION['auth']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = $_POST['content'] ?? null;
  $article_id = $_POST['article_id'] ?? null;

  // Vérification des informations du formulaire
  if (!$user_auth || !$article_id || !$content) {
    die("Votre formulaire a été mal rempli !");
  }

  // Échapper le contenu pour éviter les XSS
  $content = htmlspecialchars($content);

  // Vérification de l'existence de l'article
  $query = $pdo->prepare('SELECT COUNT(*) FROM articles WHERE id = :article_id');
  $query->execute(['article_id' => $article_id]);
  $articleExists = $query->fetchColumn();

  if (!$articleExists) {
    die("Ho ! L'article $article_id n'existe pas !");
  }

  // Insertion du commentaire
  $query = $pdo->prepare('INSERT INTO comments (content, article_id, user_id, created_at) VALUES (:content, :article_id, :user_auth, NOW())');
  $query->execute(compact('content', 'article_id', 'user_auth'));

  // Rediriger vers la page de l'article après l'ajout du commentaire
  header("Location: article.php?id=" . $article_id);
  exit;
}

// Code de gestion de session pour la sécurité
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
  session_unset();
  session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();
