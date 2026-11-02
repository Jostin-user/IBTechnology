<?php

require_once __DIR__ . '/../models/Zone.php';

class LivraisonController
{
    private $zoneModel;

    public function __construct($db = null)
    {
        if ($db === null) {
            require_once __DIR__ . '/../config/database.php';
            $db = (new Database())->getConnexion();
        }
        $this->zoneModel = new Zone($db);
    }

    private function sendJson($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function regions()
    {
        $stmt = $this->zoneModel->getByType('region');
        $this->sendJson($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    }

    public function villes()
    {
        $regionId = (int) ($_GET['region_id'] ?? 0);
        $stmt = $regionId > 0
            ? $this->zoneModel->getChildren($regionId)
            : $this->zoneModel->getByType('ville');
        $this->sendJson($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    }

    public function quartiers()
    {
        $villeId = (int) ($_GET['ville_id'] ?? 0);
        if ($villeId <= 0) {
            $this->sendJson([]);
        }
        $stmt = $this->zoneModel->getChildren($villeId);
        $this->sendJson($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    }
}
