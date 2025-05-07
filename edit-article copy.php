<?php

declare(strict_types=1);
// Active le typage strict pour ce fichier. Avec strict_types à 1, PHP générera une TypeError
// si vous tentez d’utiliser un type incompatible dans les signatures de fonctions/méthodes :contentReference[oaicite:0]{index=0}

session_start([
  // Démarre ou reprend la session en renforçant la sécurité des cookies :
  'cookie_secure'   => true,   // n'envoie le cookie que via HTTPS :contentReference[oaicite:1]{index=1}
  'cookie_httponly' => true,   // empêche l'accès au cookie depuis JavaScript :contentReference[oaicite:2]{index=2}
  'use_strict_mode' => true,   // rejette les ID de session non valides pour prévenir le fixation d'ID :contentReference[oaicite:3]{index=3}
  'cookie_samesite' => 'Lax',  // réduit le risque de CSRF :contentReference[oaicite:4]{index=4}
]);

require_once __DIR__ . '/database/database.php';
// Inclut le fichier de connexion PDO relatif au dossier actuel (__DIR__)
// require_once évite les inclusions multiples et lève une erreur fatale si le fichier manque :contentReference[oaicite:5]{index=5}

$errors = [];
// Initialise la variable d’erreur pour accumuler les messages utilisateur

// Récupère et valide l’ID depuis la query string
$id = filter_input(
  INPUT_GET,
  'id',
  FILTER_VALIDATE_INT,
  ['options' => ['min_range' => 1]]
);
// filter_input validera l'ID comme entier ≥1 ou renverra false/NULL si invalide :contentReference[oaicite:6]{index=6}

if ($id === false || $id === null) {
  // ID non valide : redirection sécurisée vers l’admin
  header('Location: admin.php');
  exit;
}

function cleanInput(string $data): string
{
  // Supprime espaces superflus et encode les caractères spéciaux HTML
  // pour prévenir les attaques XSS lors de l'affichage :contentReference[oaicite:7]{index=7}
  return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

try {
  // Prépare et exécute la requête de lecture de l'article
  $stmt = $pdo->prepare(
    'SELECT title, slug, introduction, content 
         FROM articles 
         WHERE id = :id'
  );
  $stmt->execute(['id' => $id]);
  $article = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($article === false) {
    // Lance une exception métier si l’article n’existe pas :contentReference[oaicite:8]{index=8}
    throw new RuntimeException('Article non trouvé.');
  }

  // Initialisation des variables avec les données récupérées
  $title        = $article['title'];
  $slug         = $article['slug'];
  $introduction = $article['introduction'];
  $content      = $article['content'];
} catch (\Throwable $e) {
  // En cas d'erreur, on stocke un message flash et on redirige
  $_SESSION['error'] = 'Impossible de récupérer l\'article.';
  header('Location: admin.php');
  exit;
}

if (isset($_POST['update'])) {
  echo 'ok';
  die;
  // Vérifie que le formulaire a bien été soumis en POST :contentReference[oaicite:9]{index=9}
  $title        = cleanInput((string) filter_input(INPUT_POST, 'title', FILTER_DEFAULT));
  $slug         = strtolower(str_replace(' ', '-', $title));
  $introduction = cleanInput((string) filter_input(INPUT_POST, 'introduction', FILTER_DEFAULT));
  $content      = cleanInput((string) filter_input(INPUT_POST, 'content', FILTER_DEFAULT));
  $articleId    = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

  // Validation des données soumises
  if ($articleId !== $id || empty($title) || empty($introduction) || empty($content)) {
    $_SESSION['error'] = 'Veuillez remplir tous les champs correctement.';
  } else {
    try {
      // Prépare et exécute la mise à jour
      $stmt = $pdo->prepare(
        'UPDATE articles 
                 SET title = :title,
                     slug  = :slug,
                     introduction = :introduction,
                     content = :content,
                     updated_at = NOW()
                 WHERE id = :id'
      );
      $stmt->execute([
        'title'        => $title,
        'slug'         => $slug,
        'introduction' => $introduction,
        'content'      => $content,
        'id'           => $articleId,
      ]);
      // Message de succès et redirection
      $_SESSION['alert-success'] = 'Article mis à jour avec succès.';
      header('Location: admin.php');
      exit;
    } catch (\PDOException $e) {
      // Gestion des erreurs PDO par exception :contentReference[oaicite:10]{index=10}
      $_SESSION['error'] = 'Erreur lors de la mise à jour.';
    }
  }
}

$pageTitle = 'Éditer un article'; // Titre de la page pour le layout
ob_start();
// Mise en tampon du HTML de la vue
require __DIR__ . '/layouts/articles/edit-article_html.php';
$pageContent = (string) ob_get_clean();
// Inclusion du layout global
require __DIR__ . '/layouts/layout_html.php';
