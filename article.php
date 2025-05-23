<?php
session_start();
require_once 'database/database.php';

$error = [];

$article_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($article_id === NULL || $article_id === false) {
    $error['article_id'] = "Le parametre id  est invalide.";
}
$sql = "SELECT * FROM articles WHERE id =:article_id";
$query = $pdo->prepare($sql);
$query->execute(compact('article_id'));
$article = $query->fetch();

//echo "<pre>";
//var_dump($article);
//echo "</pre>";


/**
 * 3. Récupération des commentaires de l'article en question et les users
 * Pareil, toujours une requête préparée pour sécuriser la donnée filée par l'utilisateur (cet enfoiré en puissance !)
 */

$sql = "SELECT comments.*, users.username 
 FROM comments
 JOIN users ON comments.user_id = users.id 

 WHERE article_id = :article_id";

$query = $pdo->prepare($sql);

// On exécute la requête en précisant le paramètre :article_id
$query->execute(compact('article_id'));

// On fouille le résultat pour en extraire les données réelles des commentaires
$commentaires = $query->fetchAll();



// / 1--On affiche le titre autre

$pageTitle = 'Accueil des articles';

// 2-Debut du tampon de la page de sortie

ob_start();

// 3-inclure le layout de la page show
require_once 'layouts/articles/show_html.php';

//4-recuperation du contenu du tampon de la page show
$pageContent = ob_get_clean();

//5-Inclure le layout de la page de sortie
require_once 'layouts/layout_html.php';
