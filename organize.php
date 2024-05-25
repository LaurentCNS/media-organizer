<?php

// Chemins des dossiers nécéssaires
$sourceFolder = __DIR__ . DIRECTORY_SEPARATOR . 'medias_input';
$destinationFolder = __DIR__ . DIRECTORY_SEPARATOR . 'medias_output';

// Affichage des chemins pour debug
echo "Source Folder: $sourceFolder\n";
echo "Destination Folder: $destinationFolder\n";

// Méthode principaple
organizeFilesByYear($sourceFolder, $destinationFolder);


function organizeFilesByYear($sourceFolder, $destinationFolder) {
    // Vérification de la source d'entrée
    if (!is_dir($sourceFolder)) {
        die("Source folder does not exist: $sourceFolder");
    }

    // Vérification et création si nécéssaires de la source de sortie
    if (!is_dir($destinationFolder)) {
        if (!mkdir($destinationFolder, 0777, true)) {
            die("Failed to create destination folder: $destinationFolder");
        }
    }

    // Parcours récursif des dossiers dans medias_input
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceFolder));
    // Tableau pour stocker le nombre de photos pour chaque date et heure
    $dateTimeCounts = [];
    foreach ($files as $file) {
        // Vérification si c'est  un fichier
        if ($file->isFile()) {
            // Obtenir le chemin absolu
            $filePath = $file->getRealPath();
            // Récupérer la date de création du fichier
            $creationDate = getCreationDate($filePath);
            $newFileName = '';
            // Si on a la date/heure
            if ($creationDate) {
                $timestamp = $creationDate->format('dmY_His');
                // Vérifier s'il existe déjà des photos avec la même date et heure
                if (isset($dateTimeCounts[$timestamp])) {
                    $count = ++$dateTimeCounts[$timestamp]; // Incrémenter le compteur
                } else {
                    $count = 1; // Initialiser le compteur à 1 s'il n'y a pas de photos avec la même date et heure
                    $dateTimeCounts[$timestamp] = 1; // Stocker le compteur dans le tableau
                }
                $year = $creationDate->format('Y');
                $yearFolder = $destinationFolder . DIRECTORY_SEPARATOR . $year;
                // Création du dossier année si n'éxiste pas
                if (!is_dir($yearFolder)) {
                    mkdir($yearFolder, 0777, true);
                }
                // Création du nouveau fichier dans le bon repertoire avec un nom composé de plusieurs éléménts
                $newFileName = $yearFolder . DIRECTORY_SEPARATOR . 'picture_' . $timestamp . '_' . $count . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
            } else {
                // Gérer les fichiers sans date de création
                $noDateFolder = $destinationFolder . DIRECTORY_SEPARATOR . 'no_date';
                if (!is_dir($noDateFolder)) {
                    mkdir($noDateFolder, 0777, true);
                }
                $timestamp = date('dmY_His');
                // Création du nouveau fichier dans la date actuelle
                $newFileName = $noDateFolder . DIRECTORY_SEPARATOR . 'picture_' . $timestamp . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
            }

            // Assurez-vous que le nouveau nom de fichier est unique
            $counter = 1;
            $originalFileName = $newFileName;
            while (file_exists($newFileName)) {
                $newFileName = pathinfo($originalFileName, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($originalFileName, PATHINFO_FILENAME) . '_' . $counter . '.' . pathinfo($originalFileName, PATHINFO_EXTENSION);
                $counter++;
            }

            rename($filePath, $newFileName);
            echo "Moved $filePath to $newFileName\n";
        }
    }
}

function getCreationDate($filePath) {
    // Extraire l'extension du fichier
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    // Renvoyer la bonne méthode en fonction du type (photos ou video)
    if (in_array($extension, ['png', 'jpg', 'jpeg', 'tiff', 'bmp', 'gif'])) {
        return getImageCreationDate($filePath);
    } elseif (in_array($extension, ['mp4', 'avi', 'mov', 'mkv'])) {
        return getVideoCreationDate($filePath);
    }
    return null;
}

function getImageCreationDate($filePath) {
    // Vérification si la méthode exif_read_data est disponible dans l'environnement PHP
    if (function_exists('exif_read_data')) {
        // Obtenir la date de la photo
        $exifData = @exif_read_data($filePath);
        // Si les infos sont disponibles on retourne la date
        if ($exifData !== false && isset($exifData['DateTimeOriginal'])) {
            return DateTime::createFromFormat('Y:m:d H:i:s', $exifData['DateTimeOriginal']);
        }
    }
    return null;
}

function getVideoCreationDate($filePath) {
    $output = [];
    $returnVar = null;
    // Extraire les infos dans fichier multimédia
    exec("ffprobe -v error -show_entries format=creation_time -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filePath), $output, $returnVar);
    // Si les infos sont disponibles on retourne la date
    if ($returnVar == 0 && !empty($output)) {
        return DateTime::createFromFormat('Y-m-d\TH:i:s\Z', trim($output[0]));
    }
    return null;
}
?>
