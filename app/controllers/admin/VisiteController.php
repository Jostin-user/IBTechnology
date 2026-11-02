<?php
require_once __DIR__ . '/../../models/Visite';
class VisiteController
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }
    public function enregistrer()
    {
        $stmt = $this->db->prepare("INSERT INTO visites(session_id, page, ip_address, user_agent) 
        VALUES(?,?,?,?)
        ");

        $stmt->bind_param(
            "ssss",
            session_id(),
            $_SERVER['REQUEST_URI'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        );

        $stmt->execute();
    }
    public function getTodayCount()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total
            FROM visites
            WHERE DATE(date_visite) = CURDATE()
        ");

        return $stmt->fetch()['total'];
    }

    public function getLast30DaysCount()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total
            FROM visites
            WHERE date_visite >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        return $stmt->fetch()['total'];
    }
}
?>