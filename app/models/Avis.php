<?php

class Avis
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Ajouter ou mettre à jour un avis
     */
    public function ajouterAvis($produit_id, $utilisateur_id, $note, $titre, $commentaire)
    {
        // Vérifier si l'utilisateur a déjà laissé un avis
        $stmt = $this->db->prepare("SELECT id FROM avis_produits WHERE produit_id = ? AND utilisateur_id = ?");
        $stmt->bind_param("ii", $produit_id, $utilisateur_id);
        $stmt->execute();
        $existant = $stmt->get_result()->fetch_assoc();

        if ($existant) {
            // Mettre à jour l'avis existant
            return $this->mettreAJourAvis($existant['id'], $note, $titre, $commentaire);
        }

        // Insérer un nouvel avis
        $stmt = $this->db->prepare(
            "INSERT INTO avis_produits (produit_id, utilisateur_id, note, titre, commentaire, statut) 
             VALUES (?, ?, ?, ?, ?, 'approuve')"
        );

        $stmt->bind_param("iisss", $produit_id, $utilisateur_id, $note, $titre, $commentaire);
        if ($stmt->execute()) {
            $this->mettreAJourStatistiquesAvis($produit_id);
            return true;
        }
        return false;
    }

    /**
     * Mettre à jour un avis
     */
    private function mettreAJourAvis($avis_id, $note, $titre, $commentaire)
    {
        $stmt = $this->db->prepare(
            "UPDATE avis_produits SET note = ?, titre = ?, commentaire = ? WHERE id = ?"
        );

        $stmt->bind_param("issi", $note, $titre, $commentaire, $avis_id);
        return $stmt->execute();
    }

    /**
     * Récupérer tous les avis d'un produit
     */
    public function getAvisProduit($produit_id, $limit = 10, $offset = 0)
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.nom_complet 
             FROM avis_produits a
             JOIN users u ON a.utilisateur_id = u.id
             WHERE a.produit_id = ? AND a.statut = 'approuve'
             ORDER BY a.date_creation DESC
             LIMIT ? OFFSET ?"
        );

        $stmt->bind_param("iii", $produit_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Compter les avis approuvés d'un produit
     */
    public function compterAvisProduit($produit_id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM avis_produits WHERE produit_id = ? AND statut = 'approuve'");
        $stmt->bind_param("i", $produit_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }

    /**
     * Récupérer la note moyenne d'un produit
     */
    public function getNoteMoyenne($produit_id)
    {
        $stmt = $this->db->prepare(
            "SELECT AVG(note) as moyenne FROM avis_produits WHERE produit_id = ? AND statut = 'approuve'"
        );

        $stmt->bind_param("i", $produit_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return round($result['moyenne'] ?? 0, 1);
    }

    /**
     * Récupérer la distribution des notes
     */
    public function getDistributionNotes($produit_id)
    {
        $stmt = $this->db->prepare(
            "SELECT note, COUNT(*) as count FROM avis_produits 
             WHERE produit_id = ? AND statut = 'approuve'
             GROUP BY note
             ORDER BY note DESC"
        );

        $stmt->bind_param("i", $produit_id);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($results as $row) {
            $distribution[$row['note']] = $row['count'];
        }

        return $distribution;
    }

    /**
     * Vérifier si l'utilisateur peut laisser un avis
     */
    public function peutLaisserAvis($produit_id, $utilisateur_id)
    {
        // Vérifier si l'utilisateur a acheté ce produit
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM commande_produits cp
             JOIN commandes c ON cp.commande_id = c.id
             WHERE cp.produit_id = ? AND c.utilisateur_id = ? AND c.statut IN ('validee', 'livree')"
        );

        $stmt->bind_param("ii", $produit_id, $utilisateur_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    /**
     * Récupérer l'avis de l'utilisateur pour un produit
     */
    public function getAvisUtilisateur($produit_id, $utilisateur_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM avis_produits WHERE produit_id = ? AND utilisateur_id = ?"
        );

        $stmt->bind_param("ii", $produit_id, $utilisateur_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Mettre à jour les statistiques d'avis
     */
    private function mettreAJourStatistiquesAvis($produit_id)
    {
        $moyenne = $this->getNoteMoyenne($produit_id);
        $count = $this->compterAvisProduit($produit_id);

        $stmt = $this->db->prepare("UPDATE produits SET note_moyenne = ?, nombre_avis = ? WHERE id = ?");
        $stmt->bind_param("dii", $moyenne, $count, $produit_id);
        $stmt->execute();
    }

    /**
     * Supprimer un avis (admin)
     */
    public function supprimerAvis($avis_id)
    {
        $stmt = $this->db->prepare("SELECT produit_id FROM avis_produits WHERE id = ?");
        $stmt->bind_param("i", $avis_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $produit_id = $result['produit_id'] ?? null;

        $stmt = $this->db->prepare("DELETE FROM avis_produits WHERE id = ?");
        $stmt->bind_param("i", $avis_id);

        if ($stmt->execute() && $produit_id) {
            $this->mettreAJourStatistiquesAvis($produit_id);
            return true;
        }
        return false;
    }

    /**
     * Obtenir tous les avis en attente d'approbation (admin)
     */
    public function getAvisEnAttente()
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.nom_complet, p.nom as produit_nom
             FROM avis_produits a
             JOIN users u ON a.utilisateur_id = u.id
             JOIN produits p ON a.produit_id = p.id
             WHERE a.statut = 'en_attente'
             ORDER BY a.date_creation ASC"
        );

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Approuver un avis (admin)
     */
    public function approuverAvis($avis_id)
    {
        $stmt = $this->db->prepare("SELECT produit_id FROM avis_produits WHERE id = ?");
        $stmt->bind_param("i", $avis_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $produit_id = $result['produit_id'] ?? null;

        $stmt = $this->db->prepare("UPDATE avis_produits SET statut = 'approuve' WHERE id = ?");
        $stmt->bind_param("i", $avis_id);

        if ($stmt->execute() && $produit_id) {
            $this->mettreAJourStatistiquesAvis($produit_id);
            return true;
        }
        return false;
    }
}
