<?php

/**
 * Classe helper pour gérer les slugs et le SEO
 */
class SlugHelper
{
    /**
     * Génère un slug à partir d'une chaîne
     * 
     * @param string $text Le texte à convertir en slug
     * @param int $id L'ID de l'élément (pour garantir l'unicité)
     * @return string Le slug généré
     */
    public static function generateSlug($text, $id = null)
    {
        // Convertir en minuscules et remplacer les caractères spéciaux
        $slug = mb_strtolower($text, 'UTF-8');
        
        // Remplacer les caractères accentués
        $accents = array(
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ì' => 'i',
            'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'ò' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
            'ç' => 'c', 'ñ' => 'n',
            'œ' => 'oe', 'æ' => 'ae'
        );
        
        foreach ($accents as $from => $to) {
            $slug = str_replace($from, $to, $slug);
        }
        
        // Remplacer les espaces et caractères non-alphanumériques par des tirets
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Supprimer les tirets au début et à la fin
        $slug = trim($slug, '-');
        
        // Ajouter l'ID à la fin pour garantir l'unicité
        if ($id !== null) {
            $slug = $slug . '-' . (int)$id;
        }
        
        return $slug;
    }

    /**
     * Extrait l'ID d'un slug
     * Le slug est supposé être au format: "mon-titre-123"
     * 
     * @param string $slug Le slug
     * @return int|null L'ID ou null si non trouvé
     */
    public static function extractIdFromSlug($slug)
    {
        // Le dernier segment séparé par un tiret devrait être l'ID
        $parts = explode('-', $slug);
        $lastPart = end($parts);
        
        return is_numeric($lastPart) ? (int)$lastPart : null;
    }

    /**
     * Valide si un slug est valide
     * 
     * @param string $slug Le slug à valider
     * @return bool
     */
    public static function isValidSlug($slug)
    {
        return !empty($slug) && preg_match('/^[a-z0-9\-]+$/', $slug);
    }

    /**
     * Génère les meta tags SEO
     * 
     * @param string $title Le titre de la page
     * @param string $description La description
     * @param string $url L'URL canonique
     * @param string $image L'image pour les réseaux sociaux (optionnel)
     * @return string Les meta tags HTML
     */
    public static function generateMetaTags($title, $description, $url, $image = null)
    {
        $html = '';
        $html .= '<title>' . htmlspecialchars($title) . '</title>' . "\n";
        $html .= '<meta name="description" content="' . htmlspecialchars($description) . '">' . "\n";
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
        $html .= '<link rel="canonical" href="' . htmlspecialchars($url) . '">' . "\n";
        
        // Open Graph pour les réseaux sociaux
        $html .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">' . "\n";
        $html .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">' . "\n";
        $html .= '<meta property="og:url" content="' . htmlspecialchars($url) . '">' . "\n";
        $html .= '<meta property="og:type" content="website">' . "\n";
        
        if ($image) {
            $html .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . "\n";
        }
        
        // Twitter Card
        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $html .= '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">' . "\n";
        $html .= '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">' . "\n";
        
        if ($image) {
            $html .= '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">' . "\n";
        }
        
        return $html;
    }
}
