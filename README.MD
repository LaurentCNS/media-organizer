# Organisateur de Médias

## Description
L'Organisateur de Médias est un script PHP conçu pour organiser automatiquement les fichiers multimédias (photos et vidéos) par année de création. Il parcourt un répertoire source contenant des fichiers multimédias et les déplace dans des sous-répertoires correspondant à l'année de création de chaque fichier multimédia. De plus, il gère les fichiers multimédias sans date de création en les déplaçant vers un répertoire séparé.

## Fonctionnalités
- Organisation automatique des fichiers multimédias par année de création.
- Gestion des fichiers multimédias sans date de création.
- Évitement des conflits de noms de fichiers.
- Support des formats de fichiers courants ('png', 'jpg', 'jpeg', 'tiff', 'bmp', 'gif', 'mp4', 'avi', 'mov', 'mkv').

## Utilisation
1. Clonez ou téléchargez ce dépôt sur votre système.
2. Placez vos fichiers multimédias dans le répertoire `medias_input`.
3. Exécutez la commande suivante dans le SHELL `php organize.php`.
4. Les fichiers multimédias organisés seront déplacés dans le répertoire `medias_output`.

## Configuration
- Vous pouvez modifier les répertoires source et destination dans le script si nécessaire.
- Assurez-vous que votre environnement d'exécution PHP dispose des dépendances requises (par exemple, l'extension PHP Exif pour récupérer les dates de création des fichiers multimédias).

## Exigences
- PHP 7.x (pour la version PHP).
- Extension PHP Exif (pour récupérer les dates de création des fichiers multimédias).

## Auteur
- Laurent Cantos
