<?php
class Position {
    private $id;
    private $latitude;
    private $longitude;
    private $date;
    private $imei;

    function __construct($id, $latitude, $longitude, $date, $imei) {
        $this->id = $id;              // souvent null (AUTO_INCREMENT)
        $this->latitude = $latitude;  // double
        $this->longitude = $longitude;// double
        $this->date = $date;          // "YYYY-MM-DD HH:mm:ss"
        $this->imei = $imei;          // identifiant appareil
    }

    function getId() { return $this->id; }
    function getLatitude() { return $this->latitude; }
    function getLongitude() { return $this->longitude; }
    function getDate() { return $this->date; }
    function getImei() { return $this->imei; }

    function setId($id) { $this->id = $id; }
    function setLatitude($latitude) { $this->latitude = $latitude; }
    function setLongitude($longitude) { $this->longitude = $longitude; }
    function setDate($date) { $this->date = $date; }
    function setImei($imei) { $this->imei = $imei; }
}