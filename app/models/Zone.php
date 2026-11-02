<?php
class Zone
{
    private $conn;
    private $table_name = "zones_livraison";

    public $id;
    public $nom;
    public $type;
    public $parent_id;
    public $frais_livraison;
    public $description;
    public $actif;
    public $date_creation;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Récupérer toutes les zones
    public function getAll()
    {
        $query = " SELECT z.*, p.nom as parent_nom
                  FROM " . $this->table_name . " z
                  LEFT JOIN " . $this->table_name . " p ON z.parent_id = p.id
                  ORDER BY z.type, z.nom";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Récupérer les zones par type
    public function getByType($type)
    {
        $query = "SELECT * FROM zones_livraison
                  WHERE type=? AND actif='1'
                  ORDER BY nom";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $type);
        $stmt->execute();
        return $stmt;
    }

    // Récupérer les zones enfants
    public function getChildren($parent_id)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE parent_id = ? AND actif = '1'
                  ORDER BY nom";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        return $stmt;
    }

    // Créer une zone
    public function create()
    {
        $query = "INSERT INTO zones_livraison (nom, type, parent_id, frais_livraison, description) VALUES (?, ?, ?,
                  ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->parent_id = $this->parent_id ? htmlspecialchars(strip_tags($this->parent_id)) : null;
        $this->frais_livraison = htmlspecialchars(strip_tags($this->frais_livraison));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        // Lier les paramètres
        $stmt->bind_param("sssds", $this->nom, $this->type, $this->parent_id, $this->frais_livraison, $this->description);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Lire une zone
    public function readOne()
    {
        $query = "SELECT z.*, p.nom as parent_nom
                  FROM " . $this->table_name . " z
                  LEFT JOIN " . $this->table_name . " p ON z.parent_id = p.id
                  WHERE z.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $this->nom = $row['nom'];
            $this->type = $row['type'];
            $this->parent_id = $row['parent_id'];
            $this->frais_livraison = $row['frais_livraison'];
            $this->description = $row['description'] ?? '';
            $this->actif = $row['actif'];
            $this->date_creation = $row['date_creation'];
            return true;
        }
        return false;
    }

    // Mettre à jour une zone
    public function update()
    {
        $query = "UPDATE zones_livraison
                  SET nom=?, type=?, parent_id=?,
                      frais_livraison=?
                  WHERE id=?";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->parent_id = $this->parent_id ? htmlspecialchars(strip_tags($this->parent_id)) : null;
        $this->frais_livraison = htmlspecialchars(strip_tags($this->frais_livraison));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Lier les paramètres
        $stmt->bind_param("ssiii", $this->nom, $this->type, $this->parent_id, $this->frais_livraison, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer une zone
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Activer/Désactiver une zone
    public function toggleActive()
    {
        $query = "UPDATE " . $this->table_name . "
                  SET actif = CASE WHEN actif = '1' THEN '0' ELSE '1' END
                  WHERE id =?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>