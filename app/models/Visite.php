<?php

class Visite
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
        $se=session_id() ;

        $stmt->bind_param("ssss",  $se,
            $_SERVER['REQUEST_URI'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']) ;

        $stmt->execute();
    }
    public function getTodayCount()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total
            FROM visites
            WHERE DATE(date_visite) = CURDATE()
        ");

        return $stmt->fetch_assoc()['total'];
    }

    public function getLast30DaysCount()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total
            FROM visites
            WHERE date_visite >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        return $stmt->fetch_assoc()['total'];
    }
}
?>